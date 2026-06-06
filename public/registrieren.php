<?php
require_once __DIR__ . '/../app/session/guard.php';
requireGuest();
?>  
  
  <!DOCTYPE html>
<?php
require_once __DIR__ . '/../app/session/guard.php';
requireGuest();
?>
<html lang="de">
  <head>
    <?php include 'head.php'; ?>
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
            <a href="login.php" class="register-link">Schon Registriert?</a>
          </p>
        </form>
            
      </div>
    </div>
  
  </body>
</html>

