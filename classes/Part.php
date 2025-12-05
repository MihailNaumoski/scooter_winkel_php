<?php
// Part class
class Part {
    private $id;
    private $part;
    private $purchasePrice;
    private $sellPrice;
    private $packed;

    public function __construct($part = '', $purchasePrice = 0, $sellPrice = 0) {
        $this->part = $part;
        $this->purchasePrice = $purchasePrice;
        $this->sellPrice = $sellPrice;
        $this->packed = 0;
    }

    public function getInfo() {
        return $this->part . " - €" . number_format($this->sellPrice, 2, ',', '.');
    }

    public function berekenWinst() {
        return $this->sellPrice - $this->purchasePrice;
    }

    public static function fromArray($data) {
        $part = new self();
        $part->setId($data['id']);
        $part->setPart($data['part']);
        $part->setPurchasePrice($data['purchase_price'] ?? 0);
        $part->setSellPrice($data['sell_price']);
        $part->setPacked($data['packed'] ?? 0);
        return $part;
    }

    // getters
    public function getId() { return $this->id; }
    public function getPart() { return $this->part; }
    public function getPurchasePrice() { return $this->purchasePrice; }
    public function getSellPrice() { return $this->sellPrice; }
    public function getPacked() { return $this->packed; }

    // setters
    public function setId($id) { $this->id = $id; }
    public function setPart($part) { $this->part = $part; }
    public function setPurchasePrice($purchasePrice) { $this->purchasePrice = $purchasePrice; }
    public function setSellPrice($sellPrice) { $this->sellPrice = $sellPrice; }
    public function setPacked($packed) { $this->packed = $packed; }
}
