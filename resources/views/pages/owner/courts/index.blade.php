@extends('layouts.owner')
@section('title', 'Canchas')
@section('page_title', 'Gestión de canchas')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div></div>
    <button class="btn btn-dark" style="border-radius:12px;font-weight:600;font-size:14px" onclick="openCourtModal()">
        <i class="fa-solid fa-plus me-2"></i>Nueva cancha
    </button>
</div>

@forelse($venues as $venue)
<div class="stat-card mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="fw-700 mb-0">{{ $venue->name }}</h6>
        <button class="btn btn-sm" style="border-radius:10px;border:1.5px solid #e0e0e0;font-size:12px;font-weight:600"
                onclick="openCourtModal('{{ $venue->id }}')">
            <i class="fa-solid fa-plus me-1"></i>Agregar cancha
        </button>
    </div>

    <div class="row g-3">
        @forelse($venue->courts as $court)
        <div class="col-lg-4 col-md-6">
            <div class="p-3 rounded-3" style="border:1.5px solid #f0f0f0">
                @php $courtImages = collect($court->images ?? [])->filter()->values(); @endphp
                @if($courtImages->count())
                    <img src="{{ \Storage::disk('s3')->url($courtImages[0]) }}" class="w-100 rounded-3 mb-2" style="height:120px;object-fit:cover">
                @else
                    <div class="w-100 rounded-3 mb-2 d-flex align-items-center justify-content-center" style="height:80px;background:#f5f5f5">
                        <i class="fa-solid fa-futbol" style="font-size:28px;color:#ccc"></i>
                    </div>
                @endif
                <div class="fw-700" style="font-size:14px">{{ $court->name }}</div>
                <div class="d-flex align-items-center justify-content-between mt-1">
                    <span class="badge" style="background:#111;color:#fff;border-radius:20px;font-size:10px">{{ \App\Models\Court::sportLabel($court->sport) }}</span>
                    <span class="fw-700" style="font-size:13px">₡{{ number_format($court->price_per_hour, 0, ',', '.') }}/hr</span>
                </div>
                <div class="d-flex gap-1 flex-wrap mt-2">
                    @foreach(($court->features ?? []) as $f)
                    <span style="font-size:10px;background:#f0f0f0;border-radius:20px;padding:2px 8px;color:#555">{{ $f }}</span>
                    @endforeach
                </div>
                <div class="d-flex gap-2 mt-3">
                    <button class="btn btn-sm flex-grow-1" style="border-radius:10px;border:1.5px solid #e0e0e0;font-size:11px;font-weight:600"
                            onclick="openSchedules('{{ $court->id }}')">
                        <i class="fa-solid fa-clock me-1"></i>Horarios
                    </button>
                    <button class="btn btn-sm flex-grow-1" style="border-radius:10px;border:1.5px solid #e0e0e0;font-size:11px;font-weight:600"
                            onclick="openBlockouts('{{ $court->id }}')">
                        <i class="fa-solid fa-ban me-1"></i>Bloqueos
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12 text-muted" style="font-size:13px">No hay canchas en esta sede.</div>
        @endforelse
    </div>
</div>
@empty
<div class="stat-card text-center py-5">
    <i class="fa-solid fa-store fa-3x text-muted opacity-30 mb-3 d-block"></i>
    <p class="fw-700 mb-1">Primero creá una sede</p>
    <a href="{{ route('owner.venues.index') }}" class="btn btn-dark mt-2" style="border-radius:12px;font-weight:600">Ir a sedes</a>
</div>
@endforelse

{{-- Modal nueva cancha --}}
<div class="modal fade" id="courtModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:24px;border:none">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="fw-800">Nueva cancha</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <input type="hidden" id="c_venue_id">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:13px">Sede *</label>
                        <select id="c_venue_select" class="form-select" style="border-radius:12px">
                            @foreach($venues as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label fw-600" style="font-size:13px">Nombre de la cancha *</label>
                        <input type="text" id="c_name" class="form-control" style="border-radius:12px" placeholder="Ej: Cancha A">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-600" style="font-size:13px">Deporte *</label>
                        <select id="c_sport" class="form-select" style="border-radius:12px">
                            <option value="futbol">⚽ Fútbol</option>
                            <option value="basquetbol">🏀 Baloncesto</option>
                            <option value="tenis">🎾 Tenis</option>
                            <option value="padel">🏸 Pádel</option>
                            <option value="volleyball">🏐 Volleyball</option>
                            <option value="beisbol">⚾ Béisbol</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:13px">Precio por hora (₡) *</label>
                        <input type="number" id="c_price" class="form-control" style="border-radius:12px" placeholder="15000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:13px">Duración del slot</label>
                        <select id="c_slot" class="form-select" style="border-radius:12px">
                            <option value="60">60 min (1 hora)</option>
                            <option value="30">30 min</option>
                            <option value="90">90 min</option>
                            <option value="120">120 min</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:13px">Características (separado por coma)</label>
                        <input type="text" id="c_features" class="form-control" style="border-radius:12px" placeholder="Iluminación, Vestuarios, Parqueo">
                        <small class="text-muted">Se usarán como filtros en el marketplace</small>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:13px">Fotos de la cancha</label>
                        <input type="file" id="c_images" class="form-control" accept="image/*" multiple style="border-radius:12px">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button class="btn" style="border-radius:12px;border:1.5px solid #e0e0e0" data-bs-dismiss="modal">Cancelar</button>
                <button id="btnSaveCourt" class="btn btn-dark" style="border-radius:12px;font-weight:700" onclick="saveCourt()">Crear cancha</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal horarios --}}
<div class="modal fade" id="schedulesModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:24px;border:none">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="fw-800">Horarios de apertura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4" id="schedulesBody">
                <input type="hidden" id="sch_court_id">
                @php $days = ['mon'=>'Lunes','tue'=>'Martes','wed'=>'Miércoles','thu'=>'Jueves','fri'=>'Viernes','sat'=>'Sábado','sun'=>'Domingo']; @endphp
                @foreach($days as $key => $label)
                <div class="d-flex align-items-center gap-3 py-2" style="border-bottom:1px solid #f5f5f5">
                    <div style="width:80px;font-size:13px;font-weight:600">{{ $label }}</div>
                    <input type="checkbox" class="form-check-input day-check" id="day_{{ $key }}" value="{{ $key }}" onchange="toggleDay('{{ $key }}')">
                    <input type="time" id="open_{{ $key }}" class="form-control form-control-sm d-none" style="border-radius:8px;max-width:100px" value="06:00">
                    <span class="d-none" id="dash_{{ $key }}">a</span>
                    <input type="time" id="close_{{ $key }}" class="form-control form-control-sm d-none" style="border-radius:8px;max-width:100px" value="22:00">
                </div>
                @endforeach
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button class="btn" style="border-radius:12px;border:1.5px solid #e0e0e0" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-dark" style="border-radius:12px;font-weight:700" onclick="saveSchedules()">Guardar horarios</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal bloqueos --}}
<div class="modal fade" id="blockoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:24px;border:none">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="fw-800">Agregar bloqueo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <input type="hidden" id="blk_court_id">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:13px">Fecha</label>
                        <input type="date" id="blk_date" class="form-control" style="border-radius:12px">
                    </div>
                    <div class="col-12">
                        <label class="d-flex align-items-center gap-2" style="font-size:13px;cursor:pointer">
                            <input type="checkbox" id="blk_fullday" onchange="toggleFullDay()"> Día completo
                        </label>
                    </div>
                    <div id="blk_time_fields" class="col-12">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px">Desde</label>
                                <input type="time" id="blk_start" class="form-control" style="border-radius:12px">
                            </div>
                            <div class="col-6">
                                <label class="form-label" style="font-size:12px">Hasta</label>
                                <input type="time" id="blk_end" class="form-control" style="border-radius:12px">
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:13px">Motivo (opcional)</label>
                        <input type="text" id="blk_reason" class="form-control" style="border-radius:12px" placeholder="Mantenimiento, evento privado...">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button class="btn" style="border-radius:12px;border:1.5px solid #e0e0e0" data-bs-dismiss="modal">Cancelar</button>
                <button class="btn btn-dark" style="border-radius:12px;font-weight:700" onclick="saveBlockout()">Agregar bloqueo</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const courtModal     = new bootstrap.Modal(document.getElementById('courtModal'));
const schedulesModal = new bootstrap.Modal(document.getElementById('schedulesModal'));
const blockoutModal  = new bootstrap.Modal(document.getElementById('blockoutModal'));

function openCourtModal(venueId = null) {
    if (venueId) document.getElementById('c_venue_select').value = venueId;
    courtModal.show();
}

async function saveCourt() {
    const btn = document.getElementById('btnSaveCourt');
    const formData = new FormData();
    formData.append('venue_id',       document.getElementById('c_venue_select').value);
    formData.append('name',           document.getElementById('c_name').value.trim());
    formData.append('sport',          document.getElementById('c_sport').value);
    formData.append('price_per_hour', document.getElementById('c_price').value);
    formData.append('slot_duration',  document.getElementById('c_slot').value);

    const features = document.getElementById('c_features').value.split(',').map(s => s.trim()).filter(Boolean);
    features.forEach(f => formData.append('features[]', f));

    const imgs = document.getElementById('c_images').files;
    for (let i = 0; i < imgs.length; i++) formData.append('images[]', imgs[i]);

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Guardando...';

    try {
        await axios.post('/owner/canchas', formData, { headers: { 'Content-Type': 'multipart/form-data' } });
        Toast.fire({ icon: 'success', title: '¡Cancha creada!' });
        setTimeout(() => location.reload(), 1200);
    } catch(e) {
        Toast.fire({ icon: 'error', title: e.response?.data?.message || 'Error.' });
        btn.disabled = false;
        btn.innerHTML = 'Crear cancha';
    }
}

function openSchedules(courtId) {
    document.getElementById('sch_court_id').value = courtId;
    schedulesModal.show();
}

function toggleDay(day) {
    const checked = document.getElementById('day_' + day).checked;
    ['open_','close_','dash_'].forEach(p => {
        const el = document.getElementById(p + day);
        if (el) el.classList.toggle('d-none', !checked);
    });
}

async function saveSchedules() {
    const courtId = document.getElementById('sch_court_id').value;
    const days = ['mon','tue','wed','thu','fri','sat','sun'];
    const schedules = [];

    days.forEach(d => {
        if (document.getElementById('day_' + d).checked) {
            schedules.push({
                day_of_week: d,
                open_time:   document.getElementById('open_' + d).value,
                close_time:  document.getElementById('close_' + d).value,
                active: true,
            });
        }
    });

    if (!schedules.length) {
        Toast.fire({ icon: 'warning', title: 'Seleccioná al menos un día.' });
        return;
    }

    try {
        await axios.post(`/owner/canchas/${courtId}/horarios`, { schedules });
        Toast.fire({ icon: 'success', title: 'Horarios guardados.' });
        schedulesModal.hide();
    } catch(e) {
        Toast.fire({ icon: 'error', title: 'Error guardando horarios.' });
    }
}

function openBlockouts(courtId) {
    document.getElementById('blk_court_id').value = courtId;
    blockoutModal.show();
}

function toggleFullDay() {
    const fullDay = document.getElementById('blk_fullday').checked;
    document.getElementById('blk_time_fields').classList.toggle('d-none', fullDay);
}

async function saveBlockout() {
    const courtId = document.getElementById('blk_court_id').value;
    const fullDay = document.getElementById('blk_fullday').checked;
    const payload = {
        block_date: document.getElementById('blk_date').value,
        full_day:   fullDay,
        reason:     document.getElementById('blk_reason').value,
    };
    if (!fullDay) {
        payload.start_time = document.getElementById('blk_start').value;
        payload.end_time   = document.getElementById('blk_end').value;
    }

    try {
        await axios.post(`/owner/canchas/${courtId}/bloqueos`, payload);
        Toast.fire({ icon: 'success', title: 'Bloqueo agregado.' });
        blockoutModal.hide();
    } catch(e) {
        Toast.fire({ icon: 'error', title: 'Error.' });
    }
}
</script>
@endpush
