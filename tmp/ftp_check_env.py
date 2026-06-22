import ftplib, io

ftp_host = "ftpupload.net"
ftp_user = "if0_41967135"
ftp_pass = "tiUzMXg7kcrfp"

try:
    ftp = ftplib.FTP(ftp_host, ftp_user, ftp_pass)
    ftp.cwd("/agendabarbearia.infinityfree.me/htdocs")
    
    # Download .env
    buf = io.BytesIO()
    try:
        ftp.retrbinary("RETR .env", buf.write)
        print("=== .env atual no servidor ===")
        print(buf.getvalue().decode())
    except:
        print("ERRO: .env nao encontrado")
    
    # List key dirs
    print("\n=== Verificando diretorios ===")
    for d in ["bootstrap/cache", "storage", "vendor"]:
        try:
            ftp.cwd(d)
            files = ftp.nlst(".")
            print(f"{d}/: {len(files)} itens")
            ftp.cwd("/agendabarbearia.infinityfree.me/htdocs")
        except:
            print(f"{d}/: NAO ENCONTRADO")
            ftp.cwd("/agendabarbearia.infinityfree.me/htdocs")
    
    # Clear cache files again
    try:
        ftp.delete("bootstrap/cache/packages.php")
        print("\nDeletado packages.php")
    except:
        pass
    try:
        ftp.delete("bootstrap/cache/services.php")
        print("Deletado services.php")
    except:
        pass
    
    # Try to delete config cache
    try:
        ftp.delete("bootstrap/cache/config.php")
        print("Deletado config.php")
    except:
        pass
    
    ftp.quit()
except Exception as e:
    print(f"Erro: {e}")
