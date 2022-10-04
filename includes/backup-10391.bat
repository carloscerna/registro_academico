cls
SET PGPASSWORD=Orellana
pg_dump -U postgres -v -F c registro_academico_10391 > c:\wamp64\www\registro_academico_10391.dump
cls
xcopy c:\wamp64\www\registro_academico_10391.dump "E:\Mi unidad\CE10391\respaldo" /y
xcopy c:\wamp64\www\registro_academico_10391.dump "D:\CE10391\respaldo" /y

xcopy c:\wamp64\www\registro_academico_10391.dump "C:\Users\MINEDUCYT\OneDrive - Ministerio de Educación, Gobierno de El Salvador\CE10391\Respaldo" /Y