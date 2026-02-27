@echo off
echo Création de la base de données el_maestro...
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS el_maestro;"

echo Importation des tables admin...
mysql -u root -p el_maestro < database/admin_tables.sql

echo Importation des tables principales...
mysql -u root -p el_maestro < database/database.sql

echo.
echo Base de données el_maestro créée avec succès!
echo.
echo Admin par défaut:
echo   Email: admin@elmaestro.bj
echo   Mot de passe: admin123
echo.
pause
