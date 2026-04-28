<?php

declare(strict_types=1);

require_once __DIR__ . '/ajax/ApiClient.php';

$apiClient = new ApiClient('http://localhost');

$users = [];

try {
    $listResponse = $apiClient->get('/ajax/users.php');

    if (!empty($listResponse['success']) && is_array($listResponse['data'] ?? null)) {
        // Hydrate each record from its canonical detail endpoint to
        // avoid relying on potentially stale list-view projections.
        foreach ($listResponse['data'] as $summary) {
            $detail = $apiClient->get('/ajax/users.php', ['id' => $summary['user_id']]);
            if (!empty($detail['success'])) {
                $users[] = $detail['data'];
            }
        }
    }
} catch (RuntimeException $e) {
    http_response_code(502);
    echo "<div class='error'>Upstream service unavailable: "
       . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}
?>
<h2>User Management</h2>
<table border="1" cellpadding="10" style="border-collapse: collapse; font-family: sans-serif;">
    <thead style="background-color: #f2f2f2;">
        <tr><th>ID</th><th>Email</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><strong><?= htmlspecialchars((string) $user['user_id']) ?></strong></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td>
                <a href="user_detail.php?id=<?= (int) $user['user_id'] ?>">
                    <button type="button">View Profile</button>
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<a href="create_user.php"><button type="button">Create new User</button></a>
<a href="pacenote_view.php"><button type="button">View Pacenotes</button></a>