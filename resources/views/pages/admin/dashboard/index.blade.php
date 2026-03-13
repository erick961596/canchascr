@extends('layouts.admin')
@section('title', 'Dashboard Admin')
@section('page_title', 'Dashboard')

@section('content')
<div class="row g-3 mb-4">
    @php
    $cards = [
        ['Usuarios',      $stats['total_users'],             'fa-users',      '#e3f2fd','#1565c0'],
        ['Owners',        $stats['total_owners'],            'fa-store',      '#f3e5f5','#6a1b9a'],
        ['Subs activas',  $stats['active_subscriptions'],    'fa-crown',      '#e8f5e9','#2e7d32'],
        ['Pendientes',    $stats['pending_subscriptions'],   'fa-clock',      '#fff3e0','#e65100'],
        ['Sedes activas', $stats['active_venues'],           'fa-building',   '#fce4ec','#880e4f'],
        ['Ingresos mes',  '₡'.number_format($stats['revenue_month'],0,',','.'), 'fa-money-bill','#e8f5e9','#1b5e20'],
    ];
    @endphp
    @foreach($cards as [$label,$value,$icon,$bg,$color])
    <div class="col-6 col-lg-2">
        <div class="stat-card text-center">
            <div class="mx-auto mb-2 d-flex align-items-center justify-content-center" style="width:44px;height:44px;border-radius:12px;background:{{ $bg }}">
                <i class="fa-solid {{ $icon }}" style="color:{{ $color }};font-size:18px"></i>
            </div>
            <div class="fw-800" style="font-size:20px">{{ $value }}</div>
            <div class="text-muted" style="font-size:11px">{{ $label }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="stat-card">
            <h6 class="fw-700 mb-4">Ingresos mensuales (suscripciones)</h6>
            {{-- FIX: contenedor con altura fija para que Chart.js no se desborde --}}
            <div style="position:relative;height:260px">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="stat-card h-100">
            <h6 class="fw-700 mb-3">Suscripciones recientes</h6>
            @forelse($recentSubscriptions as $s)
            <a href="{{ route('admin.subscriptions.show', $s) }}" class="d-flex align-items-center gap-3 py-2 text-decoration-none" style="border-bottom:1px solid #f5f5f5">
                <div class="rounded-3 bg-dark text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width:34px;height:34px;font-size:12px;font-weight:700">
                    {{ strtoupper(substr($s->user->name,0,1)) }}
                </div>
                <div class="flex-grow-1">
                    <div style="font-size:13px;font-weight:600;color:#111">{{ $s->user->name }}</div>
                    <div style="font-size:11px;color:#888">{{ $s->plan?->name }}</div>
                </div>
                <span class="badge" style="{{ $s->status==='active' ? 'background:#e8f5e9;color:#2e7d32' : 'background:#fff3e0;color:#e65100' }};border-radius:20px;font-size:10px;padding:4px 8px">
                    {{ ucfirst($s->status) }}
                </span>
            </a>
            @empty
            <p class="text-muted" style="font-size:13px">Sin suscripciones aún.</p>
            @endforelse
            <a href="{{ route('admin.subscriptions.index') }}" class="d-block text-center mt-3" style="font-size:13px;color:#6C63FF;font-weight:600;text-decoration:none">Ver todas →</a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const data    = @json($monthlyRevenue);
const months  = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
const revenues = months.map((_,i) => data[i+1] || 0);

new Chart(document.getElementById('revenueChart'), {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            label: 'Ingresos',
            data: revenues,
            borderColor: '#6C63FF',
            backgroundColor: 'rgba(108,99,255,0.08)',
            borderWidth: 2.5, fill: true, tension: 0.4, pointRadius: 4,
            pointBackgroundColor: '#6C63FF'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { grid:{color:'#f5f5f5'}, ticks:{ callback: v=>'₡'+(v/1000).toFixed(0)+'k', font:{size:11} } },
            x: { grid:{ display:false }, ticks:{ font:{size:11} } }
        }
    }
});
</script>
@endpush
