<?php
/**
 * Standalone-Demo / Beispiel für PaceNoteService.
 * Wird NICHT von der App eingebunden - nur zur manuellen Ausführung:
 *   php app/services/PaceNoteService.demo.php
 */

require_once __DIR__ . '/PaceNoteService.php';

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
