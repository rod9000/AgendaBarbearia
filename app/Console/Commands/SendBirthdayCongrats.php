<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\NotificationLog;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendBirthdayCongrats extends Command
{
    protected $signature = 'customers:birthday-congrats';
    protected $description = 'Send birthday congratulations via WhatsApp';

    public function handle()
    {
        $today = Carbon::now();

        $customers = Customer::whereMonth('birth_date', $today->month)
            ->whereDay('birth_date', $today->day)
            ->get();

        if ($customers->isEmpty()) {
            $this->info('No birthdays today.');
            return;
        }

        $wa = new WhatsAppService();

        foreach ($customers as $customer) {
            $msg = "Feliz Aniversário, {$customer->name}!\n"
                 . "A equipe da Clínica de Estética deseja um dia maravilhoso cheio de alegria!\n"
                 . "Para comemorar, agende uma sessão especial conosco!";

            $sent = $wa->send($customer->phone, $msg);

            NotificationLog::create([
                'customer_id' => $customer->id,
                'type'        => 'birthday',
                'recipient'   => $customer->phone,
                'message'     => $msg,
                'status'      => $sent ? 'sent' : 'failed',
                'sent_at'     => $sent ? Carbon::now() : null,
            ]);

            $this->info("Birthday wish sent to {$customer->name}");
        }
    }
}
