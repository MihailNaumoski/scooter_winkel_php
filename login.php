<?php
// Login pagina
session_start();

require_once 'classes/Database.php';
require_once 'classes/AuthManager.php';

// Database connectie
$database = new Database();
$db = $database->getConnection();
$authManager = new AuthManager($db);

// Als al ingelogd, redirect naar home
if($authManager->isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// Handle login
$error = null;
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if($authManager->login($username, $password)) {
        header('Location: index.php');
        exit;
    } else {
        $error = 'Gebruikersnaam of wachtwoord onjuist.';
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Vesuvio Scootershop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <h1>Vesuvio Scootershop</h1>
        <h2>Inloggen</h2>

        <?php if($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <label>Gebruikersnaam:</label>
            <input type="text" name="username" required autofocus>

            <label>Wachtwoord:</label>
            <input type="password" name="password" required>

            <button type="submit">Inloggen</button>
        </form>
    </div>
</body>
</html>
