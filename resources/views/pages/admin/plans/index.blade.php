@extends('layouts.admin')
@section('title', 'Planes')
@section('page_title', 'Planes de suscripción')

@section('content')
<div class="d-flex justify-content-end mb-4">
    <button class="btn btn-dark" style="border-radius:12px;font-weight:600;font-size:14px" onclick="openModal()">
        <i class="fa-solid fa-plus me-2"></i>Nuevo plan
    </button>
</div>

<div class="row g-3">
    @forelse($plans as $plan)
    <div class="col-lg-4">
        {{-- FIX: all plan data stored in data-* attributes, no inline JS args --}}
        <div class="stat-card position-relative {{ !$plan->active ? 'opacity-50' : '' }}"
             data-plan-id="{{ $plan->id }}"
             data-plan-name="{{ $plan->name }}"
             data-plan-desc="{{ $plan->description }}"
             data-plan-price="{{ $plan->price }}"
             data-plan-courts="{{ $plan->court_limit }}"
             data-plan-priceid="{{ $plan->onvopay_price_id ?? $plan->onvopay_id }}"
             data-plan-active="{{ $plan->active ? '1' : '0' }}">

            <div class="d-flex align-items-start justify-content-between mb-3">
                <div>
                    <h5 class="fw-800 mb-0">{{ $plan->name }}</h5>
                    <div class="text-muted" style="font-size:12px;margin-top:2px">{{ $plan->description }}</div>
                </div>
                <span style="{{ $plan->active ? 'background:#e8f5e9;color:#2e7d32' : 'background:#f5f5f5;color:#888' }};border-radius:20px;font-size:11px;padding:4px 10px;white-space:nowrap;flex-shrink:0">
                    {{ $plan->active ? '● Activo' : '○ Inactivo' }}
                </span>
            </div>

            <div class="fw-800 mb-3" style="font-size:32px;letter-spacing:-1px">
                ₡{{ number_format($plan->price,0,',','.') }}<span class="text-muted fw-400" style="font-size:13px">/mes</span>
            </div>

            <div class="d-flex gap-3 mb-4" style="font-size:13px">
                <span><i class="fa-solid fa-futbol me-1 text-muted"></i><strong>{{ $plan->court_limit }}</strong> canchas</span>
                <span><i class="fa-solid fa-users me-1 text-muted"></i><strong>{{ $plan->subscriptions_count }}</strong> suscriptores</span>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-sm flex-grow-1 btn-edit-plan"
                        style="border:1.5px solid #e0e0e0;border-radius:10px;font-size:12px;font-weight:600">
                    <i class="fa-solid fa-pen me-1"></i>Editar
                </button>
                <button class="btn btn-sm btn-toggle-plan"
                        data-id="{{ $plan->id }}"
                        style="border:1.5px solid #e0e0e0;border-radius:10px;font-size:12px;font-weight:600;width:38px"
                        title="{{ $plan->active ? 'Desactivar' : 'Activar' }}">
                    <i class="fa-solid {{ $plan->active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                </button>
                <button class="btn btn-sm btn-delete-plan"
                        data-id="{{ $plan->id }}"
                        style="background:#ffebee;color:#c62828;border-radius:10px;font-size:12px;width:38px">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="stat-card text-center py-5">
            <i class="fa-solid fa-layer-group fa-3x text-muted opacity-25 mb-3 d-block"></i>
            <p class="fw-700">No hay planes creados</p>
            <button class="btn btn-dark" style="border-radius:12px" onclick="openModal()">Crear primer plan</button>
        </div>
    </div>
    @endforelse
</div>

{{-- Modal crear/editar --}}
<div class="modal fade" id="planModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:24px;border:none;box-shadow:0 20px 60px rgba(0,0,0,0.15)">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="fw-800" id="modalTitle">Nuevo plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-2">
                <input type="hidden" id="p_id">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:13px">Nombre del plan *</label>
                        <input type="text" id="p_name" class="form-control" style="border-radius:12px" placeholder="Ej: Básico, Profesional...">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:13px">Descripción</label>
                        <input type="text" id="p_desc" class="form-control" style="border-radius:12px" placeholder="Breve descripción del plan">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-600" style="font-size:13px">Precio mensual (₡) *</label>
                        <input type="number" id="p_price" class="form-control" style="border-radius:12px" placeholder="15000" min="0">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-600" style="font-size:13px">Límite de canchas *</label>
                        <input type="number" id="p_courts" class="form-control" style="border-radius:12px" placeholder="2" min="1">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:13px">ONVO Price ID <small class="text-muted fw-400">(para pago con tarjeta)</small></label>
                        <input type="text" id="p_priceid" class="form-control" style="border-radius:12px;font-family:monospace" placeholder="cmklig02j32j5js200psne5v6">
                        <small class="text-muted">Obtenerlo en ONVO Pay → Productos → Precios</small>
                    </div>
                    <div class="col-12" id="activeToggleWrap" style="display:none">
                        <div class="p-3 rounded-3" style="background:#f9f9f9">
                            <label class="d-flex align-items-center gap-2 mb-0" style="font-size:13px;cursor:pointer;font-weight:600">
                                <input type="checkbox" id="p_active" class="form-check-input" style="width:18px;height:18px">
                                Plan activo (visible para owners)
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-3">
                <button class="btn" style="border-radius:12px;border:1.5px solid #e0e0e0;font-weight:600" data-bs-dismiss="modal">Cancelar</button>
                <button id="btnSavePlan" class="btn btn-dark fw-700" style="border-radius:12px;min-width:130px" onclick="savePlan()">
                    <i class="fa-solid fa-floppy-disk me-2"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Init modal after DOM ready
const planModal = new bootstrap.Modal(document.getElementById('planModal'), { backdrop: true, keyboard: true });

function openModal() {
    document.getElementById('modalTitle').textContent = 'Nuevo plan';
    document.getElementById('p_id').value    = '';
    document.getElementById('p_name').value  = '';
    document.getElementById('p_desc').value  = '';
    document.getElementById('p_price').value = '';
    document.getElementById('p_courts').value = '';
    document.getElementById('p_active').checked = true;
    document.getElementById('activeToggleWrap').style.display = 'none';
    planModal.show();
}

// Edit buttons — read data from card's data-* attributes
document.querySelectorAll('.btn-edit-plan').forEach(btn => {
    btn.addEventListener('click', function() {
        const card = this.closest('[data-plan-id]');
        document.getElementById('modalTitle').textContent = 'Editar plan';
        document.getElementById('p_id').value     = card.dataset.planId;
        document.getElementById('p_name').value   = card.dataset.planName;
        document.getElementById('p_desc').value   = card.dataset.planDesc;
        document.getElementById('p_price').value  = card.dataset.planPrice;
        document.getElementById('p_courts').value = card.dataset.planCourts;
        document.getElementById('p_active').checked = card.dataset.planActive === '1';
        document.getElementById('p_priceid').value  = card.dataset.planPriceid ?? '';
        document.getElementById('activeToggleWrap').style.display = 'block';
        planModal.show();
    });
});

async function savePlan() {
    const btn    = document.getElementById('btnSavePlan');
    const id     = document.getElementById('p_id').value;
    const name   = document.getElementById('p_name').value.trim();
    const price  = document.getElementById('p_price').value;
    const courts = document.getElementById('p_courts').value;

    if (!name || !price || !courts) {
        Toast.fire({ icon: 'warning', title: 'Completá los campos obligatorios.' });
        return;
    }

    const payload = {
        name:        name,
        description: document.getElementById('p_desc').value,
        price:       parseFloat(price),
        court_limit: parseInt(courts),
        onvopay_price_id: document.getElementById('p_priceid').value,
        active:      document.getElementById('p_active').checked ? 1 : 0,
    };

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Guardando...';

    try {
        if (id) {
            await axios.put(`/admin/planes/${id}`, payload);
            Toast.fire({ icon: 'success', title: 'Plan actualizado.' });
        } else {
            await axios.post('/admin/planes', payload);
            Toast.fire({ icon: 'success', title: '¡Plan creado!' });
        }
        planModal.hide();
        setTimeout(() => location.reload(), 1000);
    } catch(e) {
        Toast.fire({ icon: 'error', title: e.response?.data?.message || 'Error al guardar.' });
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-floppy-disk me-2"></i>Guardar';
    }
}

// Toggle
document.querySelectorAll('.btn-toggle-plan').forEach(btn => {
    btn.addEventListener('click', async function() {
        const id = this.dataset.id;
        try {
            const res = await axios.patch(`/admin/planes/${id}/toggle`);
            Toast.fire({ icon: 'success', title: res.data.message });
            setTimeout(() => location.reload(), 800);
        } catch(e) { Toast.fire({ icon: 'error', title: 'Error.' }); }
    });
});

// Delete
document.querySelectorAll('.btn-delete-plan').forEach(btn => {
    btn.addEventListener('click', async function() {
        const id = this.dataset.id;
        const { isConfirmed } = await Swal.fire({
            title: '¿Eliminar este plan?',
            text: 'Solo si no tiene suscripciones activas.',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#c62828', confirmButtonText: 'Eliminar', cancelButtonText: 'Cancelar'
        });
        if (!isConfirmed) return;
        try {
            await axios.delete(`/admin/planes/${id}`);
            Toast.fire({ icon: 'success', title: 'Plan eliminado.' });
            setTimeout(() => location.reload(), 800);
        } catch(e) {
            Toast.fire({ icon: 'error', title: e.response?.data?.message || 'Error.' });
        }
    });
});
</script>
@endpush
