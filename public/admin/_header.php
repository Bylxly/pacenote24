<?php

require_once __DIR__ . '/../../app/session/guard.php';
requireAdmin();

$adminTitle      = $adminTitle      ?? 'Admin Panel';
$adminBreadcrumb = $adminBreadcrumb ?? [['label' => 'User Management', 'href' => null]];
$adminToggle     = $adminToggle     ?? null;

$h = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $h($adminTitle) ?></title>
  <link href="../assets/vendor/bootstrap/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/css/stylesheetmain.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<nav class="navbar navbar-expand-lg px-3">
  <div class="container-fluid">
    <a class="navbar-brand" href="../index.php">
      Pacenotes24<span>.de</span>
      <span class="badge ms-2 align-middle" style="background:var(--accent);color:#000;font-size:.6rem;letter-spacing:1px;">ADMIN</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link<?= $adminToggle === 'users'     ? ' active' : '' ?>" href="adminpanel.php">Users</a></li>
        <li class="nav-item"><a class="nav-link<?= $adminToggle === 'groups'    ? ' active' : '' ?>" href="groups.php">Gruppen</a></li>
        <li class="nav-item"><a class="nav-link<?= $adminToggle === 'pacenotes' ? ' active' : '' ?>" href="pacenote_view.php">Pacenotes</a></li>
      </ul>
      <a href="../index.php" class="btn btn-outline-light btn-sm me-2">Zur App</a>
      <button class="btn btn-outline-danger btn-sm" onclick="adminLogout()">Logout</button>
    </div>
  </div>
</nav>

<div class="admin-subbar px-3 py-2">
  <nav class="admin-header__breadcrumb">
    <?php
    $lastIndex = array_key_last($adminBreadcrumb);
    foreach ($adminBreadcrumb as $i => $crumb) {
        if ($i > 0) {
            echo '<span>›</span>';
        }
        if ($i === $lastIndex || empty($crumb['href'])) {
            echo '<strong>' . $h($crumb['label']) . '</strong>';
        } else {
            echo '<a href="' . $h($crumb['href']) . '">' . $h($crumb['label']) . '</a>';
        }
    }
    ?>
  </nav>
</div>

<div class="modal fade" id="confirmDeleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Wirklich löschen?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Diese Aktion kann nicht rückgängig gemacht werden.</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Löschen</button>
            </div>
        </div>
    </div>
</div>

<script src="../assets/vendor/bootstrap/bootstrap.bundle.min.js" defer></script>
<script>
    function adminLogout() {
        fetch('../ajax/auth/logout.php', { method: 'POST' })
            .then(() => { window.location.href = '../login.php'; });
    }
</script>
