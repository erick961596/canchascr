<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\{Plan, Subscription, SubscriptionPayment};
use App\Services\LogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Http, Log, Storage};

class SubscriptionController extends Controller
{
    private string $apiBase = 'https://api.onvopay.com/v1';

    public function index()
    {
        $plans        = Plan::where('active', true)->orderBy('price')->get();
        $subscription = auth()->user()->subscriptions()->with('plan')->latest()->first();
        return view('pages.owner.subscription.index', compact('plans', 'subscription'));
    }

    public function create(Request $request)
    {
        $request->validate([
            'plan_id'        => 'required|exists:plans,id',
            'payment_method' => 'required|in:card,manual',
            // tarjeta
            'card_holder'    => 'required_if:payment_method,card',
            'card_number'    => 'required_if:payment_method,card',
            'card_exp_month' => 'required_if:payment_method,card',
            'card_exp_year'  => 'required_if:payment_method,card',
            'card_cvc'       => 'required_if:payment_method,card',
            // manual
            'uploaded_proof_path' => 'required_if:payment_method,manual',
        ]);

        $user = auth()->user();
        $plan = Plan::findOrFail($request->plan_id);

        if ($request->payment_method === 'manual') {
            return $this->createManualSubscription($request, $plan, $user);
        }

        return $this->createCardSubscription($request, $plan, $user);
    }

    public function uploadProof(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120']);

        $path = Storage::disk('s3')->put(
            'subscriptions/proofs/' . auth()->id(),
            $request->file('file')
        );

        return response()->json(['path' => $path]);
    }

    // -----------------------------------------------------------------------
    // SINPE / Manual
    // -----------------------------------------------------------------------
    private function createManualSubscription($request, $plan, $user)
    {
        $proofPath = $request->input('uploaded_proof_path');
        if (!$proofPath) {
            return response()->json(['message' => 'El comprobante es obligatorio para SINPE.'], 422);
        }

        DB::transaction(function () use ($plan, $user, $proofPath) {
            $sub = Subscription::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'plan_id'        => $plan->id,
                    'status'         => 'pending',
                    'payment_method' => 'manual',
                    'price'          => $plan->price,
                    'starts_at'      => now(),
                    'ends_at'        => now()->addMonth(),
                ]
            );

            SubscriptionPayment::create([
                'subscription_id' => $sub->id,
                'amount'          => $plan->price,
                'method'          => 'manual',
                'status'          => 'pending',
                'proof_path'      => $proofPath,
            ]);
        });

        LogService::subscription('subscription_pending_sinpe',
            "Owner {$user->name} solicitó suscripción SINPE plan {$plan->name}",
            ['plan_id' => $plan->id],
            $user->id
        );

        // Manual puede ser redirect o JSON según si viene de fetch o form normal
        if (request()->expectsJson()) {
            return response()->json(['message' => 'Comprobante recibido. Activaremos tu suscripción en máximo 24 horas.']);
        }
        return redirect()->route('owner.subscription.index')
            ->with('success', 'Comprobante recibido. Activaremos tu suscripción en un plazo máximo de 24 horas.');
    }

    // -----------------------------------------------------------------------
    // Tarjeta — ONVO + 3DS
    // -----------------------------------------------------------------------
    private function createCardSubscription($request, $plan, $user)
    {
        try {
            $secret = config('services.onvopay.secret');

            if (!$secret) {
                abort(500, 'ONVO secret no configurado');
            }

            $priceId = $plan->onvopay_price_id ?? $plan->onvopay_id;
            if (!$priceId) {
                return response()->json([
                    'message' => 'Este plan no tiene configurado su Price ID en ONVO. Contactá al administrador.'
                ], 422);
            }

            // 1️⃣ Customer
            if (!$user->onvo_customer_id) {
                $customerResponse = Http::withToken($secret)
                    ->post("{$this->apiBase}/customers", [
                        'name'  => $user->name,
                        'email' => $user->email,
                    ]);

                if (!$customerResponse->successful()) {
                    return response()->json([
                        'message' => 'Error creando customer en ONVO.',
                        'debug'   => $customerResponse->json(),
                    ], 422);
                }

                $user->update(['onvo_customer_id' => $customerResponse->json('id')]);
            }

            // 2️⃣ Payment method
            $pmResponse = Http::withToken($secret)
                ->post("{$this->apiBase}/payment-methods", [
                    'customerId' => $user->onvo_customer_id,
                    'type'       => 'card',
                    'billing'    => ['name' => $request->card_holder],
                    'card'       => [
                        'number'     => preg_replace('/\s+/', '', $request->card_number),
                        'expMonth'   => (int) $request->card_exp_month,
                        'expYear'    => (int) $request->card_exp_year,
                        'cvv'        => $request->card_cvc,
                        'holderName' => $request->card_holder,
                    ],
                ]);

            if (!$pmResponse->successful()) {
                LogService::error('onvo_pm_failed',
                    "Error creando payment method para {$user->name}: " . $pmResponse->body(),
                    [], $user->id
                );
                return response()->json([
                    'message' => 'Error creando método de pago. Verificá los datos de la tarjeta.',
                    'debug'   => $pmResponse->json(),
                ], 422);
            }

            $paymentMethodId = $pmResponse->json('id');

            // 3️⃣ Subscription (allow_incomplete)
            $subResponse = Http::withToken($secret)
                ->post("{$this->apiBase}/subscriptions", [
                    'customerId'      => $user->onvo_customer_id,
                    'paymentBehavior' => 'allow_incomplete',
                    'items'           => [['priceId' => $priceId, 'quantity' => 1]],
                ]);

            if (!$subResponse->successful()) {
                return response()->json([
                    'message' => 'No se pudo crear la suscripción en ONVO.',
                    'debug'   => $subResponse->json(),
                ], 422);
            }

            $onvoSub         = $subResponse->json();
            $subscriptionId  = data_get($onvoSub, 'id');
            $paymentIntentId = data_get($onvoSub, 'latestInvoice.paymentIntentId');

            if (!$subscriptionId || !$paymentIntentId) {
                return response()->json([
                    'message' => 'ONVO no retornó un paymentIntent. Intentá más tarde.',
                    'debug'   => $onvoSub,
                ], 422);
            }

            // 4️⃣ Confirmar payment intent (3DS)
            $confirmResponse = Http::withToken($secret)
                ->post("{$this->apiBase}/payment-intents/{$paymentIntentId}/confirm", [
                    'paymentMethodId' => $paymentMethodId,
                ]);

            if (!$confirmResponse->successful()) {
                return response()->json([
                    'message' => 'No se pudo confirmar el pago.',
                    'debug'   => $confirmResponse->json(),
                ], 422);
            }

            $confirmedIntent = $confirmResponse->json();

            // 5️⃣ Guardar localmente como incomplete (webhook lo activa)
            $sub = Subscription::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'plan_id'                => $plan->id,
                    'status'                 => 'incomplete',
                    'payment_method'         => 'card',
                    'onvo_subscription_id'   => $subscriptionId,
                    'onvo_payment_intent_id' => $paymentIntentId,
                    'onvo_payment_method_id' => $paymentMethodId,
                    'price'                  => $plan->price,
                    'starts_at'              => now(),
                    'ends_at'                => now()->addMonth(),
                ]
            );

            SubscriptionPayment::create([
                'subscription_id' => $sub->id,
                'amount'          => $plan->price,
                'method'          => 'card',
                'status'          => 'pending',
            ]);

            LogService::subscription('subscription_card_initiated',
                "Owner {$user->name} inició suscripción tarjeta plan {$plan->name}",
                ['onvo_subscription_id' => $subscriptionId, 'plan_id' => $plan->id],
                $user->id
            );

            // 6️⃣ Respuesta al front — el JS decide si necesita 3DS
            return response()->json([
                'subscription_id'       => $subscriptionId,
                'payment_intent_id'     => $paymentIntentId,
                'payment_intent_status' => data_get($confirmedIntent, 'status'),
                'next_action'           => data_get($confirmedIntent, 'nextAction'),
            ]);

        } catch (\Throwable $e) {
            Log::error('ONVO subscription error', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
            ]);
            LogService::error('subscription_exception',
                "Excepción: {$e->getMessage()}",
                ['plan_id' => $plan->id ?? null],
                $user->id ?? null
            );
            return response()->json(['message' => 'Error interno procesando la suscripción.'], 500);
        }
    }
}
