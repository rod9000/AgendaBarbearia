import ftplib, os

ftp_host = "ftpupload.net"
ftp_user = "if0_41967135"
ftp_pass = "tiUzMXg7kcrfp"

BASE = "/agendabarbearia.infinityfree.me/htdocs"

views_to_upload = [
    # Auth
    "resources/views/auth/login.blade.php",
    "resources/views/auth/register.blade.php",
    "resources/views/auth/verify-email.blade.php",
    "resources/views/auth/forgot-password.blade.php",
    "resources/views/auth/reset-password.blade.php",
    "resources/views/auth/confirm-password.blade.php",
    # Layouts
    "resources/views/layouts/guest.blade.php",
    "resources/views/layouts/app.blade.php",
    "resources/views/layouts/navigation.blade.php",
    # Components
    "resources/views/components/application-logo.blade.php",
    "resources/views/components/auth-card.blade.php",
    "resources/views/components/auth-session-status.blade.php",
    "resources/views/components/auth-validation-errors.blade.php",
    "resources/views/components/button.blade.php",
    "resources/views/components/dropdown.blade.php",
    "resources/views/components/dropdown-link.blade.php",
    "resources/views/components/input.blade.php",
    "resources/views/components/label.blade.php",
    "resources/views/components/nav-link.blade.php",
    "resources/views/components/responsive-nav-link.blade.php",
    "resources/views/components/status-badge.blade.php",
    # Public
    "resources/views/public/booking.blade.php",
    "resources/views/public/reagendar.blade.php",
    "resources/views/public/confirmacao.blade.php",
    # Dashboard
    "resources/views/dashboard.blade.php",
    "resources/views/welcome.blade.php",
    # Admin
    "resources/views/admin/dashboard.blade.php",
    "resources/views/admin/appointments/index.blade.php",
    "resources/views/admin/appointments/modal.blade.php",
    "resources/views/admin/appointments/detail_modal.blade.php",
    "resources/views/admin/services/index.blade.php",
    "resources/views/admin/services/create.blade.php",
    "resources/views/admin/services/edit.blade.php",
    "resources/views/admin/customers/index.blade.php",
    "resources/views/admin/customers/create.blade.php",
    "resources/views/admin/customers/edit.blade.php",
    "resources/views/admin/customers/show.blade.php",
    "resources/views/admin/products/index.blade.php",
    "resources/views/admin/products/create.blade.php",
    "resources/views/admin/products/edit.blade.php",
    "resources/views/admin/products/show.blade.php",
    "resources/views/admin/products/stock-report.blade.php",
    "resources/views/admin/financial/index.blade.php",
    "resources/views/admin/commissions/index.blade.php",
    "resources/views/admin/commissions/professional.blade.php",
    "resources/views/admin/reports/index.blade.php",
    "resources/views/admin/settings/working-hours.blade.php",
    "resources/views/admin/settings/blocked-slots.blade.php",
    "resources/views/admin/settings/backup.blade.php",
    "resources/views/admin/logs/index.blade.php",
    "resources/views/admin/users/index.blade.php",
    "resources/views/admin/users/create.blade.php",
    "resources/views/admin/users/edit.blade.php",
    "resources/views/admin/loyalty/index.blade.php",
    "resources/views/admin/loyalty/create.blade.php",
    "resources/views/admin/loyalty/edit.blade.php",
    "resources/views/admin/loyalty/customer.blade.php",
    "resources/views/trial/expired.blade.php",
]

try:
    ftp = ftplib.FTP(ftp_host, ftp_user, ftp_pass)
    
    for local_path in views_to_upload:
        if not os.path.exists(local_path):
            print(f"AUSENTE: {local_path}")
            continue
        
        remote_path = f"{BASE}/{local_path}"
        remote_dir = os.path.dirname(remote_path)
        
        # Ensure remote dir exists
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
        
        with open(local_path, 'rb') as f:
            ftp.storbinary(f"STOR {remote_path}", f)
        print(f"OK: {local_path}")
    
    ftp.quit()
    print(f"\nUpload concluido!")
except Exception as e:
    print(f"Erro: {e}")
