@extends('layouts.owner')
@section('title', 'Promociones')
@section('page_title', 'Promociones')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <p class="text-muted mb-0" style="font-size:13px">Creá descuentos por fechas para atraer más clientes</p>
    <button class="btn btn-dark" style="border-radius:12px;font-weight:600;font-size:14px" onclick="openPromoModal()">
        <i class="fa-solid fa-tag me-2"></i>Nueva promoción
    </button>
</div>

@php
$today = now()->toDateString();
@endphp

@forelse($venues as $venue)
<div class="stat-card mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="fw-700 mb-0">{{ $venue->name }}</h6>
        <button class="btn btn-sm" style="border-radius:10px;border:1.5px solid #e0e0e0;font-size:12px;font-weight:600"
                onclick="openPromoModal('{{ $venue->id }}')">
            <i class="fa-solid fa-plus me-1"></i>Agregar promo
        </button>
    </div>

    @forelse($venue->promotions as $promo)
    @php
        $isActive = $promo->active && $today >= $promo->starts_at->format('Y-m-d') && $today <= $promo->ends_at->format('Y-m-d');
        $isExpired = $today > $promo->ends_at->format('Y-m-d');
        $isPending = $today < $promo->starts_at->format('Y-m-d');
    @endphp
    <div class="d-flex align-items-center justify-content-between py-3" style="border-bottom:1px solid #f5f5f5">
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center rounded-3"
                 style="width:42px;height:42px;background:{{ $isActive ? '#fff3e0' : '#f5f5f5' }};font-size:18px">
                {{ $promo->type === 'percentage' ? '💯' : '💰' }}
            </div>
            <div>
                <div class="fw-700" style="font-size:14px">{{ $promo->name }}</div>
                <div style="font-size:12px;color:#666">
                    {{ $promo->display_label }} ·
                    {{ $promo->starts_at->format('d/m/Y') }} – {{ $promo->ends_at->format('d/m/Y') }}
                </div>
                @if(!empty($promo->court_ids))
                    <div class="text-muted" style="font-size:11px">
                        Aplica a {{ count($promo->court_ids) }} cancha(s) específica(s)
                    </div>
                @else
                    <div class="text-muted" style="font-size:11px">Aplica a todas las canchas</div>
                @endif
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            @if($isActive)
                <span class="badge" style="background:#e8f5e9;color:#2e7d32;border-radius:20px;font-size:10px;padding:3px 10px">Activa</span>
            @elseif($isPending)
                <span class="badge" style="background:#fff3e0;color:#e65100;border-radius:20px;font-size:10px;padding:3px 10px">Próxima</span>
            @elseif($isExpired)
                <span class="badge" style="background:#fafafa;color:#999;border-radius:20px;font-size:10px;padding:3px 10px">Expirada</span>
            @else
                <span class="badge" style="background:#fafafa;color:#999;border-radius:20px;font-size:10px;padding:3px 10px">Inactiva</span>
            @endif
            <button class="btn btn-sm" style="border:none;background:none;color:#aaa"
                    onclick="editPromo({{ json_encode($promo) }}, '{{ $venue->id }}')">
                <i class="fa-solid fa-pen-to-square"></i>
            </button>
            <button class="btn btn-sm" style="border:none;background:none;color:#e53935"
                    onclick="deletePromo('{{ $promo->id }}')">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    </div>
    @empty
    <div class="text-muted" style="font-size:13px">Sin promociones aún.</div>
    @endforelse
</div>
@empty
<div class="stat-card text-center py-5">
    <i class="fa-solid fa-tag fa-3x text-muted opacity-30 mb-3 d-block"></i>
    <p class="fw-700 mb-1">No tenés sedes aún</p>
    <a href="{{ route('owner.venues.index') }}" class="btn btn-dark mt-2" style="border-radius:12px;font-weight:600">Ir a sedes</a>
</div>
@endforelse

{{-- Modal --}}
<div class="modal fade" id="promoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:24px;border:none">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="fw-800" id="promoModalTitle">Nueva promoción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <input type="hidden" id="promo_id">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:13px">Sede *</label>
                        <select id="promo_venue_id" class="form-select" style="border-radius:12px" onchange="loadCourtsForPromo(this.value)">
                            @foreach($venues as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:13px">Nombre de la promo *</label>
                        <input type="text" id="promo_name" class="form-control" style="border-radius:12px" placeholder="Ej: Tardes de martes 20% off">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:13px">Descripción (opcional)</label>
                        <input type="text" id="promo_description" class="form-control" style="border-radius:12px" placeholder="Solo canchas de fútbol">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-600" style="font-size:13px">Tipo *</label>
                        <select id="promo_type" class="form-select" style="border-radius:12px">
                            <option value="percentage">Porcentaje (%)</option>
                            <option value="fixed">Monto fijo (₡)</option>
                        </select>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label fw-600" style="font-size:13px" id="promo_value_label">Valor (%) *</label>
                        <input type="number" id="promo_value" class="form-control" style="border-radius:12px" placeholder="20">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:13px">Fecha inicio *</label>
                        <input type="date" id="promo_starts" class="form-control" style="border-radius:12px">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:13px">Fecha fin *</label>
                        <input type="date" id="promo_ends" class="form-control" style="border-radius:12px">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:13px">Canchas donde aplica</label>
                        <div id="promo_courts_container" class="d-flex flex-wrap gap-2 p-2 rounded-3" style="border:1.5px solid #e0e0e0;min-height:42px">
                            <span class="text-muted" style="font-size:12px">Cargando canchas...</span>
                        </div>
                        <small class="text-muted">Si no seleccionás ninguna, aplica a todas las canchas de la sede.</small>
                    </div>
                    <div class="col-12 d-none" id="promo_active_wrap">
                        <label class="d-flex align-items-center gap-2" style="cursor:pointer;font-size:13px">
                            <input type="checkbox" id="promo_active" checked> Promoción activa
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button class="btn" style="border-radius:12px;border:1.5px solid #e0e0e0" data-bs-dismiss="modal">Cancelar</button>
                <button id="btnSavePromo" class="btn btn-dark" style="border-radius:12px;font-weight:700" onclick="savePromo()">Guardar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const promoModal = new bootstrap.Modal(document.getElementById('promoModal'));

// Venue courts data for checkboxes
const venueCourtMap = {
    @foreach($venues as $v)
    '{{ $v->id }}': [
        @foreach($v->courts as $c)
        { id: '{{ $c->id }}', name: '{{ addslashes($c->name) }}' },
        @endforeach
    ],
    @endforeach
};

document.getElementById('promo_type').addEventListener('change', function() {
    document.getElementById('promo_value_label').textContent =
        this.value === 'percentage' ? 'Valor (%) *' : 'Valor (₡) *';
});

function loadCourtsForPromo(venueId, selectedIds = []) {
    const courts = venueCourtMap[venueId] || [];
    const container = document.getElementById('promo_courts_container');
    if (!courts.length) {
        container.innerHTML = '<span class="text-muted" style="font-size:12px">Sin canchas en esta sede.</span>';
        return;
    }
    container.innerHTML = courts.map(c => `
        <label class="d-flex align-items-center gap-1 px-2 py-1 rounded-3" style="cursor:pointer;border:1.5px solid #e0e0e0;font-size:12px;background:#fafafa">
            <input type="checkbox" name="promo_court" value="${c.id}" ${selectedIds.includes(c.id) ? 'checked' : ''}>
            ${c.name}
        </label>
    `).join('');
}

function openPromoModal(venueId = null) {
    document.getElementById('promo_id').value = '';
    document.getElementById('promo_name').value = '';
    document.getElementById('promo_description').value = '';
    document.getElementById('promo_type').value = 'percentage';
    document.getElementById('promo_value').value = '';
    document.getElementById('promo_starts').value = '';
    document.getElementById('promo_ends').value = '';
    document.getElementById('promo_active_wrap').classList.add('d-none');
    document.getElementById('promo_active').checked = true;
    document.getElementById('promo_value_label').textContent = 'Valor (%) *';
    document.getElementById('promoModalTitle').textContent = 'Nueva promoción';

    const firstVenueId = venueId || document.getElementById('promo_venue_id').value;
    if (venueId) document.getElementById('promo_venue_id').value = venueId;
    loadCourtsForPromo(firstVenueId);
    promoModal.show();
}

function editPromo(promo, venueId) {
    document.getElementById('promo_id').value          = promo.id;
    document.getElementById('promo_name').value        = promo.name;
    document.getElementById('promo_description').value = promo.description || '';
    document.getElementById('promo_type').value        = promo.type;
    document.getElementById('promo_value').value       = promo.value;
    document.getElementById('promo_starts').value      = promo.starts_at ? promo.starts_at.substring(0,10) : '';
    document.getElementById('promo_ends').value        = promo.ends_at   ? promo.ends_at.substring(0,10)   : '';
    document.getElementById('promo_venue_id').value    = venueId;
    document.getElementById('promo_active').checked    = !!promo.active;
    document.getElementById('promo_active_wrap').classList.remove('d-none');
    document.getElementById('promo_value_label').textContent = promo.type === 'percentage' ? 'Valor (%) *' : 'Valor (₡) *';
    document.getElementById('promoModalTitle').textContent = 'Editar promoción';

    loadCourtsForPromo(venueId, promo.court_ids || []);
    promoModal.show();
}

async function savePromo() {
    const id  = document.getElementById('promo_id').value;
    const btn = document.getElementById('btnSavePromo');

    const courtIds = [...document.querySelectorAll('input[name="promo_court"]:checked')]
        .map(el => el.value);

    const payload = {
        venue_id:    document.getElementById('promo_venue_id').value,
        name:        document.getElementById('promo_name').value.trim(),
        description: document.getElementById('promo_description').value.trim(),
        type:        document.getElementById('promo_type').value,
        value:       document.getElementById('promo_value').value,
        starts_at:   document.getElementById('promo_starts').value,
        ends_at:     document.getElementById('promo_ends').value,
        court_ids:   courtIds.length ? courtIds : null,
        active:      document.getElementById('promo_active').checked,
    };

    if (!payload.name || !payload.value || !payload.starts_at || !payload.ends_at) {
        Toast.fire({ icon: 'warning', title: 'Completá todos los campos requeridos.' });
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

    try {
        if (id) {
            await axios.put(`/owner/promociones/${id}`, payload);
        } else {
            await axios.post('/owner/promociones', payload);
        }
        Toast.fire({ icon: 'success', title: id ? 'Promoción actualizada.' : 'Promoción creada.' });
        setTimeout(() => location.reload(), 1100);
    } catch(e) {
        Toast.fire({ icon: 'error', title: e.response?.data?.message || 'Error.' });
        btn.disabled = false;
        btn.innerHTML = 'Guardar';
    }
}

async function deletePromo(id) {
    const { isConfirmed } = await Swal.fire({
        title: '¿Eliminar promoción?', icon: 'warning',
        showCancelButton: true, confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar', confirmButtonColor: '#111',
    });
    if (!isConfirmed) return;
    try {
        await axios.delete(`/owner/promociones/${id}`);
        Toast.fire({ icon: 'success', title: 'Eliminada.' });
        setTimeout(() => location.reload(), 1000);
    } catch(e) {
        Toast.fire({ icon: 'error', title: 'Error.' });
    }
}

// Init
loadCourtsForPromo(document.getElementById('promo_venue_id')?.value);
document.getElementById('promo_venue_id')?.addEventListener('change', e => loadCourtsForPromo(e.target.value));
</script>
@endpush
