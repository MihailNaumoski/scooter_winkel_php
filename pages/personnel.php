<?php
session_start();

require_once '../classes/Database.php';
require_once '../classes/PersonnelManager.php';
require_once '../classes/AuthManager.php';

$database = new Database();
$db = $database->getConnection();

$authManager = new AuthManager($db);
$authManager->requireRole('Management');

$personnelManager = new PersonnelManager($db);

// create
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_personnel'])) {
    $personnelManager->create([
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'address' => $_POST['address'],
        'username' => $_POST['username'],
        'password' => $_POST['password'],
        'roles' => $_POST['roles'] ?? []
    ]);
    header('Location: personnel.php?success=created');
    exit;
}

// update
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_personnel'])) {
    $personnelManager->update($_POST['personnel_id'], [
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'address' => $_POST['address'],
        'roles' => $_POST['roles'] ?? []
    ]);

    if(!empty($_POST['password'])) {
        $personnelManager->updatePassword($_POST['personnel_id'], $_POST['password']);
    }

    header('Location: personnel.php?success=updated');
    exit;
}

// delete
if(isset($_GET['delete'])) {
    $personnelManager->delete($_GET['delete']);
    header('Location: personnel.php?success=deleted');
    exit;
}

$staff = $personnelManager->getAll();

$editPerson = null;
if(isset($_GET['edit'])) {
    $editPerson = $personnelManager->getById($_GET['edit']);
}

$availableRoles = ['Management', 'Magazijn', 'Verzending'];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personeel - Vesuvio Scootershop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/nav.php'; ?>

    <div class="container">
        <h1>Personeel Beheer</h1>

        <?php if(isset($_GET['success'])): ?>
            <div class="success">
                <?php
                    if($_GET['success'] === 'created') echo 'Personeelslid toegevoegd!';
                    if($_GET['success'] === 'updated') echo 'Personeelslid bijgewerkt!';
                    if($_GET['success'] === 'deleted') echo 'Personeelslid verwijderd!';
                ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <h2><?php echo $editPerson ? 'Bewerken' : 'Nieuw personeelslid'; ?></h2>
            <form method="POST">
                <?php if($editPerson): ?>
                    <input type="hidden" name="personnel_id" value="<?php echo $editPerson['id']; ?>">
                <?php endif; ?>

                <label>Naam:</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($editPerson['name'] ?? ''); ?>" required>

                <label>Email:</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($editPerson['email'] ?? ''); ?>" required>

                <label>Adres:</label>
                <input type="text" name="address" value="<?php echo htmlspecialchars($editPerson['address'] ?? ''); ?>" required>

                <label>Gebruikersnaam:</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($editPerson['username'] ?? ''); ?>" <?php echo $editPerson ? 'readonly' : 'required'; ?>>

                <label>Wachtwoord <?php echo $editPerson ? '(leeg = niet wijzigen)' : ''; ?>:</label>
                <input type="password" name="password" <?php echo $editPerson ? '' : 'required'; ?>>

                <label>Rollen:</label>
                <div class="checkbox-group">
                    <?php foreach($availableRoles as $role): ?>
                        <?php
                        $checked = '';
                        if($editPerson && in_array($role, $editPerson['roles'])) {
                            $checked = 'checked';
                        }
                        ?>
                        <label class="checkbox-label">
                            <input type="checkbox" name="roles[]" value="<?php echo $role; ?>" <?php echo $checked; ?>>
                            <?php echo $role; ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <button type="submit" name="<?php echo $editPerson ? 'update_personnel' : 'create_personnel'; ?>">
                    <?php echo $editPerson ? 'Opslaan' : 'Toevoegen'; ?>
                </button>

                <?php if($editPerson): ?>
                    <a href="personnel.php" class="btn-secondary">Annuleren</a>
                <?php endif; ?>
            </form>
        </div>

        <h2>Overzicht</h2>
        <table>
            <thead>
                <tr>
                    <th>Naam</th>
                    <th>Email</th>
                    <th>Adres</th>
                    <th>Gebruikersnaam</th>
                    <th>Rollen</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($staff as $person): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($person['name']); ?></td>
                        <td><?php echo htmlspecialchars($person['email']); ?></td>
                        <td><?php echo htmlspecialchars($person['address']); ?></td>
                        <td><?php echo htmlspecialchars($person['username']); ?></td>
                        <td><?php echo htmlspecialchars(implode(', ', $person['roles'])); ?></td>
                        <td>
                            <a href="?edit=<?php echo $person['id']; ?>">Bewerken</a>
                            <a href="?delete=<?php echo $person['id']; ?>" onclick="return confirm('Weet je het zeker?')">Verwijderen</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
