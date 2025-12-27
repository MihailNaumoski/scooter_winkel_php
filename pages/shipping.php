<?php
session_start();

require_once '../classes/Database.php';
require_once '../classes/ShippingManager.php';
require_once '../classes/AuthManager.php';

$database = new Database();
$db = $database->getConnection();

$authManager = new AuthManager($db);
$authManager->requireRole('Verzending');

$shippingManager = new ShippingManager($db);

if(isset($_GET['ship']) && isset($_GET['order_id'])) {
    $orderId = intval($_GET['order_id']);
    $shippingManager->markAsInDelivery($orderId);
    header('Location: label.php?order_id=' . $orderId);
    exit;
}

$readyOrders = $shippingManager->getReadyToShipOrders();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verzending - Vesuvio Scootershop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/nav.php'; ?>

    <div class="container">
        <h1>Verzending</h1>
        <p>Bestellingen klaar voor verzending</p>

        <?php if(empty($readyOrders)): ?>
            <p>Geen bestellingen klaar.</p>
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
                        <th>Actie</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($readyOrders as $order): ?>
                        <tr>
                            <td>#<?php echo $order->getId(); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($order->getDate())); ?></td>
                            <td><?php echo htmlspecialchars($order->getRecipient()); ?></td>
                            <td><?php echo htmlspecialchars($order->getCompanyName()); ?></td>
                            <td>
                                <?php echo htmlspecialchars($order->getAddressline1()); ?><br>
                                <?php echo htmlspecialchars($order->getAddressline2()); ?>
                            </td>
                            <td><?php echo htmlspecialchars($order->getCountry()); ?></td>
                            <td>
                                <a href="?ship=1&order_id=<?php echo $order->getId(); ?>" class="btn-primary">In bezorging</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
