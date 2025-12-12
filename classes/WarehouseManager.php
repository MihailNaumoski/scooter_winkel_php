<?php
require_once 'BaseManager.php';

class WarehouseManager extends BaseManager {
    protected $table = 'orderrules';

    public function getUnpackedItems() {
        $stmt = $this->db->prepare("
            SELECT r.id, r.order_id, r.packed, o.recipient, o.date, p.part
            FROM orderrules r
            JOIN orders o ON r.order_id = o.id
            JOIN parts p ON r.part_id = p.id
            WHERE r.packed = 0
            ORDER BY o.date ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function markAsPacked($orderruleId) {
        $stmt = $this->db->prepare("UPDATE orderrules SET packed = 1 WHERE id = ?");
        return $stmt->execute([$orderruleId]);
    }

    public function isOrderFullyPacked($orderId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM orderrules WHERE order_id = ? AND packed = 0");
        $stmt->execute([$orderId]);
        return $stmt->fetch()['cnt'] == 0;
    }

    public function create($data) { return false; }

    public function update($id, $data) {
        if(isset($data['packed'])) {
            $stmt = $this->db->prepare("UPDATE orderrules SET packed = ? WHERE id = ?");
            return $stmt->execute([$data['packed'], $id]);
        }
        return false;
    }
}
