@echo off
echo Création de la base de données el_maestro...

# Vérifier si MySQL est disponible
where mysql >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo MySQL n'est pas installé ou non trouvé dans le PATH
    echo Veuillez installer MySQL ou l'ajouter au PATH système
    echo.
    echo Options:
    echo 1. Installer MySQL (https://dev.mysql.com/downloads/mysql/)
    echo 2. Utiliser phpMyAdmin via Laragon
    echo 3. Utiliser HeidiSQL ou autre client MySQL
    echo.
    pause
    exit /b
)

# Demander le mot de passe MySQL
set /p mysql_password=Entrez le mot de passe MySQL (laisser vide si aucun): 

echo Création de la base de données...
mysql -u root -p%mysql_password% -e "CREATE DATABASE IF NOT EXISTS el_maestro;" 2>nul
if %ERRORLEVEL% neq 0 (
    echo Erreur lors de la création de la base de données
    echo Vérifiez que MySQL est démarré et les identifiants sont corrects
    pause
    exit /b
)

echo Importation des tables admin...
mysql -u root -p%mysql_password% el_maestro < database\admin_tables.sql 2>nul
if %ERRORLEVEL% neq 0 (
    echo Erreur lors de l'importation des tables admin
    pause
    exit /b
)

echo Importation des tables principales...
mysql -u root -p%mysql_password% el_maestro < database\database.sql 2>nul
if %ERRORLEVEL% neq 0 (
    echo Erreur lors de l'importation des tables principales
    pause
    exit /b
)

echo.
echo ========================================
echo Base de données el_maestro créée avec succès!
echo ========================================
echo.
echo Admin par défaut:
echo   Email: admin@elmaestro.bj
echo   Mot de passe: admin123
echo.
echo Accès admin:
echo   URL: http://localhost:5173/admin/login
echo.
echo Prochaines étapes:
echo   1. Démarrer le frontend: npm run dev
echo   2. Démarrer le backend: php -S localhost:8000 -t backend/api
echo.
pause
