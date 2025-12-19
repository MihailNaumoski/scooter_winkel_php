<?php
session_start();

require_once 'classes/Database.php';
require_once 'classes/OrderManager.php';

$database = new Database();
$db = $database->getConnection();
$orderManager = new OrderManager($db);

$orders = $orderManager->getAll();

$editOrder = null;
$allParts = [];
$currentPartIds = [];
if(isset($_GET['edit'])) {
    $editOrder = $orderManager->getOrderWithParts(intval($_GET['edit']));
    $allParts = $orderManager->getAllParts();
    $currentPartIds = $orderManager->getOrderPartIds(intval($_GET['edit']));
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $orderId = intval($_POST['order_id']);

    $orderManager->update($orderId, [
        'recipient' => $_POST['recipient'],
        'company_name' => $_POST['company_name'],
        'addressline1' => $_POST['addressline1'],
        'addressline2' => $_POST['addressline2'],
        'country' => $_POST['country']
    ]);

    $selectedParts = $_POST['parts'] ?? [];
    $orderManager->updateOrderParts($orderId, $selectedParts);

    header('Location: index.php?success=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bestellingen - Vesuvio Scootershop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/nav.php'; ?>

    <div class="container">
        <h1>Bestellingen</h1>

        <?php if(isset($_GET['success'])): ?>
            <div class="success">Bestelling bijgewerkt!</div>
        <?php endif; ?>

        <?php if($editOrder): ?>
            <div class="edit-form">
                <h2>Bestelling #<?php echo $editOrder->getId(); ?> bewerken</h2>
                <form method="POST">
                    <input type="hidden" name="order_id" value="<?php echo $editOrder->getId(); ?>">

                    <h3>Klantgegevens</h3>

                    <label>Ontvanger:</label>
                    <input type="text" name="recipient" value="<?php echo htmlspecialchars($editOrder->getRecipient()); ?>" required>

                    <label>Bedrijfsnaam:</label>
                    <input type="text" name="company_name" value="<?php echo htmlspecialchars($editOrder->getCompanyName()); ?>">

                    <label>Adresregel 1:</label>
                    <input type="text" name="addressline1" value="<?php echo htmlspecialchars($editOrder->getAddressline1()); ?>" required>

                    <label>Adresregel 2:</label>
                    <input type="text" name="addressline2" value="<?php echo htmlspecialchars($editOrder->getAddressline2()); ?>" required>

                    <label>Land:</label>
                    <input type="text" name="country" value="<?php echo htmlspecialchars($editOrder->getCountry()); ?>" required>

                    <h3>Artikelen</h3>
                    <div class="checkbox-group">
                        <?php foreach($allParts as $part): ?>
                            <?php $checked = in_array($part['id'], $currentPartIds) ? 'checked' : ''; ?>
                            <label class="checkbox-label">
                                <input type="checkbox" name="parts[]" value="<?php echo $part['id']; ?>" <?php echo $checked; ?>>
                                <?php echo htmlspecialchars($part['part']); ?> - €<?php echo number_format($part['sell_price'], 2, ',', '.'); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" name="update_order">Opslaan</button>
                    <a href="index.php" class="btn-secondary">Annuleren</a>
                </form>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Bestelnr</th>
                        <th>Datum</th>
                        <th>Ontvanger</th>
                        <th>Bedrijf</th>
                        <th>Adres</th>
                        <th>Land</th>
                        <th>Status</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order->getId(); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($order->getDate())); ?></td>
                            <td><?php echo htmlspecialchars($order->getRecipient()); ?></td>
                            <td><?php echo htmlspecialchars($order->getCompanyName()); ?></td>
                            <td><?php echo htmlspecialchars($order->getAddressline1()); ?></td>
                            <td><?php echo htmlspecialchars($order->getCountry()); ?></td>
                            <td><?php echo htmlspecialchars($order->getStatus() ?: 'Nieuw'); ?></td>
                            <td><a href="?edit=<?php echo $order->getId(); ?>">Bewerken</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
