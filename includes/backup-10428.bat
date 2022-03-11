cls
SET PGPASSWORD=Orellana
pg_dump -U postgres -v -F c registro_academico_10428 > c:\wamp64\www\registro_academico_10428.dump
cls
xcopy c:\wamp64\www\registro_academico_10428.dump "G:\Mi unidad\CE10428\respaldo" /y