<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=el_maestro', 'root', '');
    $stmt = $db->query('DESCRIBE plates');
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo 'Colonnes: ' . implode(', ', $columns);
} catch (Exception $e) {
    echo 'Erreur: ' . $e->getMessage();
}
?>
