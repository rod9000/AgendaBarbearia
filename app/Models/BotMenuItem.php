<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotMenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'menu_number',
        'label',
        'action',
        'response_text',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public static function getActionTypes(): array
    {
        return [
            'booking' => 'Agendar Horário',
            'services' => 'Serviços e Preços',
            'working_hours' => 'Horários de Funcionamento',
            'consult' => 'Consultar Agendamentos',
            'cancel' => 'Cancelar Agendamento',
            'location' => 'Localização',
            'custom' => 'Texto Customizado',
        ];
    }

    public function getActionLabel(): string
    {
        return self::getActionTypes()[$this->action] ?? $this->action;
    }
}
