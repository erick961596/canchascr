@extends('layouts.admin')
@section('title', $venue->name)
@section('page_title', $venue->name)

@section('content')
<div class="row g-4">
    {{-- Left: venue info --}}
    <div class="col-lg-4">
        <div class="stat-card mb-3">
            <div class="d-flex align-items-start justify-content-between mb-3">
                <h6 class="fw-700 mb-0">Información</h6>
                <span class="badge {{ $venue->active ? 'badge-active' : 'badge-inactive' }}">
                    {{ $venue->active ? 'Activa' : 'Inactiva' }}
                </span>
            </div>
            <table class="w-100" style="font-size:13px">
                <tr class="border-bottom"><td class="text-muted py-2 pe-3">Owner</td><td class="fw-600 py-2">{{ $venue->owner->name }}</td></tr>
                <tr class="border-bottom"><td class="text-muted py-2">Provincia</td><td class="fw-600 py-2">{{ $venue->province }}</td></tr>
                <tr class="border-bottom"><td class="text-muted py-2">Cantón</td><td class="fw-600 py-2">{{ $venue->canton }}</td></tr>
                <tr class="border-bottom"><td class="text-muted py-2">Distrito</td><td class="fw-600 py-2">{{ $venue->district }}</td></tr>
                <tr class="border-bottom"><td class="text-muted py-2">Dirección</td><td class="py-2">{{ $venue->address ?? '—' }}</td></tr>
                <tr class="border-bottom"><td class="text-muted py-2">Teléfono</td><td class="py-2">{{ $venue->phone ?? '—' }}</td></tr>
                <tr><td class="text-muted py-2">Registro</td><td class="py-2">{{ $venue->created_at->format('d/m/Y') }}</td></tr>
            </table>
            @if($venue->amenities)
            <div class="mt-3 d-flex flex-wrap gap-1">
                @foreach($venue->amenities as $a)
                <span style="background:#f5f5f5;border-radius:20px;padding:3px 10px;font-size:11px;color:#555">{{ $a }}</span>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Rating summary --}}
        <div class="stat-card">
            <h6 class="fw-700 mb-3">Rating</h6>
            @php
                $avg   = round($venue->ratings->avg('rating') ?? 0, 1);
                $total = $venue->ratings->count();
                $dist  = [5=>0,4=>0,3=>0,2=>0,1=>0];
                foreach($venue->ratings as $r) $dist[$r->rating]++;
            @endphp
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="text-center">
                    <div class="fw-800" style="font-size:40px;line-height:1;color:#f59e0b">{{ $avg > 0 ? $avg : '—' }}</div>
                    <div style="color:#f59e0b;font-size:16px">
                        @for($i=1;$i<=5;$i++)<span>{{ $i <= round($avg) ? '★' : '☆' }}</span>@endfor
                    </div>
                    <div class="text-muted" style="font-size:11px">{{ $total }} reseñas</div>
                </div>
                <div class="flex-grow-1">
                    @foreach([5,4,3,2,1] as $star)
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span style="font-size:11px;width:8px">{{ $star }}</span>
                        <div class="flex-grow-1 rounded-pill" style="height:6px;background:#f0f0f0;overflow:hidden">
                            <div class="rounded-pill" style="height:100%;width:{{ $total > 0 ? ($dist[$star]/$total*100) : 0 }}%;background:#f59e0b"></div>
                        </div>
                        <span class="text-muted" style="font-size:11px;width:16px">{{ $dist[$star] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @foreach($venue->ratings->take(3) as $r)
            <div class="py-2" style="border-top:1px solid #f5f5f5">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="fw-600" style="font-size:12px">{{ $r->user->name }}</span>
                    <span style="color:#f59e0b;font-size:11px">{{ str_repeat('★',$r->rating) }}{{ str_repeat('☆',5-$r->rating) }}</span>
                </div>
                @if($r->comment)
                <p class="text-muted mb-0" style="font-size:12px">{{ $r->comment }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- Right: courts --}}
    <div class="col-lg-8">
        <div class="stat-card">
            <h6 class="fw-700 mb-4">Canchas ({{ $venue->courts->count() }})</h6>
            @forelse($venue->courts as $court)
            <div class="p-3 rounded-3 mb-3" style="border:1.5px solid #f0f0f0">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <span class="fw-700" style="font-size:14px">{{ $court->name }}</span>
                        <span class="badge ms-2" style="background:#111;color:#fff;border-radius:20px;font-size:10px">
                            {{ \App\Models\Court::sportLabel($court->sport) }}
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-700" style="font-size:14px">₡{{ number_format($court->price_per_hour,0,',','.') }}/hr</span>
                        <span class="badge {{ $court->active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $court->active ? 'Activa' : 'Inactiva' }}
                        </span>
                    </div>
                </div>
                {{-- Features --}}
                @if($court->features)
                <div class="d-flex flex-wrap gap-1 mb-2">
                    @foreach($court->features as $f)
                    <span style="background:#f5f5f5;border-radius:20px;padding:2px 8px;font-size:11px;color:#555">{{ $f }}</span>
                    @endforeach
                </div>
                @endif
                {{-- Schedules --}}
                @if($court->schedules->count())
                <div class="d-flex flex-wrap gap-1">
                    @php $dayNames=['mon'=>'L','tue'=>'M','wed'=>'X','thu'=>'J','fri'=>'V','sat'=>'S','sun'=>'D']; @endphp
                    @foreach($court->schedules->where('active',true) as $sch)
                    <span style="background:#e8eaf6;color:#283593;border-radius:8px;padding:2px 7px;font-size:10px;font-weight:700">
                        {{ $dayNames[$sch->day_of_week] ?? $sch->day_of_week }}
                        {{ substr($sch->open_time,0,5) }}–{{ substr($sch->close_time,0,5) }}
                    </span>
                    @endforeach
                </div>
                @endif
            </div>
            @empty
            <p class="text-muted" style="font-size:13px">Sin canchas registradas.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
