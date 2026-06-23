import ftplib

ftp_host = 'ftpupload.net'
ftp_user = 'if0_41967135'
ftp_pass = 'tiUzMXg7kcrfp'

base = 'C:/Users/Precode TI/Documents/GitHub/AgendaBarbearia'
remote_base = '/agendabarbearia.infinityfree.me/htdocs'

files = [
    ('app/Http/Kernel.php', '/app/Http/Kernel.php'),
    ('app/Http/Middleware/CheckRole.php', '/app/Http/Middleware/CheckRole.php'),
    ('app/Http/Controllers/Admin/BackupController.php', '/app/Http/Controllers/Admin/BackupController.php'),
    ('app/Observers/UserObserver.php', '/app/Observers/UserObserver.php'),
    ('routes/web.php', '/routes/web.php'),
]

try:
    ftp = ftplib.FTP(ftp_host, ftp_user, ftp_pass)
    print('Conectado ao FTP')

    for local_rel, remote_rel in files:
        local_path = base + '/' + local_rel
        remote_path = remote_base + remote_rel
        try:
            with open(local_path, 'rb') as f:
                ftp.storbinary('STOR ' + remote_path, f)
            print('OK: ' + local_rel)
        except Exception as e:
            print('Erro: ' + local_rel + ' - ' + str(e))

    ftp.quit()
    print('Upload de seguranca concluido!')
except Exception as e:
    print('Erro conexao: ' + str(e))
