<?php
$usuario = "postgres";
$contraseña = "Orellana"; // Cambia esto por tu contraseña
$newDbName = $_POST["newDbName"] ?? "";
$backupPath = $_POST["backupPath"] ?? "";

if (empty($newDbName) || empty($backupPath)) {
    echo "Error: Debe ingresar el nuevo nombre de la base y la ubicación del respaldo.";
    exit;
}

putenv("PGPASSWORD=$contraseña");

$comando_create = "createdb -U $usuario $newDbName";
exec($comando_create);
$comando_restore = "pg_restore -U $usuario -d $newDbName -v $backupPath/$newDbName.backup";
exec($comando_restore, $salida, $resultado);

echo $resultado === 0 ? "Base de datos $newDbName restaurada correctamente." : "Error al restaurar la base de datos.";