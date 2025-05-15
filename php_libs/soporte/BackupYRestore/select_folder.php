<?php
$comando = 'powershell.exe -Command "[System.Windows.Forms.FolderBrowserDialog]::new().ShowDialog() | Out-Null; [System.Windows.Forms.FolderBrowserDialog]::new().SelectedPath"';
$carpeta = shell_exec($comando);

if (!empty($carpeta)) {
    echo trim($carpeta);
} else {
    echo "Error al seleccionar carpeta.";
}
?>