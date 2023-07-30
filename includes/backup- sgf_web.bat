cls
SET PGPASSWORD=Orellana
pg_dump -U postgres -v -F c sgf_web > c:\wamp64\www\sgf_web.dump
cls
xcopy c:\wamp64\www\sgf_web.dump "G:\Mi unidad\CE10391\respaldo" /y
cls
