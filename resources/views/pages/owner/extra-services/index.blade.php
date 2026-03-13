@extends('layouts.owner')
@section('title', 'Servicios adicionales')
@section('page_title', 'Servicios adicionales')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <p class="text-muted mb-0" style="font-size:13px">Servicios que podés ofrecer al rentar tus canchas (alquiler de tacos, petos, etc.)</p>
    <button class="btn btn-dark" style="border-radius:12px;font-weight:600;font-size:14px" onclick="openServiceModal()">
        <i class="fa-solid fa-plus me-2"></i>Nuevo servicio
    </button>
</div>

@forelse($venues as $venue)
<div class="stat-card mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h6 class="fw-700 mb-0">{{ $venue->name }}</h6>
        <button class="btn btn-sm" style="border-radius:10px;border:1.5px solid #e0e0e0;font-size:12px;font-weight:600"
                onclick="openServiceModal('{{ $venue->id }}')">
            <i class="fa-solid fa-plus me-1"></i>Agregar servicio
        </button>
    </div>

    @forelse($venue->extraServices as $svc)
    <div class="d-flex align-items-center justify-content-between py-3" style="border-bottom:1px solid #f5f5f5">
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center justify-content-center rounded-3" style="width:42px;height:42px;background:#f5f5f5;font-size:18px">🛎</div>
            <div>
                <div class="fw-700" style="font-size:14px">{{ $svc->name }}</div>
                @if($svc->description)
                    <div class="text-muted" style="font-size:12px">{{ $svc->description }}</div>
                @endif
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="fw-700" style="font-size:15px">₡{{ number_format($svc->price, 0, ',', '.') }}</span>
            <span class="badge" style="{{ $svc->active ? 'background:#e8f5e9;color:#2e7d32' : 'background:#fafafa;color:#999' }};border-radius:20px;font-size:10px;padding:3px 10px">
                {{ $svc->active ? 'Activo' : 'Inactivo' }}
            </span>
            <button class="btn btn-sm" style="border:none;background:none;color:#aaa" title="Editar"
                    onclick="editService('{{ $svc->id }}','{{ addslashes($svc->name) }}','{{ addslashes($svc->description ?? '') }}','{{ $svc->price }}','{{ $svc->active ? 1 : 0 }}','{{ $venue->id }}')">
                <i class="fa-solid fa-pen-to-square"></i>
            </button>
            <button class="btn btn-sm" style="border:none;background:none;color:#e53935" title="Eliminar"
                    onclick="deleteService('{{ $svc->id }}')">
                <i class="fa-solid fa-trash"></i>
            </button>
        </div>
    </div>
    @empty
    <div class="text-muted" style="font-size:13px">Sin servicios aún.</div>
    @endforelse
</div>
@empty
<div class="stat-card text-center py-5">
    <i class="fa-solid fa-store fa-3x text-muted opacity-30 mb-3 d-block"></i>
    <p class="fw-700 mb-1">Primero creá una sede</p>
    <a href="{{ route('owner.venues.index') }}" class="btn btn-dark mt-2" style="border-radius:12px;font-weight:600">Ir a sedes</a>
</div>
@endforelse

{{-- Modal --}}
<div class="modal fade" id="serviceModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:24px;border:none">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="fw-800" id="serviceModalTitle">Nuevo servicio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <input type="hidden" id="svc_id">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:13px">Sede *</label>
                        <select id="svc_venue_id" class="form-select" style="border-radius:12px">
                            @foreach($venues as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-600" style="font-size:13px">Nombre del servicio *</label>
                        <input type="text" id="svc_name" class="form-control" style="border-radius:12px" placeholder="Ej: Alquiler de tacos">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600" style="font-size:13px">Precio (₡) *</label>
                        <input type="number" id="svc_price" class="form-control" style="border-radius:12px" placeholder="2500">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:13px">Descripción (opcional)</label>
                        <input type="text" id="svc_description" class="form-control" style="border-radius:12px" placeholder="Incluye botines talla 38-46">
                    </div>
                    <div class="col-12 d-none" id="svc_active_wrap">
                        <label class="d-flex align-items-center gap-2" style="cursor:pointer;font-size:13px">
                            <input type="checkbox" id="svc_active" checked> Servicio activo
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button class="btn" style="border-radius:12px;border:1.5px solid #e0e0e0" data-bs-dismiss="modal">Cancelar</button>
                <button id="btnSaveService" class="btn btn-dark" style="border-radius:12px;font-weight:700" onclick="saveService()">Guardar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const serviceModal = new bootstrap.Modal(document.getElementById('serviceModal'));

function openServiceModal(venueId = null) {
    document.getElementById('svc_id').value = '';
    document.getElementById('svc_name').value = '';
    document.getElementById('svc_description').value = '';
    document.getElementById('svc_price').value = '';
    document.getElementById('svc_active').checked = true;
    document.getElementById('svc_active_wrap').classList.add('d-none');
    document.getElementById('serviceModalTitle').textContent = 'Nuevo servicio';
    if (venueId) document.getElementById('svc_venue_id').value = venueId;
    serviceModal.show();
}

function editService(id, name, desc, price, active, venueId) {
    document.getElementById('svc_id').value = id;
    document.getElementById('svc_name').value = name;
    document.getElementById('svc_description').value = desc;
    document.getElementById('svc_price').value = price;
    document.getElementById('svc_active').checked = active == 1;
    document.getElementById('svc_venue_id').value = venueId;
    document.getElementById('svc_active_wrap').classList.remove('d-none');
    document.getElementById('serviceModalTitle').textContent = 'Editar servicio';
    serviceModal.show();
}

async function saveService() {
    const id  = document.getElementById('svc_id').value;
    const btn = document.getElementById('btnSaveService');
    const payload = {
        venue_id:    document.getElementById('svc_venue_id').value,
        name:        document.getElementById('svc_name').value.trim(),
        description: document.getElementById('svc_description').value.trim(),
        price:       document.getElementById('svc_price').value,
        active:      document.getElementById('svc_active').checked,
    };

    if (!payload.name || !payload.price) {
        Toast.fire({ icon: 'warning', title: 'Nombre y precio son requeridos.' });
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

    try {
        if (id) {
            await axios.put(`/owner/servicios/${id}`, payload);
        } else {
            await axios.post('/owner/servicios', payload);
        }
        Toast.fire({ icon: 'success', title: id ? 'Servicio actualizado.' : 'Servicio creado.' });
        setTimeout(() => location.reload(), 1100);
    } catch(e) {
        Toast.fire({ icon: 'error', title: e.response?.data?.message || 'Error.' });
        btn.disabled = false;
        btn.innerHTML = 'Guardar';
    }
}

async function deleteService(id) {
    const { isConfirmed } = await Swal.fire({
        title: '¿Eliminar servicio?',
        text: 'Los servicios ya usados en reservas no se afectan.',
        icon: 'warning', showCancelButton: true,
        confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar',
        confirmButtonColor: '#111',
    });
    if (!isConfirmed) return;

    try {
        await axios.delete(`/owner/servicios/${id}`);
        Toast.fire({ icon: 'success', title: 'Eliminado.' });
        setTimeout(() => location.reload(), 1000);
    } catch(e) {
        Toast.fire({ icon: 'error', title: 'Error.' });
    }
}
</script>
@endpush
