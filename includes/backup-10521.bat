cls
SET PGPASSWORD=Orellana
pg_dump -U postgres -v -F c registro_academico_10521 > c:\wamp64\www\registro_academico_10521.dump
cls
xcopy c:\wamp64\www\registro_academico_10521.dump "G:\Mi unidad\CE10521\respaldo" /y
