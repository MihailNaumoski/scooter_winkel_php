<?php
session_start();

require_once '../classes/Database.php';
require_once '../classes/ShippingManager.php';
require_once '../classes/AuthManager.php';

$database = new Database();
$db = $database->getConnection();

$authManager = new AuthManager($db);
$authManager->requireRole('Verzending');

if(!isset($_GET['order_id'])) {
    die('Geen bestelling.');
}

$orderId = intval($_GET['order_id']);
$shippingManager = new ShippingManager($db);
$order = $shippingManager->getLabelData($orderId);

if(!$order) {
    die('Bestelling niet gevonden.');
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Label #<?php echo $order['id']; ?></title>
    <style>
        @media print {
            .no-print { display: none; }
        }
        body { font-family: Arial; padding: 20px; }
        .label { border: 3px solid #000; padding: 30px; max-width: 400px; margin: 0 auto; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 15px; margin-bottom: 20px; }
        .address { font-size: 16px; line-height: 1.6; margin-bottom: 20px; }
        .info { border-top: 1px dashed #000; padding-top: 15px; font-size: 12px; }
        .btn { display: block; margin: 20px auto; padding: 10px 30px; font-size: 16px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn" onclick="window.print()">Print als PDF</button>
        <p style="text-align:center;font-size:12px;">Kies "Opslaan als PDF" bij printen</p>
    </div>

    <div class="label">
        <div class="header">
            <h1 style="margin:0;font-size:18px;">VESUVIO SCOOTERSHOP</h1>
            <p style="margin:5px 0 0;">Adreslabel</p>
        </div>

        <div class="address">
            <?php if($order['company_name']): ?>
                <div><strong><?php echo htmlspecialchars($order['company_name']); ?></strong></div>
            <?php endif; ?>
            <div>t.a.v. <?php echo htmlspecialchars($order['recipient']); ?></div>
            <div><?php echo htmlspecialchars($order['addressline1']); ?></div>
            <div><?php echo htmlspecialchars($order['addressline2']); ?></div>
            <div><strong><?php echo htmlspecialchars($order['country']); ?></strong></div>
        </div>

        <div class="info">
            <p><strong>Bestelling:</strong> #<?php echo $order['id']; ?></p>
            <p><strong>Datum:</strong> <?php echo date('d-m-Y', strtotime($order['date'])); ?></p>
            <p><strong>Inhoud:</strong> <?php echo htmlspecialchars($order['parts_list']); ?></p>
        </div>
    </div>
</body>
</html>
