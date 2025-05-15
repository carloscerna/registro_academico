<?php
$usuario = "postgres";
$contraseña = "Orellana"; // Cambia esto por tu contraseña
$dbName = $_POST["dbName"] ?? "";
$backupPath = $_POST["backupPath"] ?? "";

if (empty($dbName) || empty($backupPath)) {
    echo "Error: Debe seleccionar una base de datos y una ubicación para el respaldo.";
    exit;
}

putenv("PGPASSWORD=$contraseña");
$comando = "pg_dump -U $usuario -F c -b -v -f $backupPath/$dbName.backup $dbName";
exec($comando, $salida, $resultado);

echo $resultado === 0 ? "Respaldo guardado en $backupPath" : "Error al respaldar la base de datos.";