@echo off
echo === Copiando archivo SQL al contenedor ===
docker cp src\db_socios.sql juntavecinos-mysql:/db_socios.sql

if %errorlevel% neq 0 (
    echo ❌ Error al copiar el archivo al contenedor.
    pause
    exit /b
)

echo === Ejecutando restauración en la base de datos 'socios' ===
docker exec -i juntavecinos-mysql sh -c "mysql -u app_user -p12345 socios < /db_socios.sql"

if %errorlevel% neq 0 (
    echo ❌ Error al ejecutar el script SQL.
) else (
    echo ✅ Restauración completada con éxito.
)

pause