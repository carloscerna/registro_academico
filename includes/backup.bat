@echo off
cls
SETLOCAL ENABLEDELAYEDEXPANSION

:: ==============================
:: Configuración
:: ==============================
SET PGPASSWORD=Orellana
SET "PGUSER=postgres"
SET "DBNAME=registro_academico_10391"
SET "BACKUPDIR=C:\wamp64\www"
SET "DRIVEDIR=H:\Mi unidad\10391\respaldo"
SET "EXTRADIR=D:\CE10391\respaldo"

:: ==============================
:: Construcción de fecha/hora segura
:: ==============================
SET "YYYY=%DATE:~6,4%"
SET "MM=%DATE:~3,2%"
SET "DD=%DATE:~0,2%"
SET "HH=%TIME:~0,2%"
SET "MN=%TIME:~3,2%"
SET "SS=%TIME:~6,2%"
SET "HH=%HH: =0%"

SET "DATESTAMP=%YYYY%%MM%%DD%_%HH%%MN%%SS%"
SET "FILENAME=%DBNAME%.dump"
SET "ZIPFILE=%DBNAME%.zip"
SET "LOGFILE=%BACKUPDIR%\backup_log_.txt"

:: ==============================
:: Respaldo local
:: ==============================
echo [%DATE% %TIME%] Iniciando respaldo de %DBNAME%... >> "%LOGFILE%"
echo [%DATE% %TIME%] Ejecutando: pg_dump -U %PGUSER% -v -F c %DBNAME% ^> "%BACKUPDIR%\%FILENAME%" >> "%LOGFILE%"

pg_dump -U %PGUSER% -v -F c %DBNAME% > "%BACKUPDIR%\%FILENAME%"

IF ERRORLEVEL 1 (
    echo [%DATE% %TIME%] ERROR: Falló pg_dump >> "%LOGFILE%"
    exit /b 1
) ELSE (
    for %%A in ("%BACKUPDIR%\%FILENAME%") do set SIZE=%%~zA
    echo [%DATE% %TIME%] Respaldo local creado: %FILENAME% (Tamaño: !SIZE! bytes) >> "%LOGFILE%"
)

:: ==============================
:: Compresión automática
:: ==============================
powershell -command "Compress-Archive -Path '%BACKUPDIR%\%FILENAME%' -DestinationPath '%BACKUPDIR%\%ZIPFILE%' -Force"
for %%A in ("%BACKUPDIR%\%ZIPFILE%") do set ZIPSIZE=%%~zA
echo [%DATE% %TIME%] Archivo comprimido: %ZIPFILE% (Tamaño: !ZIPSIZE! bytes) >> "%LOGFILE%"

:: ==============================
:: Copia al Drive
:: ==============================
echo [%DATE% %TIME%] Ejecutando: xcopy "%BACKUPDIR%\%ZIPFILE%" "%DRIVEDIR%\" /Y >> "%LOGFILE%"
xcopy "%BACKUPDIR%\%ZIPFILE%" "%DRIVEDIR%\" /Y >nul

IF ERRORLEVEL 1 (
    echo [%DATE% %TIME%] ERROR: Falló copia al Drive >> "%LOGFILE%"
) ELSE (
    for %%A in ("%DRIVEDIR%\%ZIPFILE%") do set DRIVESIZE=%%~zA
    echo [%DATE% %TIME%] Copia al Drive completada: %ZIPFILE% (Tamaño: !DRIVESIZE! bytes) >> "%LOGFILE%"
)

:: ==============================
:: Copia adicional a D:
:: ==============================
echo [%DATE% %TIME%] Ejecutando: xcopy "%BACKUPDIR%\%ZIPFILE%" "%EXTRADIR%\" /Y >> "%LOGFILE%"
xcopy "%BACKUPDIR%\%ZIPFILE%" "%EXTRADIR%\" /Y >nul

IF ERRORLEVEL 1 (
    echo [%DATE% %TIME%] ERROR: Falló copia a D:\ >> "%LOGFILE%"
) ELSE (
    for %%A in ("%EXTRADIR%\%ZIPFILE%") do set EXTRASIZE=%%~zA
    echo [%DATE% %TIME%] Copia adicional completada en D:\ (Tamaño: !EXTRASIZE! bytes) >> "%LOGFILE%"
)

:: ==============================
:: Rotación de respaldos (mantener últimos 7 días)
:: ==============================
forfiles /p "%BACKUPDIR%" /m *.dump /d -7 /c "cmd /c del @path"
forfiles /p "%BACKUPDIR%" /m *.zip /d -7 /c "cmd /c del @path"
forfiles /p "%DRIVEDIR%" /m *.zip /d -7 /c "cmd /c del @path"
forfiles /p "%EXTRADIR%" /m *.zip /d -7 /c "cmd /c del @path"

echo [%DATE% %TIME%] Rotación aplicada: eliminados respaldos mayores a 7 días >> "%LOGFILE%"

echo [%DATE% %TIME%] Proceso finalizado. >> "%LOGFILE%"
ENDLOCAL
exit
