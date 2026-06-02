    <!DOCTYPE html>
<?php
require_once __DIR__ . '/../app/session/guard.php';
requireGuest();
?>
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
  </head>

  <body>

    <div class="login-wrapper d-flex align-items-center justify-content-center min-vh-100">
      <div class="login-card card shadow-sm p-4 p-md-5">

        <!-- Logo / Icon -->
        <div class="text-center mb-4">
          <h1 class="login-title">Willkommen</h1>
          <p class="login-subtitle">Always drive save</p>
        </div>
        
        <div class="card">
            <div class="card__header">
            <span class="card__title">Neuen Nutzer erstellen</span>
            </div>
            <div class="card__body">
            <form id="create-form">

                <div class="form-group">
                <label for="email">Benutzername</label>
                <input type="email" id="email" name="email" placeholder="nutzer@beispiel.de" required>
                </div>

                <div class="form-group">
                <label for="password">Passwort</label>
                <input type="password" id="password" name="password" placeholder="Mindestens 8 Zeichen + Sonderzeichen" required>
                </div>

                <button type="submit" class="btn btn-primary">Nutzer anlegen</button>

            </form>
            </div>
        </div>

          <!-- Trennlinie -->
          <div class="divider d-flex align-items-center my-3">
            <span></span>
            <p class="mx-3 mb-0">oder</p>
            <span></span>
          </div>

          <!-- Login -->
          <p class="text-center mb-0 register-text">
            <a href="http://localhost/dhbw/pacenote24/public/login.php" class="register-link">Schon Registriert?</a>
          </p>
        </form>
            
      </div>
    </div>
  
  </body>
</html>

