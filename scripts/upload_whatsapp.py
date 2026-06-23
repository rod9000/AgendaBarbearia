import ftplib

ftp_host = 'ftpupload.net'
ftp_user = 'if0_41967135'
ftp_pass = 'tiUzMXg7kcrfp'

base = 'C:/Users/Precode TI/Documents/GitHub/AgendaBarbearia'
remote_base = '/agendabarbearia.infinityfree.me/htdocs'

files = [
    # WhatsApp Service
    ('app/Services/WhatsAppService.php', '/app/Services/WhatsAppService.php'),

    # Appointment Service
    ('app/Services/AppointmentService.php', '/app/Services/AppointmentService.php'),
    ('app/Services/BookingService.php', '/app/Services/BookingService.php'),

    # Controllers
    ('app/Http/Controllers/Admin/AppointmentController.php', '/app/Http/Controllers/Admin/AppointmentController.php'),
    ('app/Http/Controllers/Admin/CustomerController.php', '/app/Http/Controllers/Admin/CustomerController.php'),
    ('app/Http/Controllers/PublicController.php', '/app/Http/Controllers/PublicController.php'),

    # Views
    ('resources/views/public/cancelamento.blade.php', '/resources/views/public/cancelamento.blade.php'),
    ('resources/views/admin/logs/index.blade.php', '/resources/views/admin/logs/index.blade.php'),

    # Console Commands
    ('app/Console/Kernel.php', '/app/Console/Kernel.php'),
    ('app/Console/Commands/SendReminders.php', '/app/Console/Commands/SendReminders.php'),

    # Routes
    ('routes/web.php', '/routes/web.php'),

    # Observers
    ('app/Providers/AppServiceProvider.php', '/app/Providers/AppServiceProvider.php'),
    ('app/Observers/PaymentObserver.php', '/app/Observers/PaymentObserver.php'),
    ('app/Observers/CommissionObserver.php', '/app/Observers/CommissionObserver.php'),
    ('app/Observers/WorkingHourObserver.php', '/app/Observers/WorkingHourObserver.php'),
    ('app/Observers/BlockedSlotObserver.php', '/app/Observers/BlockedSlotObserver.php'),
    ('app/Observers/LoyaltyRewardObserver.php', '/app/Observers/LoyaltyRewardObserver.php'),
    ('app/Observers/CompanyObserver.php', '/app/Observers/CompanyObserver.php'),

    # Form Requests
    ('app/Http/Requests/StoreAppointmentRequest.php', '/app/Http/Requests/StoreAppointmentRequest.php'),
    ('app/Http/Requests/UpdateAppointmentRequest.php', '/app/Http/Requests/UpdateAppointmentRequest.php'),
    ('app/Http/Requests/StoreCustomerRequest.php', '/app/Http/Requests/StoreCustomerRequest.php'),
    ('app/Http/Requests/UpdateCustomerRequest.php', '/app/Http/Requests/UpdateCustomerRequest.php'),
    ('app/Http/Requests/StoreServiceRequest.php', '/app/Http/Requests/StoreServiceRequest.php'),
    ('app/Http/Requests/StoreProductRequest.php', '/app/Http/Requests/StoreProductRequest.php'),

    # Env
    ('.env', '/.env'),
]

try:
    ftp = ftplib.FTP(ftp_host, ftp_user, ftp_pass)
    print('Conectado ao FTP')

    uploaded = 0
    errors = 0

    for local_rel, remote_rel in files:
        local_path = base + '/' + local_rel
        remote_path = remote_base + remote_rel
        try:
            with open(local_path, 'rb') as f:
                ftp.storbinary('STOR ' + remote_path, f)
            print('OK: ' + local_rel)
            uploaded += 1
        except Exception as e:
            print('Erro: ' + local_rel + ' - ' + str(e))
            errors += 1

    ftp.quit()
    print('\nUpload concluido! ' + str(uploaded) + ' enviados, ' + str(errors) + ' erros.')
except Exception as e:
    print('Erro conexao: ' + str(e))
