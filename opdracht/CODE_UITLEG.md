# code uitleg

dit zijn mijn aantekeningen over wat ik heb gemaakt en waarom

## meerdere rollen

in de opdracht staat dat personeel meerdere rollen moet kunnen hebben. eerst had ik gewoon een `role` kolom in personnel maar dan kan je maar 1 rol opslaan

dus ik heb een nieuwe tabel gemaakt:

```sql
CREATE TABLE personnel_roles (
  id int NOT NULL AUTO_INCREMENT,
  personnel_id int NOT NULL,
  role varchar(50) NOT NULL,
  PRIMARY KEY (id)
);
```

nu kan jan bijvoorbeeld zowel Management als Magazijn zijn

### hoe het werkt in php

in PersonnelManager heb ik functies gemaakt om rollen op te halen en op te slaan:

```php
public function getRoles($personnelId) {
    $stmt = $this->db->prepare("SELECT role FROM personnel_roles WHERE personnel_id = ?");
    $stmt->execute([$personnelId]);
    $rows = $stmt->fetchAll();

    $roles = [];
    foreach($rows as $row) {
        $roles[] = $row['role'];
    }
    return $roles;
}
```

bij opslaan delete ik eerst alle oude rollen en dan insert ik de nieuwe. dat is makkelijker dan checken welke er al zijn

### checkboxes in het formulier

voor de rollen gebruik ik checkboxes met `name="roles[]"`. die [] is belangrijk want dan maakt php er een array van. als je 2 checkboxes aanvinkt krijg je `$_POST['roles'] = ['Management', 'Magazijn']`

## toegang checken

in AuthManager check ik of iemand een bepaalde rol heeft:

```php
public function hasRole($requiredRole) {
    if(!$this->isLoggedIn()) {
        return false;
    }

    $roles = $_SESSION['roles'] ?? [];

    // management mag altijd alles
    if(in_array('Management', $roles)) {
        return true;
    }

    return in_array($requiredRole, $roles);
}
```

ik gebruik `in_array()` om te checken of de rol in de array zit. management heeft altijd toegang, die hoeven niet specifieke rol te hebben

## pdf labels

voor verzending moet je een adres label kunnen printen. ik heb geen pdf library gebruikt want dat is veel gedoe. in plaats daarvan heb ik gewoon een html pagina gemaakt die je kan printen als pdf

```php
$authManager->requireRole('Verzending');
$orderId = intval($_GET['order_id']);
$order = $shippingManager->getLabelData($orderId);
```

de css zorgt dat de print knop niet mee geprint wordt:

```css
@media print {
    .no-print { display: none; }
}
```

je klikt gewoon op print en dan kies je "opslaan als pdf" in de browser

## bewerken van onderdelen

bij orders moet je kunnen aanpassen welke onderdelen erbij zitten. werkt ongeveer hetzelfde als rollen - checkboxes met `name="parts[]"` en bij opslaan eerst delete dan insert

```php
public function updateOrderParts($orderId, $partIds) {
    // eerst oude weg
    $stmt = $this->db->prepare("DELETE FROM orderrules WHERE order_id = ?");
    $stmt->execute([$orderId]);

    // dan nieuwe toevoegen
    foreach($partIds as $partId) {
        $stmt = $this->db->prepare("INSERT INTO orderrules (order_id, part_id, packed) VALUES (?, ?, 0)");
        $stmt->execute([$orderId, $partId]);
    }
}
```

## navigatie

in nav.php check ik welke menu items iemand mag zien. vroeger was het `$_SESSION['role']` maar nu is het een array dus gebruik ik `in_array()`:

```php
$userRoles = $_SESSION['roles'] ?? [];
if(in_array('Magazijn', $userRoles)) {
    // toon magazijn link
}
```

## oop dingen

wat ik heb gebruikt:
- encapsulatie: de $db variabele is private in de managers
- overerving: PersonnelManager extends BaseManager
- polymorfisme: create() en update() zijn anders in elke manager
- prepared statements: alle queries met ? zodat je geen sql injection krijgt
