<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=el_maestro', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ajouter la colonne is_available si elle n'existe pas
    $alterQuery = "ALTER TABLE plates ADD COLUMN is_available BOOLEAN DEFAULT TRUE";
    $db->exec($alterQuery);
    echo "Colonne is_available ajoutée avec succès\n";
    
    // Vérifier que tous les plats sont maintenant disponibles
    $updateQuery = "UPDATE plates SET is_available = TRUE WHERE is_available IS NULL";
    $stmt = $db->exec($updateQuery);
    echo "Plats mis à jour: $stmt lignes\n";
    
    // Vérifier la structure finale
    $stmt = $db->query('DESCRIBE plates');
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Structure finale: " . implode(', ', $columns) . "\n";
    
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
?>
