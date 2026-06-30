<?php

namespace App\Services\Bot;

use App\Models\Company;
use App\Models\Conversation;
use App\Models\Customer;

class ConversationService
{
    public function findOrCreate(string $phone, Company $company): Conversation
    {
        $conversation = Conversation::where('company_id', $company->id)
            ->where('phone', $phone)
            ->first();

        if (!$conversation) {
            $customer = Customer::where('phone', $phone)->first();

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

            if (!$conversation->customer_id) {
                $customer = Customer::where('phone', $phone)->first();
                if ($customer) {
                    $conversation->update(['customer_id' => $customer->id]);
                }
            }
        }

        return $conversation;
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
