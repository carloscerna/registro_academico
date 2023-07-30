cls
SET PGPASSWORD=Orellana
pg_dump -U postgres -v -F c registro_academico > c:\wamp64\www\registro_academico.dump
cls
xcopy c:\wamp64\www\registro_academico.dump "G:\Mi unidad\CE10391\respaldo" /y
cls
