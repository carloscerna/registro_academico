<?php
$usuario = "postgres";
$contraseña = "Orellana"; // Usa tu contraseña real

// 1️⃣ Establecer la contraseña en el entorno
putenv("PGPASSWORD=$contraseña");

// 2️⃣ Ejecutar el comando para obtener las bases de datos
$comando = "psql -U $usuario -d postgres -t -c \"SELECT datname FROM pg_database WHERE datistemplate = false\"";
exec($comando, $bases, $resultado);

if ($resultado === 0) {
    // 3️⃣ Formatear salida para el <select>
    foreach ($bases as $base) {
        echo "<option value='" . trim($base) . "'>" . trim($base) . "</option>";
    }
} else {
    echo "<option>Error al obtener bases de datos</option>";
}
?>