@extends('layouts.owner')
@section('title', 'Mis Sedes')
@section('page_title', 'Mis sedes')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div></div>
    <button class="btn btn-dark" style="border-radius:12px;font-weight:600;font-size:14px" onclick="openVenueModal()">
        <i class="fa-solid fa-plus me-2"></i>Nueva sede
    </button>
</div>

<div class="row g-3">
    @forelse($venues as $venue)
    <div class="col-lg-6">
        <div class="stat-card h-100">
            <div class="d-flex gap-3">
                @if($venue->logo)
                    <img src="{{ \Storage::disk('s3')->url($venue->logo) }}" class="rounded-3 flex-shrink-0" style="width:56px;height:56px;object-fit:cover">
                @else
                    <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width:56px;height:56px;background:#f5f5f5">
                        <i class="fa-solid fa-building" style="font-size:22px;color:#888"></i>
                    </div>
                @endif
                <div class="flex-grow-1">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="fw-700" style="font-size:16px">{{ $venue->name }}</div>
                            <div class="text-muted" style="font-size:12px"><i class="fa-solid fa-location-dot me-1"></i>{{ $venue->canton }}, {{ $venue->province }}</div>
                        </div>
                        <span class="badge" style="{{ $venue->active ? 'background:#e8f5e9;color:#2e7d32' : 'background:#f5f5f5;color:#888' }};border-radius:20px;font-size:11px;padding:4px 10px">
                            {{ $venue->active ? 'Activa' : 'Inactiva' }}
                        </span>
                    </div>
                    <div class="mt-2 d-flex gap-2">
                        <span style="font-size:12px;background:#f0f0f0;border-radius:20px;padding:3px 10px;color:#555">
                            <i class="fa-solid fa-futbol me-1"></i>{{ $venue->activeCourts->count() }} canchas
                        </span>
                        @if($venue->phone)
                        <span style="font-size:12px;background:#f0f0f0;border-radius:20px;padding:3px 10px;color:#555">
                            <i class="fa-solid fa-phone me-1"></i>{{ $venue->phone }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="stat-card text-center py-5">
            <i class="fa-solid fa-building fa-3x text-muted opacity-30 mb-3 d-block"></i>
            <p class="fw-700 mb-1">Aún no tenés sedes</p>
            <p class="text-muted" style="font-size:13px">Creá tu primera sede para empezar a publicar canchas.</p>
            <button class="btn btn-dark mt-2" style="border-radius:12px;font-weight:600" onclick="openVenueModal()">
                <i class="fa-solid fa-plus me-2"></i>Crear sede
            </button>
        </div>
    </div>
    @endforelse
</div>

{{-- Modal nueva sede --}}
<div class="modal fade" id="venueModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:24px;border:none">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="fw-800">Nueva sede</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:13px">Nombre de la sede *</label>
                        <input type="text" id="v_name" class="form-control" style="border-radius:12px">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:13px">Teléfono</label>
                        <input type="tel" id="v_phone" class="form-control" style="border-radius:12px" placeholder="8888-8888">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:13px">Dirección</label>
                        <input type="text" id="v_address" class="form-control" style="border-radius:12px">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600" style="font-size:13px">Provincia *</label>
                        <select id="v_province" class="form-select" style="border-radius:12px" onchange="loadCantons()">
                            <option value="">Seleccionar</option>
                            @foreach($provinces as $p)
                            <option value="{{ $p }}">{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600" style="font-size:13px">Cantón *</label>
                        <select id="v_canton" class="form-select" style="border-radius:12px" onchange="loadDistricts()">
                            <option value="">Seleccionar provincia</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600" style="font-size:13px">Distrito *</label>
                        <select id="v_district" class="form-select" style="border-radius:12px">
                            <option value="">Seleccionar cantón</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:13px">Descripción</label>
                        <textarea id="v_description" class="form-control" rows="3" style="border-radius:12px;resize:none"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:13px">Logo (opcional)</label>
                        <input type="file" id="v_logo" class="form-control" accept="image/*" style="border-radius:12px">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:13px">Fotos del complejo</label>
                        <input type="file" id="v_images" class="form-control" accept="image/*" multiple style="border-radius:12px">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button class="btn" style="border-radius:12px;border:1.5px solid #e0e0e0" data-bs-dismiss="modal">Cancelar</button>
                <button id="btnSaveVenue" class="btn btn-dark" style="border-radius:12px;font-weight:700" onclick="saveVenue()">Crear sede</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const venueModal = new bootstrap.Modal(document.getElementById('venueModal'));

function openVenueModal() { venueModal.show(); }

async function loadCantons() {
    const province = document.getElementById('v_province').value;
    if (!province) return;
    const res = await axios.get('/owner/sedes/cantones', { params: { province } });
    const sel = document.getElementById('v_canton');
    sel.innerHTML = '<option value="">Seleccionar</option>' + res.data.map(c => `<option value="${c}">${c}</option>`).join('');
    document.getElementById('v_district').innerHTML = '<option value="">Seleccionar cantón</option>';
}

async function loadDistricts() {
    const province = document.getElementById('v_province').value;
    const canton   = document.getElementById('v_canton').value;
    if (!province || !canton) return;
    const res = await axios.get('/owner/sedes/distritos', { params: { province, canton } });
    const sel = document.getElementById('v_district');
    sel.innerHTML = '<option value="">Seleccionar</option>' + res.data.map(d => `<option value="${d}">${d}</option>`).join('');
}

async function saveVenue() {
    const btn = document.getElementById('btnSaveVenue');
    const name     = document.getElementById('v_name').value.trim();
    const province = document.getElementById('v_province').value;
    const canton   = document.getElementById('v_canton').value;
    const district = document.getElementById('v_district').value;

    if (!name || !province || !canton || !district) {
        Toast.fire({ icon: 'warning', title: 'Completá los campos obligatorios.' });
        return;
    }

    const formData = new FormData();
    formData.append('name',        name);
    formData.append('description', document.getElementById('v_description').value);
    formData.append('phone',       document.getElementById('v_phone').value);
    formData.append('address',     document.getElementById('v_address').value);
    formData.append('province',    province);
    formData.append('canton',      canton);
    formData.append('district',    district);

    const logo = document.getElementById('v_logo').files[0];
    if (logo) formData.append('logo', logo);

    const imgs = document.getElementById('v_images').files;
    for (let i = 0; i < imgs.length; i++) formData.append('images[]', imgs[i]);

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Guardando...';

    try {
        await axios.post('/owner/sedes', formData, { headers: { 'Content-Type': 'multipart/form-data' } });
        Toast.fire({ icon: 'success', title: '¡Sede creada!' });
        setTimeout(() => location.reload(), 1200);
    } catch(e) {
        Toast.fire({ icon: 'error', title: e.response?.data?.message || 'Error al crear.' });
        btn.disabled = false;
        btn.innerHTML = 'Crear sede';
    }
}
</script>
@endpush
