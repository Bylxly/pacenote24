<?php
// Error Setup, remove later
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Config Setup
$config = require '/opt/lampp/htdocs/DHBW/app/config/config.local.php';
$dbConfig = $config['database'];

// Connection Setup
$dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);

    // Fetch Users
    $stmt = $pdo->query("SELECT user_id, email FROM users");
    $users = $stmt->fetchAll();

    // Build the UI
    echo "<h2>User Management</h2>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; font-family: sans-serif;'>";
    echo "<thead style='background-color: #f2f2f2;'>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
          </thead>";
    echo "<tbody>";

    foreach ($users as $user) {
        $id = $user['user_id'];
        $email = $user['email'];

        echo "<tr>";
        echo "<td><strong>{$id}</strong></td>";
        echo "<td>{$email}</td>";
        echo "<td>
                <a href='user_detail.php?id={$id}'>
                    <button type='button' style='cursor:pointer;'>View Profile</button>
                </a>
              </td>";
        echo "</tr>";
    }

    echo "</tbody></table>";
    echo "<a href='create_user.php'> <button type='button'>Create new User!</button>";

} catch (PDOException $e) {
    echo "<div style='color:red;'>Connection failed: " . $e->getMessage() . "</div>";
}
?>