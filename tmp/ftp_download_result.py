import ftplib

ftp_host = "ftpupload.net"
ftp_user = "if0_41967135"
ftp_pass = "tiUzMXg7kcrfp"

try:
    ftp = ftplib.FTP(ftp_host, ftp_user, ftp_pass)
    ftp.cwd("/agendabarbearia.infinityfree.me/htdocs/tmp")
    
    # Try to download result file
    try:
        with open("tmp/test_db_result.txt", "wb") as f:
            ftp.retrbinary("RETR test_db_result.txt", f.write)
        with open("tmp/test_db_result.txt", "r") as f:
            print(f.read())
    except:
        print("Arquivo de resultado nao encontrado. O script PHP nao foi executado.")
        print("Listando diretorio tmp:")
        for f in ftp.nlst("."):
            print(f"  {f}")
    
    ftp.quit()
except Exception as e:
    print(f"Erro: {e}")
