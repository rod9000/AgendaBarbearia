import ftplib

ftp_host = "ftpupload.net"
ftp_user = "if0_41967135"
ftp_pass = "tiUzMXg7kcrfp"

try:
    ftp = ftplib.FTP(ftp_host, ftp_user, ftp_pass)
    ftp.cwd("/agendabarbearia.infinityfree.me/htdocs")
    
    # Upload phpinfo
    with open("tmp/phpinfo.php", "rb") as f:
        ftp.storbinary("STOR phpinfo.php", f)
    print("Upload phpinfo.php OK")
    
    ftp.quit()
    print("\nAcesse: https://agendabarbearia.infinityfree.me/phpinfo.php")
except Exception as e:
    print(f"Erro: {e}")
