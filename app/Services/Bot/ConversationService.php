<?php

namespace App\Services\Bot;

use App\Models\Company;
use App\Models\Conversation;
use App\Models\Customer;

class ConversationService
{
    public function findOrCreate(string $phone, Company $company, ?string $pushName = null): Conversation
    {
        $conversation = Conversation::where('company_id', $company->id)
            ->where('phone', $phone)
            ->first();

        $customer = Customer::where('phone', $phone)->first();

        // Sempre atualizar nome se pushName e valido e diferente do atual
        $resolvedName = $this->resolveName($pushName, $customer);

        if ($customer && $resolvedName && $customer->name !== $resolvedName) {
            $customer->update(['name' => $resolvedName]);
        }

        if (!$conversation) {
            if (!$customer) {
                $customer = Customer::create([
                    'name' => $resolvedName ?: 'Cliente WhatsApp',
                    'phone' => $phone,
                ]);
            }

            $conversation = Conversation::create([
                'company_id' => $company->id,
                'phone' => $phone,
                'customer_id' => $customer?->id,
                'state' => 'initial',
                'context' => null,
                'last_message_at' => now(),
            ]);
        } else {
            $conversation->update(['last_message_at' => now()]);

            if (!$conversation->customer_id && $customer) {
                $conversation->update(['customer_id' => $customer->id]);
            } elseif (!$conversation->customer_id && !$customer) {
                $customer = Customer::create([
                    'name' => $resolvedName ?: 'Cliente WhatsApp',
                    'phone' => $phone,
                ]);
                $conversation->update(['customer_id' => $customer->id]);
            }
        }

        return $conversation;
    }

    private function resolveName(?string $pushName, ?Customer $customer): ?string
    {
        if ($pushName && trim($pushName) !== '' && trim($pushName) !== 'Cliente WhatsApp') {
            return trim($pushName);
        }

        if ($customer && $customer->name && $customer->name !== 'Cliente WhatsApp') {
            return $customer->name;
        }

        if ($pushName && trim($pushName) !== '') {
            return trim($pushName);
        }

        return null;
    }

    public function updateState(Conversation $conversation, string $state, ?array $context = null): void
    {
        $update = ['state' => $state];
        if ($context !== null) {
            $existing = $conversation->context ?? [];
            $update['context'] = array_merge($existing, $context);
        }
        $conversation->update($update);
    }

    public function reset(Conversation $conversation): void
    {
        $conversation->update([
            'state' => 'initial',
            'context' => null,
        ]);
    }

    public function logMessage(Conversation $conversation, string $direction, string $content): void
    {
        $conversation->messages()->create([
            'direction' => $direction,
            'content' => $content,
        ]);
    }

    public function linkCustomer(Conversation $conversation, Customer $customer): void
    {
        if (!$conversation->customer_id) {
            $conversation->update(['customer_id' => $customer->id]);
        }
    }
}
