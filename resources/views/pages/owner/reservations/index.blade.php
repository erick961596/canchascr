@extends('layouts.owner')
@section('title', 'Reservas')
@section('page_title', 'Gestión de reservas')

@section('content')

{{-- Top bar --}}
<div class="d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex gap-2 flex-wrap">
        <select id="calCourtFilter" class="form-select form-select-sm" style="border-radius:10px;max-width:180px" onchange="reloadCalendar()">
            <option value="">Todas las canchas</option>
            @foreach($venues as $venue)
                @foreach($venue->courts as $court)
                <option value="{{ $court->id }}">{{ $court->name }} ({{ $venue->name }})</option>
                @endforeach
            @endforeach
        </select>
    </div>
    <button class="btn btn-dark" style="border-radius:12px;font-weight:600;font-size:14px" onclick="openManualModal()">
        <i class="fa-solid fa-plus me-2"></i>Reserva manual
    </button>
</div>

{{-- Calendar --}}
<div class="stat-card mb-4" style="padding:20px">
    <div id="calendar"></div>
</div>

{{-- Table --}}
<div class="stat-card">
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-3">
            <select name="status" class="form-select form-select-sm" style="border-radius:10px" onchange="this.form.submit()">
                <option value="">Todos los estados</option>
                <option value="pending"   {{ request('status')=='pending'   ?'selected':'' }}>Pendiente</option>
                <option value="confirmed" {{ request('status')=='confirmed' ?'selected':'' }}>Confirmada</option>
                <option value="cancelled" {{ request('status')=='cancelled' ?'selected':'' }}>Cancelada</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="date" name="date" class="form-control form-control-sm" style="border-radius:10px"
                   value="{{ request('date') }}" onchange="this.form.submit()">
        </div>
        <div class="col-md-3">
            <select name="court" class="form-select form-select-sm" style="border-radius:10px" onchange="this.form.submit()">
                <option value="">Todas las canchas</option>
                @foreach($venues as $venue)
                    @foreach($venue->courts as $court)
                    <option value="{{ $court->id }}" {{ request('court')==$court->id?'selected':'' }}>{{ $court->name }}</option>
                    @endforeach
                @endforeach
            </select>
        </div>
        @if(request()->hasAny(['status','date','court']))
        <div class="col-md-2">
            <a href="{{ route('owner.reservations.index') }}" class="btn btn-sm w-100" style="border:1px solid #e0e0e0;border-radius:10px">Limpiar</a>
        </div>
        @endif
    </form>

    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Cancha</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Total</th>
                    <th>Estado pago</th>
                    <th>Tipo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservations as $r)
                <tr>
                    <td>
                        <div class="fw-600" style="font-size:13px">
                            {{ $r->is_manual ? ($r->client_name ?? '—') : $r->user->name }}
                        </div>
                        <div class="text-muted" style="font-size:11px">
                            {{ $r->is_manual ? ($r->client_phone ?? '') : ($r->user->phone ?? $r->user->email) }}
                        </div>
                    </td>
                    <td style="font-size:13px">
                        {{ $r->court->name }}<br>
                        <span class="text-muted" style="font-size:11px">{{ $r->court->venue->name }}</span>
                    </td>
                    <td style="font-size:13px">{{ $r->reservation_date->format('d/m/Y') }}</td>
                    <td style="font-size:12px">{{ $r->start_time }} – {{ $r->end_time }}</td>
                    <td class="fw-700" style="font-size:13px">
                        ₡{{ number_format($r->total_price,0,',','.') }}
                        @if($r->discount_amount > 0)
                            <div class="text-muted" style="font-size:10px">-₡{{ number_format($r->discount_amount,0,',','.') }}</div>
                        @endif
                    </td>
                    <td>
                        <span class="badge" style="{{ $r->payment_status==='verified' ? 'background:#e8f5e9;color:#2e7d32' : ($r->payment_status==='rejected' ? 'background:#ffebee;color:#c62828' : 'background:#fff3e0;color:#e65100') }};border-radius:20px;font-size:10px;padding:3px 10px">
                            {{ match($r->payment_status){'verified'=>'Verificado','rejected'=>'Rechazado',default=>'Pendiente'} }}
                        </span>
                    </td>
                    <td>
                        @if($r->is_manual)
                            <span class="badge" style="background:#e3f2fd;color:#1565c0;border-radius:20px;font-size:10px;padding:3px 10px">Manual</span>
                        @else
                            <span class="badge" style="background:#f3e5f5;color:#7b1fa2;border-radius:20px;font-size:10px;padding:3px 10px">Online</span>
                        @endif
                    </td>
                    <td>
                        @if($r->status === 'pending' && !$r->is_manual)
                        <div class="d-flex gap-1">
                            <button onclick="confirmRes('{{ $r->id }}')" class="btn btn-sm" style="background:#e8f5e9;color:#2e7d32;border-radius:8px;font-size:11px;padding:4px 10px;font-weight:600">✓</button>
                            <button onclick="rejectRes('{{ $r->id }}')"  class="btn btn-sm" style="background:#ffebee;color:#c62828;border-radius:8px;font-size:11px;padding:4px 10px;font-weight:600">✗</button>
                        </div>
                        @elseif($r->is_manual)
                        <button onclick="openEditModal('{{ $r->id }}')" class="btn btn-sm" style="border:1.5px solid #e0e0e0;border-radius:8px;font-size:11px;padding:4px 10px">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        @else
                            {!! $r->status_badge !!}
                        @endif
                    </td>
                </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4" style="font-size:13px">No hay reservas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $reservations->links() }}
</div>

{{-- ============ MODAL: RESERVA MANUAL ============ --}}
<div class="modal fade" id="manualModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:24px;border:none">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="fw-800"><i class="fa-solid fa-phone me-2" style="color:#888;font-size:16px"></i>Reserva manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:13px">Cancha *</label>
                        <select id="m_court" class="form-select" style="border-radius:12px" onchange="loadManualSlots()">
                            @foreach($venues as $venue)
                                @foreach($venue->courts as $court)
                                <option value="{{ $court->id }}" data-price="{{ $court->price_per_hour }}">{{ $venue->name }} — {{ $court->name }}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-600" style="font-size:13px">Fecha *</label>
                        <input type="date" id="m_date" class="form-control" style="border-radius:12px"
                               value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" onchange="loadManualSlots()">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-600" style="font-size:13px">Desde *</label>
                        <input type="time" id="m_start" class="form-control" style="border-radius:12px">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-600" style="font-size:13px">Hasta *</label>
                        <input type="time" id="m_end" class="form-control" style="border-radius:12px">
                    </div>

                    {{-- Slots disponibles --}}
                    <div class="col-12">
                        <div class="fw-600 mb-2" style="font-size:12px;color:#888">SLOTS DISPONIBLES</div>
                        <div id="m_slots" class="d-flex flex-wrap gap-2">
                            <span class="text-muted" style="font-size:12px">Elegí la fecha y cancha</span>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <label class="form-label fw-600" style="font-size:13px">Nombre del cliente *</label>
                        <input type="text" id="m_client_name" class="form-control" style="border-radius:12px" placeholder="Juan Pérez">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-600" style="font-size:13px">Teléfono</label>
                        <input type="text" id="m_client_phone" class="form-control" style="border-radius:12px" placeholder="8888-8888">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:13px">Estado de pago *</label>
                        <select id="m_payment_status" class="form-select" style="border-radius:12px">
                            <option value="verified">Pagado ✓</option>
                            <option value="pending">Pendiente de cobro</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:13px">Notas internas</label>
                        <input type="text" id="m_notes" class="form-control" style="border-radius:12px" placeholder="Pagó en efectivo, grupo de 8 personas...">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 flex-column align-items-stretch gap-2">
                <div id="m_summary" class="d-none p-3 rounded-3" style="background:#f8f8f8;font-size:13px">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total estimado</span>
                        <span class="fw-700" id="m_total">—</span>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn flex-grow-1" style="border-radius:12px;border:1.5px solid #e0e0e0" data-bs-dismiss="modal">Cancelar</button>
                    <button id="btnSaveManual" class="btn btn-dark flex-grow-1" style="border-radius:12px;font-weight:700" onclick="saveManual()">Crear reserva</button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============ MODAL: EDITAR RESERVA ============ --}}
<div class="modal fade" id="editResModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:24px;border:none">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="fw-800">Editar reserva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4">
                <input type="hidden" id="er_id">
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label fw-600" style="font-size:13px">Nombre del cliente</label>
                        <input type="text" id="er_client_name" class="form-control" style="border-radius:12px">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-600" style="font-size:13px">Teléfono</label>
                        <input type="text" id="er_client_phone" class="form-control" style="border-radius:12px">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:13px">Estado</label>
                        <select id="er_status" class="form-select" style="border-radius:12px">
                            <option value="confirmed">Confirmada</option>
                            <option value="cancelled">Cancelada</option>
                            <option value="no_show">No se presentó</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-600" style="font-size:13px">Pago</label>
                        <select id="er_payment_status" class="form-select" style="border-radius:12px">
                            <option value="verified">Verificado</option>
                            <option value="pending">Pendiente</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-600" style="font-size:13px">Notas</label>
                        <input type="text" id="er_notes" class="form-control" style="border-radius:12px">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button class="btn" style="border-radius:12px;border:1.5px solid #e0e0e0" data-bs-dismiss="modal">Cancelar</button>
                <button id="btnUpdateRes" class="btn btn-dark" style="border-radius:12px;font-weight:700" onclick="updateReservation()">Guardar cambios</button>
            </div>
        </div>
    </div>
</div>

{{-- ============ MODAL: VER DETALLE DESDE CALENDARIO ============ --}}
<div class="modal fade" id="calDetailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:24px;border:none">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="fw-800" id="cal_title">Detalle de reserva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4" id="cal_body"></div>
            <div class="modal-footer border-0 px-4 pb-4" id="cal_footer"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const manualModal    = new bootstrap.Modal(document.getElementById('manualModal'));
const editResModal   = new bootstrap.Modal(document.getElementById('editResModal'));
const calDetailModal = new bootstrap.Modal(document.getElementById('calDetailModal'));

// ─── CALENDAR ───────────────────────────────────────────────────────────────
const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
    initialView: 'timeGridWeek',
    locale: 'es',
    firstDay: 1,
    slotMinTime: '06:00:00',
    slotMaxTime: '23:00:00',
    height: 560,
    headerToolbar: {
        left:   'prev,next today',
        center: 'title',
        right:  'dayGridMonth,timeGridWeek,timeGridDay',
    },
    buttonText: { today:'Hoy', month:'Mes', week:'Semana', day:'Día' },
    eventColor: '#111',
    eventBorderColor: 'transparent',
    eventTextColor: '#fff',
    slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
    events: async (info, success) => {
        const res = await axios.get('/owner/reservas/calendario-data', {
            params: {
                start: info.startStr,
                end:   info.endStr,
                court: document.getElementById('calCourtFilter').value,
            }
        });
        success(res.data);
    },
    eventDidMount: info => {
        const p = info.event.extendedProps;
        info.el.style.borderRadius = '8px';
        info.el.style.fontSize = '11px';
        info.el.style.padding = '2px 4px';
    },
    eventClick: info => {
        const e  = info.event;
        const p  = e.extendedProps;
        const id = e.id;

        document.getElementById('cal_title').textContent = e.title;
        document.getElementById('cal_body').innerHTML = `
            <div class="row g-2" style="font-size:13px">
                <div class="col-6"><span class="text-muted">Cliente</span><br><b>${p.user}</b></div>
                <div class="col-6"><span class="text-muted">Teléfono</span><br><b>${p.phone}</b></div>
                <div class="col-6"><span class="text-muted">Cancha</span><br><b>${p.court}</b></div>
                <div class="col-6"><span class="text-muted">Total</span><br><b>₡${Number(p.total).toLocaleString('es-CR',{maximumFractionDigits:0})}</b></div>
                <div class="col-6"><span class="text-muted">Estado pago</span><br>
                    <span class="badge" style="${p.payment_status==='verified'?'background:#e8f5e9;color:#2e7d32':'background:#fff3e0;color:#e65100'};border-radius:20px;font-size:10px;padding:3px 10px">
                        ${p.payment_status==='verified'?'Verificado':'Pendiente'}
                    </span>
                </div>
                <div class="col-6"><span class="text-muted">Tipo</span><br>
                    <span class="badge" style="${p.is_manual?'background:#e3f2fd;color:#1565c0':'background:#f3e5f5;color:#7b1fa2'};border-radius:20px;font-size:10px;padding:3px 10px">
                        ${p.is_manual?'Manual':'Online'}
                    </span>
                </div>
                ${p.notes ? `<div class="col-12"><span class="text-muted">Notas</span><br>${p.notes}</div>` : ''}
                ${p.proof_url ? `<div class="col-12"><a href="${p.proof_url}" target="_blank" class="btn btn-sm btn-dark rounded-3" style="font-size:12px">Ver comprobante</a></div>` : ''}
            </div>`;

        let footerHtml = `<button class="btn" style="border-radius:12px;border:1.5px solid #e0e0e0" data-bs-dismiss="modal">Cerrar</button>`;

        if (p.is_manual) {
            footerHtml += `<button class="btn btn-dark" style="border-radius:12px;font-weight:600" onclick="calDetailModal.hide();openEditModal('${id}')">Editar</button>`;
        } else if (p.status === 'pending') {
            footerHtml += `
                <button class="btn" style="background:#e8f5e9;color:#2e7d32;border-radius:12px;font-weight:600" onclick="confirmRes('${id}')">✓ Confirmar</button>
                <button class="btn" style="background:#ffebee;color:#c62828;border-radius:12px;font-weight:600" onclick="rejectRes('${id}')">✗ Rechazar</button>`;
        }

        document.getElementById('cal_footer').innerHTML = footerHtml;
        calDetailModal.show();
    },
});
calendar.render();

function reloadCalendar() { calendar.refetchEvents(); }

// ─── MANUAL RESERVATION ────────────────────────────────────────────────────
async function loadManualSlots() {
    const courtId = document.getElementById('m_court').value;
    const date    = document.getElementById('m_date').value;
    if (!courtId || !date) return;

    const container = document.getElementById('m_slots');
    container.innerHTML = '<span class="text-muted" style="font-size:12px">Cargando...</span>';

    try {
        const { data } = await axios.get(`/app/cancha/${courtId}/slots`, { params: { date } });
        if (!data.length) {
            container.innerHTML = '<span class="text-muted" style="font-size:12px">Sin horarios para este día.</span>';
            return;
        }
        container.innerHTML = data.map(s => `
            <button type="button"
                class="slot-btn${!s.available ? ' unavailable' : ''}"
                style="font-size:11px;padding:4px 10px;border-radius:8px;border:1.5px solid ${s.available?'#e0e0e0':'#f0f0f0'};background:${s.available?'#fff':'#f8f8f8'};color:${s.available?'#111':'#bbb'};cursor:${s.available?'pointer':'default'}"
                ${!s.available ? 'disabled' : ''}
                onclick="selectManualSlot('${s.start}','${s.end}', this)">
                ${s.start}–${s.end}
            </button>`).join('');
    } catch(e) {
        container.innerHTML = '<span class="text-danger" style="font-size:12px">Error cargando slots.</span>';
    }
}

function selectManualSlot(start, end, el) {
    document.querySelectorAll('#m_slots .slot-btn').forEach(b => {
        b.style.background = '#fff';
        b.style.borderColor = '#e0e0e0';
        b.style.color = '#111';
    });
    el.style.background = '#111';
    el.style.borderColor = '#111';
    el.style.color = '#fff';

    document.getElementById('m_start').value = start;
    document.getElementById('m_end').value   = end;

    // Calculate price
    const pricePerHour = parseFloat(document.getElementById('m_court').selectedOptions[0].dataset.price || 0);
    const hours = (new Date('2000-01-01T' + end) - new Date('2000-01-01T' + start)) / 3600000;
    const total = pricePerHour * hours;
    document.getElementById('m_total').textContent = '₡' + total.toLocaleString('es-CR', { maximumFractionDigits: 0 });
    document.getElementById('m_summary').classList.remove('d-none');
}

function openManualModal() {
    document.getElementById('m_client_name').value  = '';
    document.getElementById('m_client_phone').value = '';
    document.getElementById('m_notes').value        = '';
    document.getElementById('m_start').value        = '';
    document.getElementById('m_end').value          = '';
    document.getElementById('m_summary').classList.add('d-none');
    document.getElementById('m_date').value         = '{{ date("Y-m-d") }}';
    loadManualSlots();
    manualModal.show();
}

async function saveManual() {
    const btn = document.getElementById('btnSaveManual');
    const payload = {
        court_id:         document.getElementById('m_court').value,
        reservation_date: document.getElementById('m_date').value,
        start_time:       document.getElementById('m_start').value,
        end_time:         document.getElementById('m_end').value,
        client_name:      document.getElementById('m_client_name').value.trim(),
        client_phone:     document.getElementById('m_client_phone').value.trim(),
        payment_status:   document.getElementById('m_payment_status').value,
        notes:            document.getElementById('m_notes').value.trim(),
    };

    if (!payload.start_time || !payload.end_time) {
        Toast.fire({ icon: 'warning', title: 'Seleccioná un horario.' }); return;
    }
    if (!payload.client_name) {
        Toast.fire({ icon: 'warning', title: 'Ingresá el nombre del cliente.' }); return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Guardando...';

    try {
        await axios.post('/owner/reservas/manual', payload);
        Toast.fire({ icon: 'success', title: 'Reserva manual creada.' });
        manualModal.hide();
        setTimeout(() => location.reload(), 1100);
    } catch(e) {
        Toast.fire({ icon: 'error', title: e.response?.data?.message || 'Error.' });
        btn.disabled = false;
        btn.innerHTML = 'Crear reserva';
    }
}

// ─── EDIT RESERVATION ──────────────────────────────────────────────────────
async function openEditModal(id) {
    try {
        const { data } = await axios.get(`/owner/reservas/${id}`);
        document.getElementById('er_id').value             = id;
        document.getElementById('er_client_name').value    = data.client_name  || '';
        document.getElementById('er_client_phone').value   = data.client_phone || '';
        document.getElementById('er_status').value         = data.status;
        document.getElementById('er_payment_status').value = data.payment_status;
        document.getElementById('er_notes').value          = data.notes || '';
        editResModal.show();
    } catch(e) {
        Toast.fire({ icon: 'error', title: 'No se pudo cargar la reserva.' });
    }
}

async function updateReservation() {
    const id  = document.getElementById('er_id').value;
    const btn = document.getElementById('btnUpdateRes');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

    try {
        await axios.patch(`/owner/reservas/${id}`, {
            client_name:    document.getElementById('er_client_name').value,
            client_phone:   document.getElementById('er_client_phone').value,
            status:         document.getElementById('er_status').value,
            payment_status: document.getElementById('er_payment_status').value,
            notes:          document.getElementById('er_notes').value,
        });
        Toast.fire({ icon: 'success', title: 'Reserva actualizada.' });
        editResModal.hide();
        setTimeout(() => location.reload(), 1100);
    } catch(e) {
        Toast.fire({ icon: 'error', title: 'Error.' });
        btn.disabled = false;
        btn.innerHTML = 'Guardar cambios';
    }
}

// ─── CONFIRM / REJECT ──────────────────────────────────────────────────────
async function confirmRes(id) {
    const { isConfirmed } = await Swal.fire({
        title:'¿Confirmar reserva?', icon:'question', showCancelButton:true,
        confirmButtonColor:'#000', confirmButtonText:'Confirmar', cancelButtonText:'Cancelar',
    });
    if (!isConfirmed) return;
    await axios.patch(`/owner/reservas/${id}/confirmar`);
    Toast.fire({ icon:'success', title:'Confirmada.' });
    calDetailModal.hide();
    setTimeout(() => location.reload(), 1000);
}

async function rejectRes(id) {
    const { isConfirmed } = await Swal.fire({
        title:'¿Rechazar reserva?', icon:'warning', showCancelButton:true,
        confirmButtonColor:'#c62828', confirmButtonText:'Rechazar', cancelButtonText:'Cancelar',
    });
    if (!isConfirmed) return;
    await axios.patch(`/owner/reservas/${id}/rechazar`);
    Toast.fire({ icon:'success', title:'Rechazada.' });
    calDetailModal.hide();
    setTimeout(() => location.reload(), 1000);
}
</script>
@endpush
