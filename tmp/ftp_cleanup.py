import ftplib

ftp_host = "ftpupload.net"
ftp_user = "if0_41967135"
ftp_pass = "tiUzMXg7kcrfp"

BASE = "/agendabarbearia.infinityfree.me/htdocs"

to_delete = [
    f"{BASE}/_migrate.php",
    f"{BASE}/_create_admin.php",
    f"{BASE}/_seed_services.php",
    f"{BASE}/_clear_cache.php",
    f"{BASE}/tmp/test_db.php",
    f"{BASE}/tmp/test_db2.php",
    f"{BASE}/tmp/test_db_result.txt",
    f"{BASE}/tmp/phpinfo.php",
    f"{BASE}/phpinfo.php",
]

try:
    ftp = ftplib.FTP(ftp_host, ftp_user, ftp_pass)
    deleted = 0
    not_found = 0

    # Delete individual files
    for path in to_delete:
        try:
            ftp.delete(path)
            print(f"DELETED: {path}")
            deleted += 1
        except:
            print(f"NOT FOUND: {path}")
            not_found += 1

    # Try to delete tmp directory recursively
    try:
        # First list and delete contents of tmp
        try:
            ftp.cwd(f"{BASE}/tmp")
            items = ftp.nlst(".")
            for item in items:
                if item not in (".", ".."):
                    try:
                        ftp.delete(item)
                        print(f"DELETED: tmp/{item}")
                    except:
                        try:
                            # Try to change into subdirectory and delete contents
                            ftp.cwd(item)
                            subitems = ftp.nlst(".")
                            for sub in subitems:
                                if sub not in (".", ".."):
                                    try:
                                        ftp.delete(sub)
                                        print(f"DELETED: tmp/{item}/{sub}")
                                    except:
                                        pass
                            ftp.cwd(f"{BASE}/tmp")
                        except:
                            pass
            ftp.cwd("/")
            # Remove the now-empty tmp directory
            ftp.rmd(f"{BASE}/tmp")
            print(f"DELETED: tmp/ directory")
        except:
            ftp.cwd("/")
    except Exception as e:
        print(f"Could not fully clean tmp: {e}")

    ftp.quit()
    print(f"\nConcluido: {deleted} deletados, {not_found} ausentes")
except Exception as e:
    print(f"Erro: {e}")
