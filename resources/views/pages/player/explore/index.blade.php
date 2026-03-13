@extends('layouts.player')
@section('title', 'Explorar - SuperCancha')
@section('header_title', 'Explorar canchas')

@section('content')
<div class="px-3 mt-2">
    {{-- Filters --}}
    <form method="GET" action="{{ route('player.explore') }}" id="filterForm">
        <div class="bg-white rounded-4 p-3 mb-4 shadow-sm">
            <div class="row g-2">
                <div class="col-6">
                    <select name="province" id="sel_province" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="">Provincia</option>
                        @foreach($provinces as $p)
                            <option value="{{ $p }}" {{ request('province') == $p ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <select name="canton" id="sel_canton" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="">Cantón</option>
                        @foreach($cantons as $c)
                            <option value="{{ $c }}" {{ request('canton') == $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <select name="district" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="">Distrito</option>
                        @foreach($districts as $d)
                            <option value="{{ $d }}" {{ request('district') == $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <select name="sport" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="">Deporte</option>
                        @foreach($sports as $s)
                            <option value="{{ $s }}" {{ request('sport') == $s ? 'selected' : '' }}>{{ \App\Models\Court::sportLabel($s) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @if(request()->hasAny(['province','canton','district','sport','feature']))
                <div class="mt-2">
                    <a href="{{ route('player.explore') }}" class="btn btn-sm w-100" style="border-radius:10px;border:1px solid #eee;font-size:12px;">
                        <i class="fa-solid fa-xmark me-1"></i> Limpiar filtros
                    </a>
                </div>
            @endif
        </div>
    </form>

    {{-- Results --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <span style="font-size:13px;color:#888">{{ $venues->total() }} sedes encontradas</span>
    </div>

    <div class="row g-3 pb-4">
        @forelse($venues as $venue)
        <div class="col-12">
            <a href="{{ route('player.venue', $venue->slug) }}" class="text-decoration-none">
                <div class="card-venue">
                    @if($venue->images && count($venue->images))
                        <img src="{{ \Storage::disk('s3')->url($venue->images[0]) }}" class="w-100" style="height:160px;object-fit:cover" alt="{{ $venue->name }}">
                    @else
                        <div style="height:160px;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center">
                            <i class="fa-solid fa-building text-white" style="font-size:36px;opacity:.6"></i>
                        </div>
                    @endif
                    <div class="p-3">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="fw-700" style="font-size:16px;color:#111">{{ $venue->name }}</div>
                                <div class="text-muted" style="font-size:12px">
                                    <i class="fa-solid fa-location-dot me-1"></i>{{ $venue->district }}, {{ $venue->canton }}
                                </div>
                            </div>
                            <div class="text-end">
                                @php $avg = round($venue->ratings()->avg('rating') ?? 0, 1); @endphp
                                @if($avg > 0)
                                <div style="color:#f59e0b;font-size:12px;font-weight:700">★ {{ $avg }}</div>
                                <div class="text-muted" style="font-size:10px">{{ $venue->ratings()->count() }} reseñas</div>
                                @else
                                <span style="font-size:11px;background:#f5f5f5;padding:4px 10px;border-radius:20px;font-weight:600;color:#333">
                                    {{ $venue->activeCourts->count() }} {{ $venue->activeCourts->count() == 1 ? 'cancha' : 'canchas' }}
                                </span>
                                @endif
                            </div>
                        </div>
                        <div class="d-flex gap-1 flex-wrap mt-2">
                            @foreach($venue->activeCourts->unique('sport') as $court)
                                <span class="badge" style="background:#111;color:#fff;font-size:10px;border-radius:20px;padding:3px 10px">{{ \App\Models\Court::sportLabel($court->sport) }}</span>
                            @endforeach
                        </div>
                        @if($venue->amenities && count($venue->amenities))
                        <div class="d-flex gap-2 mt-2 flex-wrap">
                            @foreach(array_slice($venue->amenities, 0, 4) as $a)
                                <span style="font-size:11px;color:#666"><i class="fa-solid fa-check me-1 text-success" style="font-size:10px"></i>{{ $a }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </a>
        </div>
        @empty
            <div class="col-12 text-center py-5">
                <div style="font-size:48px;margin-bottom:12px">🔍</div>
                <p class="text-muted fw-600">No encontramos canchas con esos filtros.</p>
                <a href="{{ route('player.explore') }}" class="btn btn-sm" style="border-radius:12px;border:1.5px solid #000;font-weight:600">Ver todas</a>
            </div>
        @endforelse
    </div>

    {{ $venues->links() }}
</div>
@endsection
