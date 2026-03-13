<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\{Subscription, SubscriptionPayment};
use App\Services\{LogService, NotificationService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OnvoWebhookController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    public function handle(Request $request)
    {
        // Validate webhook secret
        $secret = config('services.onvopay.webhook_secret');
        if ($secret && $request->header('X-Webhook-Secret') !== $secret) {
            LogService::warning('webhook_invalid_secret', 'Webhook recibido con secret inválido', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $event = $request->input('event');
        $data  = $request->input('data', []);

        LogService::info('webhook_received', "Webhook ONVO: {$event}", ['data' => $data]);

        match($event) {
            'subscription.activated',
            'subscription.payment.succeeded' => $this->handleSubscriptionActivated($data),
            'subscription.payment.failed'    => $this->handleSubscriptionFailed($data),
            'subscription.renewal.succeeded' => $this->handleRenewal($data),
            'subscription.renewal.failed'    => $this->handleRenewalFailed($data),
            'subscription.canceled'          => $this->handleCanceled($data),
            default => LogService::info('webhook_unhandled', "Evento no manejado: {$event}", $data),
        };

        return response()->json(['received' => true]);
    }

    private function handleSubscriptionActivated(array $data): void
    {
        $onvoSubId = $data['id'] ?? $data['subscriptionId'] ?? null;
        if (!$onvoSubId) return;

        $sub = Subscription::where('onvo_subscription_id', $onvoSubId)->first();
        if (!$sub) {
            LogService::warning('webhook_sub_not_found', "Suscripción ONVO no encontrada: {$onvoSubId}");
            return;
        }

        DB::transaction(function () use ($sub, $data) {
            $sub->update([
                'status'          => 'active',
                'starts_at'       => now(),
                'ends_at'         => now()->addMonth(),
                'last_payment_at' => now(),
            ]);

            // Mark latest pending payment as succeeded
            $sub->payments()->where('status','pending')->latest()->first()
                ?->update(['status' => 'succeeded', 'paid_at' => now()]);
        });

        $this->notifications->subscriptionActivated($sub->user);

        LogService::subscription('subscription_activated',
            "Suscripción activada para {$sub->user->name} (plan {$sub->plan?->name})",
            ['subscription_id' => $sub->id, 'onvo_id' => $onvoSubId],
            $sub->user_id
        );
    }

    private function handleSubscriptionFailed(array $data): void
    {
        $onvoSubId = $data['id'] ?? $data['subscriptionId'] ?? null;
        $sub = $onvoSubId ? Subscription::where('onvo_subscription_id', $onvoSubId)->first() : null;
        if (!$sub) return;

        $sub->update(['status' => 'failed']);
        $sub->payments()->where('status','pending')->latest()->first()
            ?->update(['status' => 'rejected']);

        LogService::payment('subscription_payment_failed',
            "Pago fallido para {$sub->user->name}",
            ['subscription_id' => $sub->id, 'onvo_id' => $onvoSubId],
            $sub->user_id
        );
    }

    private function handleRenewal(array $data): void
    {
        $onvoSubId = $data['id'] ?? $data['subscriptionId'] ?? null;
        $sub = $onvoSubId ? Subscription::where('onvo_subscription_id', $onvoSubId)->first() : null;
        if (!$sub) return;

        DB::transaction(function () use ($sub, $data) {
            $sub->update([
                'status'          => 'active',
                'ends_at'         => now()->addMonth(),
                'last_payment_at' => now(),
            ]);
            SubscriptionPayment::create([
                'subscription_id' => $sub->id,
                'amount'          => $sub->price,
                'method'          => 'card',
                'status'          => 'succeeded',
                'paid_at'         => now(),
            ]);
        });

        LogService::subscription('subscription_renewed',
            "Suscripción renovada para {$sub->user->name}",
            ['subscription_id' => $sub->id],
            $sub->user_id
        );
    }

    private function handleRenewalFailed(array $data): void
    {
        $onvoSubId = $data['id'] ?? $data['subscriptionId'] ?? null;
        $sub = $onvoSubId ? Subscription::where('onvo_subscription_id', $onvoSubId)->first() : null;
        if (!$sub) return;

        $sub->update(['status' => 'past_due']);

        LogService::payment('subscription_renewal_failed',
            "Renovación fallida para {$sub->user->name}",
            ['subscription_id' => $sub->id],
            $sub->user_id
        );
    }

    private function handleCanceled(array $data): void
    {
        $onvoSubId = $data['id'] ?? $data['subscriptionId'] ?? null;
        $sub = $onvoSubId ? Subscription::where('onvo_subscription_id', $onvoSubId)->first() : null;
        if (!$sub) return;

        $sub->update(['status' => 'canceled']);

        LogService::subscription('subscription_canceled',
            "Suscripción cancelada para {$sub->user->name}",
            ['subscription_id' => $sub->id],
            $sub->user_id
        );
    }
}
