@extends('layouts.player')
@section('title', $venue->name . ' - SuperCancha')

@section('content')
<div>
    {{-- Hero images --}}
    @if($venue->images && count($venue->images))
    <div class="position-relative" style="height:240px;overflow:hidden">
        <img src="{{ \Storage::disk('s3')->url($venue->images[0]) }}" class="w-100 h-100" style="object-fit:cover">
        <div style="position:absolute;inset:0;background:linear-gradient(to bottom, transparent 50%, rgba(0,0,0,0.6))"></div>
        <a href="{{ route('player.explore') }}" class="position-absolute btn bg-white rounded-circle shadow-sm" style="top:16px;left:16px;width:38px;height:38px;display:flex;align-items:center;justify-content:center">
            <i class="fa-solid fa-arrow-left" style="font-size:14px"></i>
        </a>
    </div>
    @else
    <div class="position-relative" style="height:180px;background:linear-gradient(135deg,#1a1a2e,#16213e);display:flex;align-items:center;justify-content:center">
        <i class="fa-solid fa-building text-white" style="font-size:48px;opacity:.3"></i>
        <a href="{{ route('player.explore') }}" class="position-absolute btn bg-white rounded-circle shadow-sm" style="top:16px;left:16px;width:38px;height:38px;display:flex;align-items:center;justify-content:center">
            <i class="fa-solid fa-arrow-left" style="font-size:14px"></i>
        </a>
    </div>
    @endif

    <div class="px-4 pt-4">
        {{-- Venue info --}}
        <div class="d-flex align-items-start gap-3 mb-3">
            @if($venue->logo)
            <img src="{{ \Storage::disk('s3')->url($venue->logo) }}" class="rounded-3 flex-shrink-0" style="width:56px;height:56px;object-fit:cover">
            @endif
            <div>
                <h1 class="fw-800 mb-1" style="font-size:22px">{{ $venue->name }}</h1>
                <div class="text-muted" style="font-size:13px">
                    <i class="fa-solid fa-location-dot me-1"></i>{{ $venue->address ?? $venue->district.', '.$venue->canton }}
                </div>
                @if($venue->phone)
                <div class="text-muted" style="font-size:13px">
                    <i class="fa-solid fa-phone me-1"></i>{{ $venue->phone }}
                </div>
                @endif
            </div>
        </div>

        @if($venue->description)
        <p class="text-muted mb-4" style="font-size:14px;line-height:1.6">{{ $venue->description }}</p>
        @endif

        {{-- Amenities --}}
        @if($venue->amenities && count($venue->amenities))
        <div class="mb-4">
            <h3 class="fw-700 mb-2" style="font-size:15px">Servicios</h3>
            <div class="d-flex flex-wrap gap-2">
                @foreach($venue->amenities as $a)
                <span style="background:#f5f5f5;border-radius:20px;padding:5px 12px;font-size:12px;font-weight:600;color:#444">
                    <i class="fa-solid fa-check me-1 text-success" style="font-size:10px"></i>{{ $a }}
                </span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Map --}}
        @if($venue->lat && $venue->lng)
        <div class="mb-4">
            <h3 class="fw-700 mb-2" style="font-size:15px">Ubicación</h3>
            <div id="venueMap" style="height:200px;border-radius:16px;overflow:hidden"></div>
        </div>
        @endif

        {{-- Courts --}}
        {{-- Rating summary + form --}}
        @php
            $avgRating   = round($venue->ratings()->avg('rating') ?? 0, 1);
            $ratingCount = $venue->ratings()->count();
            $userRating  = $venue->ratings()->where('user_id', auth()->id())->first();
        @endphp
        <div class="mb-4 p-4 rounded-4" style="background:#f9f9f9">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="text-center">
                    <div class="fw-800" style="font-size:36px;line-height:1;color:#f59e0b">{{ $avgRating > 0 ? $avgRating : '—' }}</div>
                    <div style="color:#f59e0b;font-size:15px">
                        @for($i=1;$i<=5;$i++)<span>{{ $i <= round($avgRating) ? '★' : '☆' }}</span>@endfor
                    </div>
                    <div class="text-muted" style="font-size:11px">{{ $ratingCount }} {{ $ratingCount === 1 ? 'reseña' : 'reseñas' }}</div>
                </div>
                <div class="flex-grow-1">
                    <h3 class="fw-700 mb-1" style="font-size:14px">{{ $userRating ? 'Tu calificación' : 'Calificá esta sede' }}</h3>
                    <p class="text-muted mb-2" style="font-size:12px">
                        {{ $userRating ? 'Ya calificaste. Podés actualizar.' : 'Solo si tenés una reserva confirmada.' }}
                    </p>
                    <div class="d-flex gap-1 mb-2" id="starRow">
                        @for($i=1;$i<=5;$i++)
                        <span class="star-btn" data-val="{{ $i }}"
                              style="font-size:28px;cursor:pointer;color:{{ $userRating && $userRating->rating >= $i ? '#f59e0b' : '#ddd' }};transition:color .15s"
                              onmouseenter="hoverStars({{ $i }})" onmouseleave="resetStars()" onclick="selectStar({{ $i }})">★</span>
                        @endfor
                    </div>
                    <textarea id="ratingComment" class="form-control mb-2" rows="2" style="border-radius:12px;font-size:13px;resize:none" placeholder="Comentario opcional...">{{ $userRating?->comment }}</textarea>
                    <button onclick="submitRating('{{ $venue->id }}')" class="btn btn-sm fw-700" style="background:#111;color:#fff;border-radius:10px;font-size:12px;padding:6px 16px">
                        {{ $userRating ? 'Actualizar' : 'Enviar calificación' }}
                    </button>
                </div>
            </div>
        </div>

        <h3 class="fw-700 mb-3" style="font-size:15px">Canchas disponibles</h3>
        <div class="row g-3 pb-5">
            @forelse($venue->activeCourts as $court)
            <div class="col-12">
                <a href="{{ route('player.court', [$venue->slug, $court->id]) }}" class="text-decoration-none">
                    <div class="card border-0 shadow-sm" style="border-radius:20px;overflow:hidden">
                        <div class="d-flex">
                            @if($court->images && count($court->images))
                                <img src="{{ \Storage::disk('s3')->url($court->images[0]) }}" style="width:100px;height:100px;object-fit:cover;flex-shrink:0">
                            @else
                                <div style="width:100px;height:100px;background:linear-gradient(135deg,#667eea,#764ba2);flex-shrink:0;display:flex;align-items:center;justify-content:center">
                                    <i class="fa-solid fa-futbol text-white" style="font-size:24px"></i>
                                </div>
                            @endif
                            <div class="p-3 d-flex flex-column justify-content-between flex-grow-1">
                                <div>
                                    <div class="fw-700" style="font-size:15px;color:#111">{{ $court->name }}</div>
                                    <span class="badge" style="background:#111;color:#fff;border-radius:20px;font-size:10px;padding:2px 8px">
                                        {{ \App\Models\Court::sportLabel($court->sport) }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="fw-800" style="font-size:17px">₡{{ number_format($court->price_per_hour, 0, ',', '.') }}<span class="text-muted fw-400" style="font-size:11px">/hr</span></span>
                                    <span style="font-size:12px;color:#6C63FF;font-weight:700">Reservar →</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            @empty
            <div class="col-12 text-center text-muted py-3" style="font-size:13px">No hay canchas activas.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($venue->lat && $venue->lng)
<script>
const map = L.map('venueMap').setView([{{ $venue->lat }}, {{ $venue->lng }}], 16);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
L.marker([{{ $venue->lat }}, {{ $venue->lng }}]).addTo(map).bindPopup('<b>{{ $venue->name }}</b>').openPopup();
</script>
@endif
<script>
let selectedStar = {{ optional($userRating)->rating ?? 0 }};

function hoverStars(n) {
    document.querySelectorAll('.star-btn').forEach((s,i) => s.style.color = i < n ? '#f59e0b' : '#ddd');
}
function resetStars() {
    document.querySelectorAll('.star-btn').forEach((s,i) => s.style.color = i < selectedStar ? '#f59e0b' : '#ddd');
}
function selectStar(n) {
    selectedStar = n;
    resetStars();
}
async function submitRating(venueId) {
    if (!selectedStar) { Toast.fire({ icon:'warning', title:'Seleccioná una calificación.' }); return; }
    try {
        const res = await axios.post(`/app/sede/${venueId}/calificar`, {
            rating:  selectedStar,
            comment: document.getElementById('ratingComment').value,
        });
        Toast.fire({ icon:'success', title: res.data.message });
    } catch(e) {
        Toast.fire({ icon:'error', title: e.response?.data?.message || 'No podés calificar aún.' });
    }
}
</script>
@endpush
