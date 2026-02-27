<?php
class Plate {
    private $conn;
    private $table_name = "plates";

    public $id;
    public $name;
    public $description;
    public $price;
    public $category;
    public $image;
    public $rating;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll() {
        $query = "SELECT p.*, AVG(r.rating) as avg_user_rating 
                  FROM " . $this->table_name . " p
                  LEFT JOIN reviews r ON p.id = r.plate_id
                  GROUP BY p.id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function addReview($plate_id, $user_name, $rating, $comment) {
        if ($rating < 1 || $rating > 6) return false;
        $query = "INSERT INTO reviews (plate_id, user_name, rating, comment) VALUES (:p, :u, :r, :c)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":p", $plate_id);
        $stmt->bindParam(":u", $user_name);
        $stmt->bindParam(":r", $rating);
        $stmt->bindParam(":c", $comment);
        return $stmt->execute();
    }
}
?>
