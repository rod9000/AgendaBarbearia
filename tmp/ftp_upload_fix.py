import ftplib, os

ftp_host = "ftpupload.net"
ftp_user = "if0_41967135"
ftp_pass = "tiUzMXg7kcrfp"

BASE = "/agendabarbearia.infinityfree.me/htdocs"

files = [
    ("app/Providers/AppServiceProvider.php", f"{BASE}/app/Providers/AppServiceProvider.php"),
    ("_migrate.php", f"{BASE}/_migrate.php"),
]

try:
    ftp = ftplib.FTP(ftp_host, ftp_user, ftp_pass)
    for local, remote in files:
        remote_dir = os.path.dirname(remote)
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
        
        with open(local, 'rb') as f:
            ftp.storbinary(f"STOR {remote}", f)
        print(f"OK: {local}")
    
    ftp.quit()
    print("\nAcesse: https://agendabarbearia.infinityfree.me/_migrate.php")
except Exception as e:
    print(f"Erro: {e}")
