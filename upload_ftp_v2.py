import ftplib
import os

ftp_host = "ftpupload.net"
ftp_user = "if0_41967135"
ftp_pass = "tiUzMXg7kcrfp"

REMOTE_BASE = "agendabarbearia.infinityfree.me/htdocs"

files = [
    # === MIGRATIONS ===
    "database/migrations/2026_06_25_000001_create_sales_table.php",
    "database/migrations/2026_06_25_000002_create_sale_product_table.php",
    "database/migrations/2026_06_25_000003_create_user_page_permissions_table.php",

    # === MODELS ===
    "app/Models/Sale.php",
    "app/Models/UserPagePermission.php",
    "app/Models/User.php",

    # === CONTROLLERS ===
    "app/Http/Controllers/Admin/SaleController.php",
    "app/Http/Controllers/Admin/DashboardController.php",
    "app/Http/Controllers/Admin/UserController.php",

    # === MIDDLEWARE ===
    "app/Http/Middleware/CheckPagePermission.php",

    # === KERNEL ===
    "app/Http/Kernel.php",

    # === COMMANDS ===
    "app/Console/Commands/SeedUserPermissions.php",

    # === SEEDERS ===
    "database/seeders/CompanySeeder.php",

    # === ROUTES ===
    "routes/web.php",

    # === CSS ===
    "public/css/app.css",

    # === VIEWS ===
    "resources/views/admin/sales/index.blade.php",
    "resources/views/admin/sales/create.blade.php",
    "resources/views/admin/sales/show.blade.php",
    "resources/views/admin/dashboard.blade.php",
    "resources/views/admin/users/create.blade.php",
    "resources/views/admin/users/edit.blade.php",
    "resources/views/layouts/navigation.blade.php",
    "resources/views/admin/appointments/detail_modal.blade.php",
    "resources/views/admin/appointments/index.blade.php",

    # === ENV ===
    ".env.ftp",
]

cache_files_to_delete = [
    "/htdocs/bootstrap/cache/packages.php",
    "/htdocs/bootstrap/cache/services.php",
]

def full_remote_path(local_file):
    return '/' + REMOTE_BASE + '/' + local_file

def ensure_remote_dir(ftp, remote_file):
    remote_dir = os.path.dirname(remote_file)
    try:
        ftp.cwd(remote_dir)
    except:
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

def delete_remote_file(ftp, remote_file):
    try:
        ftp.delete(remote_file)
        print(f"Deletado (cache): {remote_file}")
        return True
    except:
        return False

try:
    ftp = ftplib.FTP(ftp_host, ftp_user, ftp_pass)
    print(f"Conectado ao FTP: {ftp_host}")

    print("\n--- Limpando caches antigos ---")
    for cache_file in cache_files_to_delete:
        delete_remote_file(ftp, cache_file)

    print("\n--- Enviando arquivos ---")
    uploaded = 0
    skipped = 0

    for local_file in files:
        if os.path.exists(local_file):
            remote_path = full_remote_path(local_file)
            ensure_remote_dir(ftp, remote_path)
            with open(local_file, 'rb') as f:
                ftp.storbinary(f'STOR {remote_path}', f)
            print(f"OK: {local_file}")
            uploaded += 1
        else:
            print(f"Arquivo nao encontrado: {local_file}")
            skipped += 1

    ftp.quit()
    print(f"\nUpload completo! {uploaded} enviados, {skipped} ignorados.")

except Exception as e:
    print(f"Erro: {e}")
