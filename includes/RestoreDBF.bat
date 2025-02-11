cls
SET PGPASSWORD=Orellana
pg_restore.exe --host "localhost" --port "5432" --username "postgres" --no-password --role "postgres" --dbname "registro_academico_10391" --verbose "C:\wamp64\www\registro_academico\registro_academico_10391.backup"