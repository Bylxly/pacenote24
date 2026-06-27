<?php $current = basename($_SERVER['PHP_SELF']); ?>
<nav class="navbar navbar-expand-lg px-3">
    <div class="container-fluid">
        <a class="navbar-brand" href="home.php">Pacenotes24<span>.de</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link <?= $current==='home.php'?'active':'' ?>"   href="home.php">Home</a></li>
                <li class="nav-item"><a class="nav-link <?= $current==='index.php'?'active':'' ?>"  href="index.php">Karte</a></li>
                <li class="nav-item"><a class="nav-link <?= $current==='navigation.php'?'active':'' ?>" href="navigation.php">Viewer</a></li>
                <li class="nav-item"><a class="nav-link <?= $current==='routen.php'?'active':'' ?>" href="routen.php">Routen</a></li>
            </ul>
            <?php if (isAuthenticated()): ?>
                <?php if (hasRole(ADMIN_ROLE_ID)): ?>
                    <a href="admin/adminpanel.php" class="btn btn-outline-light btn-sm me-2">Adminpanel</a>
                <?php endif; ?>
                <a href="profil.php" class="btn btn-outline-light btn-sm me-2">Profil</a>
                <button class="btn btn-outline-danger btn-sm" onclick="logout()">Logout</button>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline-success btn-sm">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>