<?php
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['name'] ?? '';
$userRoles = $_SESSION['roles'] ?? [];
?>
<nav class="main-nav">
    <div class="nav-container">
        <a href="/index.php" class="logo">Vesuvio Scootershop</a>

        <ul class="nav-menu">
            <li><a href="/index.php">Bestellingen</a></li>

            <?php if($isLoggedIn): ?>
                <?php if(in_array('Magazijn', $userRoles) || in_array('Management', $userRoles)): ?>
                    <li><a href="/pages/warehouse.php">Magazijn</a></li>
                <?php endif; ?>

                <?php if(in_array('Verzending', $userRoles) || in_array('Management', $userRoles)): ?>
                    <li><a href="/pages/shipping.php">Verzending</a></li>
                <?php endif; ?>

                <?php if(in_array('Management', $userRoles)): ?>
                    <li><a href="/pages/management.php">Rapportages</a></li>
                    <li><a href="/pages/personnel.php">Personeel</a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>

        <div class="nav-user">
            <?php if($isLoggedIn): ?>
                <span>Welkom, <?php echo htmlspecialchars($userName); ?> (<?php echo htmlspecialchars(implode(', ', $userRoles)); ?>)</span>
                <a href="/logout.php" class="btn-logout">Uitloggen</a>
            <?php else: ?>
                <a href="/login.php" class="btn-login">Inloggen</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
