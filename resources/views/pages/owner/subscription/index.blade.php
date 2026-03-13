@extends('layouts.owner')
@section('title', 'Suscripción')
@section('page_title', 'Mi suscripción')

@push('styles')
<style>
.step-nav { display:flex; gap:0; border-bottom:2px solid #f0f0f0; margin-bottom:32px; }
.step-nav-item { padding:12px 28px; font-weight:700; font-size:14px; color:#aaa; border-bottom:2px solid transparent; margin-bottom:-2px; cursor:default; }
.step-nav-item.active { color:#6C63FF; border-bottom-color:#6C63FF; }

.method-card { border:2px solid #e8e8e8; border-radius:18px; padding:22px 20px; cursor:pointer; transition:all .18s; }
.method-card:hover { border-color:#bbb; }
.method-card.selected { border-color:#6C63FF; background:#f8f7ff; }
</style>
@endpush

@section('content')

{{-- Status banner --}}
@if($subscription)
    @if($subscription->status === 'active')
    <div class="d-flex align-items-center gap-3 p-4 rounded-3 mb-4" style="background:#e8f5e9;border:1.5px solid #a5d6a7">
        <i class="fa-solid fa-circle-check fa-lg" style="color:#2e7d32"></i>
        <div>
            <span class="fw-700" style="color:#2e7d32">Suscripción activa</span>
            <span class="text-muted ms-2" style="font-size:13px">Plan {{ $subscription->plan?->name }}
                @if($subscription->ends_at) · vence {{ $subscription->ends_at->format('d/m/Y') }} @endif
            </span>
        </div>
    </div>
    @elseif($subscription->status === 'pending')
    <div class="d-flex align-items-center gap-3 p-4 rounded-3 mb-4" style="background:#fff8e1;border:1.5px solid #ffe082">
        <i class="fa-solid fa-clock fa-lg" style="color:#f59300"></i>
        <div>
            <span class="fw-700" style="color:#e65100">En verificación</span>
            <span class="text-muted ms-2" style="font-size:13px">Recibimos tu comprobante. Activamos en máximo 24 horas.</span>
        </div>
    </div>
    @endif
@endif

<div class="row g-4">

    {{-- Columna izquierda: resumen del plan --}}
    <div class="col-lg-4">
        <div class="stat-card h-100">
            <h5 class="fw-800 mb-4">Tu suscripción</h5>

            <div class="mb-4">
                <label class="fw-600 mb-2 d-block" style="font-size:13px">Elegí tu plan</label>
                <select class="form-select" id="plan_select" style="border-radius:12px;border:1.5px solid #e0e0e0;font-weight:600">
                    @foreach($plans as $plan)
                    <option value="{{ $plan->id }}"
                        data-name="{{ $plan->name }}"
                        data-price="{{ $plan->price }}"
                        data-court-limit="{{ $plan->court_limit }}"
                        @selected(request('plan_id') == $plan->id || $loop->first)>
                        {{ $plan->name }} — ₡{{ number_format($plan->price,0,',','.') }}/mes
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="p-4 rounded-3 mb-4" style="background:#f9f9f9;border:1.5px dashed #e0e0e0">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted" style="font-size:13px">Plan</span>
                    <span class="fw-700" id="ui_plan_name">—</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted" style="font-size:13px">Precio mensual</span>
                    <span class="fw-700" id="ui_plan_price">—</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted" style="font-size:13px">Canchas activas</span>
                    <span class="fw-700" id="ui_plan_limit">—</span>
                </div>
                <hr style="border-color:#e8e8e8">
                <div class="d-flex flex-column gap-2 mt-2" style="font-size:12px;color:#666">
                    <span><i class="fa-solid fa-shield-halved me-2" style="color:#6C63FF"></i>Panel de sede, canchas y reservas</span>
                    <span><i class="fa-solid fa-calendar-check me-2" style="color:#6C63FF"></i>Agenda en tiempo real</span>
                    <span><i class="fa-solid fa-headset me-2" style="color:#6C63FF"></i>Soporte y activación asistida</span>
                </div>
            </div>

            <div class="p-3 rounded-3" style="background:#f0efff;font-size:12px;color:#4c46a8">
                <i class="fa-solid fa-circle-info me-2"></i>
                Podés pagar con <strong>tarjeta</strong> (activación inmediata) o por <strong>SINPE</strong> (hasta 24h).
            </div>
        </div>
    </div>

    {{-- Columna derecha: wizard --}}
    <div class="col-lg-8">
        <div class="stat-card">

            {{-- Step nav --}}
            <div class="step-nav" id="step_nav">
                <div class="step-nav-item active" id="nav_step1">1. Método de pago</div>
                <div class="step-nav-item"         id="nav_step2">2. Finalizar</div>
            </div>

            <form id="subscription_form" action="{{ route('owner.subscription.create') }}" method="POST">
                @csrf
                <input type="hidden" name="plan_id"            id="plan_id_input">
                <input type="hidden" name="payment_method"     id="payment_method_input" value="card">
                <input type="hidden" name="uploaded_proof_path" id="uploaded_proof_path">

                {{-- STEP 1: elegir método --}}
                <div id="step1">
                    <h5 class="fw-800 mb-1">¿Cómo querés pagar?</h5>
                    <p class="text-muted mb-4" style="font-size:13px">
                        Activando: <strong id="ui_inline_plan">—</strong> · <strong id="ui_inline_price">—</strong>/mes
                    </p>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="method-card selected" id="mc_card" onclick="selectMethod('card')">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="fa-solid fa-credit-card fa-xl" style="color:#6C63FF"></i>
                                    <div>
                                        <div class="fw-700">Tarjeta</div>
                                        <div class="text-muted" style="font-size:12px">Activación inmediata · procesado por ONVO</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="method-card" id="mc_sinpe" onclick="selectMethod('manual')">
                                <div class="d-flex align-items-center gap-3">
                                    <i class="fa-solid fa-mobile-screen fa-xl" style="color:#2e7d32"></i>
                                    <div>
                                        <div class="fw-700">SINPE / Transferencia</div>
                                        <div class="text-muted" style="font-size:12px">Activación hasta 24 horas</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 rounded-3" style="background:#fff8e1;border:1.5px solid #ffe082;font-size:13px">
                        <i class="fa-solid fa-triangle-exclamation me-2" style="color:#f59300"></i>
                        Si pagás por SINPE, tu suscripción queda <strong>pendiente</strong> hasta verificación (máx. 24h).
                        Con tarjeta el acceso es <strong>inmediato</strong>.
                    </div>

                    <div class="d-flex justify-content-end mt-5">
                        <button type="button" onclick="goStep(2)"
                                class="btn fw-700 px-5 py-3"
                                style="background:#6C63FF;color:#fff;border-radius:14px">
                            Continuar <i class="fa-solid fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>

                {{-- STEP 2: datos de pago --}}
                <div id="step2" class="d-none">
                    <h5 class="fw-800 mb-1">Finalizar suscripción</h5>
                    <p class="text-muted mb-4" style="font-size:13px">
                        Confirmá tu pago para activar <strong id="ui_inline_plan_2">—</strong> · <strong id="ui_inline_price_2">—</strong>/mes
                    </p>

                    {{-- Tarjeta --}}
                    <div id="card_form_wrapper">
                        <div class="row g-3 mb-2">
                            <div class="col-12">
                                <label class="fw-600 mb-1 d-block" style="font-size:13px">Nombre en la tarjeta *</label>
                                <input type="text" name="card_holder" class="form-control" style="border-radius:12px;border:1.5px solid #e0e0e0" placeholder="Como aparece en la tarjeta">
                            </div>
                            <div class="col-12">
                                <label class="fw-600 mb-1 d-block" style="font-size:13px">Número de tarjeta *</label>
                                <input type="text" name="card_number" id="card_number" class="form-control" style="border-radius:12px;border:1.5px solid #e0e0e0;letter-spacing:2px" placeholder="1234 5678 9012 3456" maxlength="19">
                            </div>
                            <div class="col-md-4">
                                <label class="fw-600 mb-1 d-block" style="font-size:13px">Mes *</label>
                                <input type="text" name="card_exp_month" class="form-control" style="border-radius:12px;border:1.5px solid #e0e0e0" placeholder="MM" maxlength="2">
                            </div>
                            <div class="col-md-4">
                                <label class="fw-600 mb-1 d-block" style="font-size:13px">Año *</label>
                                <input type="text" name="card_exp_year" class="form-control" style="border-radius:12px;border:1.5px solid #e0e0e0" placeholder="YYYY" maxlength="4">
                            </div>
                            <div class="col-md-4">
                                <label class="fw-600 mb-1 d-block" style="font-size:13px">CVV *</label>
                                <input type="password" name="card_cvc" class="form-control" style="border-radius:12px;border:1.5px solid #e0e0e0" placeholder="•••" maxlength="4">
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2 mb-4" style="font-size:11px;color:#aaa">
                            <i class="fa-solid fa-lock"></i> Pago cifrado y procesado de forma segura por ONVO Pay
                        </div>
                    </div>

                    {{-- SINPE --}}
                    <div id="sinpe_form_wrapper" class="d-none">
                        <div class="p-4 rounded-3 mb-4" style="background:#e8f5e9;border:1.5px dashed #a5d6a7">
                            <p class="fw-700 mb-1" style="color:#2e7d32;font-size:14px">📱 Instrucciones SINPE Móvil</p>
                            <p class="mb-1 text-muted" style="font-size:13px">
                                Realizá el pago al número <strong>{{ config('app.sinpe_number', '8888-8888') }}</strong><br>
                                Concepto: <strong>SuperCancha — {{ auth()->user()->email }}</strong>
                            </p>
                        </div>
                        <label class="fw-600 mb-2 d-block" style="font-size:13px">Subir comprobante *</label>
                        <input type="file" id="sinpe_file" class="form-control mb-1" accept="image/*,application/pdf" style="border-radius:12px;border:1.5px solid #e0e0e0">
                        <div id="proof_status" style="font-size:12px;min-height:18px"></div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" onclick="goStep(1)"
                                class="btn fw-600 px-4"
                                style="border:1.5px solid #e0e0e0;border-radius:12px;color:#666">
                            <i class="fa-solid fa-arrow-left me-2"></i> Atrás
                        </button>
                        <button type="button" id="btn_submit"
                                class="btn fw-700 px-5 py-3"
                                style="background:#6C63FF;color:#fff;border-radius:14px"
                                onclick="submitPayment()">
                            <span id="btn_label">Finalizar y activar</span>
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://js.onvopay.com/v1/"></script>
<script>
// ── Plan selector ──────────────────────────────────────────────
const planSelect   = document.getElementById('plan_select');
const planIdInput  = document.getElementById('plan_id_input');

function formatCRC(n) {
    return '₡' + Number(n || 0).toLocaleString('es-CR', { maximumFractionDigits: 0 });
}

function syncPlan() {
    const opt   = planSelect.options[planSelect.selectedIndex];
    const name  = opt.dataset.name  || opt.text;
    const price = opt.dataset.price || 0;
    const limit = opt.dataset.courtLimit || 0;

    planIdInput.value = opt.value;

    document.getElementById('ui_plan_name').textContent    = name;
    document.getElementById('ui_plan_price').textContent   = formatCRC(price);
    document.getElementById('ui_plan_limit').textContent   = limit;
    document.getElementById('ui_inline_plan').textContent  = name;
    document.getElementById('ui_inline_price').textContent = formatCRC(price);
    document.getElementById('ui_inline_plan_2').textContent  = name;
    document.getElementById('ui_inline_price_2').textContent = formatCRC(price);
}

planSelect.addEventListener('change', function () {
    syncPlan();
    const url = new URL(window.location.href);
    url.searchParams.set('plan_id', planSelect.value);
    window.history.replaceState({}, '', url.toString());
});

syncPlan();

// ── Método de pago ─────────────────────────────────────────────
let currentMethod = 'card';

function selectMethod(method) {
    currentMethod = method;
    document.getElementById('payment_method_input').value = method;

    document.getElementById('mc_card').classList.toggle('selected',  method === 'card');
    document.getElementById('mc_sinpe').classList.toggle('selected', method === 'manual');
}

// ── Stepper ────────────────────────────────────────────────────
function goStep(n) {
    document.getElementById('step1').classList.toggle('d-none', n !== 1);
    document.getElementById('step2').classList.toggle('d-none', n !== 2);
    document.getElementById('nav_step1').classList.toggle('active', n === 1);
    document.getElementById('nav_step2').classList.toggle('active', n === 2);

    if (n === 2) {
        document.getElementById('card_form_wrapper').classList.toggle('d-none',  currentMethod !== 'card');
        document.getElementById('sinpe_form_wrapper').classList.toggle('d-none', currentMethod !== 'manual');
    }
}

// ── SINPE: auto-upload comprobante ─────────────────────────────
document.getElementById('sinpe_file').addEventListener('change', async function () {
    const file = this.files[0];
    if (!file) return;
    const statusEl = document.getElementById('proof_status');
    statusEl.innerHTML = '<span class="text-muted"><i class="fa-solid fa-spinner fa-spin me-1"></i>Subiendo...</span>';

    const fd = new FormData();
    fd.append('file', file);

    try {
        const res = await axios.post('{{ route("owner.subscription.upload") }}', fd, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        document.getElementById('uploaded_proof_path').value = res.data.path;
        statusEl.innerHTML = '<span class="text-success"><i class="fa-solid fa-check me-1"></i>Comprobante listo.</span>';
    } catch (e) {
        statusEl.innerHTML = '<span class="text-danger">Error subiendo comprobante. Intentá de nuevo.</span>';
    }
});

// ── Submit ─────────────────────────────────────────────────────
const onvoClient = typeof ONVO !== 'undefined' ? ONVO("{{ config('services.onvopay.public') }}") : null;
const DASHBOARD_URL = "{{ route('owner.dashboard') }}";

async function submitPayment() {
    const btn   = document.getElementById('btn_submit');
    const label = document.getElementById('btn_label');

    btn.disabled = true;
    label.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Procesando...';

    // SINPE → envío normal del form
    if (currentMethod === 'manual') {
        const proof = document.getElementById('uploaded_proof_path').value;
        if (!proof) {
            Swal.fire({ icon: 'warning', title: 'Subí el comprobante primero.' });
            btn.disabled = false;
            label.textContent = 'Finalizar y activar';
            return;
        }
        document.getElementById('subscription_form').submit();
        return;
    }

    // Tarjeta → fetch → ONVO 3DS
    try {
        const form = document.getElementById('subscription_form');
        const res  = await fetch(form.action, {
            method:  'POST',
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}",
                'Accept':       'application/json',
            },
            body: new FormData(form),
        });

        const data = await res.json();

        if (!res.ok) {
            throw new Error(data.message || 'Error procesando el pago.');
        }

        const { payment_intent_id, next_action } = data;

        if (!payment_intent_id) {
            throw new Error('No se recibió Payment Intent de ONVO.');
        }

        // Antifraude
        if (onvoClient) {
            onvoClient.startSignalSession({ paymentIntentId: payment_intent_id });
        }

        // 3DS si ONVO lo requiere
        if (next_action && onvoClient) {
            const result = await onvoClient.handleNextAction({ paymentIntentId: payment_intent_id });
            if (result.error) {
                throw new Error('Autenticación 3DS rechazada.');
            }
        }

        Swal.fire({
            icon: 'info',
            title: 'Pago enviado',
            text: 'Estamos confirmando con el banco. Tu suscripción se activará en segundos.',
            timer: 3000, showConfirmButton: false,
        });

        setTimeout(() => { window.location.href = DASHBOARD_URL; }, 3200);

    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Error', text: err.message });
        btn.disabled = false;
        label.textContent = 'Finalizar y activar';
    }
}

// Formatear número de tarjeta mientras escribe
document.getElementById('card_number').addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '').substring(0, 16);
    this.value = v.match(/.{1,4}/g)?.join(' ') || v;
});
</script>
@endpush
