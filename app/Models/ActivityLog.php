<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'old_values',
        'new_values',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByModel($query, $modelType, $modelId)
    {
        return $query->where('model_type', $modelType)->where('model_id', $modelId);
    }

    public static function log($action, $model, $description = null, $old = null, $new = null)
    {
        return static::create([
            'user_id'    => auth()->id(),
            'action'     => $action,
            'model_type' => get_class($model),
            'model_id'   => $model->id ?? null,
            'description' => $description,
            'old_values' => $old,
            'new_values' => $new,
        ]);
    }

    public function actionLabel()
    {
        $labels = [
            'created' => 'Criação',
            'updated' => 'Atualização',
            'deleted' => 'Exclusão',
        ];
        return $labels[$this->action] ?? $this->action;
    }

    public function modelLabel()
    {
        $parts = explode('\\', $this->model_type);
        $class = end($parts);
        $labels = [
            'Customer' => 'Cliente',
            'Appointment' => 'Agendamento',
            'Service' => 'Procedimento',
            'Product' => 'Produto',
            'User' => 'Usuário',
            'AnamnesisForm' => 'Anamnese',
        ];
        return $labels[$class] ?? $class;
    }
}
