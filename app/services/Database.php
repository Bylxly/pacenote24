<?php
declare(strict_types=1);

final class Database {
  private static ?PDO $connection = null;

  public static function getConnection(): PDO {
    if (self::$connection instanceof PDO) {
      return self::$connection;
    }

    $config = require __DIR__ . '/../config/config.php';

    $local = __DIR__ . '/../config/config.local.php';
    if (file_exists($local)) {
        $localConfig = require $local;
        // Nur mergen, wenn die lokale Datei wirklich ein Array zurückgibt.
        // Eine auskommentierte config.local.php liefert sonst 1 (TypeError)
        if (is_array($localConfig)) {
            $config = array_replace_recursive($config, $localConfig);
        }
    }

    $db = $config['database'];
    $dsn = "mysql:host={$db['host']};dbname={$db['dbname']}";

    try {
      self::$connection = new PDO(
        $dsn,
        $db['username'],
        $db['password'],
        [
          PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES => false,
          PDO::ATTR_PERSISTENT => false,
        ]
      );
    } catch (PDOException $e) {
      throw new PDOException(
        'Database connection failed',
        (int)$e->getCode(),
        $e
      );
    }
    return self::$connection;
  }
}

?>