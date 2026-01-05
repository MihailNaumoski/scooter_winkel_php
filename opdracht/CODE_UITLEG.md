# Code Uitleg - Vesuvio Scootershop

Dit document legt alle code uit die is toegevoegd om te voldoen aan de examen-eisen.

---

## 1. Meerdere Rollen per Persoon

### Database: `personnel_roles` tabel

```sql
CREATE TABLE `personnel_roles` (
  `id` int NOT NULL,              -- unieke id voor elke rij
  `personnel_id` int NOT NULL,    -- verwijst naar personnel.id
  `role` varchar(50) NOT NULL     -- de rol (Management, Magazijn, Verzending)
) ENGINE=InnoDB;
```

**Waarom deze tabel?**
- De opdracht zegt: "Per persoon moeten er een of meerdere rollen kunnen worden toegevoegd"
- Met een aparte tabel kan 1 persoon meerdere rollen hebben
- Dit is een **veel-op-veel relatie** (M:N)

---

### PersonnelManager.php

```php
// haal rollen op voor een persoon
public function getRoles($personnelId) {
    // prepared statement voorkomt SQL injection
    $stmt = $this->db->prepare("SELECT role FROM personnel_roles WHERE personnel_id = ?");
    $stmt->execute([$personnelId]);  // ? wordt vervangen door $personnelId
    $rows = $stmt->fetchAll();       // haal alle resultaten op

    // maak array van rollen
    $roles = [];
    foreach($rows as $row) {
        $roles[] = $row['role'];     // voeg elke rol toe aan array
    }
    return $roles;                   // return bijv. ['Management', 'Magazijn']
}
```

**Uitleg per regel:**
1. `prepare()` - maakt een veilige SQL query klaar
2. `execute([$personnelId])` - voert query uit, vervangt ? met waarde
3. `fetchAll()` - haalt alle rijen op als array
4. `foreach` - loopt door elke rij
5. `$roles[]` - voegt rol toe aan het einde van array

```php
// sla rollen op (verwijder oude, voeg nieuwe toe)
public function saveRoles($personnelId, $roles) {
    // stap 1: verwijder alle bestaande rollen
    $stmt = $this->db->prepare("DELETE FROM personnel_roles WHERE personnel_id = ?");
    $stmt->execute([$personnelId]);

    // stap 2: voeg nieuwe rollen toe
    foreach($roles as $role) {
        $stmt = $this->db->prepare("INSERT INTO personnel_roles (personnel_id, role) VALUES (?, ?)");
        $stmt->execute([$personnelId, $role]);
    }
}
```

**Waarom eerst verwijderen?**
- Simpelste manier om rollen te updaten
- Geen check nodig of rol al bestaat
- Voorkomt dubbele rollen

---

### AuthManager.php

```php
// check of gebruiker rol heeft
public function hasRole($requiredRole) {
    if(!$this->isLoggedIn()) {
        return false;                        // niet ingelogd = geen toegang
    }

    $roles = $_SESSION['roles'] ?? [];       // haal rollen uit sessie, of lege array

    // management heeft altijd toegang
    if(in_array('Management', $roles)) {
        return true;
    }

    // check of gebruiker de rol heeft
    return in_array($requiredRole, $roles);  // true als rol in array zit
}
```

**Belangrijke functies:**
- `in_array($needle, $haystack)` - checkt of waarde in array zit
- `$_SESSION['roles'] ?? []` - null coalescing: als niet bestaat, gebruik lege array

---

### personnel.php - Checkboxes voor rollen

```php
<div class="checkbox-group">
    <?php foreach($availableRoles as $role): ?>
        <?php
        // check of rol geselecteerd is
        $checked = '';
        if($editPerson && in_array($role, $editPerson['roles'])) {
            $checked = 'checked';    // HTML attribuut dat checkbox aanvinkt
        }
        ?>
        <label class="checkbox-label">
            <input type="checkbox" name="roles[]" value="<?php echo $role; ?>" <?php echo $checked; ?>>
            <?php echo $role; ?>
        </label>
    <?php endforeach; ?>
</div>
```

**Belangrijk: `name="roles[]"`**
- De `[]` zorgt ervoor dat PHP een array maakt van alle geselecteerde checkboxes
- Als je 2 checkboxes aanvinkt, krijg je: `$_POST['roles'] = ['Management', 'Magazijn']`

---

## 2. PDF Adreslabel

### label.php - HTML pagina die als PDF kan worden geprint

```php
<?php
// check toegang - alleen verzending mag dit zien
$authManager = new AuthManager($db);
$authManager->requireRole('Verzending');

// haal order data op
$orderId = intval($_GET['order_id']);     // intval() voorkomt SQL injection
$order = $shippingManager->getLabelData($orderId);
?>
```

**HTML met print CSS:**

```html
<style>
    /* @media print - CSS die alleen geldt bij printen */
    @media print {
        body { margin: 0; }
        .no-print { display: none; }    /* verberg print-knop bij printen */
    }
</style>

<!-- Print knop -->
<button onclick="window.print()">Print als PDF</button>
```

**Hoe werkt dit?**
1. Gebruiker klikt "In bezorging" op verzendpagina
2. Order status wordt geupdate naar "In bezorging"
3. Browser opent label.php
4. Gebruiker klikt "Print als PDF"
5. Browser toont printdialoog → kies "Opslaan als PDF"

**Waarom geen echte PDF library?**
- Simpeler voor een schoolproject
- Geen externe dependencies nodig
- Browser print-to-PDF werkt prima

---

## 3. Bewerken van Bestelde Artikelen

### OrderManager.php - Nieuwe methodes

```php
// haal alle beschikbare onderdelen op
public function getAllParts() {
    $stmt = $this->db->prepare("SELECT * FROM parts ORDER BY part");
    $stmt->execute();
    return $stmt->fetchAll();    // return alle parts als array
}

// haal part_ids van een bestelling
public function getOrderPartIds($orderId) {
    $stmt = $this->db->prepare("SELECT part_id FROM orderrules WHERE order_id = ?");
    $stmt->execute([$orderId]);
    $rows = $stmt->fetchAll();

    $ids = [];
    foreach($rows as $row) {
        $ids[] = $row['part_id'];    // alleen de id's, niet hele part
    }
    return $ids;                      // bijv. [1, 3, 5]
}

// update onderdelen van bestelling
public function updateOrderParts($orderId, $partIds) {
    // stap 1: verwijder oude onderdelen
    $stmt = $this->db->prepare("DELETE FROM orderrules WHERE order_id = ?");
    $stmt->execute([$orderId]);

    // stap 2: voeg nieuwe onderdelen toe
    foreach($partIds as $partId) {
        $stmt = $this->db->prepare(
            "INSERT INTO orderrules (order_id, part_id, packed) VALUES (?, ?, 0)"
        );
        $stmt->execute([$orderId, $partId]);
        // packed = 0 betekent: niet ingepakt
    }
}
```

---

### index.php - Edit formulier met checkboxes

```php
// haal data op voor edit mode
if(isset($_GET['edit'])) {
    $editOrder = $orderManager->getOrderWithParts(intval($_GET['edit']));
    $allParts = $orderManager->getAllParts();              // alle beschikbare parts
    $currentPartIds = $orderManager->getOrderPartIds(...); // huidige geselecteerde parts
}
```

```php
// bij form submit: update ook de parts
$selectedParts = $_POST['parts'] ?? [];    // array van geselecteerde part ids
$orderManager->updateOrderParts($orderId, $selectedParts);
```

**HTML checkbox voor parts:**

```php
<?php foreach($allParts as $part): ?>
    <?php
    // check of part al in bestelling zit
    $checked = in_array($part['id'], $currentPartIds) ? 'checked' : '';
    ?>
    <label class="checkbox-label">
        <input type="checkbox" name="parts[]" value="<?php echo $part['id']; ?>" <?php echo $checked; ?>>
        <?php echo $part['part']; ?> - €<?php echo number_format($part['sell_price'], 2, ',', '.'); ?>
    </label>
<?php endforeach; ?>
```

**`number_format()` uitleg:**
- `number_format($getal, 2, ',', '.')`
- `2` = 2 decimalen
- `,` = decimaal separator (Nederlands)
- `.` = duizendtal separator

---

## 4. Navigatie met Meerdere Rollen

### nav.php

```php
// oude code (1 rol):
$userRole = $_SESSION['role'] ?? '';
if($userRole === 'Magazijn') { ... }

// nieuwe code (meerdere rollen):
$userRoles = $_SESSION['roles'] ?? [];     // array van rollen
if(in_array('Magazijn', $userRoles)) { ... }
```

**Waarom `in_array()` in plaats van `===`?**
- `===` vergelijkt 2 waarden exact
- `in_array()` checkt of waarde ergens in array zit
- Gebruiker kan nu meerdere rollen hebben

---

## 5. CSS voor Checkboxes

```css
.checkbox-group {
    background-color: white;
    padding: 1rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    max-height: 200px;    /* maximale hoogte */
    overflow-y: auto;      /* scroll als te veel items */
}

.checkbox-label {
    display: block;        /* elke checkbox op eigen regel */
    padding: 0.5rem;
    cursor: pointer;       /* hand cursor bij hover */
    font-weight: normal;   /* overschrijf bold van form labels */
    margin: 0;
}

.checkbox-label:hover {
    background-color: #f9f9f9;    /* highlight bij hover */
}
```

---

## Samenvatting OOP Concepten

| Concept | Waar toegepast |
|---------|----------------|
| **Encapsulatie** | Private `$db` in AuthManager, getters/setters in Order/Part |
| **Overerving** | PersonnelManager `extends` BaseManager |
| **Polymorfisme** | `create()` en `update()` overschreven in child classes |
| **Prepared Statements** | Alle database queries gebruiken `?` placeholders |

---

## Database Aanpassingen

Om de nieuwe functionaliteit te gebruiken, voer dit SQL uit:

```sql
-- Voeg personnel_roles tabel toe
CREATE TABLE personnel_roles (
  id int NOT NULL AUTO_INCREMENT,
  personnel_id int NOT NULL,
  role varchar(50) NOT NULL,
  PRIMARY KEY (id),
  KEY personnel_id (personnel_id),
  FOREIGN KEY (personnel_id) REFERENCES personnel(id) ON DELETE CASCADE
);

-- Migreer bestaande rollen (als je oude data hebt)
INSERT INTO personnel_roles (personnel_id, role)
SELECT id, role FROM personnel WHERE role IS NOT NULL AND role != '';

-- Verwijder oude role kolom (optioneel)
ALTER TABLE personnel DROP COLUMN role;
```
