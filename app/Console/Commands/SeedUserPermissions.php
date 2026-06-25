<?php

namespace App\Console\Commands;

use App\Http\Middleware\CheckPagePermission;
use App\Models\User;
use Illuminate\Console\Command;

class SeedUserPermissions extends Command
{
    protected $signature = 'permissions:seed';
    protected $description = 'Concede permissões padrão a usuários atendentes que ainda não possuem nenhuma';

    public function handle()
    {
        $defaults = CheckPagePermission::defaultPages();
        $count = 0;

        User::where('role', 'attendant')->chunk(100, function ($users) use ($defaults, &$count) {
            foreach ($users as $user) {
                $existing = $user->pagePermissions()->pluck('page')->toArray();

                if (!empty($existing)) {
                    continue;
                }

                $insert = [];
                foreach ($defaults as $page) {
                    $insert[] = ['page' => $page];
                }

                if (!empty($insert)) {
                    $user->pagePermissions()->createMany($insert);
                    $count++;
                }
            }
        });

        $this->info("{$count} atendente(s) receberam permissões padrão.");
        return Command::SUCCESS;
    }
}
