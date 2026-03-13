@extends('layouts.admin')
@section('title', 'Detalle Suscripción')
@section('page_title', 'Detalle de suscripción')

@section('content')
<div class="row g-4">
    <div class="col-lg-5">
        <div class="stat-card">
            <h6 class="fw-700 mb-4">Información del owner</h6>
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="rounded-3 bg-dark text-white d-flex align-items-center justify-content-center" style="width:56px;height:56px;font-size:20px;font-weight:700">
                    {{ strtoupper(substr($subscription->user->name,0,1)) }}
                </div>
                <div>
                    <div class="fw-700">{{ $subscription->user->name }}</div>
                    <div class="text-muted" style="font-size:13px">{{ $subscription->user->email }}</div>
                    @if($subscription->user->phone)
                    <div class="text-muted" style="font-size:13px">{{ $subscription->user->phone }}</div>
                    @endif
                </div>
            </div>
            <table class="w-100" style="font-size:13px">
                <tr class="border-bottom"><td class="text-muted py-2">Plan</td><td class="fw-600 py-2">{{ $subscription->plan?->name }}</td></tr>
                <tr class="border-bottom"><td class="text-muted py-2">Estado</td><td class="py-2">{!! $subscription->status_badge !!}</td></tr>
                <tr class="border-bottom"><td class="text-muted py-2">Método</td><td class="fw-600 py-2">{{ $subscription->payment_method === 'card' ? '💳 Tarjeta' : '📱 SINPE' }}</td></tr>
                <tr class="border-bottom"><td class="text-muted py-2">Precio</td><td class="fw-700 py-2">₡{{ number_format($subscription->price,0,',','.') }}</td></tr>
                <tr class="border-bottom"><td class="text-muted py-2">Inicio</td><td class="py-2">{{ $subscription->starts_at?->format('d/m/Y') ?? '—' }}</td></tr>
                <tr><td class="text-muted py-2">Vence</td><td class="py-2">{{ $subscription->ends_at?->format('d/m/Y') ?? '—' }}</td></tr>
            </table>

            <div class="mt-4">
                <label class="form-label fw-600" style="font-size:12px">Cambiar estado</label>
                <div class="d-flex gap-2">
                    <select id="statusSel" class="form-select form-select-sm" style="border-radius:10px">
                        @foreach(['active','pending','past_due','failed','canceled'] as $st)
                        <option value="{{ $st }}" {{ $subscription->status===$st?'selected':'' }}>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                    <button onclick="updateStatus()" class="btn btn-sm btn-dark" style="border-radius:10px;font-size:12px">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="stat-card">
            <h6 class="fw-700 mb-4">Historial de pagos</h6>
            @forelse($subscription->payments as $p)
            <div class="d-flex align-items-center gap-3 py-3" style="border-bottom:1px solid #f5f5f5">
                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:40px;height:40px;background:{{ $p->status==='succeeded' ? '#e8f5e9' : ($p->status==='rejected' ? '#ffebee' : '#fff3e0') }}">
                    <i class="fa-solid {{ $p->status==='succeeded' ? 'fa-check' : ($p->status==='rejected' ? 'fa-times' : 'fa-clock') }}" style="color:{{ $p->status==='succeeded' ? '#2e7d32' : ($p->status==='rejected' ? '#c62828' : '#e65100') }}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-600" style="font-size:13px">₡{{ number_format($p->amount,0,',','.') }}</div>
                    <div class="text-muted" style="font-size:11px">{{ $p->created_at->format('d/m/Y H:i') }} · {{ $p->method === 'card' ? 'Tarjeta' : 'SINPE' }}</div>
                </div>
                @if($p->proof_path)
                <a href="{{ \Storage::disk('s3')->url($p->proof_path) }}" target="_blank" class="btn btn-sm" style="background:#f0f0f0;border-radius:8px;font-size:11px">Ver</a>
                @endif
                @if($p->status === 'pending' && $p->method === 'manual')
                <div class="d-flex gap-1">
                    <form action="{{ route('admin.payments.approve', $p) }}" method="POST">@csrf <button class="btn btn-sm" style="background:#e8f5e9;color:#2e7d32;border-radius:8px;font-size:11px;font-weight:600">✓ Aprobar</button></form>
                    <form action="{{ route('admin.payments.reject', $p) }}" method="POST">@csrf <button class="btn btn-sm" style="background:#ffebee;color:#c62828;border-radius:8px;font-size:11px;font-weight:600">✗ Rechazar</button></form>
                </div>
                @endif
            </div>
            @empty
            <p class="text-muted" style="font-size:13px">Sin pagos registrados.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function updateStatus() {
    const status = document.getElementById('statusSel').value;
    try {
        await axios.put('/admin/suscripciones/{{ $subscription->id }}', { status });
        Toast.fire({ icon:'success', title:'Estado actualizado.' });
    } catch(e) { Toast.fire({ icon:'error', title:'Error.' }); }
}
</script>
@endpush
