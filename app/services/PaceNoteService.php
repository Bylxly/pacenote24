<?php

class PaceNoteService {

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

        $points = [];
        foreach ($coordinates as $coord) {
            // GeoJSON format is [longitude, latitude, (optional elevation)]
            $points[] = [
                'lat' => $coord[1],
                'lng' => $coord[0],
            ];
        }

        return $points;
    }

    /**
     * Recursively searches the GeoJSON array to find the LineString coordinates.
     * This handles raw Geometry, Features, or FeatureCollections.
     */
    private function findCoordinates(array $data): array {
        if (isset($data['type'])) {
            if ($data['type'] === 'LineString' && isset($data['coordinates'])) {
                return $data['coordinates'];
            }
            if ($data['type'] === 'Feature' && isset($data['geometry'])) {
                return $this->findCoordinates($data['geometry']);
            }
            if ($data['type'] === 'FeatureCollection' && isset($data['features'][0])) {
                // Assuming the primary route is the first feature
                return $this->findCoordinates($data['features'][0]);
            }
        }
        return [];
    }

    /**
     * Generates evenly spaced points along the route for GPS tracking/snapping.
     * Useful if you need a point exactly every X meters to check vehicle progress.
     */
    public function interpolateRoute(array $points, float $intervalMeters): array {
        if (count($points) < 2) return $points;

        $interpolated = [$points[0]];
        $remainingDistance = $intervalMeters;

        for ($i = 0; $i < count($points) - 1; $i++) {
            $p1 = $points[$i];
            $p2 = $points[$i + 1];

            $segmentLength = $this->calculateDistanceMeters($p1['lat'], $p1['lng'], $p2['lat'], $p2['lng']);
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
     * Haversine formula to calculate the distance between two points in meters
     */
    private function calculateDistanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float {
        $earthRadius = 6371000; // Radius of the earth in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}

$geoJsonInput = '{
  "type": "Feature",
  "geometry": {
    "type": "LineString",
    "coordinates": [
      [-122.4194, 37.7749], 
      [-122.4184, 37.7759],
      [-122.4174, 37.7769]
    ]
  }
}';

$processor = new PaceNoteService();

// 1. Get the exact nodes of the road path
$routePoints = $processor->extractRoutePoints($geoJsonInput);

// $routePoints now looks like:
// [
//   ['lat' => 37.7749, 'lng' => -122.4194],
//   ['lat' => 37.7759, 'lng' => -122.4184], ...
// ]

// 2. (Optional) If your navigation engine needs points strictly every 10 meters
// (e.g., for map matching or off-route detection):
$densePoints = $processor->interpolateRoute($routePoints, 10.0);

print_r($densePoints);