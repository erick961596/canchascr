<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    public function index()
    {
        $subscriptions = Subscription::with(['user','plan','payments'])->latest()->get();
        return view('pages.admin.subscriptions.index', compact('subscriptions'));
    }

    public function show(Subscription $subscription)
    {
        $subscription->load(['user','plan','payments' => fn($q) => $q->latest()]);
        return view('pages.admin.subscriptions.show', compact('subscription'));
    }

    public function pendingPayments()
    {
        $payments = SubscriptionPayment::with('subscription.user')
            ->where('method','manual')->where('status','pending')->latest()->get();
        return view('pages.admin.subscriptions.pending-payments', compact('payments'));
    }

    public function approvePayment(SubscriptionPayment $payment)
    {
        if ($payment->status !== 'pending' || $payment->method !== 'manual') {
            return back()->withErrors('Este pago no puede ser aprobado.');
        }

        DB::transaction(function () use ($payment) {
            $payment->update(['status' => 'succeeded', 'paid_at' => now()]);
            $subscription = $payment->subscription;
            $subscription->update(['status' => 'active', 'starts_at' => now(), 'ends_at' => now()->addMonth()]);
            $this->notifications->subscriptionActivated($subscription->user);
        });

        return back()->with('success', 'Pago aprobado y suscripción activada.');
    }

    public function rejectPayment(SubscriptionPayment $payment)
    {
        if ($payment->status !== 'pending') {
            return back()->withErrors('Este pago no puede ser rechazado.');
        }
        $payment->update(['status' => 'rejected']);
        return back()->with('success', 'Pago rechazado.');
    }

    public function update(Request $request, Subscription $subscription)
    {
        $request->validate(['status' => 'required|in:active,pending,past_due,failed,canceled']);
        $subscription->update(['status' => $request->status]);
        return response()->json(['message' => 'Estado actualizado.']);
    }
}
