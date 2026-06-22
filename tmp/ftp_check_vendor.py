import ftplib, io

ftp_host = "ftpupload.net"
ftp_user = "if0_41967135"
ftp_pass = "tiUzMXg7kcrfp"

try:
    ftp = ftplib.FTP(ftp_host, ftp_user, ftp_pass)
    ftp.cwd("/agendabarbearia.infinityfree.me/htdocs")
    
    # Check critical files
    files_to_check = [
        "vendor/autoload.php",
        "vendor/composer/installed.json",
        "bootstrap/app.php",
        "artisan",
        "index.php",
    ]
    
    for f in files_to_check:
        try:
            ftp.size(f)
            print(f"OK: {f}")
        except:
            print(f"AUSENTE: {f}")
    
    # List vendor dir by top-level dirs
    print("\n=== vendor/ (dirs) ===")
    ftp.cwd("vendor")
    items = ftp.nlst(".")
    dirs = [i for i in items if not i.startswith('.')]
    for d in sorted(dirs)[:30]:
        print(f"  {d}")
    print(f"  ... total {len(dirs)} itens")
    
    ftp.quit()
except Exception as e:
    print(f"Erro: {e}")
