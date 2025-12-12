<?php
require_once 'BaseManager.php';

class ReportManager extends BaseManager {
    protected $table = 'orders';

    public function getSalesPerPart($year, $month) {
        $stmt = $this->db->prepare("
            SELECT p.id, p.part, COUNT(r.id) as quantity_sold, p.sell_price,
                   SUM(p.sell_price) as total_revenue
            FROM parts p
            LEFT JOIN orderrules r ON p.id = r.part_id
            LEFT JOIN orders o ON r.order_id = o.id AND YEAR(o.date) = ? AND MONTH(o.date) = ?
            GROUP BY p.id ORDER BY total_revenue DESC
        ");
        $stmt->execute([$year, $month]);
        return $stmt->fetchAll();
    }

    public function getTotalMonthlyRevenue($year, $month) {
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(p.sell_price), 0) as total
            FROM orders o
            JOIN orderrules r ON o.id = r.order_id
            JOIN parts p ON r.part_id = p.id
            WHERE YEAR(o.date) = ? AND MONTH(o.date) = ?
        ");
        $stmt->execute([$year, $month]);
        return $stmt->fetch()['total'] ?? 0;
    }

    public function getAvailableMonths() {
        $stmt = $this->db->prepare("
            SELECT DISTINCT YEAR(date) as `year`, MONTH(date) as `month`
            FROM orders ORDER BY `year` DESC, `month` DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function create($data) { return false; }
    public function update($id, $data) { return false; }
}
