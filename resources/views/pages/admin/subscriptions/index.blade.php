@extends('layouts.admin')
@section('title', 'Suscripciones')
@section('page_title', 'Suscripciones')

@section('content')
<div class="stat-card">
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr><th>Owner</th><th>Plan</th><th>Estado</th><th>Método</th><th>Precio</th><th>Vence</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @foreach($subscriptions as $s)
                <tr>
                    <td>
                        <div class="fw-600" style="font-size:13px">{{ $s->user->name }}</div>
                        <div class="text-muted" style="font-size:11px">{{ $s->user->email }}</div>
                    </td>
                    <td style="font-size:13px">{{ $s->plan?->name ?? '—' }}</td>
                    <td>{!! $s->status_badge !!}</td>
                    <td>
                        <span style="font-size:12px">{{ $s->payment_method === 'card' ? '💳 Tarjeta' : '📱 SINPE' }}</span>
                    </td>
                    <td class="fw-700" style="font-size:13px">₡{{ number_format($s->price,0,',','.') }}</td>
                    <td style="font-size:12px;color:#666">{{ $s->ends_at?->format('d/m/Y') ?? '—' }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('admin.subscriptions.show', $s) }}" class="btn btn-sm" style="background:#f0f0f0;border-radius:8px;font-size:11px">Ver</a>
                            <select onchange="updateStatus('{{ $s->id }}', this.value)" class="form-select form-select-sm" style="border-radius:8px;font-size:11px;max-width:110px">
                                @foreach(['active','pending','past_due','failed','canceled'] as $st)
                                    <option value="{{ $st }}" {{ $s->status===$st?'selected':'' }}>{{ ucfirst($st) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
@push('scripts')
<script>
async function updateStatus(id, status) {
    try {
        await axios.put(`/admin/suscripciones/${id}`, { status });
        Toast.fire({ icon:'success', title:'Estado actualizado.' });
    } catch(e) { Toast.fire({ icon:'error', title:'Error.' }); }
}
</script>
@endpush
