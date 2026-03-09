<?php
// Script d'importation automatique des tables admin pour EL MAESTRO
// Détection automatique de la configuration Laragon

echo "🔧 IMPORTATION AUTOMATIQUE DES TABLES ADMIN - EL MAESTRO\n";
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
    echo "❌ MySQL Laragon non trouvé. Tentative avec MySQL système...\n";
    $mysqlPath = 'mysql';
}

echo "📍 MySQL trouvé: $mysqlPath\n\n";

// 2. Détecter les identifiants Laragon
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'el_maestro';

// Vérifier si le fichier de config Laragon existe
$laragonConfig = 'C:/laragon/etc/mysql/my.cnf';
if (file_exists($laragonConfig)) {
    echo "📄 Configuration Laragon détectée\n";
}

echo "🗄️ Base de données: $database\n";
echo "👤 Utilisateur: $user\n";
echo "🌐 Hôte: $host\n\n";

// 3. Importer les tables admin
$sqlFile = __DIR__ . '/database/admin_tables.sql';

if (!file_exists($sqlFile)) {
    echo "❌ Fichier SQL non trouvé: $sqlFile\n";
    exit(1);
}

echo "📂 Fichier SQL: $sqlFile\n";
echo "🔄 Début de l'importation...\n\n";

// Commande d'importation
$command = sprintf(
    '"%s" -h"%s" -u"%s" %s "%s" < "%s"',
    $mysqlPath,
    $host,
    $user,
    empty($password) ? '' : '-p"' . $password . '"',
    $database,
    $sqlFile
);

echo "⚡ Commande: $command\n\n";

// Exécuter la commande
$output = [];
$returnCode = 0;

exec($command, $output, $returnCode);

if ($returnCode === 0) {
    echo "✅ Tables admin importées avec succès!\n\n";
    echo "📋 Tables créées:\n";
    echo "   - admins\n";
    echo "   - admin_sessions\n\n";
    
    echo "👤 Administrateur par défaut:\n";
    echo "   Email: admin@elmaestro.bj\n";
    echo "   Mot de passe: admin123\n\n";
    
    echo "🎯 Prochaines étapes:\n";
    echo "   1. Démarrer le serveur backend: cd backend/api && php -S localhost:8080\n";
    echo "   2. Accéder au dashboard: http://localhost:5173/admin/login\n";
    echo "   3. S'authentifier avec les identifiants ci-dessus\n\n";
    
    echo "🎉 Dashboard admin EL MAESTRO prêt!\n";
} else {
    echo "❌ Erreur lors de l'importation!\n";
    echo "Code d'erreur: $returnCode\n";
    echo "Sortie: " . implode("\n", $output) . "\n\n";
    
    echo "🔧 Solutions possibles:\n";
    echo "   1. Vérifier que Laragon est en cours d'exécution\n";
    echo "   2. Vérifier les identifiants MySQL\n";
    echo "   3. Importer manuellement via phpMyAdmin\n\n";
}

echo "================================================\n";
echo "Appuyez sur une touche pour continuer...";
trim(fgets(STDIN));
?>
