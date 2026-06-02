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
  <link rel="stylesheet" href="../assets/css/stylesheetmain.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

<header class="admin-header">
  <span class="admin-header__badge">Admin</span>
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
  <?php if ($adminToggle !== null): ?>
  <div class="page-toggle">
    <a href="adminpanel.php"    class="page-toggle__btn<?= $adminToggle === 'users'     ? ' active' : '' ?>">Users</a>
    <a href="pacenote_view.php" class="page-toggle__btn<?= $adminToggle === 'pacenotes' ? ' active' : '' ?>">Pacenotes</a>
  </div>
  <?php endif; ?>
</header>