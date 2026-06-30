<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'whatsapp',
        'cnpj',
        'trial_starts_at',
        'trial_ends_at',
        'active',
        'evolution_api_url',
        'evolution_api_key',
        'evolution_instance_name',
        'whatsapp_type',
        'webhook_enabled',
        'bot_enabled',
        'welcome_message',
        'off_hours_message',
        'evolution_webhook_url',
        'bot_response_delay_minutes',
        'bot_off_hours_enabled',
        'razao_social',
        'endereco',
        'numero',
        'bairro',
        'cidade',
        'cep',
        'uf',
        'complemento',
    ];

    protected $casts = [
        'trial_starts_at' => 'datetime',
        'trial_ends_at' => 'date',
        'active' => 'boolean',
        'bot_off_hours_enabled' => 'boolean',
    ];

    public function evolutionConfigured(): bool
    {
        return !empty($this->evolution_api_url)
            && !empty($this->evolution_api_key)
            && !empty($this->evolution_instance_name);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function menuItems()
    {
        return $this->hasMany(BotMenuItem::class)->orderBy('sort_order');
    }

    public function getDefaultWelcomeMessage(): string
    {
        $greeting = $this->welcome_message
            ?: "Olá! Bem-vindo(a) à {$this->name}!\n\nComo posso te ajudar?";

        $items = $this->menuItems()->where('is_active', true)->get();

        $msg = $greeting . "\n\n";

        foreach ($items as $item) {
            $msg .= "{$item->menu_number}️⃣ {$item->label}\n";
        }

        $msg .= "0️⃣ Voltar\n\n";
        $msg .= "Digite o número da opção desejada:";

        return $msg;
    }

    public function getDefaultOffHoursMessage(): string
    {
        if ($this->off_hours_message) {
            return $this->off_hours_message;
        }

        return "Olá! No momento estamos fora do horário de atendimento.\n\n"
            . "Funcionamos de segunda a sábado.\n"
            . "Horário: 09:00 às 19:00\n\n"
            . "Deixe sua mensagem que retornamos no próximo horário de atendimento! 😊";
    }

    public function isBusinessOpen(): bool
    {
        $now = now()->setTimezone('America/Sao_Paulo');
        $dayOfWeek = $now->dayOfWeek;

        $workingHours = \App\Models\WorkingHour::where('day_of_week', $dayOfWeek)
            ->where('active', true)
            ->get();

        if ($workingHours->isEmpty()) {
            return false;
        }

        $currentTime = $now->format('H:i:s');
        foreach ($workingHours as $wh) {
            $start = $wh->start_time;
            $end = $wh->end_time;

            if ($end === '00:00:00') {
                if ($currentTime >= $start) {
                    return true;
                }
            } else {
                if ($currentTime >= $start && $currentTime <= $end) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isTrialExpired()
    {
        if (!$this->trial_ends_at) {
            return false;
        }
        return $this->trial_ends_at->isPast();
    }

    public function trialDaysLeft()
    {
        if (!$this->trial_ends_at) {
            return 0;
        }
        return max(0, now()->diffInDays($this->trial_ends_at, false));
    }

    public function isTrialActive()
    {
        return !$this->isTrialExpired();
    }
}
