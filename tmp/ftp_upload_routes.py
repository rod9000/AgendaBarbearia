import ftplib, os

ftp_host = "ftpupload.net"
ftp_user = "if0_41967135"
ftp_pass = "tiUzMXg7kcrfp"

BASE = "/agendabarbearia.infinityfree.me/htdocs"

files = [
    ("routes/api.php", f"{BASE}/routes/api.php"),
    ("routes/console.php", f"{BASE}/routes/console.php"),
    ("routes/channels.php", f"{BASE}/routes/channels.php"),
    ("routes/auth.php", f"{BASE}/routes/auth.php"),
    # Public files that might be missing
    ("public/.htaccess", f"{BASE}/public/.htaccess"),
    ("public/favicon.ico", f"{BASE}/public/favicon.ico"),
    ("public/robots.txt", f"{BASE}/public/robots.txt"),
]

try:
    ftp = ftplib.FTP(ftp_host, ftp_user, ftp_pass)
    for local, remote in files:
        if not os.path.exists(local):
            print(f"IGNORADO (não existe local): {local}")
            continue
        
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
    print("\nAcesse o site novamente.")
except Exception as e:
    print(f"Erro: {e}")
