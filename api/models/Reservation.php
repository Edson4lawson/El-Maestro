<?php
class Reservation {
    private $conn;
    private $table_name = "reservations";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  (customer_name, customer_email, reservation_date, reservation_time, people_count, special_request) 
                  VALUES (:name, :email, :date, :time, :people, :request)";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":name", $data->name);
        $stmt->bindParam(":email", $data->email);
        $stmt->bindParam(":date", $data->date);
        $stmt->bindParam(":time", $data->time);
        $stmt->bindParam(":people", $data->people);
        $stmt->bindParam(":request", $data->message);

        return $stmt->execute();
    }
}
?>
