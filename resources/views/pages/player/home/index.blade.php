@extends('layouts.player')
@section('title', 'Inicio - SuperCancha')

@section('content')
<div class="px-3 mt-2">
    <a href="{{ route('player.explore') }}" class="d-block text-decoration-none mb-4">
        <div class="d-flex align-items-center gap-3 px-4 py-3 bg-white rounded-pill shadow-sm">
            <i class="fa-solid fa-magnifying-glass text-muted"></i>
            <span class="text-muted" style="font-size:14px">Buscar canchas, deportes, lugares...</span>
        </div>
    </a>

    <div class="d-flex gap-2 overflow-auto pb-2 mb-4" style="scrollbar-width:none">
        @foreach([['futbol','⚽','Fútbol'],['tenis','🎾','Tenis'],['padel','🏸','Pádel'],['basquetbol','🏀','Básquet'],['volleyball','🏐','Vóley']] as [$key,$icon,$label])
        <a href="{{ route('player.explore', ['sport' => $key]) }}" class="text-decoration-none flex-shrink-0">
            <div class="px-3 py-2 bg-white rounded-pill shadow-sm d-flex align-items-center gap-2" style="font-size:13px;font-weight:600;white-space:nowrap">
                {{ $icon }} {{ $label }}
            </div>
        </a>
        @endforeach
    </div>

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="fw-800 mb-0" style="font-size:18px">Sedes destacadas</h2>
        <a href="{{ route('player.explore') }}" style="font-size:13px;font-weight:600;color:#6C63FF;text-decoration:none">Ver todas →</a>
    </div>

    <div class="row g-3 pb-3">
        @forelse($featuredVenues as $venue)
        <div class="col-12">
            <a href="{{ route('player.venue', $venue->slug) }}" class="text-decoration-none">
                <div class="card-venue d-flex overflow-hidden" style="height:110px">
                    @if($venue->images && count($venue->images))
                        <img src="{{ \Storage::disk('s3')->url($venue->images[0]) }}" style="width:110px;height:110px;object-fit:cover;flex-shrink:0" alt="{{ $venue->name }}">
                    @else
                        <div style="width:110px;height:110px;background:linear-gradient(135deg,#667eea,#764ba2);flex-shrink:0;display:flex;align-items:center;justify-content:center">
                            <i class="fa-solid fa-futbol text-white" style="font-size:28px"></i>
                        </div>
                    @endif
                    <div class="p-3 d-flex flex-column justify-content-between flex-grow-1 bg-white">
                        <div>
                            <div class="fw-700" style="font-size:15px;color:#111">{{ $venue->name }}</div>
                            <div class="text-muted" style="font-size:12px"><i class="fa-solid fa-location-dot me-1"></i>{{ $venue->canton }}, {{ $venue->province }}</div>
                        </div>
                        <div class="d-flex gap-1 flex-wrap">
                            @foreach($venue->activeCourts->take(3) as $court)
                                <span class="badge" style="background:#f0f0f0;color:#333;font-size:10px;border-radius:20px;padding:3px 8px">{{ \App\Models\Court::sportLabel($court->sport) }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="fa-solid fa-futbol fa-2x mb-2 d-block opacity-30"></i>
                No hay sedes disponibles aún.
            </div>
        @endforelse
    </div>
</div>
@endsection
