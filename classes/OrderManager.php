<?php
require_once 'BaseManager.php';
require_once 'Order.php';
require_once 'Part.php';

class OrderManager extends BaseManager {
    protected $table = 'orders';

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM orders ORDER BY date DESC");
        $stmt->execute();
        $orders = [];
        foreach($stmt->fetchAll() as $row) {
            $orders[] = Order::fromArray($row);
        }
        return $orders;
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ? Order::fromArray($data) : null;
    }

    public function getOrderWithParts($orderId) {
        $stmt = $this->db->prepare("
            SELECT o.id, o.date, o.recipient, o.company_name,
                o.addressline1, o.addressline2, o.country, o.status,
                p.id as part_id, p.part, p.sell_price, r.packed
            FROM orders o
            JOIN orderrules r ON o.id = r.order_id
            JOIN parts p ON r.part_id = p.id
            WHERE o.id = ?
        ");
        $stmt->execute([$orderId]);
        $rows = $stmt->fetchAll();

        if(empty($rows)) return null;

        $order = Order::fromArray($rows[0]);
        foreach($rows as $row) {
            $part = Part::fromArray([
                'id' => $row['part_id'],
                'part' => $row['part'],
                'purchase_price' => 0,
                'sell_price' => $row['sell_price'],
                'packed' => $row['packed']
            ]);
            $order->parts[] = $part;
        }
        return $order;
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO orders (date, recipient, company_name, addressline1, addressline2, country, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([
            $data['date'] ?? date('Y-m-d'),
            $data['recipient'],
            $data['company_name'] ?? '',
            $data['addressline1'],
            $data['addressline2'],
            $data['country'],
            $data['status'] ?? 'Nieuw'
        ]);
        return $result ? $this->db->lastInsertId() : false;
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE orders SET recipient = ?, company_name = ?,
            addressline1 = ?, addressline2 = ?, country = ? WHERE id = ?
        ");
        return $stmt->execute([
            $data['recipient'], $data['company_name'], $data['addressline1'],
            $data['addressline2'], $data['country'], $id
        ]);
    }

    public function updateStatus($orderId, $status) {
        $stmt = $this->db->prepare("UPDATE orders SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $orderId]);
    }

    public function getAllParts() {
        $stmt = $this->db->prepare("SELECT * FROM parts ORDER BY part");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getOrderPartIds($orderId) {
        $stmt = $this->db->prepare("SELECT part_id FROM orderrules WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $rows = $stmt->fetchAll();

        $ids = [];
        foreach($rows as $row) {
            $ids[] = $row['part_id'];
        }
        return $ids;
    }

    // parts updaten
    public function updateOrderParts($orderId, $partIds) {
        $stmt = $this->db->prepare("DELETE FROM orderrules WHERE order_id = ?");
        $stmt->execute([$orderId]);

        foreach($partIds as $partId) {
            $stmt = $this->db->prepare("INSERT INTO orderrules (order_id, part_id, packed) VALUES (?, ?, 0)");
            $stmt->execute([$orderId, $partId]);
        }
    }
}
