<?php
// Module B: Magazijn (Rol: Magazijn)
session_start();

require_once '../classes/Database.php';
require_once '../classes/WarehouseManager.php';
require_once '../classes/AuthManager.php';

// Database connectie
$database = new Database();
$db = $database->getConnection();

// Check toegang
$authManager = new AuthManager($db);
$authManager->requireRole('Magazijn');

// Warehouse manager
$warehouseManager = new WarehouseManager($db);

// Handle pack action
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pack_item'])) {
    $warehouseManager->markAsPacked($_POST['orderrule_id']);
    header('Location: warehouse.php?success=1');
    exit;
}

// Haal ongepakte items op
$unpackedItems = $warehouseManager->getUnpackedItems();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Magazijn - Vesuvio Scootershop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/nav.php'; ?>

    <div class="container">
        <h1>Magazijn - Inpakken</h1>

        <?php if(isset($_GET['success'])): ?>
            <div class="success">Onderdeel succesvol als ingepakt gemarkeerd!</div>
        <?php endif; ?>

        <?php if(empty($unpackedItems)): ?>
            <p>Alle onderdelen zijn ingepakt! 🎉</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Bestelnr</th>
                        <th>Datum</th>
                        <th>Ontvanger</th>
                        <th>Onderdeel</th>
                        <th>Actie</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($unpackedItems as $item): ?>
                        <tr>
                            <td>#<?php echo $item['order_id']; ?></td>
                            <td><?php echo date('d-m-Y', strtotime($item['date'])); ?></td>
                            <td><?php echo htmlspecialchars($item['recipient']); ?></td>
                            <td><?php echo htmlspecialchars($item['part']); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="orderrule_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="pack_item">✓ Ingepakt</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
