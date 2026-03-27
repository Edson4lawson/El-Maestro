<?php
// Script d'importation complète de la base de données EL MAESTRO
echo "🔧 IMPORTATION COMPLÈTE DE LA BASE DE DONNÉES EL MAESTRO\n";
echo "================================================\n\n";

// 1. Détecter la configuration Laragon
$laragonPaths = [
    'C:/laragon/bin/mysql/mysql-8.0.30-winx64/bin/mysql.exe',
    'C:/laragon/bin/mysql/bin/mysql.exe',
    'C:/laragon/bin/mysql/mysql.exe',
    'C:/laragon/bin/mysql/current/bin/mysql.exe'
];

$mysqlPath = null;
foreach ($laragonPaths as $path) {
    if (file_exists($path)) {
        $mysqlPath = $path;
        break;
    }
}

if (!$mysqlPath) {
    die("❌ MySQL non trouvé. Vérifiez l'installation Laragon.\n");
}

echo "📍 MySQL trouvé: $mysqlPath\n";
echo "🗄️ Base de données: el_maestro\n";
echo "👤 Utilisateur: root\n";
echo "🌐 Hôte: localhost\n\n";

// 2. Importer la base de données principale
$databaseFile = __DIR__ . '/backend/api/database.sql';
if (!file_exists($databaseFile)) {
    die("❌ Fichier database.sql non trouvé.\n");
}

echo "📂 Fichier SQL principal: $databaseFile\n";
echo "🔄 Début de l'importation de la base de données...\n";

$command = '"' . $mysqlPath . '" -h"localhost" -u"root" "el_maestro" < "' . $databaseFile . '"';

exec($command . ' 2>&1', $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ Base de données importée avec succès!\n";
} else {
    echo "⚠️ Erreur lors de l'importation de la base de données:\n";
    echo implode("\n", $output) . "\n\n";
    echo "🔧 Tentative de création de la base de données...\n";
    
    // Créer la base si elle n'existe pas
    $createDbCommand = '"' . $mysqlPath . '" -h"localhost" -u"root" -e "CREATE DATABASE IF NOT EXISTS el_maestro;"';
    exec($createDbCommand . ' 2>&1', $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "✅ Base de données créée. Réessayez l'importation.\n";
    }
}

// 3. Importer les tables admin
$adminTablesFile = __DIR__ . '/database/admin_tables.sql';
if (file_exists($adminTablesFile)) {
    echo "\n📂 Fichier tables admin: $adminTablesFile\n";
    echo "🔄 Importation des tables admin...\n";
    
    $adminCommand = '"' . $mysqlPath . '" -h"localhost" -u"root" "el_maestro" < "' . $adminTablesFile . '"';
    exec($adminCommand . ' 2>&1', $output, $returnCode);
    
    if ($returnCode === 0) {
        echo "✅ Tables admin importées avec succès!\n";
    } else {
        echo "⚠️ Erreur lors de l'importation des tables admin:\n";
        echo implode("\n", $output) . "\n";
    }
}

echo "\n🎯 Prochaines étapes:\n";
echo "   1. Démarrer le serveur backend: cd backend/api && php -S localhost:8080\n";
echo "   2. Accéder à l'admin: http://localhost:5173/admin/login\n";
echo "   3. Identifiants: admin@elmaestro.bj / admin123\n\n";

echo "🎉 Base de données EL MAESTRO prête!\n";
echo "==========================================\n";
?>
