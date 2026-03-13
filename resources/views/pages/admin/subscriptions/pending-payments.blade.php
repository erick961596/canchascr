@extends('layouts.admin')
@section('title', 'Pagos Pendientes')
@section('page_title', 'Pagos SINPE pendientes')

@section('content')
<div class="stat-card">
    @if($payments->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="fa-solid fa-check-circle fa-3x text-success mb-3 d-block"></i>
            <p class="fw-600">No hay pagos pendientes. ¡Todo al día!</p>
        </div>
    @else
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr><th>Owner</th><th>Plan</th><th>Monto</th><th>Comprobante</th><th>Fecha</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @foreach($payments as $p)
                <tr>
                    <td>
                        <div class="fw-600" style="font-size:13px">{{ $p->subscription->user->name }}</div>
                        <div class="text-muted" style="font-size:11px">{{ $p->subscription->user->email }}</div>
                    </td>
                    <td style="font-size:13px">{{ $p->subscription->plan?->name ?? '—' }}</td>
                    <td class="fw-700" style="font-size:13px">₡{{ number_format($p->amount,0,',','.') }}</td>
                    <td>
                        @if($p->proof_path)
                            <a href="{{ \Storage::disk('s3')->url($p->proof_path) }}" target="_blank" class="btn btn-sm" style="background:#e3f2fd;color:#1565c0;border-radius:8px;font-size:11px">
                                <i class="fa-solid fa-image me-1"></i>Ver comprobante
                            </a>
                        @else <span class="text-muted" style="font-size:12px">Sin archivo</span> @endif
                    </td>
                    <td style="font-size:12px;color:#666">{{ $p->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <form action="{{ route('admin.payments.approve', $p) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm" style="background:#e8f5e9;color:#2e7d32;border-radius:8px;font-size:11px;font-weight:600" onclick="return confirm('¿Aprobar este pago?')">
                                    ✓ Aprobar
                                </button>
                            </form>
                            <form action="{{ route('admin.payments.reject', $p) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm" style="background:#ffebee;color:#c62828;border-radius:8px;font-size:11px;font-weight:600" onclick="return confirm('¿Rechazar este pago?')">
                                    ✗ Rechazar
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
