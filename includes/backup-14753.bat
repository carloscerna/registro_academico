cls
SET PGPASSWORD=Orellana
pg_dump -U postgres -v -F c registro_academico_14753 > c:\wamp64\www\registro_academico_14753.dump
cls
xcopy c:\wamp64\www\registro_academico_14753.dump "G:\Mi unidad\CE10391\respaldo" /y
