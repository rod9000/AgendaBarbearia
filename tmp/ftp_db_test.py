import ftplib, os, io

ftp_host = "ftpupload.net"
ftp_user = "if0_41967135"
ftp_pass = "tiUzMXg7kcrfp"

try:
    ftp = ftplib.FTP(ftp_host, ftp_user, ftp_pass)
    ftp.cwd("/agendabarbearia.infinityfree.me/htdocs/tmp")
    
    # Upload test file
    with open("tmp/test_db2.php", "rb") as f:
        ftp.storbinary("STOR test_db2.php", f)
    print("Upload test_db2.php OK")
    
    ftp.quit()
    print("\nAcesse: https://agendabarbearia.infinityfree.me/tmp/test_db2.php")
    print("Depois rode: python tmp/ftp_db_test.py para baixar o resultado")
    
except Exception as e:
    print(f"Erro: {e}")
