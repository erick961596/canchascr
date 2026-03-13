@extends('layouts.owner')
@section('title', 'Dashboard - SuperCancha Owner')
@section('page_title', 'Dashboard')

@section('content')
{{-- Stats --}}
<div class="row g-3 mb-4">
    @php
    $cards = [
        ['Reservas pendientes', $stats['pending_reservations'], 'fa-clock', '#fff3e0', '#e65100'],
        ['Confirmadas hoy',     $stats['confirmed_today'],      'fa-check-circle', '#e8f5e9', '#2e7d32'],
        ['Canchas activas',     $stats['total_courts'],         'fa-futbol', '#e3f2fd', '#1565c0'],
        ['Ingresos del mes',    '₡'.number_format($stats['revenue_month'],0,',','.'), 'fa-money-bill-wave', '#f3e5f5', '#6a1b9a'],
    ];
    @endphp
    @foreach($cards as [$label, $value, $icon, $bg, $color])
    <div class="col-6 col-lg-3">
        <div class="stat-card h-100">
            <div class="stat-icon mb-3" style="background:{{ $bg }}">
                <i class="fa-solid {{ $icon }}" style="color:{{ $color }}"></i>
            </div>
            <div class="fw-800" style="font-size:22px;letter-spacing:-0.5px">{{ $value }}</div>
            <div class="text-muted" style="font-size:12px;margin-top:2px">{{ $label }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    {{-- Revenue chart --}}
    <div class="col-lg-8">
        <div class="stat-card h-100">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h6 class="fw-700 mb-0">Ingresos por mes</h6>
                <span style="font-size:12px;color:#888">{{ now()->year }}</span>
            </div>
            <div style="position:relative;height:240px">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>
    {{-- Venues list --}}
    <div class="col-lg-4">
        <div class="stat-card h-100">
            <h6 class="fw-700 mb-3">Mis sedes</h6>
            @forelse($venues as $venue)
            <div class="d-flex align-items-center gap-3 py-2 border-bottom" style="border-color:#f5f5f5!important">
                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:40px;height:40px;background:#f5f5f5">
                    <i class="fa-solid fa-building" style="font-size:16px;color:#666"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-600" style="font-size:13px">{{ $venue->name }}</div>
                    <div class="text-muted" style="font-size:11px">{{ $venue->activeCourts->count() }} canchas</div>
                </div>
                <span class="badge" style="{{ $venue->active ? 'background:#e8f5e9;color:#2e7d32' : 'background:#f5f5f5;color:#888' }};border-radius:20px;font-size:10px;padding:3px 8px">
                    {{ $venue->active ? 'Activa' : 'Inactiva' }}
                </span>
            </div>
            @empty
                <div class="text-center py-3 text-muted" style="font-size:13px">
                    <a href="{{ route('owner.venues.index') }}" style="color:#6C63FF;font-weight:600;text-decoration:none">+ Crear primera sede</a>
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- Recent reservations --}}
<div class="stat-card">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h6 class="fw-700 mb-0">Reservas recientes</h6>
        <a href="{{ route('owner.reservations.index') }}" style="font-size:13px;color:#6C63FF;font-weight:600;text-decoration:none">Ver todas →</a>
    </div>
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Usuario</th><th>Cancha</th><th>Fecha</th><th>Hora</th><th>Total</th><th>Estado</th><th>Pago</th><th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentReservations as $r)
                <tr>
                    <td>
                        <div class="fw-600" style="font-size:13px">{{ $r->user->name }}</div>
                        <div class="text-muted" style="font-size:11px">{{ $r->user->email }}</div>
                    </td>
                    <td style="font-size:13px">{{ $r->court->name }}</td>
                    <td style="font-size:13px">{{ $r->reservation_date->format('d/m/Y') }}</td>
                    <td style="font-size:12px;color:#666">{{ $r->start_time }} - {{ $r->end_time }}</td>
                    <td class="fw-700" style="font-size:13px">₡{{ number_format($r->total_price, 0, ',', '.') }}</td>
                    <td>{!! $r->status_badge !!}</td>
                    <td>
                        <span class="badge" style="{{ $r->payment_status === 'verified' ? 'background:#e8f5e9;color:#2e7d32' : ($r->payment_status === 'rejected' ? 'background:#ffebee;color:#c62828' : 'background:#fff3e0;color:#e65100') }};border-radius:20px;font-size:10px">
                            {{ match($r->payment_status) { 'verified'=>'Verificado','rejected'=>'Rechazado', default=>'Pendiente' } }}
                        </span>
                    </td>
                    <td>
                        @if($r->status === 'pending')
                        <div class="d-flex gap-1">
                            <button onclick="confirmRes('{{ $r->id }}')" class="btn btn-sm" style="background:#e8f5e9;color:#2e7d32;border-radius:8px;font-size:11px;padding:4px 10px;font-weight:600">Confirmar</button>
                            <button onclick="rejectRes('{{ $r->id }}')" class="btn btn-sm" style="background:#ffebee;color:#c62828;border-radius:8px;font-size:11px;padding:4px 10px;font-weight:600">Rechazar</button>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4" style="font-size:13px">No hay reservas aún.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
const monthlyData = @json($monthlyData);
const months = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
const revenues = months.map((_, i) => monthlyData[i+1] || 0);

new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: months,
        datasets: [{
            label: 'Ingresos (₡)',
            data: revenues,
            backgroundColor: '#000',
            borderRadius: 8,
            barThickness: 24,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { grid: { color: '#f5f5f5' }, ticks: { callback: v => '₡' + (v/1000).toFixed(0) + 'k', font: { size: 11 } } },
            x: { grid: { display: false }, ticks: { font: { size: 11 } } }
        }
    }
});

async function confirmRes(id) {
    const { isConfirmed } = await Swal.fire({ title:'¿Confirmar reserva?', icon:'question', showCancelButton:true, confirmButtonColor:'#000', confirmButtonText:'Sí, confirmar' });
    if (!isConfirmed) return;
    try {
        await axios.patch(`/owner/reservas/${id}/confirmar`);
        Toast.fire({ icon:'success', title:'Reserva confirmada.' });
        setTimeout(() => location.reload(), 1200);
    } catch(e) { Toast.fire({ icon:'error', title:'Error.' }); }
}

async function rejectRes(id) {
    const { isConfirmed } = await Swal.fire({ title:'¿Rechazar reserva?', icon:'warning', showCancelButton:true, confirmButtonColor:'#c62828', confirmButtonText:'Rechazar' });
    if (!isConfirmed) return;
    try {
        await axios.patch(`/owner/reservas/${id}/rechazar`);
        Toast.fire({ icon:'success', title:'Reserva rechazada.' });
        setTimeout(() => location.reload(), 1200);
    } catch(e) { Toast.fire({ icon:'error', title:'Error.' }); }
}
</script>
@endpush
