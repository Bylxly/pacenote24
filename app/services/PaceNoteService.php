<?php

class PaceNoteService {

    const EARTH_RADIUS_M = 6371000.0;
    const CLASSIFICATION_NEIGHBOR_SPACING = 7;
    const CLASSIFICATION_SAMPLE_DISTANCE_M = 3.0;
    const INITIAL_SUBDIVISION_MAX_M = 30.0;
    const ASCENDING_COLLAPSE_MAX_GAP = 20;

    const SEVERITY_SPEED_LIMITS_KMH = [
        1 => 30.0,
        2 => 40.0,
        3 => 50.0,
        4 => 70.0,
        5 => 90.0,
        6 => 130.0,
    ];

    /**
     * Parses GeoJSON and extracts the coordinates into a usable array of associative points.
     * GeoJSON strictly uses [longitude, latitude], but most mapping/nav systems prefer associative ['lat', 'lng'].
     */
    public function extractRoutePoints(string $geoJsonString): array {
        $data = json_decode($geoJsonString, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException("Invalid JSON provided.");
        }

        $coordinates = $this->findCoordinates($data);

        if (empty($coordinates)) {
            throw new RuntimeException("No valid LineString coordinates found in the GeoJSON.");
        }

        return array_values(array_filter(array_map(function ($coord) {
            return $this->normalizeCoordinatePair($coord);
        }, $coordinates)));
    }

    /**
     * Recursively searches the GeoJSON array to find the LineString coordinates.
     * This handles raw Geometry, Features, FeatureCollections and GeometryCollections.
     */
    private function findCoordinates(array $data): array {
        if (isset($data['type'])) {
            if ($data['type'] === 'LineString' && isset($data['coordinates'])) {
                return $data['coordinates'];
            }
            if ($data['type'] === 'Feature' && isset($data['geometry'])) {
                return $this->findCoordinates($data['geometry']);
            }
            if ($data['type'] === 'FeatureCollection' && isset($data['features']) && is_array($data['features'])) {
                foreach ($data['features'] as $feature) {
                    if (is_array($feature)) {
                        $coordinates = $this->findCoordinates($feature);
                        if (!empty($coordinates)) {
                            return $coordinates;
                        }
                    }
                }
            }
            if ($data['type'] === 'GeometryCollection' && isset($data['geometries']) && is_array($data['geometries'])) {
                foreach ($data['geometries'] as $geometry) {
                    if (is_array($geometry)) {
                        $coordinates = $this->findCoordinates($geometry);
                        if (!empty($coordinates)) {
                            return $coordinates;
                        }
                    }
                }
            }
        }
        return [];
    }

    /**
     * Converts a GeoJSON coordinate pair [lng, lat] into our internal associative representation.
     *
     * @return array|null
     */
    private function normalizeCoordinatePair($coord) {
        if (!is_array($coord) || count($coord) < 2) {
            return null;
        }

        if (!is_numeric($coord[0]) || !is_numeric($coord[1])) {
            return null;
        }

        return [
            'lat' => (float)$coord[1],
            'lng' => (float)$coord[0],
        ];
    }

    /**
     * Generates evenly spaced points along the route for GPS tracking/snapping.
     * This performs linear interpolation between route vertices.
     */
    public function interpolateRoute(array $points, float $intervalMeters): array {
        if ($intervalMeters <= 0) {
            throw new InvalidArgumentException('Interval must be greater than zero.');
        }

        if (count($points) < 2) {
            return $points;
        }

        $interpolated = [$points[0]];
        $remainingDistance = $intervalMeters;

        for ($i = 0; $i < count($points) - 1; $i++) {
            $p1 = $points[$i];
            $p2 = $points[$i + 1];

            $segmentLength = $this->calculateDistanceMeters($p1['lat'], $p1['lng'], $p2['lat'], $p2['lng']);
            if ($segmentLength <= 0.0) {
                continue;
            }

            $segmentTraveled = 0;

            while ($segmentLength - $segmentTraveled >= $remainingDistance) {
                $segmentTraveled += $remainingDistance;
                $fraction = $segmentTraveled / $segmentLength;

                // Linear interpolation is highly accurate for small road segments
                $interpolated[] = [
                    'lat' => $p1['lat'] + ($p2['lat'] - $p1['lat']) * $fraction,
                    'lng' => $p1['lng'] + ($p2['lng'] - $p1['lng']) * $fraction,
                ];

                $remainingDistance = $intervalMeters;
            }

            // Carry over the leftover distance to the next segment
            $remainingDistance -= ($segmentLength - $segmentTraveled);
        }

        // Always ensure the final destination is included
        $interpolated[] = end($points);

        return $interpolated;
    }

    /**
     * Creates a dense list of pace notes from a GeoJSON route.
     *
     * The implementation follows the article's approach:
     * 1. Subdivide long segments to roughly 30 m.
     * 2. Resample to roughly 3 m.
     * 3. Classify each point by curvature.
     * 4. Generate candidates, filter descending severity, collapse ascending severity,
     *    and remove straights.
     */
    public function createPaceNotes(string $geoJsonString): array {
        $routePoints = $this->extractRoutePoints($geoJsonString);

        if (count($routePoints) < 2) {
            return [];
        }

        $subdividedRoute = $this->interpolateRoute($routePoints, self::INITIAL_SUBDIVISION_MAX_M);
        $densePoints = $this->interpolateRoute($subdividedRoute, self::CLASSIFICATION_SAMPLE_DISTANCE_M);

        if (count($densePoints) < 3) {
            return [];
        }

        $classifiedPoints = $this->classifyRoutePoints($densePoints);
        $candidates = $this->buildInitialCandidates($classifiedPoints);
        $candidates = $this->filterDescendingSeverity($candidates);
        $candidates = $this->collapseAscendingSeverity($candidates);
        $candidates = array_values(array_filter($candidates, function (array $candidate): bool {
            return !$candidate['marking']['straight'];
        }));

        return $this->attachDistancesToCandidates($candidates, $densePoints);
    }

    /**
     * Classify each route point by curvature, turn direction and rally severity.
     */
    private function classifyRoutePoints(array $points): array {
        $classified = [];
        $neighborOffset = self::CLASSIFICATION_NEIGHBOR_SPACING;

        foreach ($points as $index => $point) {
            $marking = $this->createStraightMarking();

            if ($index >= $neighborOffset && $index + $neighborOffset < count($points)) {
                $previous = $points[$index - $neighborOffset];
                $next = $points[$index + $neighborOffset];

                $localPrev = $this->toLocalCartesianMeters($previous['lat'], $previous['lng'], $point['lat'], $point['lng']);
                $localNext = $this->toLocalCartesianMeters($next['lat'], $next['lng'], $point['lat'], $point['lng']);

                $divisor = 2.0 * ($localPrev['x'] * $localNext['z'] - $localNext['x'] * $localPrev['z']);

                if (abs($divisor) > 0.0001) {
                    $xc = ((($localPrev['x'] * $localPrev['x']) + ($localPrev['z'] * $localPrev['z'])) * $localNext['z']
                        - (($localNext['x'] * $localNext['x']) + ($localNext['z'] * $localNext['z'])) * $localPrev['z']) / $divisor;
                    $yc = ((($localPrev['x'] * $localPrev['x']) + ($localPrev['z'] * $localPrev['z'])) * $localNext['x']
                        - (($localNext['x'] * $localNext['x']) + ($localNext['z'] * $localNext['z'])) * $localPrev['x']) / (-$divisor);
                    $radius = sqrt(($xc * $xc) + ($yc * $yc));

                    if ($radius > 0) {
                        $velocityMetersPerSecond = 50.0 / 3.6;
                        $lateralAcceleration = ($velocityMetersPerSecond * $velocityMetersPerSecond) / $radius;
                        $lateralGs = $lateralAcceleration / 9.81;
                        $speedAtComfortableG = sqrt((0.3 * 9.81) * $radius) * 3.6;

                        $severity = $this->severityFromSpeedKmh($speedAtComfortableG);

                        if ($severity === 0) {
                            $marking = $this->createStraightMarking();
                        } else {
                            $marking = $this->createTurnMarking(
                                $divisor > 0 ? 'right' : 'left',
                                $severity
                            );
                            $marking['radius_m'] = $radius;
                            $marking['lateral_gs_at_50_kmh'] = $lateralGs;
                            $marking['recommended_speed_kmh'] = $speedAtComfortableG;
                        }
                    }
                }
            }

            $classified[] = [
                'index' => $index,
                'point' => $point,
                'marking' => $marking,
            ];
        }

        return $classified;
    }

    /**
     * Build a candidate list whenever the classification changes.
     */
    private function buildInitialCandidates(array $classifiedPoints): array {
        $candidates = [];
        $currentKey = null;

        foreach ($classifiedPoints as $classifiedPoint) {
            $marking = $classifiedPoint['marking'];
            $key = $this->markingKey($marking);

            if ($key !== $currentKey) {
                $candidates[] = [
                    'index' => $classifiedPoint['index'],
                    'marking' => $marking,
                ];
                $currentKey = $key;
            }
        }

        return $candidates;
    }

    /**
     * Remove notes that become less severe while continuing in the same direction.
     */
    private function filterDescendingSeverity(array $candidates): array {
        for ($i = count($candidates) - 1; $i >= 1; $i--) {
            if ($this->isMoreSevereThan($candidates[$i - 1]['marking'], $candidates[$i]['marking'])
                && $this->isSameDirection($candidates[$i - 1]['marking'], $candidates[$i]['marking'])) {
                array_splice($candidates, $i, 1);
            }
        }

        return array_values($candidates);
    }

    /**
     * Merge quickly-following, more severe markings into the earlier less severe one.
     */
    private function collapseAscendingSeverity(array $candidates): array {
        for ($i = count($candidates) - 1; $i >= 1; $i--) {
            if ($this->isSameDirection($candidates[$i]['marking'], $candidates[$i - 1]['marking'])
                && $this->isMoreSevereThan($candidates[$i]['marking'], $candidates[$i - 1]['marking'])
                && (($candidates[$i]['index'] - $candidates[$i - 1]['index']) < self::ASCENDING_COLLAPSE_MAX_GAP)) {
                $candidates[$i - 1]['marking'] = $candidates[$i]['marking'];
                array_splice($candidates, $i, 1);
            }
        }

        return array_values($candidates);
    }

    /**
     * Attach human-readable distance information to the final notes.
     */
    private function attachDistancesToCandidates(array $candidates, array $points): array {
        $cumulativeDistances = $this->buildCumulativeDistances($points);
        $notes = [];

        foreach ($candidates as $candidateIndex => $candidate) {
            $pointIndex = $candidate['index'];
            $marking = $candidate['marking'];

            $notes[] = [
                'index' => $pointIndex,
                'lat' => $points[$pointIndex]['lat'],
                'lng' => $points[$pointIndex]['lng'],
                'distance_from_start_m' => round($cumulativeDistances[$pointIndex], 1),
                'distance_from_previous_note_m' => $candidateIndex > 0
                    ? round($this->distanceBetweenRouteIndices($candidates, $candidateIndex, $cumulativeDistances), 1)
                    : null,
                'distance_to_next_note_m' => $candidateIndex < count($candidates) - 1
                    ? round($cumulativeDistances[$candidates[$candidateIndex + 1]['index']] - $cumulativeDistances[$pointIndex], 1)
                    : null,
                'note' => $marking['label'],
                'direction' => $marking['direction'],
                'severity' => $marking['severity'],
                'radius_m' => $marking['radius_m'] ?? null,
                'lateral_gs_at_50_kmh' => $marking['lateral_gs_at_50_kmh'] ?? null,
                'recommended_speed_kmh' => $marking['recommended_speed_kmh'] ?? null,
            ];
        }

        return $notes;
    }

    private function distanceBetweenRouteIndices(array $candidates, int $candidateIndex, array $cumulativeDistances): float {
        $pointIndex = $candidates[$candidateIndex]['index'];

        if ($candidateIndex === 0) {
            return $cumulativeDistances[$pointIndex];
        }

        $previousPointIndex = $candidates[$candidateIndex - 1]['index'];

        return $cumulativeDistances[$pointIndex] - $cumulativeDistances[$previousPointIndex];
    }

    private function buildCumulativeDistances(array $points): array {
        $distances = [0.0];

        for ($i = 1; $i < count($points); $i++) {
            $distances[$i] = $distances[$i - 1] + $this->calculateDistanceMeters(
                $points[$i - 1]['lat'],
                $points[$i - 1]['lng'],
                $points[$i]['lat'],
                $points[$i]['lng']
            );
        }

        return $distances;
    }

    private function createStraightMarking(): array {
        return [
            'direction' => 'straight',
            'severity' => null,
            'straight' => true,
            'label' => 'Straight',
        ];
    }

    private function createTurnMarking(string $direction, int $severity): array {
        $prefix = $direction === 'right' ? 'R' : 'L';

        return [
            'direction' => $direction,
            'severity' => $severity,
            'straight' => false,
            'label' => $prefix . $severity,
        ];
    }

    private function markingKey(array $marking): string {
        return $marking['direction'] . ':' . ($marking['severity'] === null ? 'straight' : (string)$marking['severity']);
    }

    private function isSameDirection(array $a, array $b): bool {
        return $a['direction'] === $b['direction'];
    }

    /**
     * Lower severity numbers are tighter turns, therefore "more severe".
     */
    private function isMoreSevereThan(array $a, array $b): bool {
        if ($a['straight'] && !$b['straight']) {
            return false;
        }

        if (!$a['straight'] && $b['straight']) {
            return true;
        }

        if ($a['straight'] && $b['straight']) {
            return false;
        }

        return $a['severity'] < $b['severity'];
    }

    private function severityFromSpeedKmh(float $speedKmh): int {
        if ($speedKmh > self::SEVERITY_SPEED_LIMITS_KMH[6]) {
            return 0;
        }

        foreach ([1, 2, 3, 4, 5, 6] as $severity) {
            if ($speedKmh <= self::SEVERITY_SPEED_LIMITS_KMH[$severity]) {
                return $severity;
            }
        }

        return 0;
    }

    private function toLocalCartesianMeters(float $lat, float $lng, float $originLat, float $originLng): array {
        $latRad = deg2rad($lat);
        $originLatRad = deg2rad($originLat);
        $deltaLat = deg2rad($lat - $originLat);
        $deltaLng = deg2rad($lng - $originLng);

        return [
            'x' => $deltaLng * cos(($latRad + $originLatRad) / 2.0) * self::EARTH_RADIUS_M,
            'z' => $deltaLat * self::EARTH_RADIUS_M,
        ];
    }

    /**
     * Haversine formula to calculate the distance between two points in meters
     */
    private function calculateDistanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float {
        $earthRadius = self::EARTH_RADIUS_M; // Radius of the earth in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}

    $service = new PaceNoteService();

    $examples = [
        'DHBW_Mannheim' => '{
    "type": "FeatureCollection",
    "features": [
        {
            "type": "Feature",
            "properties": {
            },
            "geometry": {
                "type": "LineString",
                "coordinates": [
                    [
                        8.534309,
                        49.474721,
                        98.5
                    ],
                    [
                        8.534238,
                        49.474401,
                        99.25
                    ],
                    [
                        8.534176,
                        49.474123,
                        99.75
                    ],
                    [
                        8.534048,
                        49.473544,
                        97.75
                    ],
                    [
                        8.535178,
                        49.473429,
                        96.25
                    ],
                    [
                        8.535610,
                        49.473388,
                        96.75
                    ],
                    [
                        8.536071,
                        49.473344,
                        97.25
                    ],
                    [
                        8.536873,
                        49.473268,
                        97.75
                    ],
                    [
                        8.537015,
                        49.473254,
                        97.75
                    ],
                    [
                        8.537103,
                        49.473397,
                        98.0
                    ],
                    [
                        8.537420,
                        49.473363,
                        96.75
                    ],
                    [
                        8.537908,
                        49.473313,
                        96.75
                    ],
                    [
                        8.538139,
                        49.474446,
                        99.0
                    ],
                    [
                        8.538177,
                        49.474598,
                        98.75
                    ],
                    [
                        8.538169,
                        49.474598,
                        98.5
                    ],
                    [
                        8.537416,
                        49.474673,
                        98.5
                    ],
                    [
                        8.537192,
                        49.474695,
                        98.25
                    ],
                    [
                        8.536656,
                        49.474745,
                        97.0
                    ],
                    [
                        8.536301,
                        49.474779,
                        97.25
                    ],
                    [
                        8.535666,
                        49.474838,
                        97.5
                    ],
                    [
                        8.535494,
                        49.474859,
                        97.5
                    ],
                    [
                        8.534824,
                        49.474915,
                        98.0
                    ]
                ]
            }
        }
    ]
}',
    ];

    echo "PaceNoteService CLI-Demo\n";
    echo str_repeat('=', 28) . "\n\n";

    foreach ($examples as $label => $geoJson) {
        echo "Beispiel: {$label}\n";
        echo str_repeat('-', 28) . "\n";

        try {
            $notes = $service->createPaceNotes($geoJson);

            if (empty($notes)) {
                echo "Keine Pace Notes gefunden.\n";
            } else {
                echo "Pace Notes gefunden: " . count($notes) . "\n";
                echo str_repeat('-', 60) . "\n";

                foreach ($notes as $i => $note) {
                    // Helper to show N/A for null values
                    $show = function ($v) {
                        return $v === null ? 'N/A' : (is_float($v) || is_numeric($v) ? (string)$v : $v);
                    };

                    printf("%2d) Index: %d | Lat: %.6f | Lng: %.6f\n", $i + 1, $note['index'], $note['lat'], $note['lng']);
                    printf("    Distance from start: %s m | From previous note: %s m | To next note: %s m\n",
                        $show($note['distance_from_start_m']),
                        $show($note['distance_from_previous_note_m']),
                        $show($note['distance_to_next_note_m'])
                    );
                    printf("    Note: %s | Direction: %s | Severity: %s\n",
                        $show($note['note']),
                        $show($note['direction']),
                        $show($note['severity'])
                    );
                    printf("    Radius (m): %s | Lateral Gs @50km/h: %s | Recommended speed (km/h): %s\n",
                        $show($note['radius_m']),
                        $show($note['lateral_gs_at_50_kmh']),
                        $show($note['recommended_speed_kmh'])
                    );
                    echo str_repeat('-', 60) . "\n";
                }
            }
        } catch (Exception $e) {
            echo 'Fehler: ' . $e->getMessage() . "\n";
        }

        echo "\n";
}

