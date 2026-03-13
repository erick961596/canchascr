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
        $expectedSecret = config('services.onvopay.webhook_secret');
        $receivedSecret = $request->header('X-Webhook-Secret');

        if (!$expectedSecret || $receivedSecret !== $expectedSecret) {
            LogService::warning('webhook_invalid_secret', 'Webhook recibido con secret inválido', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // ONVO usa "type", no "event"
        $event = $request->input('type');
        $data  = $request->input('data', []);

        LogService::info('webhook_received', "Webhook ONVO: {$event}", ['data' => $data]);

        match($event) {
            // Pago único o primer pago de suscripción exitoso
            'payment-intent.succeeded'      => $this->handlePaymentIntentSucceeded($data),

            // Renovación mensual exitosa
            'subscription.renewal.succeeded' => $this->handleRenewal($data),

            // Pagos fallidos
            'payment-intent.failed'          => $this->handlePaymentIntentFailed($data),
            'subscription.renewal.failed'    => $this->handleRenewalFailed($data),

            // Pago diferido (SINPE procesando)
            'payment-intent.deferred'        => $this->handleDeferred($data),

            default => LogService::info('webhook_unhandled', "Evento no manejado: {$event}", $data),
        };

        return response()->json(['received' => true]);
    }

    // -----------------------------------------------------------------------
    // payment-intent.succeeded
    // Activa la suscripción cuando el primer pago es confirmado
    // data.id = paymentIntentId — hay que buscar por onvo_payment_intent_id
    // -----------------------------------------------------------------------
    private function handlePaymentIntentSucceeded(array $data): void
    {
        $paymentIntentId = $data['id'] ?? null;
        if (!$paymentIntentId) return;

        $sub = Subscription::where('onvo_payment_intent_id', $paymentIntentId)->first();

        if (!$sub) {
            LogService::warning('webhook_sub_not_found',
                "No se encontró suscripción para payment intent: {$paymentIntentId}",
                ['data' => $data]
            );
            return;
        }

        // Ya estaba activa (webhook duplicado), ignorar
        if ($sub->status === 'active') return;

        DB::transaction(function () use ($sub) {
            $sub->update([
                'status'          => 'active',
                'starts_at'       => now(),
                'ends_at'         => now()->addMonth(),
                'last_payment_at' => now(),
            ]);

            $sub->payments()->where('status', 'pending')->latest()->first()
                ?->update(['status' => 'succeeded', 'paid_at' => now()]);
        });

        $this->notifications->subscriptionActivated($sub->user);

        LogService::subscription('subscription_activated',
            "Suscripción activada para {$sub->user->name} (plan {$sub->plan?->name})",
            ['subscription_id' => $sub->id, 'payment_intent_id' => $paymentIntentId],
            $sub->user_id
        );
    }

    // -----------------------------------------------------------------------
    // subscription.renewal.succeeded
    // data.subscriptionId (no data.id)
    // -----------------------------------------------------------------------
    private function handleRenewal(array $data): void
    {
        $onvoSubId = $data['subscriptionId'] ?? null;
        if (!$onvoSubId) return;

        $sub = Subscription::where('onvo_subscription_id', $onvoSubId)->first();
        if (!$sub) return;

        DB::transaction(function () use ($sub, $data) {
            $sub->update([
                'status'          => 'active',
                'ends_at'         => isset($data['periodEnd'])
                    ? \Carbon\Carbon::parse($data['periodEnd'])
                    : now()->addMonth(),
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
            ['subscription_id' => $sub->id, 'onvo_id' => $onvoSubId],
            $sub->user_id
        );
    }

    // -----------------------------------------------------------------------
    // payment-intent.failed
    // data.id = paymentIntentId
    // -----------------------------------------------------------------------
    private function handlePaymentIntentFailed(array $data): void
    {
        $paymentIntentId = $data['id'] ?? null;
        if (!$paymentIntentId) return;

        $sub = Subscription::where('onvo_payment_intent_id', $paymentIntentId)->first();
        if (!$sub) return;

        $sub->update(['status' => 'failed']);
        $sub->payments()->where('status', 'pending')->latest()->first()
            ?->update(['status' => 'rejected']);

        LogService::payment('subscription_payment_failed',
            "Pago fallido para {$sub->user->name} — " . ($data['error']['message'] ?? 'sin detalle'),
            ['subscription_id' => $sub->id, 'error' => $data['error'] ?? null],
            $sub->user_id
        );
    }

    // -----------------------------------------------------------------------
    // subscription.renewal.failed
    // data.subscriptionId
    // -----------------------------------------------------------------------
    private function handleRenewalFailed(array $data): void
    {
        $onvoSubId = $data['subscriptionId'] ?? null;
        if (!$onvoSubId) return;

        $sub = Subscription::where('onvo_subscription_id', $onvoSubId)->first();
        if (!$sub) return;

        $sub->update(['status' => 'past_due']);

        LogService::payment('subscription_renewal_failed',
            "Renovación fallida para {$sub->user->name} — intento #{$data['attemptCount']}",
            [
                'subscription_id'  => $sub->id,
                'attempt_count'    => $data['attemptCount'] ?? null,
                'next_attempt'     => $data['nextPaymentAttempt'] ?? null,
                'error'            => $data['error'] ?? null,
            ],
            $sub->user_id
        );
    }

    // -----------------------------------------------------------------------
    // payment-intent.deferred
    // Pago en proceso (ej: SINPE), no activar aún — solo loguear
    // -----------------------------------------------------------------------
    private function handleDeferred(array $data): void
    {
        LogService::info('webhook_payment_deferred',
            "Pago diferido en proceso — customer: " . ($data['customerId'] ?? '?'),
            ['data' => $data]
        );
    }
}
