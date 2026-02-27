<?php
class Order {
    private $conn;
    private $table_name = "orders";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (customer_name, customer_phone, customer_address, total_price, payment_method, tracking_number) 
                  VALUES (:name, :phone, :address, :total, :method, :tracking)";
        
        $stmt = $this->conn->prepare($query);
        $tracking = 'MAESTRO-' . strtoupper(uniqid());

        $stmt->bindParam(":name", $data->name);
        $stmt->bindParam(":phone", $data->phone);
        $stmt->bindParam(":address", $data->address);
        $stmt->bindParam(":total", $data->total);
        $stmt->bindParam(":method", $data->payment_method);
        $stmt->bindParam(":tracking", $tracking);

        if ($stmt->execute()) {
            $order_id = $this->conn->lastInsertId();
            // Store items
            foreach ($data->items as $item) {
                $this->addItem($order_id, $item);
            }
            return $tracking;
        }
        return false;
    }

    private function addItem($order_id, $item) {
        $query = "INSERT INTO order_items (order_id, plate_id, quantity, price_at_time) 
                  VALUES (:oid, :pid, :qty, :price)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":oid", $order_id);
        $stmt->bindParam(":pid", $item->id);
        $stmt->bindParam(":qty", $item->quantity);
        $stmt->bindParam(":price", $item->price);
        $stmt->execute();
    }
}
?>
