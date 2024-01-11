cls
SET PGPASSWORD=Orellana
pg_dump -U postgres -v -F c registro_academico > c:\wamp64\www\registro_academico_14753.dump
cls
xcopy c:\wamp64\www\registro_academico_14753.dump "H:\Mi unidad\14753\respaldo" /y
