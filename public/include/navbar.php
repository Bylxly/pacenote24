<?php $current = basename($_SERVER['PHP_SELF']); ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const navbar = document.querySelector('.navbar');

  // Scroll-Effekt
  const onScroll = () => navbar.classList.toggle('scrolled', window.scrollY > 20);
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // Mobile: Menü nach Link-Klick schließen
  window.addEventListener('load', () => {
    document.querySelectorAll('#navMenu .nav-link').forEach(link => {
      link.addEventListener('click', () => {
        const menu = document.getElementById('navMenu');
        if (menu.classList.contains('show')) {
          bootstrap.Collapse.getOrCreateInstance(menu).hide();
        }
      });
    });
  });
});
</script>
<nav class="navbar navbar-expand-lg px-3">
    <div class="container-fluid">
        <a class="navbar-brand" href="./home.php">Pacenotes24<span>.de</span></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-expanded="false">
            <span class="toggler-bar"></span>
            <span class="toggler-bar"></span>
            <span class="toggler-bar"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link <?= $current==='home.php'?'active':'' ?>"   href="./home.php">Home</a></li>
                <li class="nav-item"><a class="nav-link <?= $current==='karte.php'?'active':'' ?>"  href="./karte.php">Karte</a></li>
                <li class="nav-item"><a class="nav-link <?= $current==='navigation.php'?'active':'' ?>" href="./navigation.php">Viewer</a></li>
                <li class="nav-item"><a class="nav-link <?= $current==='routen.php'?'active':'' ?>" href="./routen.php">Routen</a></li>
            </ul>
            <?php if (isAuthenticated()): ?>
                <?php if (hasRole(ADMIN_ROLE_ID)): ?>
                    <a href="./admin/adminpanel.php" class="btn btn-outline-light btn-sm me-2">Adminpanel</a>
                <?php endif; ?>
                <button class="btn btn-outline-danger btn-sm" onclick="logout()">Logout</button>
            <?php else: ?>
                <a href="./login.php" class="btn btn-outline-success btn-sm">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>