<?php

class PaceNoteService {

    const EARTH_RADIUS_M = 6371000.0;
    const CLASSIFICATION_NEIGHBOR_SPACING = 7;
    const CLASSIFICATION_SAMPLE_DISTANCE_M = 3.0;
    const INITIAL_SUBDIVISION_MAX_M = 30.0;
    const ASCENDING_COLLAPSE_MAX_GAP = 20;
    const GRADIENT_WINDOW_M = 20.0;
    const GRADIENT_FLAT_THRESHOLD_PERCENT = 1.0;
    const LATERAL_G_COMFORT = 0.5;

    const SEVERITY_SPEED_LIMITS_KMH = [
        1 => 30.0,
        2 => 40.0,
        3 => 50.0,
        4 => 70.0,
        5 => 90.0,
        6 => 130.0,
    ];

    /**
     * Parses a GeoJSON string and returns route coordinates as associative ['lat', 'lng', 'elevation_m'] points.
     * GeoJSON uses [longitude, latitude] order; this method normalises to named keys.
     *
     * @throws InvalidArgumentException if the input is not valid JSON
     * @throws RuntimeException if no LineString geometry is found
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
     * Recursively searches a decoded GeoJSON structure for the first LineString coordinates array.
     * Handles Feature, FeatureCollection, and GeometryCollection wrappers.
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
     * Converts a raw GeoJSON coordinate [lng, lat] or [lng, lat, elevation] to an associative point.
     * Returns null if the entry is malformed or contains non-numeric values.
     */
    private function normalizeCoordinatePair($coord): ?array {
        if (!is_array($coord) || count($coord) < 2) {
            return null;
        }

        if (!is_numeric($coord[0]) || !is_numeric($coord[1])) {
            return null;
        }

        return [
            'lat' => (float)$coord[1],
            'lng' => (float)$coord[0],
            'elevation_m' => isset($coord[2]) && is_numeric($coord[2]) ? (float)$coord[2] : null,
        ];
    }

    /**
     * Resamples a polyline to evenly spaced points at the given interval using linear interpolation.
     * The first and last original points are always included in the result.
     *
     * @throws InvalidArgumentException if $intervalMeters <= 0
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

                $interpolated[] = [
                    'lat' => $p1['lat'] + ($p2['lat'] - $p1['lat']) * $fraction,
                    'lng' => $p1['lng'] + ($p2['lng'] - $p1['lng']) * $fraction,
                    'elevation_m' => ($p1['elevation_m'] !== null && $p2['elevation_m'] !== null)
                        ? $p1['elevation_m'] + ($p2['elevation_m'] - $p1['elevation_m']) * $fraction
                        : null,
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
     * Resamples a polyline at $intervalMeters using quadratic Bezier smoothing at each vertex.
     *
     * Instead of walking straight through the original vertices (linear interpolation),
     * the path is rerouted through the midpoints of consecutive edges. At each interior
     * vertex P[i], a quadratic Bezier from M[i-1] → M[i] with P[i] as the control point
     * rounds the corner. This prevents phantom high-severity notes at 90° GPS artefacts.
     *
     * Equivalent to the "cubic midpoint interpolation" step described in:
     * https://voidcomputing.hu/blog/rally-pace-notes/
     */
    private function cubicMidpointSample(array $points, float $intervalMeters): array {
        $n = count($points);

        if ($n < 3) {
            return $this->interpolateRoute($points, $intervalMeters);
        }

        // Midpoint of every consecutive edge
        $mids = [];
        for ($i = 0; $i < $n - 1; $i++) {
            $mids[$i] = [
                'lat' => ($points[$i]['lat'] + $points[$i + 1]['lat']) / 2.0,
                'lng' => ($points[$i]['lng'] + $points[$i + 1]['lng']) / 2.0,
                'elevation_m' => ($points[$i]['elevation_m'] !== null && $points[$i + 1]['elevation_m'] !== null)
                    ? ($points[$i]['elevation_m'] + $points[$i + 1]['elevation_m']) / 2.0
                    : null,
            ];
        }

        // Build a dense polyline that approximates the smooth Bezier path.
        // Layout: P[0] → M[0] → Bezier(M[0]→M[1], ctrl=P[1]) → … → M[n-2] → P[n-1]
        $smooth = [$points[0], $mids[0]];

        for ($i = 1; $i < $n - 1; $i++) {
            $p0 = $mids[$i - 1]; // start of Bezier
            $p1 = $points[$i];   // control point (the original vertex)
            $p2 = $mids[$i];     // end of Bezier

            // 20 subdivisions keep each sub-segment well below the 3 m sampling interval
            for ($step = 1; $step <= 20; $step++) {
                $t  = $step / 20.0;
                $mt = 1.0 - $t;

                $smooth[] = [
                    'lat' => $mt * $mt * $p0['lat'] + 2.0 * $mt * $t * $p1['lat'] + $t * $t * $p2['lat'],
                    'lng' => $mt * $mt * $p0['lng'] + 2.0 * $mt * $t * $p1['lng'] + $t * $t * $p2['lng'],
                    'elevation_m' => ($p0['elevation_m'] !== null && $p1['elevation_m'] !== null && $p2['elevation_m'] !== null)
                        ? $mt * $mt * $p0['elevation_m'] + 2.0 * $mt * $t * $p1['elevation_m'] + $t * $t * $p2['elevation_m']
                        : null,
                ];
            }
        }

        $smooth[] = $points[$n - 1];

        return $this->interpolateRoute($smooth, $intervalMeters);
    }

    /**
     * Main entry point: parses a GeoJSON route and returns an array of pace note entries.
     * Each note contains position, direction, severity (1–6), corner radius, and gradient data.
     * Returns an empty array if the route has too few points to classify.
     */
    public function createPaceNotes(string $geoJsonString): array {
        $routePoints = $this->extractRoutePoints($geoJsonString);

        if (count($routePoints) < 2) {
            return [];
        }

        $subdividedRoute = $this->interpolateRoute($routePoints, self::INITIAL_SUBDIVISION_MAX_M);
        $densePoints = $this->cubicMidpointSample($subdividedRoute, self::CLASSIFICATION_SAMPLE_DISTANCE_M);

        if (count($densePoints) < 3) {
            return [];
        }

        $classifiedPoints = $this->classifyRoutePoints($densePoints);
        $candidates = $this->buildInitialCandidates($classifiedPoints);
        $candidates = $this->filterDescendingSeverity($candidates);
        $candidates = $this->collapseAscendingSeverity($candidates);
        $candidates = array_values(array_filter($candidates, function (array $candidate): bool {
            return $candidate['marking']['direction'] !== 'straight';
        }));

        return $this->attachDistancesToCandidates($candidates, $densePoints);
    }

    /**
     * Assigns a curvature marking (direction + severity) to every dense sample point.
     * Uses three-point circle geometry with neighbours at ±CLASSIFICATION_NEIGHBOR_SPACING steps.
     * Points too close to either end of the array default to a straight marking.
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
                        $speedAtComfortableG = sqrt((self::LATERAL_G_COMFORT * 9.81) * $radius) * 3.6;
                        $severity = $this->severityFromSpeedKmh($speedAtComfortableG);

                        if ($severity === 0) {
                            $marking = $this->createStraightMarking();
                        } else {
                            $marking = $this->createTurnMarking(
                                $divisor > 0 ? 'right' : 'left',
                                $severity
                            );
                            $marking['radius_m'] = $radius;
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
     * Collapses the per-point classification into a candidate list, emitting one entry
     * each time the marking (direction + severity) changes.
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
     * Removes a note when the corner immediately loosens in the same turn direction
     * (e.g. R3 → R5 collapses to R3, because the driver is already warned about the tighter apex).
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
     * Merges a tighter follow-up corner into its predecessor when both share the same direction
     * and are within ASCENDING_COLLAPSE_MAX_GAP sample points apart.
     * The earlier note inherits the higher severity so the driver gets the worst-case warning first.
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
     * Converts raw candidates into the final note format, appending cumulative distances,
     * inter-note distances, position coordinates, and gradient data.
     */
    private function attachDistancesToCandidates(array $candidates, array $points): array {
        $cumulativeDistances = $this->buildCumulativeDistances($points);
        $notes = [];

        foreach ($candidates as $candidateIndex => $candidate) {
            $pointIndex = $candidate['index'];
            $marking = $candidate['marking'];
            $gradientPercent = $this->getGradientAtIndex($points, $pointIndex, $cumulativeDistances);

            $notes[] = [
                'lat' => $points[$pointIndex]['lat'],
                'lng' => $points[$pointIndex]['lng'],
                'elevation_m' => $points[$pointIndex]['elevation_m'] ?? null,
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
                'gradient_percent' => $gradientPercent !== null ? round($gradientPercent, 2) : null,
                'gradient_type' => $this->gradientType($gradientPercent),
            ];
        }

        return $notes;
    }

    /**
     * Returns the route distance (m) between a candidate and the one before it.
     * For the first candidate, returns its distance from the start of the route.
     */
    private function distanceBetweenRouteIndices(array $candidates, int $candidateIndex, array $cumulativeDistances): float {
        $pointIndex = $candidates[$candidateIndex]['index'];

        if ($candidateIndex === 0) {
            return $cumulativeDistances[$pointIndex];
        }

        $previousPointIndex = $candidates[$candidateIndex - 1]['index'];

        return $cumulativeDistances[$pointIndex] - $cumulativeDistances[$previousPointIndex];
    }

    /**
     * Returns an array where index i holds the total route distance (m) from points[0] to points[i].
     */
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

    /**
     * Computes the road gradient (in %) at a given route index by averaging elevation change
     * over a symmetric window of GRADIENT_WINDOW_M metres on each side.
     * Formula: gradient% = (Δelevation / Δhorizontal_distance) × 100
     */
    private function getGradientAtIndex(array $points, int $index, array $cumulativeDistances): ?float {
        if (($points[$index]['elevation_m'] ?? null) === null) {
            return null;
        }

        $halfWindow = self::GRADIENT_WINDOW_M / 2.0;
        $refDist = $cumulativeDistances[$index];

        $beforeIdx = $index;
        for ($i = $index - 1; $i >= 0; $i--) {
            if ($refDist - $cumulativeDistances[$i] <= $halfWindow) {
                $beforeIdx = $i;
            } else {
                break;
            }
        }

        $afterIdx = $index;
        for ($i = $index + 1; $i < count($points); $i++) {
            if ($cumulativeDistances[$i] - $refDist <= $halfWindow) {
                $afterIdx = $i;
            } else {
                break;
            }
        }

        if ($beforeIdx === $afterIdx
            || ($points[$beforeIdx]['elevation_m'] ?? null) === null
            || ($points[$afterIdx]['elevation_m'] ?? null) === null) {
            return null;
        }

        $horizontalDist = $cumulativeDistances[$afterIdx] - $cumulativeDistances[$beforeIdx];
        if ($horizontalDist <= 0.0) {
            return null;
        }

        return (($points[$afterIdx]['elevation_m'] - $points[$beforeIdx]['elevation_m']) / $horizontalDist) * 100.0;
    }

    /**
     * Classifies a gradient percentage as 'uphill', 'downhill', or 'flat'.
     * Returns null when no elevation data is available (gradient is null).
     * The flat threshold is ±GRADIENT_FLAT_THRESHOLD_PERCENT.
     */
    private function gradientType(?float $gradientPercent): ?string {
        if ($gradientPercent === null) {
            return null;
        }
        if ($gradientPercent > self::GRADIENT_FLAT_THRESHOLD_PERCENT) {
            return 'uphill';
        }
        if ($gradientPercent < -self::GRADIENT_FLAT_THRESHOLD_PERCENT) {
            return 'downhill';
        }
        return 'flat';
    }

    /**
     * Serialises pace notes to a versioned JSON string.
     *
     * JSON schema (schema_version 1.0):
     * {
     *   "schema_version": "1.0",
     *   "generated_at": "<ISO-8601 UTC>",
     *   "route": {
     *     "total_distance_m": <float>,
     *     "total_notes": <int>,
     *     "has_elevation_data": <bool>
     *   },
     *   "notes": [
     *     {
     *       "lat": <float>,   "lng": <float>,   "elevation_m": <float|null>,
     *       "distance_from_start_m": <float>,
     *       "distance_from_previous_note_m": <float|null>,
     *       "distance_to_next_note_m": <float|null>,
     *       "note": "<L|R><1-6>",
     *       "direction": "left"|"right",
     *       "severity": <int 1-6>,
     *       "radius_m": <float|null>,
     *       "gradient_percent": <float|null>,
     *       "gradient_type": "uphill"|"downhill"|"flat"|null
     *     }
     *   ]
     * }
     */
    public function exportToJson(array $notes): string {
        $hasElevation = !empty($notes) && ($notes[0]['elevation_m'] ?? null) !== null;
        $totalDistance = !empty($notes) ? (float)(end($notes)['distance_from_start_m'] ?? 0) : 0.0;

        $roundedNotes = array_map(function (array $note): array {
            return [
                'lat'                          => round($note['lat'], 7),
                'lng'                          => round($note['lng'], 7),
                'elevation_m'                  => $note['elevation_m'] !== null ? round($note['elevation_m'], 1) : null,
                'distance_from_start_m'        => $note['distance_from_start_m'],
                'distance_from_previous_note_m'=> $note['distance_from_previous_note_m'],
                'distance_to_next_note_m'      => $note['distance_to_next_note_m'],
                'note'                         => $note['note'],
                'direction'                    => $note['direction'],
                'severity'                     => $note['severity'],
                'radius_m'                     => $note['radius_m'] !== null ? round($note['radius_m'], 1) : null,
                'gradient_percent'             => $note['gradient_percent'],
                'gradient_type'               => $note['gradient_type'],
            ];
        }, $notes);

        $envelope = [
            'schema_version' => '1.0',
            'generated_at' => (new DateTime('now', new DateTimeZone('UTC')))->format(DateTime::ATOM),
            'route' => [
                'total_distance_m' => $totalDistance,
                'total_notes' => count($notes),
                'has_elevation_data' => $hasElevation,
            ],
            'notes' => $roundedNotes,
        ];

        $prevPrecision = ini_set('serialize_precision', -1);
        $json = json_encode($envelope, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        ini_set('serialize_precision', $prevPrecision);

        if ($json === false) {
            throw new RuntimeException('JSON encoding failed: ' . json_last_error_msg());
        }

        return $json;
    }

    /**
     * Validates a pace notes array and returns a list of plausibility error strings.
     * An empty return value means all notes passed validation.
     */
    public function validatePaceNotes(array $notes): array {
        $errors = [];

        if (empty($notes)) {
            $errors[] = 'No pace notes generated.';
            return $errors;
        }

        foreach ($notes as $i => $note) {
            $lat = $note['lat'] ?? null;
            $lng = $note['lng'] ?? null;

            if (!is_numeric($lat) || $lat < -90.0 || $lat > 90.0) {
                $errors[] = "Note #$i: invalid latitude '$lat'.";
            }
            if (!is_numeric($lng) || $lng < -180.0 || $lng > 180.0) {
                $errors[] = "Note #$i: invalid longitude '$lng'.";
            }

            $severity = $note['severity'] ?? null;
            if ($severity !== null && (!is_int($severity) || $severity < 1 || $severity > 6)) {
                $errors[] = "Note #$i: severity '$severity' out of range [1-6].";
            }

            $distStart = $note['distance_from_start_m'] ?? null;
            if ($distStart !== null && $distStart < 0.0) {
                $errors[] = "Note #$i: negative distance_from_start_m.";
            }

            $distNext = $note['distance_to_next_note_m'] ?? null;
            if ($distNext !== null && $distNext < 0.0) {
                $errors[] = "Note #$i: negative distance_to_next_note_m.";
            }

            $radiusM = $note['radius_m'] ?? null;
            if ($radiusM !== null && $radiusM < 0.0) {
                $errors[] = "Note #$i: negative radius_m.";
            }

            $gradient = $note['gradient_percent'] ?? null;
            if ($gradient !== null && abs($gradient) > 100.0) {
                $errors[] = "Note #$i: implausible gradient $gradient%.";
            }
        }

        return $errors;
    }

    /**
     * Returns a marking array representing a straight section (no turn, direction = 'straight').
     */
    private function createStraightMarking(): array {
        return [
            'direction' => 'straight',
            'severity' => null,
            'label' => 'Straight',
        ];
    }

    /**
     * Returns a marking array for a directional turn with the given severity class (1–6).
     * The label uses the rally co-driver shorthand, e.g. 'L3' or 'R1'.
     */
    private function createTurnMarking(string $direction, int $severity): array {
        $prefix = $direction === 'right' ? 'R' : 'L';

        return [
            'direction' => $direction,
            'severity' => $severity,
            'label' => $prefix . $severity,
        ];
    }

    /**
     * Produces a string key that uniquely identifies a marking by direction and severity.
     * Used to detect changes between consecutive classified points.
     */
    private function markingKey(array $marking): string {
        return $marking['direction'] . ':' . ($marking['severity'] === null ? 'straight' : (string)$marking['severity']);
    }

    /**
     * Returns true when both markings share the same direction (both left, right, or straight).
     */
    private function isSameDirection(array $a, array $b): bool {
        return $a['direction'] === $b['direction'];
    }

    /**
     * Lower severity numbers are tighter turns, therefore "more severe".
     */
    private function isMoreSevereThan(array $a, array $b): bool {
        $aIsStraight = $a['direction'] === 'straight';
        $bIsStraight = $b['direction'] === 'straight';

        if ($aIsStraight && !$bIsStraight) {
            return false;
        }

        if (!$aIsStraight && $bIsStraight) {
            return true;
        }

        if ($aIsStraight) {
            return false;
        }

        return $a['severity'] < $b['severity'];
    }

    /**
     * Maps a corner's comfortable-speed estimate (km/h) to a severity class 1–6.
     * Returns 0 when the speed exceeds the class-6 limit, treating the section as a straight.
     */
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

    /**
     * Projects a geographic coordinate into a local Cartesian (x, z) plane in metres,
     * centred on the given origin. Uses an equirectangular approximation, which is accurate
     * enough for the short inter-point distances used in curvature classification.
     */
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
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        return self::EARTH_RADIUS_M * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Primary entry point for pace note generation.
     *
     * Runs the full pipeline: GeoJSON parsing → route classification → validation → JSON serialisation.
     * Always use this method instead of calling the individual steps manually.
     *
     * Returns a JSON string conforming to schema version 1.0 (see exportToJson()).
     * An empty notes array is a valid result for routes with no classifiable corners.
     *
     * @throws InvalidArgumentException if $geoJson is malformed or contains no LineString
     * @throws RuntimeException if the generated notes fail plausibility validation
     */
    public function generatePaceNotes(string $geoJson): string {
        $notes = $this->createPaceNotes($geoJson);

        if (!empty($notes)) {
            $errors = $this->validatePaceNotes($notes);
            if (!empty($errors)) {
                throw new RuntimeException('Pace note validation failed: ' . implode('; ', $errors));
            }
        }

        return $this->exportToJson($notes);
    }
}

    $service = new PaceNoteService();

    // Example without elevation (2D GeoJSON)
    $geoJsonFlat = '{
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "properties": {},
      "geometry": {
        "type": "LineString",
        "coordinates": [
          [
            9.4771822,
            48.8080271
          ],
          [
            9.4777764,
            48.8083238
          ],
          [
            9.4788787,
            48.8083932
          ],
          [
            9.4786103,
            48.8095547
          ],
          [
            9.4785222,
            48.8097284
          ],
          [
            9.4783995,
            48.8098955
          ],
          [
            9.4776755,
            48.8106694
          ],
          [
            9.4774402,
            48.8111485
          ],
          [
            9.4772631,
            48.8115833
          ],
          [
            9.4771406,
            48.812009
          ],
          [
            9.4770379,
            48.8125891
          ],
          [
            9.4768197,
            48.8133416
          ],
          [
            9.476658,
            48.8138573
          ],
          [
            9.4765372,
            48.8141742
          ],
          [
            9.4764345,
            48.8145292
          ],
          [
            9.4764174,
            48.8145941
          ],
          [
            9.4763789,
            48.8146624
          ],
          [
            9.4763018,
            48.8147074
          ],
          [
            9.476212,
            48.8147018
          ],
          [
            9.476082,
            48.814664
          ],
          [
            9.4758112,
            48.8144975
          ],
          [
            9.4757412,
            48.8144651
          ],
          [
            9.4756667,
            48.8144559
          ],
          [
            9.4755872,
            48.8144566
          ],
          [
            9.4755133,
            48.8144975
          ],
          [
            9.4753176,
            48.8148004
          ],
          [
            9.475125,
            48.815147
          ],
          [
            9.4749838,
            48.8153846
          ],
          [
            9.4748633,
            48.8156211
          ],
          [
            9.4748511,
            48.8159567
          ],
          [
            9.4748903,
            48.8162809
          ],
          [
            9.4750523,
            48.8168397
          ],
          [
            9.4751593,
            48.8170116
          ],
          [
            9.4753048,
            48.8171863
          ],
          [
            9.4756487,
            48.8176363
          ],
          [
            9.475758,
            48.8177399
          ],
          [
            9.4758372,
            48.8178349
          ],
          [
            9.4759955,
            48.8179422
          ],
          [
            9.4760911,
            48.8180584
          ],
          [
            9.4760793,
            48.8180894
          ],
          [
            9.4759647,
            48.8180904
          ],
          [
            9.4757299,
            48.818025
          ],
          [
            9.475423,
            48.8179536
          ],
          [
            9.4750709,
            48.8178823
          ],
          [
            9.4748542,
            48.8178645
          ],
          [
            9.474764,
            48.8178942
          ],
          [
            9.4749174,
            48.818025
          ],
          [
            9.4755404,
            48.8184648
          ],
          [
            9.4761542,
            48.8188453
          ],
          [
            9.4769487,
            48.8191128
          ],
          [
            9.4775716,
            48.8192911
          ],
          [
            9.4781223,
            48.8194219
          ]
        ]
      }
    }
  ]
}';

    // Example with elevation data (3D GeoJSON: [lng, lat, elevation_m])
    $geoJsonWith3D = '{
  "type": "FeatureCollection",
  "features": [{
    "type": "Feature",
    "properties": {},
    "geometry": {
      "type": "LineString",
      "coordinates": [
        [8.5343503, 49.474967,  100.0],
        [8.5340566, 49.4735374, 105.0],
        [8.5368834, 49.4732659, 118.0],
        [8.5371968, 49.4746997, 112.0],
        [8.5343438, 49.474967,   98.0],
        [8.5380586, 49.4745597,  94.0]
      ]
    }
  }]
}';

    $examples = [
        'DHBW_Mannheim_2D (keine Höhendaten)' => $geoJsonFlat,
        'DHBW_Mannheim_3D (mit Höhendaten)'   => $geoJsonWith3D,
    ];

    echo "PaceNoteService CLI-Demo\n";
    echo str_repeat('=', 60) . "\n\n";

    $show = static function ($v, int $decimals = 2): string {
        if ($v === null) {
            return 'N/A';
        }
        return is_float($v) ? number_format($v, $decimals) : (string)$v;
    };

    foreach ($examples as $label => $geoJson) {
        echo "Beispiel: $label\n";
        echo str_repeat('-', 60) . "\n";

        try {
            $notes = $service->createPaceNotes($geoJson);

            if (empty($notes)) {
                echo "Keine Pace Notes gefunden.\n\n";
                continue;
            }

            echo 'Pace Notes gefunden: ' . count($notes) . "\n";
            echo str_repeat('-', 60) . "\n";

            foreach ($notes as $i => $note) {
                printf(
                    "%2d) [%s]  %-4s  Richtung: %-5s  Schärfe: %s\n",
                    $i + 1,
                    $show($note['distance_from_start_m'], 0) . ' m',
                    $note['note'],
                    $note['direction'],
                    $show($note['severity'], 0)
                );
                printf(
                    "    Lat: %.6f | Lng: %.6f | Höhe: %s m\n",
                    $note['lat'],
                    $note['lng'],
                    $show($note['elevation_m'], 1)
                );
                printf(
                    "    Radius: %s m\n",
                    $show($note['radius_m'], 0)
                );
                printf(
                    "    Gradient: %s%%  (%s)  | Abstand z. nächsten: %s m\n",
                    $show($note['gradient_percent']),
                    $note['gradient_type'] ?? 'N/A',
                    $show($note['distance_to_next_note_m'], 0)
                );
                echo str_repeat('-', 60) . "\n";
            }

            // Validation
            $errors = $service->validatePaceNotes($notes);
            if (empty($errors)) {
                echo "Validierung: OK - alle Werte plausibel.\n";
            } else {
                echo "Validierungsfehler:\n";
                foreach ($errors as $err) {
                    echo "  - $err\n";
                }
            }

            // JSON export
            $outputFile = sys_get_temp_dir() . '/pace_notes_' . preg_replace('/\W+/', '_', $label) . '.json';
            file_put_contents($outputFile, $service->exportToJson($notes));
            echo "JSON exportiert nach: $outputFile\n";

        } catch (Exception $e) {
            echo 'Fehler: ' . $e->getMessage() . "\n";
        }

        echo "\n";
    }

