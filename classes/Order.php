<?php
// Order class
class Order {
    private $id;
    private $date;
    private $companyName;
    private $recipient;
    private $addressline1;
    private $addressline2;
    private $country;
    private $status;
    public $parts = [];

    public function __construct($recipient = '', $addressline1 = '', $addressline2 = '', $country = '') {
        $this->recipient = $recipient;
        $this->addressline1 = $addressline1;
        $this->addressline2 = $addressline2;
        $this->country = $country;
        $this->date = date('Y-m-d');
        $this->status = 'Nieuw';
    }

    public function getInfo() {
        return $this->recipient . " - " . $this->country;
    }

    // totaal berekenen
    public function berekenTotaal() {
        $totaal = 0;
        foreach ($this->parts as $part) {
            $totaal += $part->getSellPrice();
        }
        return $totaal;
    }

    // maakt object van db rij
    public static function fromArray($data) {
        $order = new self();
        $order->setId($data['id']);
        $order->setDate($data['date']);
        $order->setCompanyName($data['company_name'] ?? '');
        $order->setRecipient($data['recipient']);
        $order->setAddressline1($data['addressline1']);
        $order->setAddressline2($data['addressline2']);
        $order->setCountry($data['country']);
        $order->setStatus($data['status'] ?? '');
        return $order;
    }

    // getters
    public function getId() { return $this->id; }
    public function getDate() { return $this->date; }
    public function getCompanyName() { return $this->companyName; }
    public function getRecipient() { return $this->recipient; }
    public function getAddressline1() { return $this->addressline1; }
    public function getAddressline2() { return $this->addressline2; }
    public function getCountry() { return $this->country; }
    public function getStatus() { return $this->status; }

    // setters
    public function setId($id) { $this->id = $id; }
    public function setDate($date) { $this->date = $date; }
    public function setCompanyName($companyName) { $this->companyName = $companyName; }
    public function setRecipient($recipient) { $this->recipient = $recipient; }
    public function setAddressline1($addressline1) { $this->addressline1 = $addressline1; }
    public function setAddressline2($addressline2) { $this->addressline2 = $addressline2; }
    public function setCountry($country) { $this->country = $country; }
    public function setStatus($status) { $this->status = $status; }
}
