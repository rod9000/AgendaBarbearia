import ftplib

ftp_host = 'ftpupload.net'
ftp_user = 'if0_41967135'
ftp_pass = 'tiUzMXg7kcrfp'

base = 'C:/Users/Precode TI/Documents/GitHub/AgendaBarbearia'
remote_base = '/agendabarbearia.infinityfree.me/htdocs'

files = [
    ('resources/views/components/application-logo.blade.php', '/resources/views/components/application-logo.blade.php'),
    ('app/Http/Kernel.php', '/app/Http/Kernel.php'),
    ('app/Models/Company.php', '/app/Models/Company.php'),
    ('app/Models/User.php', '/app/Models/User.php'),
    ('app/Http/Middleware/CheckTrial.php', '/app/Http/Middleware/CheckTrial.php'),
    ('resources/views/trial/expired.blade.php', '/resources/views/trial/expired.blade.php'),
    ('routes/web.php', '/routes/web.php'),
    ('database/migrations/2026_06_22_000001_create_companies_table.php', '/database/migrations/2026_06_22_000001_create_companies_table.php'),
    ('database/migrations/2026_06_22_000002_add_company_id_to_users_table.php', '/database/migrations/2026_06_22_000002_add_company_id_to_users_table.php'),
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
    print('Upload concluido!')
except Exception as e:
    print('Erro conexao: ' + str(e))
