
<?php
require_once __DIR__ . '/../app/session/guard.php';
requireGuest();
?>
<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Mein Projekt</title>

    <!-- Bootstrap CSS -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <!-- Custom CSS-->
    <link
      href="./assets/css/stylesheetmain.css"
      rel="stylesheet"
    />
    <script src="./assets/js/auth.js" defer></script>

  </head>

  <body>

    <div class="login-wrapper d-flex align-items-center justify-content-center min-vh-100">
      <div class="login-card card shadow-sm p-4 p-md-5">

        <!-- Logo / Icon -->
        <div class="text-center mb-4">
          <h1 class="login-title">Willkommen</h1>
          <p class="login-subtitle">Always drive save</p>
        </div>

        <!-- Formular -->
        <form id="loginFormular">
          <!-- Benutzer -->
          <div class="mb-3">
            <label for="email" class="form-label">Benutername</label>
            <input
              type="text"
              class="form-control"
              id="email"
              name="email"
              placeholder="IchRaseNicht24"
              required
            />
          </div>

          <!-- Passwort -->
          <div class="mb-3">
            <label for="pass" class="form-label">
              Passwort
            </label>
            <input
              class="form-control"
              id="password"
              name="password"
              type="password"
              placeholder="••••••••"
              required
            />
          </div>
            <div id="loginError" class="alert alert-danger d-none"></div>
          <!-- Login Button -->
          <button type="submit" class="btn btn-primary login-btn w-100 mb-3">
            Anmelden
          </button>

          <!-- Trennlinie -->
          <div class="divider d-flex align-items-center my-3">
            <span></span>
            <p class="mx-3 mb-0">oder</p>
            <span></span>
          </div>

          <!-- Registrieren -->
          <p class="text-center mb-0 register-text">
            Noch kein Konto?
            <a href="registrieren.php" class="register-link">Jetzt registrieren</a>
          </p>
        </form>
            
      </div>
    </div>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
</html>

