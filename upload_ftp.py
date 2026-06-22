import ftplib
import os

# Credenciais FTP
ftp_host = "ftpupload.net"
ftp_user = "if0_41967135"
ftp_pass = "tiUzMXg7kcrfp"

REMOTE_BASE = "agendabarbearia.infinityfree.me/htdocs"

# Arquivos a fazer upload (local -> remoto)
files = [
    # Migrations
    ("database/migrations/2026_06_03_000001_create_activity_logs_table.php", "/htdocs/database/migrations/2026_06_03_000001_create_activity_logs_table.php"),
    ("database/migrations/2026_06_03_000002_create_payments_table.php", "/htdocs/database/migrations/2026_06_03_000002_create_payments_table.php"),
    ("database/migrations/2026_06_03_000003_add_stock_fields_to_products_table.php", "/htdocs/database/migrations/2026_06_03_000003_add_stock_fields_to_products_table.php"),
    ("database/migrations/2026_06_03_000004_create_stock_movements_table.php", "/htdocs/database/migrations/2026_06_03_000004_create_stock_movements_table.php"),
    ("database/migrations/2026_06_03_000005_add_commission_to_services_table.php", "/htdocs/database/migrations/2026_06_03_000005_add_commission_to_services_table.php"),
    ("database/migrations/2026_06_03_000006_create_commissions_table.php", "/htdocs/database/migrations/2026_06_03_000006_create_commissions_table.php"),
    ("database/migrations/2026_06_03_000007_create_working_hours_table.php", "/htdocs/database/migrations/2026_06_03_000007_create_working_hours_table.php"),
    ("database/migrations/2026_06_03_000008_create_blocked_slots_table.php", "/htdocs/database/migrations/2026_06_03_000008_create_blocked_slots_table.php"),
    ("database/migrations/2026_06_03_000009_add_recurring_to_appointments_table.php", "/htdocs/database/migrations/2026_06_03_000009_add_recurring_to_appointments_table.php"),
    ("database/migrations/2026_06_03_000010_create_product_service_table.php", "/htdocs/database/migrations/2026_06_03_000010_create_product_service_table.php"),
    ("database/migrations/2026_06_03_000011_create_appointment_service_table.php", "/htdocs/database/migrations/2026_06_03_000011_create_appointment_service_table.php"),
    ("database/migrations/2026_06_03_000012_add_confirmation_token_to_appointments_table.php", "/htdocs/database/migrations/2026_06_03_000012_add_confirmation_token_to_appointments_table.php"),

    # Models
    ("app/Models/Service.php", "/htdocs/app/Models/Service.php"),
    ("app/Models/Product.php", "/htdocs/app/Models/Product.php"),
    ("app/Models/User.php", "/htdocs/app/Models/User.php"),
    ("app/Models/Commission.php", "/htdocs/app/Models/Commission.php"),
    ("app/Models/Appointment.php", "/htdocs/app/Models/Appointment.php"),
    ("app/Models/ActivityLog.php", "/htdocs/app/Models/ActivityLog.php"),
    ("app/Models/Payment.php", "/htdocs/app/Models/Payment.php"),
    ("app/Models/StockMovement.php", "/htdocs/app/Models/StockMovement.php"),
    ("app/Models/WorkingHour.php", "/htdocs/app/Models/WorkingHour.php"),
    ("app/Models/BlockedSlot.php", "/htdocs/app/Models/BlockedSlot.php"),
    ("app/Models/NotificationLog.php", "/htdocs/app/Models/NotificationLog.php"),

    # Controllers
    ("app/Http/Controllers/Admin/ServiceController.php", "/htdocs/app/Http/Controllers/Admin/ServiceController.php"),
    ("app/Http/Controllers/Admin/AppointmentController.php", "/htdocs/app/Http/Controllers/Admin/AppointmentController.php"),
    ("app/Http/Controllers/Admin/FinancialController.php", "/htdocs/app/Http/Controllers/Admin/FinancialController.php"),
    ("app/Http/Controllers/Admin/ProductController.php", "/htdocs/app/Http/Controllers/Admin/ProductController.php"),
    ("app/Http/Controllers/Admin/CommissionController.php", "/htdocs/app/Http/Controllers/Admin/CommissionController.php"),
    ("app/Http/Controllers/Admin/CustomerController.php", "/htdocs/app/Http/Controllers/Admin/CustomerController.php"),
    ("app/Http/Controllers/Admin/ReportController.php", "/htdocs/app/Http/Controllers/Admin/ReportController.php"),
    ("app/Http/Controllers/Admin/BackupController.php", "/htdocs/app/Http/Controllers/Admin/BackupController.php"),
    ("app/Http/Controllers/Admin/SettingsController.php", "/htdocs/app/Http/Controllers/Admin/SettingsController.php"),
    ("app/Http/Controllers/Admin/LogController.php", "/htdocs/app/Http/Controllers/Admin/LogController.php"),
    ("app/Http/Controllers/Admin/MigrationController.php", "/htdocs/app/Http/Controllers/Admin/MigrationController.php"),
    ("app/Http/Controllers/PublicController.php", "/htdocs/app/Http/Controllers/PublicController.php"),

    # Observers
    ("app/Observers/CustomerObserver.php", "/htdocs/app/Observers/CustomerObserver.php"),
    ("app/Observers/AppointmentObserver.php", "/htdocs/app/Observers/AppointmentObserver.php"),
    ("app/Observers/ServiceObserver.php", "/htdocs/app/Observers/ServiceObserver.php"),
    ("app/Observers/ProductObserver.php", "/htdocs/app/Observers/ProductObserver.php"),
    ("app/Observers/UserObserver.php", "/htdocs/app/Observers/UserObserver.php"),
    ("app/Observers/AnamnesisFormObserver.php", "/htdocs/app/Observers/AnamnesisFormObserver.php"),

    # Services
    ("app/Services/WhatsAppService.php", "/htdocs/app/Services/WhatsAppService.php"),

    # HTTP Kernel
    ("app/Http/Kernel.php", "/htdocs/app/Http/Kernel.php"),

    # Console
    ("app/Console/Kernel.php", "/htdocs/app/Console/Kernel.php"),
    ("app/Console/Commands/SendReminders.php", "/htdocs/app/Console/Commands/SendReminders.php"),
    ("app/Console/Commands/RunBackup.php", "/htdocs/app/Console/Commands/RunBackup.php"),

    # Trial System
    ("app/Models/Company.php", "/htdocs/app/Models/Company.php"),
    ("app/Http/Middleware/CheckTrial.php", "/htdocs/app/Http/Middleware/CheckTrial.php"),
    ("resources/views/trial/expired.blade.php", "/htdocs/resources/views/trial/expired.blade.php"),
    ("database/migrations/2026_06_22_000001_create_companies_table.php", "/htdocs/database/migrations/2026_06_22_000001_create_companies_table.php"),
    ("database/migrations/2026_06_22_000002_add_company_id_to_users_table.php", "/htdocs/database/migrations/2026_06_22_000002_add_company_id_to_users_table.php"),

    # Providers
    ("app/Providers/AppServiceProvider.php", "/htdocs/app/Providers/AppServiceProvider.php"),

    # Routes
    ("routes/web.php", "/htdocs/routes/web.php"),
    ("routes/api.php", "/htdocs/routes/api.php"),
    ("routes/console.php", "/htdocs/routes/console.php"),
    ("routes/channels.php", "/htdocs/routes/channels.php"),

    # Views
    ("resources/views/layouts/navigation.blade.php", "/htdocs/resources/views/layouts/navigation.blade.php"),
    ("resources/views/layouts/app.blade.php", "/htdocs/resources/views/layouts/app.blade.php"),
    ("resources/views/auth/login.blade.php", "/htdocs/resources/views/auth/login.blade.php"),
    ("resources/views/auth/register.blade.php", "/htdocs/resources/views/auth/register.blade.php"),
    ("resources/views/auth/verify-email.blade.php", "/htdocs/resources/views/auth/verify-email.blade.php"),
    ("resources/views/auth/forgot-password.blade.php", "/htdocs/resources/views/auth/forgot-password.blade.php"),
    ("resources/views/auth/reset-password.blade.php", "/htdocs/resources/views/auth/reset-password.blade.php"),
    ("resources/views/auth/confirm-password.blade.php", "/htdocs/resources/views/auth/confirm-password.blade.php"),
    ("resources/views/admin/services/create.blade.php", "/htdocs/resources/views/admin/services/create.blade.php"),
    ("resources/views/admin/services/edit.blade.php", "/htdocs/resources/views/admin/services/edit.blade.php"),
    ("resources/views/admin/services/index.blade.php", "/htdocs/resources/views/admin/services/index.blade.php"),
    ("resources/views/admin/financial/index.blade.php", "/htdocs/resources/views/admin/financial/index.blade.php"),
    ("resources/views/admin/products/create.blade.php", "/htdocs/resources/views/admin/products/create.blade.php"),
    ("resources/views/admin/products/edit.blade.php", "/htdocs/resources/views/admin/products/edit.blade.php"),
    ("resources/views/admin/products/show.blade.php", "/htdocs/resources/views/admin/products/show.blade.php"),
    ("resources/views/admin/customers/show.blade.php", "/htdocs/resources/views/admin/customers/show.blade.php"),
    ("resources/views/admin/commissions/index.blade.php", "/htdocs/resources/views/admin/commissions/index.blade.php"),
    ("resources/views/admin/reports/index.blade.php", "/htdocs/resources/views/admin/reports/index.blade.php"),
    ("resources/views/admin/settings/backup.blade.php", "/htdocs/resources/views/admin/settings/backup.blade.php"),
    ("resources/views/admin/settings/working-hours.blade.php", "/htdocs/resources/views/admin/settings/working-hours.blade.php"),
    ("resources/views/admin/logs/index.blade.php", "/htdocs/resources/views/admin/logs/index.blade.php"),
    ("resources/views/public/booking.blade.php", "/htdocs/resources/views/public/booking.blade.php"),
    ("resources/views/public/reagendar.blade.php", "/htdocs/resources/views/public/reagendar.blade.php"),
    ("resources/views/public/confirmacao.blade.php", "/htdocs/resources/views/public/confirmacao.blade.php"),
    ("resources/views/admin/appointments/index.blade.php", "/htdocs/resources/views/admin/appointments/index.blade.php"),
    ("resources/views/admin/appointments/modal.blade.php", "/htdocs/resources/views/admin/appointments/modal.blade.php"),
    ("resources/views/admin/appointments/detail_modal.blade.php", "/htdocs/resources/views/admin/appointments/detail_modal.blade.php"),
    ("resources/views/admin/dashboard.blade.php", "/htdocs/resources/views/admin/dashboard.blade.php"),

    # Components
    ("resources/views/components/application-logo.blade.php", "/htdocs/resources/views/components/application-logo.blade.php"),
    ("resources/views/components/button.blade.php", "/htdocs/resources/views/components/button.blade.php"),
    ("resources/views/components/input.blade.php", "/htdocs/resources/views/components/input.blade.php"),
    ("resources/views/components/dropdown.blade.php", "/htdocs/resources/views/components/dropdown.blade.php"),
    ("resources/views/components/status-badge.blade.php", "/htdocs/resources/views/components/status-badge.blade.php"),

    # CSS/JS (compiled assets)
    ("public/css/app.css", "/htdocs/public/css/app.css"),
    ("public/js/app.js", "/htdocs/public/js/app.js"),

    # Images
    ("public/images/barber-logo.png", "/htdocs/public/images/barber-logo.png"),

    # Config
    ("tailwind.config.js", "/htdocs/tailwind.config.js"),
    ("resources/css/app.css", "/htdocs/resources/css/app.css"),

    # Env (locale .env.ftp -> servidor .env)
    (".env.ftp", "/htdocs/.env"),

]

# Arquivos de cache que PRECISAM ser deletados no servidor (causam o erro 500)
cache_files_to_delete = [
    "/htdocs/bootstrap/cache/packages.php",
    "/htdocs/bootstrap/cache/services.php",
]

def full_remote_path(remote_file):
    """Converte /htdocs/... para o caminho remoto completo"""
    return '/' + REMOTE_BASE + remote_file[len('/htdocs'):]

def ensure_remote_dir(ftp, remote_file):
    """Garante que o diretorio remoto existe"""
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
    """Deleta arquivo remoto se existir"""
    try:
        ftp.delete(remote_file)
        print(f"Deletado (cache): {remote_file}")
        return True
    except:
        return False

try:
    ftp = ftplib.FTP(ftp_host, ftp_user, ftp_pass)
    print(f"Conectado ao FTP: {ftp_host}")

    # PASSO 1: Deletar arquivos de cache problemáticos
    print("\n--- Limpando caches antigos ---")
    deleted_count = 0
    for cache_file in cache_files_to_delete:
        remote = full_remote_path(cache_file)
        if delete_remote_file(ftp, remote):
            deleted_count += 1
    if deleted_count == 0:
        print("(nenhum cache encontrado - talvez ja tenham sido removidos)")

    # PASSO 2: Upload dos arquivos
    print("\n--- Enviando arquivos ---")
    uploaded = 0
    skipped = 0

    for local_file, remote_file in files:
        remote_path = full_remote_path(remote_file)
        if os.path.exists(local_file):
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
    print(f"\nAntes de acessar o site, rode as migrations:")
    print(f"https://agendabarbearia.infinityfree.me/_migrate.php")

except Exception as e:
    print(f"Erro: {e}")
