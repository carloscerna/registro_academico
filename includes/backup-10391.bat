cls
SET PGPASSWORD=Orellana
pg_dump -U postgres -v -F c registro_academico_10391 > c:\wamp64\www\registro_academico_10391.dump
cls
xcopy c:\wamp64\www\registro_academico_10391.dump "G:\Mi unidad\CE10391\respaldo" /y