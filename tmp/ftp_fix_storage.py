import ftplib

ftp_host = "ftpupload.net"
ftp_user = "if0_41967135"
ftp_pass = "tiUzMXg7kcrfp"

BASE = "/agendabarbearia.infinityfree.me/htdocs"

dirs_to_ensure = [
    "storage/framework/cache/data",
    "storage/framework/sessions",
    "storage/framework/testing",
    "storage/framework/views",
    "storage/logs",
    "storage/app/public",
]

try:
    ftp = ftplib.FTP(ftp_host, ftp_user, ftp_pass)
    
    for d in dirs_to_ensure:
        full = f"{BASE}/{d}"
        parts = full.strip('/').split('/')
        current = ''
        for part in parts:
            current += '/' + part
            try:
                ftp.cwd(current)
            except:
                ftp.mkd(current)
                ftp.cwd(current)
        print(f"OK: {d}")
    
    ftp.cwd("/")
    
    # Also ensure bootstrap/cache exists
    try:
        ftp.cwd(f"{BASE}/bootstrap/cache")
        print("OK: bootstrap/cache")
    except:
        ftp.mkd(f"{BASE}/bootstrap/cache")
        print("CRIADO: bootstrap/cache")
    
    ftp.quit()
    print("\nEstrutura de diretorios garantida.")
except Exception as e:
    print(f"Erro: {e}")
