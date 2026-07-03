<?php

namespace App\Services\Bot;

use App\Models\Appointment;
use App\Models\Company;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkingHour;
use App\Services\WhatsAppService;
use Carbon\Carbon;

class BotHandler
{
    protected WhatsAppService $whatsapp;
    protected ConversationService $conversationService;
    protected ?Conversation $currentConversation = null;

    public function __construct(WhatsAppService $whatsapp, ConversationService $conversationService)
    {
        $this->whatsapp = $whatsapp;
        $this->conversationService = $conversationService;
    }

    public function handle(string $phone, string $message, Company $company, ?string $pushName = null): void
    {
        if (\App\Models\BlockedNumber::isBlocked($phone, $company->id)) {
            return;
        }

        $this->whatsapp->forCompany($company);
        $this->currentConversation = $this->conversationService->findOrCreate($phone, $company, $pushName);
        $conversation = $this->currentConversation;

        $this->conversationService->logMessage($conversation, 'inbound', $message);

        if ($conversation->isExpired()) {
            $this->conversationService->reset($conversation);
            $this->send($phone, $company->getDefaultWelcomeMessage());
            return;
        }

        $text = trim($message);

        if ($text === '0' && $conversation->state === 'initial') {
            $this->send($phone, $company->getDefaultWelcomeMessage());
            return;
        }

        if (!$company->isBusinessOpen() && $conversation->state === 'initial') {
            $this->send($phone, $company->getDefaultOffHoursMessage());
            return;
        }

        $state = $conversation->state;

        match ($state) {
            'initial' => $this->handleInitialMenu($conversation, $text, $company),
            'choosing_service' => $this->handleChoosingService($conversation, $text),
            'choosing_professional' => $this->handleChoosingProfessional($conversation, $text),
            'choosing_date' => $this->handleChoosingDate($conversation, $text),
            'choosing_time' => $this->handleChoosingTime($conversation, $text),
            'asking_name' => $this->handleAskName($conversation, $text),
            'confirming' => $this->handleConfirming($conversation, $text),
            'consulting_appointments' => $this->handleConsultingAppointments($conversation, $text),
            'cancelling' => $this->handleCancelling($conversation, $text),
            'rescheduling' => $this->handleRescheduling($conversation, $text),
            'showing_location' => $this->handleShowingLocation($conversation, $text, $company),
            'showing_hours' => $this->handleShowingLocation($conversation, $text, $company),
            default => $this->conversationService->reset($conversation),
        };
    }

    private function handleInitialMenu(Conversation $conversation, string $text, Company $company): void
    {
        $phone = $conversation->phone;

        $menuItems = $company->menuItems()->where('is_active', true)->get();

        if ($menuItems->isEmpty()) {
            $this->send($phone, "Menu não configurado. Digite 0️⃣ para voltar.");
            return;
        }

        $menuItem = $menuItems->firstWhere('menu_number', (int) $text);

        if (!$menuItem) {
            $options = $menuItems->map(fn($i) => "{$i->menu_number}️⃣ {$i->label}")->implode("\n");
            $this->send($phone, "Opção inválida. Escolha uma opção:\n\n{$options}\n\n0️⃣ Voltar\n\nDigite o número da opção desejada:");
            return;
        }

        match ($menuItem->action) {
            'booking' => $this->startBooking($conversation),
            'services' => $this->sendServiceList($conversation),
            'working_hours' => $this->sendWorkingHours($conversation, $company),
            'consult' => $this->startConsultAppointments($conversation),
            'cancel' => $this->startCancellation($conversation),
            'location' => $this->sendLocation($conversation),
            'custom' => $this->sendCustomResponse($conversation, $menuItem->response_text),
        };
    }

    private function sendCustomResponse(Conversation $conversation, ?string $text): void
    {
        $this->send($conversation->phone, $text ?: "Sem resposta configurada.\n\n0️⃣ Voltar ao menu.");
    }

    private function startBooking(Conversation $conversation): void
    {
        $services = Service::where('active', true)->orderBy('name')->get();

        if ($services->isEmpty()) {
            $this->send($conversation->phone, "No momento não há serviços disponíveis. Tente novamente mais tarde.");
            $this->conversationService->reset($conversation);
            return;
        }

        $msg = "Ótimo! Escolha o serviço desejado:\n\n";
        foreach ($services as $index => $service) {
            $emoji = $this->emojiNumber($index + 1);
            $msg .= "{$emoji} {$service->name} - R$ " . number_format($service->price, 2, ',', '.') . " ({$service->duration_min}min)\n";
        }
        $msg .= "\n0️⃣ Voltar ao menu\n\nDigite o número do serviço:";

        $this->conversationService->updateState($conversation, 'choosing_service', [
            'services' => $services->pluck('id')->toArray(),
        ]);

        $this->send($conversation->phone, $msg);
    }

    private function handleChoosingService(Conversation $conversation, string $text): void
    {
        if (in_array($text, ['0'])) {
            $this->conversationService->reset($conversation);
            $this->send($conversation->phone, $conversation->company->getDefaultWelcomeMessage());
            return;
        }

        $serviceIds = $conversation->getCtx('services', []);
        $index = (int) $text - 1;

        if ($index < 0 || $index >= count($serviceIds)) {
            $this->send($conversation->phone, "Opção inválida. Digite o número correspondente ao serviço:");
            return;
        }

        $serviceId = $serviceIds[$index];
        $service = Service::find($serviceId);

        if (!$service) {
            $this->send($conversation->phone, "Serviço não encontrado. Tente novamente.");
            $this->conversationService->reset($conversation);
            return;
        }

        $this->conversationService->updateState($conversation, 'choosing_professional', [
            'services' => $serviceIds,
            'selected_service_id' => $serviceId,
        ]);

        $users = User::where('active', true)->where('role', 'attendant')->get();

        if ($users->isEmpty()) {
            $this->send($conversation->phone, "No momento não há profissionais disponíveis. Tente novamente mais tarde.");
            $this->conversationService->reset($conversation);
            return;
        }

        $msg = "Serviço: *{$service->name}*\n\nEscolha o profissional:\n\n";
        foreach ($users as $index => $user) {
            $emoji = $this->emojiNumber($index + 1);
            $msg .= "{$emoji} {$user->name}\n";
        }
        $msg .= "\n0️⃣ Voltar ao serviço\n\nDigite o número do profissional:";

        $this->send($conversation->phone, $msg);
    }

    private function handleChoosingProfessional(Conversation $conversation, string $text): void
    {
        if (in_array($text, ['0'])) {
            $this->startBooking($conversation);
            return;
        }

        $users = User::where('active', true)->where('role', 'attendant')->get()->values();
        $index = (int) $text - 1;

        if ($index < 0 || $index >= $users->count()) {
            $this->send($conversation->phone, "Opção inválida. Digite o número correspondente ao profissional:\n\n0️⃣ Voltar ao serviço");
            return;
        }

        $userId = $users[$index]->id;

        $this->conversationService->updateState($conversation, 'choosing_date', [
            'selected_user_id' => $userId,
        ]);

        $dates = $this->getSuggestedDates($conversation);
        $msg = "Profissional: *{$users[$index]->name}*\n\nDigite a data desejada (DD/MM):\n";
        if (!empty($dates)) {
            $msg .= "\nDatas disponíveis:\n";
            foreach ($dates as $date) {
                $msg .= "• {$date}\n";
            }
        }

        $this->send($conversation->phone, $msg);
    }

    private function handleChoosingDate(Conversation $conversation, string $text): void
    {
        if (in_array($text, ['0'])) {
            $this->handleChoosingProfessionalFromBack($conversation);
            return;
        }

        $date = $this->parseDate($text);

        if (!$date) {
            $dates = $this->getSuggestedDates($conversation);
            $msg = "Data inválida. Use o formato DD/MM (ex: 25/06).\n";
            if (!empty($dates)) {
                $msg .= "\nDatas disponíveis:\n";
                foreach ($dates as $d) {
                    $msg .= "• {$d}\n";
                }
            }
            $msg .= "\n0️⃣ Voltar ao profissional";
            $this->send($conversation->phone, $msg);
            return;
        }

        $carbon = Carbon::parse($date);

        if ($carbon->isPast()) {
            $dates = $this->getSuggestedDates($conversation);
            $msg = "Essa data já passou. Escolha uma data futura (DD/MM).\n";
            if (!empty($dates)) {
                $msg .= "\nDatas disponíveis:\n";
                foreach ($dates as $d) {
                    $msg .= "• {$d}\n";
                }
            }
            $msg .= "\n0️⃣ Voltar ao profissional";
            $this->send($conversation->phone, $msg);
            return;
        }

        $dayOfWeek = $carbon->dayOfWeek;
        $hasWorkingHours = WorkingHour::where('day_of_week', $dayOfWeek)
            ->where('active', true)
            ->exists();

        if (!$hasWorkingHours) {
            $dates = $this->getSuggestedDates($conversation);
            $msg = "Não há atendimento nesse dia.\n";
            if (!empty($dates)) {
                $msg .= "\nDatas disponíveis:\n";
                foreach ($dates as $d) {
                    $msg .= "• {$d}\n";
                }
            }
            $msg .= "\nDigite a data desejada (DD/MM):";
            $this->send($conversation->phone, $msg);
            return;
        }

        $this->conversationService->updateState($conversation, 'choosing_time', [
            'selected_date' => $carbon->format('Y-m-d'),
        ]);

        $slots = $this->getAvailableSlots($conversation, $carbon->format('Y-m-d'));

        if (empty($slots)) {
            $suggestions = $this->getSuggestedDates($conversation);
            $msg = "Não há horários disponíveis em *{$carbon->format('d/m/Y')}*.\n\n";
            if (!empty($suggestions)) {
                $msg .= "Datas com horários disponíveis:\n\n";
                foreach ($suggestions as $sug) {
                    $msg .= "• {$sug}\n";
                }
                $msg .= "\nDigite a data desejada (DD/MM):";
            } else {
                $msg .= "Tente outra data (DD/MM).";
            }
            $this->send($conversation->phone, $msg);
            $this->conversationService->updateState($conversation, 'choosing_date');
            return;
        }

        $msg = "Data: *{$carbon->format('d/m/Y')}*\n\nHorários disponíveis:\n\n";
        foreach ($slots as $index => $slot) {
            $emoji = $this->emojiNumber($index + 1);
            $msg .= "{$emoji} {$slot}\n";
        }
        $msg .= "\n0️⃣ Voltar\n\nDigite o número do horário:";

        $this->conversationService->updateState($conversation, 'choosing_time', [
            'selected_date' => $carbon->format('Y-m-d'),
            'slots' => $slots,
        ]);

        $this->send($conversation->phone, $msg);
    }

    private function handleChoosingProfessionalFromBack(Conversation $conversation): void
    {
        $users = User::where('active', true)->where('role', 'attendant')->get();

        $this->conversationService->updateState($conversation, 'choosing_professional', [
            'services' => $conversation->getCtx('services', []),
            'selected_service_id' => $conversation->getCtx('selected_service_id'),
        ]);

        $msg = "Escolha o profissional:\n\n";
        foreach ($users as $index => $user) {
            $emoji = $this->emojiNumber($index + 1);
            $msg .= "{$emoji} {$user->name}\n";
        }
        $msg .= "\n0️⃣ Voltar\n\nDigite o número do profissional:";

        $this->send($conversation->phone, $msg);
    }

    private function handleChoosingTime(Conversation $conversation, string $text): void
    {
        if (in_array($text, ['0'])) {
            $selectedDate = $conversation->getCtx('selected_date');
            $this->send($conversation->phone, "Escolha uma data (DD/MM).\n\n0️⃣ Voltar ao profissional");
            $this->conversationService->updateState($conversation, 'choosing_date');
            return;
        }

        $slots = $conversation->getCtx('slots', []);
        $index = (int) $text - 1;

        if ($index < 0 || $index >= count($slots)) {
            $this->send($conversation->phone, "Opção inválida. Digite o número correspondente ao horário:\n\n0️⃣ Voltar");
            return;
        }

        $time = $slots[$index];
        $date = $conversation->getCtx('selected_date');
        $rescheduleId = $conversation->getCtx('reschedule_appointment_id');

        if ($rescheduleId) {
            $appointment = Appointment::find($rescheduleId);
            if ($appointment) {
                $appointment->update([
                    'start' => Carbon::parse("{$date} {$time}"),
                    'end' => Carbon::parse("{$date} {$time}")->addMinutes($appointment->services->first()->duration_min),
                ]);

                $serviceNames = $appointment->services->pluck('name')->implode(', ');
                $msg = "Horário alterado com sucesso!\n\n"
                    . "Serviço: {$serviceNames}\n"
                    . "Nova data: {$date}\n"
                    . "Novo horário: {$time}\n\n"
                    . "0️⃣ Voltar ao menu.";

                $this->send($conversation->phone, $msg);
                $this->conversationService->reset($conversation);
                return;
            }
        }

        $serviceId = $conversation->getCtx('selected_service_id');
        $service = Service::find($serviceId);
        $userId = $conversation->getCtx('selected_user_id');
        $user = User::find($userId);

        $customerName = $conversation->customer?->name;

        if (!$customerName || $customerName === 'Cliente WhatsApp') {
            $this->conversationService->updateState($conversation, 'asking_name', [
                'selected_time' => $time,
            ]);
            $this->send($conversation->phone, "Qual é o seu nome?");
            return;
        }

        $msg = "*Resumo do Agendamento*\n\n"
            . "Serviço: {$service->name}\n"
            . "Profissional: {$user->name}\n"
            . "Data: {$date}\n"
            . "Horário: {$time}\n"
            . "Valor: R$ " . number_format($service->price, 2, ',', '.') . "\n"
            . "Cliente: {$customerName}\n\n"
            . "1️⃣ Confirmar agendamento\n"
            . "0️⃣ Cancelar e voltar ao menu";

        $this->conversationService->updateState($conversation, 'confirming', [
            'selected_time' => $time,
        ]);

        $this->send($conversation->phone, $msg);
    }

    private function handleAskName(Conversation $conversation, string $text): void
    {
        if (in_array($text, ['0'])) {
            $this->conversationService->reset($conversation);
            $this->send($conversation->phone, $conversation->company->getDefaultWelcomeMessage());
            return;
        }

        $name = trim($text);

        if (empty($name) || mb_strlen($name) < 2) {
            $this->send($conversation->phone, "Por favor, digite seu nome:");
            return;
        }

        $conversation->customer->update(['name' => $name]);

        $serviceId = $conversation->getCtx('selected_service_id');
        $service = Service::find($serviceId);
        $userId = $conversation->getCtx('selected_user_id');
        $user = User::find($userId);
        $date = $conversation->getCtx('selected_date');
        $time = $conversation->getCtx('selected_time');

        $msg = "*Resumo do Agendamento*\n\n"
            . "Serviço: {$service->name}\n"
            . "Profissional: {$user->name}\n"
            . "Data: {$date}\n"
            . "Horário: {$time}\n"
            . "Valor: R$ " . number_format($service->price, 2, ',', '.') . "\n"
            . "Cliente: {$name}\n\n"
            . "1️⃣ Confirmar agendamento\n"
            . "0️⃣ Cancelar e voltar ao menu";

        $this->conversationService->updateState($conversation, 'confirming', [
            'selected_time' => $time,
        ]);

        $this->send($conversation->phone, $msg);
    }

    private function handleConfirming(Conversation $conversation, string $text): void
    {
        if (in_array($text, ['0'])) {
            $this->conversationService->reset($conversation);
            $this->send($conversation->phone, $conversation->company->getDefaultWelcomeMessage());
            return;
        }

        if ($text === '1') {
            $this->createAppointment($conversation);
        } else {
            $this->send($conversation->phone, "1️⃣ Confirmar ou 0️⃣ Voltar ao menu:");
        }
    }

    private function createAppointment(Conversation $conversation): void
    {
        $phone = $conversation->phone;
        $serviceId = $conversation->getCtx('selected_service_id');
        $userId = $conversation->getCtx('selected_user_id');
        $date = $conversation->getCtx('selected_date');
        $time = $conversation->getCtx('selected_time');

        $service = Service::find($serviceId);
        $user = User::find($userId);

        if (!$service || !$user) {
            $this->send($phone, "Erro ao criar agendamento. Tente novamente.");
            $this->conversationService->reset($conversation);
            return;
        }

        $conversation->load('customer');
        $customer = $conversation->customer;

        if (!$customer) {
            $customer = Customer::where('phone', $phone)->first();
            if ($customer) {
                $conversation->update(['customer_id' => $customer->id]);
            }
        }

        if (!$customer) {
            $customerName = 'Cliente WhatsApp';
            $customer = Customer::create([
                'name' => $customerName,
                'phone' => $phone,
            ]);
            $conversation->update(['customer_id' => $customer->id]);
        }

        $start = Carbon::parse("{$date} {$time}");
        $end = $start->copy()->addMinutes($service->duration_min);

        $appointment = Appointment::create([
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'service_id' => $service->id,
            'start' => $start,
            'end' => $end,
            'status' => 'scheduled',
        ]);

        $appointment->services()->attach($service->id, [
            'price' => $service->price,
            'duration_min' => $service->duration_min,
        ]);

        $customer->name = 'Cliente WhatsApp';
        $customer->phone = $phone;
        $customer->save();

        $this->whatsapp->sendConfirmation($appointment);

        $reagendarLink = url('/reagendar/' . $appointment->confirmation_token);
        $cancelarLink = url('/cancelar/' . $appointment->confirmation_token);

        $msg = "*Agendamento confirmado!*\n\n"
            . "Serviço: {$service->name}\n"
            . "Profissional: {$user->name}\n"
            . "Data: {$start->format('d/m/Y')}\n"
            . "Horário: {$start->format('H:i')}\n"
            . "Valor: R$ " . number_format($service->price, 2, ',', '.') . "\n\n"
            . "🔗 Reagendar: {$reagendarLink}\n"
            . "❌ Cancelar: {$cancelarLink}\n\n"
            . "0️⃣ Voltar ao menu principal.";

        $this->send($phone, $msg);
        $this->conversationService->reset($conversation);
    }

    private function sendWorkingHours(Conversation $conversation, Company $company): void
    {
        $days = [
            0 => 'Domingo',
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
        ];

        $workingHours = WorkingHour::where('active', true)
            ->orderBy('day_of_week')
            ->get()
            ->groupBy('day_of_week');

        $msg = "*Horários de Funcionamento*\n\n";

        foreach ($days as $dayNum => $dayName) {
            if ($dayNum === Carbon::SUNDAY) {
                $msg .= "{$dayName}: Fechado\n";
                continue;
            }

            if (isset($workingHours[$dayNum])) {
                $hours = $workingHours[$dayNum];
                $times = $hours->map(fn($wh) => "{$wh->start_time} às {$wh->end_time}")->implode(' / ');
                $msg .= "{$dayName}: {$times}\n";
            } else {
                $msg .= "{$dayName}: Fechado\n";
            }
        }

        $msg .= "\n0️⃣ Voltar ao menu.";

        $this->conversationService->updateState($conversation, 'showing_hours');
        $this->send($conversation->phone, $msg);
    }

    private function sendServiceList(Conversation $conversation): void
    {
        $services = Service::where('active', true)->orderBy('name')->get();

        if ($services->isEmpty()) {
            $this->send($conversation->phone, "No momento não há serviços disponíveis.");
            $this->send($conversation->phone, "0️⃣ Voltar ao menu.");
            return;
        }

        $msg = "*Serviços e Preços*\n\n";

        foreach ($services as $service) {
            $msg .= "• *{$service->name}*\n";
            $msg .= "  R$ " . number_format($service->price, 2, ',', '.') . " | {$service->duration_min}min\n";
            if ($service->description) {
                $msg .= "  {$service->description}\n";
            }
            $msg .= "\n";
        }

        $msg .= "1️⃣ Agendar um horário.\n0️⃣ Voltar ao menu.";

        $this->send($conversation->phone, $msg);
    }

    private function startConsultAppointments(Conversation $conversation): void
    {
        $customer = $conversation->customer;

        if (!$customer) {
            $this->send($conversation->phone, "Você ainda não possui agendamentos.\n\n1️⃣ Agendar um horário.\n0️⃣ Voltar ao menu.");
            $this->conversationService->reset($conversation);
            return;
        }

        $appointments = Appointment::where('customer_id', $customer->id)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->where('start', '>=', now())
            ->with('services', 'user')
            ->orderBy('start')
            ->get();

        if ($appointments->isEmpty()) {
            $this->send($conversation->phone, "Você não possui agendamentos futuros.\n\n1️⃣ Agendar.\n0️⃣ Voltar ao menu.");
            $this->conversationService->reset($conversation);
            return;
        }

        $msg = "*Seus Próximos Agendamentos*\n\n";

        foreach ($appointments as $index => $appointment) {
            $serviceNames = $appointment->services->pluck('name')->implode(', ');
            $totalPrice = $appointment->services->sum('pivot.price');
            $reagendarLink = url('/reagendar/' . $appointment->confirmation_token);
            $cancelarLink = url('/cancelar/' . $appointment->confirmation_token);

            $msg .= $appointment->start->format('d/m/Y H:i') . "\n";
            $msg .= "   Serviço: {$serviceNames}\n";
            $msg .= "   Profissional: {$appointment->user->name}\n";
            $msg .= "   Valor: R$ " . number_format($totalPrice, 2, ',', '.') . "\n";
            $msg .= "   Status: " . $this->translateStatus($appointment->status) . "\n";
            $msg .= "   🔗 Reagendar: {$reagendarLink}\n";
            $msg .= "   ❌ Cancelar: {$cancelarLink}\n\n";
        }

        $msg .= "1️⃣ Agendar outro.\n0️⃣ Voltar ao menu.";

        $this->conversationService->updateState($conversation, 'consulting_appointments', [
            'appointment_ids' => $appointments->pluck('id')->toArray(),
        ]);

        $this->send($conversation->phone, $msg);
    }

    private function handleConsultingAppointments(Conversation $conversation, string $text): void
    {
        if (in_array($text, ['0'])) {
            $this->conversationService->reset($conversation);
            $this->send($conversation->phone, $conversation->company->getDefaultWelcomeMessage());
            return;
        }

        if ($text === '1') {
            $this->conversationService->updateState($conversation, 'initial');
            $this->startBooking($conversation);
        } elseif ($text === '3') {
            $this->startReschedule($conversation);
        } elseif ($text === '5') {
            $this->conversationService->updateState($conversation, 'initial');
            $this->startCancellation($conversation);
        } else {
            $this->send($conversation->phone, "1️⃣ Agendar, 3️⃣ Alterar, 5️⃣ Cancelar ou 0️⃣ Voltar:");
        }
    }

    private function startCancellation(Conversation $conversation): void
    {
        $customer = $conversation->customer;

        if (!$customer) {
            $this->send($conversation->phone, "Você não possui agendamentos para cancelar.\n\n0️⃣ Voltar ao menu.");
            $this->conversationService->reset($conversation);
            return;
        }

        $appointments = Appointment::where('customer_id', $customer->id)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->where('start', '>=', now())
            ->with('services')
            ->orderBy('start')
            ->get();

        if ($appointments->isEmpty()) {
            $this->send($conversation->phone, "Você não possui agendamentos futuros para cancelar.\n\n0️⃣ Voltar ao menu.");
            $this->conversationService->reset($conversation);
            return;
        }

        $msg = "*Cancelar Agendamento*\n\nEscolha o agendamento que deseja cancelar:\n\n";

        foreach ($appointments as $index => $appointment) {
            $serviceNames = $appointment->services->pluck('name')->implode(', ');
            $emoji = $this->emojiNumber($index + 1);
            $msg .= "{$emoji} {$serviceNames} - {$appointment->start->format('d/m/Y H:i')}\n";
        }

        $msg .= "\n0️⃣ Voltar ao menu\n\nDigite o número do agendamento:";

        $this->conversationService->updateState($conversation, 'cancelling', [
            'appointment_ids' => $appointments->pluck('id')->toArray(),
        ]);

        $this->send($conversation->phone, $msg);
    }

    private function handleCancelling(Conversation $conversation, string $text): void
    {
        if (in_array($text, ['0'])) {
            $this->conversationService->reset($conversation);
            $this->send($conversation->phone, $conversation->company->getDefaultWelcomeMessage());
            return;
        }

        $appointmentIds = $conversation->getCtx('appointment_ids', []);
        $index = (int) $text - 1;

        if ($index < 0 || $index >= count($appointmentIds)) {
            $this->send($conversation->phone, "Opção inválida. Digite o número correspondente ao agendamento:");
            return;
        }

        $appointmentId = $appointmentIds[$index];
        $appointment = Appointment::with('services', 'customer')->find($appointmentId);

        if (!$appointment) {
            $this->send($conversation->phone, "Agendamento não encontrado.");
            $this->conversationService->reset($conversation);
            return;
        }

        $appointment->update(['status' => 'cancelled']);

        $this->whatsapp->sendCancellation($appointment);

        $serviceNames = $appointment->services->pluck('name')->implode(', ');

        $msg = "Agendamento cancelado com sucesso!\n\n"
            . "Serviço: {$serviceNames}\n"
            . "Data: {$appointment->start->format('d/m/Y H:i')}\n\n"
            . "1️⃣ Agendar novamente.\n0️⃣ Voltar ao menu.";

        $this->send($conversation->phone, $msg);
        $this->conversationService->reset($conversation);
    }

    private function startReschedule(Conversation $conversation): void
    {
        $customer = $conversation->customer;

        if (!$customer) {
            $this->send($conversation->phone, "Você não possui agendamentos para alterar.\n\n0️⃣ Voltar ao menu.");
            $this->conversationService->reset($conversation);
            return;
        }

        $appointments = Appointment::where('customer_id', $customer->id)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->where('start', '>=', now())
            ->with('services')
            ->orderBy('start')
            ->get();

        if ($appointments->isEmpty()) {
            $this->send($conversation->phone, "Você não possui agendamentos futuros.\n\n0️⃣ Voltar ao menu.");
            $this->conversationService->reset($conversation);
            return;
        }

        $msg = "*Alterar Horário*\n\nEscolha o agendamento que deseja alterar:\n\n";

        $emojis = ['1️⃣','2️⃣','3️⃣','4️⃣','5️⃣','6️⃣','7️⃣','8️⃣','9️⃣'];
        foreach ($appointments as $index => $appointment) {
            $serviceNames = $appointment->services->pluck('name')->implode(', ');
            $emoji = $emojis[$index] ?? ($index + 1);
            $msg .= "{$emoji} {$serviceNames} - {$appointment->start->format('d/m/Y H:i')}\n";
        }

        $msg .= "\n0️⃣ Voltar ao menu\n\nDigite o número do agendamento:";

        $this->conversationService->updateState($conversation, 'rescheduling', [
            'appointment_ids' => $appointments->pluck('id')->toArray(),
        ]);

        $this->send($conversation->phone, $msg);
    }

    private function handleRescheduling(Conversation $conversation, string $text): void
    {
        if (in_array($text, ['0'])) {
            $this->conversationService->reset($conversation);
            $this->send($conversation->phone, $conversation->company->getDefaultWelcomeMessage());
            return;
        }

        $appointmentIds = $conversation->getCtx('appointment_ids', []);
        $index = (int) $text - 1;

        if ($index < 0 || $index >= count($appointmentIds)) {
            $this->send($conversation->phone, "Opção inválida. Digite o número do agendamento:\n\n0️⃣ Voltar");
            return;
        }

        $appointmentId = $appointmentIds[$index];
        $appointment = Appointment::with('services', 'user')->find($appointmentId);

        if (!$appointment) {
            $this->send($conversation->phone, "Agendamento não encontrado.");
            $this->conversationService->reset($conversation);
            return;
        }

        $this->conversationService->updateState($conversation, 'choosing_date', [
            'reschedule_appointment_id' => $appointmentId,
        ]);

        $this->send($conversation->phone, "Escolha a nova data (DD/MM).\n\n0️⃣ Voltar");
    }

    private function sendLocation(Conversation $conversation): void
    {
        $company = $conversation->company;

        $msg = "*Localização*\n\n"
            . "{$company->name}\n";

        if (!empty($company->phone)) {
            $msg .= "{$company->phone}\n";
        }

        if (!empty($company->email)) {
            $msg .= "{$company->email}\n";
        }

        $msg .= "\n0️⃣ Voltar ao menu.";

        $this->conversationService->updateState($conversation, 'showing_location');
        $this->send($conversation->phone, $msg);
    }

    private function handleShowingLocation(Conversation $conversation, string $text, Company $company): void
    {
        if (in_array($text, ['0'])) {
            $this->conversationService->reset($conversation);
            $this->send($conversation->phone, $company->getDefaultWelcomeMessage());
            return;
        }

        $this->conversationService->reset($conversation);
        $this->send($conversation->phone, $company->getDefaultWelcomeMessage());
    }

    private function send(string $phone, string $message): void
    {
        try {
            $this->whatsapp->send($phone, $message);
        } catch (\Exception $e) {
            \Log::error('[Bot] Erro ao enviar mensagem:', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }

        if ($this->currentConversation) {
            $this->conversationService->logMessage($this->currentConversation, 'outbound', $message);
            $this->currentConversation->update(['last_message_at' => now()]);
        }
    }

    private function translateStatus(string $status): string
    {
        return match ($status) {
            'scheduled' => 'Agendado',
            'confirmed' => 'Confirmado',
            'in_progress' => 'Em andamento',
            'completed' => 'Concluído',
            'cancelled' => 'Cancelado',
            'no_show' => 'Não compareceu',
            default => $status,
        };
    }

    private function parseDate(string $text): ?string
    {
        $text = trim($text);

        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})$/', $text, $matches)) {
            $day = (int) $matches[1];
            $month = (int) $matches[2];
            $year = now()->year;

            if ($month < now()->month) {
                $year++;
            }

            $date = Carbon::createFromDate($year, $month, $day);
            if ($date->isValid()) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    private function getAvailableSlots(Conversation $conversation, string $date): array
    {
        $serviceId = $conversation->getCtx('selected_service_id');
        $userId = $conversation->getCtx('selected_user_id');
        $service = Service::find($serviceId);

        if (!$service) {
            return [];
        }

        $dateCarbon = Carbon::parse($date);
        $dayOfWeek = $dateCarbon->dayOfWeek;

        $workingHours = WorkingHour::where('day_of_week', $dayOfWeek)
            ->where('active', true)
            ->where(function ($q) use ($userId) {
                $q->whereNull('user_id');
                if ($userId) {
                    $q->orWhere('user_id', $userId);
                }
            })
            ->get();

        if ($workingHours->isEmpty()) {
            return [];
        }

        $startWork = $workingHours->min('start_time');

        $hasMidnight = $workingHours->contains('end_time', '00:00:00');
        $endWork = $hasMidnight ? '00:00:00' : $workingHours->max('end_time');

        $endIsMidnight = ($endWork === '00:00:00');

        $existingAppointments = Appointment::whereDate('start', $date)
            ->whereIn('status', ['scheduled', 'confirmed', 'in_progress'])
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->get();

        $slots = [];
        $current = Carbon::parse("{$date} {$startWork}");

        if ($endIsMidnight) {
            $end = Carbon::parse("{$date} 23:59:00");
        } else {
            $end = Carbon::parse("{$date} {$endWork}");
        }

        while ($current->copy()->addMinutes($service->duration_min)->lte($end)) {
            $slotEnd = $current->copy()->addMinutes($service->duration_min);

            $overlaps = false;
            foreach ($existingAppointments as $appointment) {
                if ($current->lt($appointment->end) && $slotEnd->gt($appointment->start)) {
                    $overlaps = true;
                    break;
                }
            }

            if (!$overlaps && ($dateCarbon->isFuture() || !$dateCarbon->isToday() || $current->isFuture())) {
                $slots[] = $current->format('H:i');
            }

            $current->addMinutes(30);
        }

        return $slots;
    }

    private function getSuggestedDates(Conversation $conversation): array
    {
        $userId = $conversation->getCtx('selected_user_id');

        $workingDays = WorkingHour::where('active', true)
            ->where(function ($q) use ($userId) {
                $q->whereNull('user_id');
                if ($userId) {
                    $q->orWhere('user_id', $userId);
                }
            })
            ->pluck('day_of_week')
            ->unique()
            ->toArray();

        if (empty($workingDays)) {
            return [];
        }

        $suggestions = [];
        $checkDate = Carbon::tomorrow();
        $limit = Carbon::tomorrow()->addDays(7);

        while ($checkDate->lte($limit)) {
            $dayOfWeek = $checkDate->dayOfWeek;

            if (in_array($dayOfWeek, $workingDays)) {
                $suggestions[] = $checkDate->format('d/m') . " ({$checkDate->locale('pt_BR')->isoFormat('ddd')})";
            }

            $checkDate->addDay();
        }

        return $suggestions;
    }

    private function emojiNumber(int $number): string
    {
        $emojis = [
            0 => '0️⃣', 1 => '1️⃣', 2 => '2️⃣', 3 => '3️⃣', 4 => '4️⃣',
            5 => '5️⃣', 6 => '6️⃣', 7 => '7️⃣', 8 => '8️⃣', 9 => '9️⃣',
        ];
        $digits = str_split((string) $number);
        $result = '';
        foreach ($digits as $digit) {
            $result .= $emojis[(int) $digit];
        }
        return $result;
    }
}
