<?php
// Module D: Management (Rol: Management)
session_start();

require_once '../classes/Database.php';
require_once '../classes/ReportManager.php';
require_once '../classes/AuthManager.php';

// Database connectie
$database = new Database();
$db = $database->getConnection();

// Check toegang
$authManager = new AuthManager($db);
$authManager->requireRole('Management');

// Report manager
$reportManager = new ReportManager($db);

// Haal beschikbare maanden op
$availableMonths = $reportManager->getAvailableMonths();

// Standaard huidige maand
$selectedYear = $_GET['year'] ?? date('Y');
$selectedMonth = $_GET['month'] ?? date('m');

// Haal rapporten op
$salesPerPart = $reportManager->getSalesPerPart($selectedYear, $selectedMonth);
$totalRevenue = $reportManager->getTotalMonthlyRevenue($selectedYear, $selectedMonth);

// Maandnamen
$monthNames = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maart', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Augustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'December'
];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Rapportages - Vesuvio Scootershop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/nav.php'; ?>

    <div class="container">
        <h1>Management Rapportages</h1>

        <!-- Maand selectie -->
        <div class="month-selector">
            <form method="GET">
                <label>Selecteer maand:</label>
                <select name="month">
                    <?php for($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>" <?php echo ($m == $selectedMonth) ? 'selected' : ''; ?>>
                            <?php echo $monthNames[$m]; ?>
                        </option>
                    <?php endfor; ?>
                </select>

                <select name="year">
                    <?php for($y = 2024; $y <= date('Y'); $y++): ?>
                        <option value="<?php echo $y; ?>" <?php echo ($y == $selectedYear) ? 'selected' : ''; ?>>
                            <?php echo $y; ?>
                        </option>
                    <?php endfor; ?>
                </select>

                <button type="submit">Toon rapport</button>
            </form>
        </div>

        <h2><?php echo $monthNames[(int)$selectedMonth] . ' ' . $selectedYear; ?></h2>

        <!-- Totale omzet -->
        <div class="total-revenue">
            <h3>Totale omzet</h3>
            <p class="big-number">€<?php echo number_format($totalRevenue, 2, ',', '.'); ?></p>
        </div>

        <!-- Verkoop per onderdeel -->
        <h3>Verkoop per onderdeel</h3>
        <table>
            <thead>
                <tr>
                    <th>Onderdeel</th>
                    <th>Aantal verkocht</th>
                    <th>Prijs per stuk</th>
                    <th>Totale omzet</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($salesPerPart as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['part']); ?></td>
                        <td><?php echo $item['quantity_sold']; ?> stuks</td>
                        <td>€<?php echo number_format($item['sell_price'], 2, ',', '.'); ?></td>
                        <td>€<?php echo number_format($item['total_revenue'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
