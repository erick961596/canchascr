@extends('layouts.admin')
@section('title', 'Promociones')
@section('page_title', 'Promociones')

@section('content')
<div class="stat-card">
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr><th>Promoción</th><th>Sede</th><th>Owner</th><th>Descuento</th><th>Vigencia</th><th>Estado</th></tr>
            </thead>
            <tbody>
                @forelse($promotions as $promo)
                @php
                    $today = now()->toDateString();
                    $live  = $promo->active && $today >= $promo->starts_at->format('Y-m-d') && $today <= $promo->ends_at->format('Y-m-d');
                @endphp
                <tr>
                    <td>
                        <div class="fw-600" style="font-size:13px">{{ $promo->name }}</div>
                        @if($promo->description)
                            <div class="text-muted" style="font-size:11px">{{ $promo->description }}</div>
                        @endif
                    </td>
                    <td style="font-size:13px">{{ $promo->venue->name }}</td>
                    <td style="font-size:13px">{{ $promo->venue->owner->name }}</td>
                    <td class="fw-700" style="font-size:13px">{{ $promo->display_label }}</td>
                    <td style="font-size:12px">
                        {{ $promo->starts_at->format('d/m/Y') }} – {{ $promo->ends_at->format('d/m/Y') }}
                    </td>
                    <td>
                        @if($live)
                            <span class="badge" style="background:#e8f5e9;color:#2e7d32;border-radius:20px;font-size:10px;padding:3px 10px">Activa</span>
                        @elseif($today > $promo->ends_at->format('Y-m-d'))
                            <span class="badge" style="background:#fafafa;color:#999;border-radius:20px;font-size:10px;padding:3px 10px">Expirada</span>
                        @else
                            <span class="badge" style="background:#fff3e0;color:#e65100;border-radius:20px;font-size:10px;padding:3px 10px">Próxima</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Sin promociones registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $promotions->links() }}
</div>
@endsection
