<?php
require_once 'BaseManager.php';
require_once 'Order.php';

class ShippingManager extends BaseManager {
    protected $table = 'orders';

    public function getReadyToShipOrders() {
        $stmt = $this->db->prepare("
            SELECT o.* FROM orders o
            WHERE NOT EXISTS (SELECT 1 FROM orderrules r WHERE r.order_id = o.id AND r.packed = 0)
            AND (o.status = '' OR o.status IS NULL)
            ORDER BY o.date
        ");
        $stmt->execute();
        $orders = [];
        foreach($stmt->fetchAll() as $row) {
            $orders[] = Order::fromArray($row);
        }
        return $orders;
    }

    public function getOrderForShipping($orderId) {
        $stmt = $this->db->prepare("
            SELECT o.*, GROUP_CONCAT(p.part SEPARATOR ', ') as parts_list
            FROM orders o
            JOIN orderrules r ON o.id = r.order_id
            JOIN parts p ON r.part_id = p.id
            WHERE o.id = ? GROUP BY o.id
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetch();
    }

    public function markAsInDelivery($orderId) {
        $stmt = $this->db->prepare("UPDATE orders SET status = 'In bezorging' WHERE id = ?");
        return $stmt->execute([$orderId]);
    }

    public function getLabelData($orderId) {
        return $this->getOrderForShipping($orderId);
    }

    public function create($data) { return false; }
    public function update($id, $data) { return false; }
}
