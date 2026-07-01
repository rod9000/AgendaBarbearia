import ftplib
import os

ftp_host = "ftpupload.net"
ftp_user = "if0_41967135"
ftp_pass = "tiUzMXg7kcrfp"

REMOTE_BASE = "agendabarbearia.infinityfree.me/htdocs"

files = [
    # === MIGRATIONS ===
    "database/migrations/2026_06_23_000001_add_whatsapp_to_companies_table.php",
    "database/migrations/2026_06_25_000004_add_evolution_api_to_companies_table.php",
    "database/migrations/2026_06_25_000008_add_bot_fields_to_companies_table.php",
    "database/migrations/2026_06_25_000009_add_webhook_url_to_companies_table.php",
    "database/migrations/2026_06_26_000001_create_receive_webhooks_table.php",
    "database/migrations/2026_06_26_000001_create_blocked_numbers_table.php",
    "database/migrations/2026_06_26_000002_make_customer_fields_nullable.php",
    "database/migrations/2026_06_26_000002_add_bot_settings_to_companies_table.php",
    "database/migrations/2026_06_26_000006_create_conversations_table.php",
    "database/migrations/2026_06_26_000007_create_bot_messages_table.php",
    "database/migrations/2026_06_26_000008_add_bot_fields_to_companies_table.php",
    "database/migrations/2026_06_26_000009_add_webhook_url_to_companies_table.php",
    "database/migrations/2026_06_26_000001_create_receive_webhooks_table.php",

    # === MODELS ===
    "app/Models/Company.php",
    "app/Models/Conversation.php",
    "app/Models/BotMessage.php",
    "app/Models/BlockedNumber.php",
    "app/Models/BotMenuItem.php",
    "app/Models/ReceiveWebhook.php",
    "app/Models/Customer.php",
    "app/Models/User.php",
    "app/Models/Service.php",
    "app/Models/Appointment.php",
    "app/Models/WorkingHour.php",

    # === SERVICES ===
    "app/Services/WhatsAppService.php",
    "app/Services/Bot/BotHandler.php",
    "app/Services/Bot/ConversationService.php",

    # === CONTROLLERS ===
    "app/Http/Controllers/Api/WebhookController.php",
    "app/Http/Controllers/Admin/EvolutionController.php",
    "app/Http/Controllers/Admin/BotController.php",
    "app/Http/Controllers/Admin/BotMessagesController.php",
    "app/Http/Controllers/Admin/BotMenuController.php",
    "app/Http/Controllers/Admin/BlockedNumberController.php",
    "app/Http/Controllers/Admin/WebhookLogController.php",
    "app/Http/Controllers/Admin/DashboardController.php",

    # === ROUTES ===
    "routes/api.php",
    "routes/web.php",

    # === CONFIG ===
    "config/services.php",

    # === VIEWS ===
    "resources/views/admin/settings/evolution.blade.php",
    "resources/views/admin/settings/bot.blade.php",
    "resources/views/admin/settings/blocked-numbers.blade.php",
    "resources/views/admin/settings/webhook-logs.blade.php",
    "resources/views/admin/settings/webhook-detail.blade.php",
    "resources/views/admin/bot-messages/index.blade.php",
    "resources/views/admin/bot-messages/show.blade.php",
    "resources/views/admin/dashboard.blade.php",
    "resources/views/layouts/navigation.blade.php",
    "resources/views/admin/customers/index.blade.php",
    "resources/views/admin/customers/show.blade.php",
    "resources/views/admin/customers/create.blade.php",
    "resources/views/admin/customers/edit.blade.php",
    "resources/views/components/dropdown.blade.php",
    "resources/views/components/nav-link.blade.php",
    "resources/views/components/responsive-nav-link.blade.php",

    # === ENV ===
    ".env",

    # === CSS ===
    "public/css/app.css",
]

cache_files_to_delete = [
    "/htdocs/bootstrap/cache/packages.php",
    "/htdocs/bootstrap/cache/services.php",
    "/htdocs/bootstrap/cache/config.php",
    "/htdocs/storage/framework/views/*.php",
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
        return True
    except:
        return False

try:
    ftp = ftplib.FTP(ftp_host, ftp_user, ftp_pass)
    print(f"Conectado ao FTP: {ftp_host}")

    print("\n--- Limpando caches ---")
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
            print(f"Nao encontrado: {local_file}")
            skipped += 1

    ftp.quit()
    print(f"\nUpload completo! {uploaded} enviados, {skipped} ignorados.")

except Exception as e:
    print(f"Erro: {e}")
