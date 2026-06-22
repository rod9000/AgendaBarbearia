import ftplib
import os

ftp_host = "ftpupload.net"
ftp_user = "if0_41967135"
ftp_pass = "tiUzMXg7kcrfp"

try:
    ftp = ftplib.FTP(ftp_host, ftp_user, ftp_pass)
    print("Conectado ao FTP")

    # Upload test_db.php to /agendabarbearia.infinityfree.me/htdocs/tmp/
    local_file = "tmp/test_db.php"
    remote_path = "/agendabarbearia.infinityfree.me/htdocs/tmp/test_db.php"

    # Ensure remote dir exists
    remote_dir = os.path.dirname(remote_path)
    parts = remote_dir.strip('/').split('/')
    current = ''
    for part in parts:
        current += '/' + part
        try:
            ftp.cwd(current)
        except:
            ftp.mkd(current)
            ftp.cwd(current)
    ftp.cwd('/')

    with open(local_file, 'rb') as f:
        ftp.storbinary(f'STOR {remote_path}', f)
    print(f"Upload OK: {remote_path}")
    print(f"\nAcesse: https://agendabarbearia.infinityfree.me/tmp/test_db.php")

    ftp.quit()
except Exception as e:
    print(f"Erro: {e}")
