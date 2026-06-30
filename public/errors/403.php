<?php
http_response_code(403);
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <base href="<?= $base ?>">
    <title>Fehlende Berechtigung</title>
    <link href="../assets/vendor/bootstrap/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height:100vh">
  <div class="text-center">
    <h1>403</h1>
    <p>Nanu. Du hast keine Berechtigung, auf diese Seite zuzugreifen.</p>
    <a href="../home.php" class="btn btn-primary">Zurück zur Startseite</a>
  </div>
</body>
</html>