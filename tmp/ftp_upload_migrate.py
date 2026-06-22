import ftplib

ftp_host = "ftpupload.net"
ftp_user = "if0_41967135"
ftp_pass = "tiUzMXg7kcrfp"

try:
    ftp = ftplib.FTP(ftp_host, ftp_user, ftp_pass)
    with open("_migrate.php", "rb") as f:
        ftp.storbinary("STOR /agendabarbearia.infinityfree.me/htdocs/_migrate.php", f)
    print("OK: _migrate.php atualizado")
    ftp.quit()
except Exception as e:
    print(f"Erro: {e}")
