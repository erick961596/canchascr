@extends('layouts.player')
@section('title', $court->name . ' - SuperCancha')

@section('content')
<div>
    {{-- Court image --}}
    @if($court->images && count($court->images))
        <div class="position-relative">
            <img src="{{ \Storage::disk('s3')->url($court->images[0]) }}" class="w-100" style="height:260px;object-fit:cover">
            <a href="{{ route('player.venue', $venue->slug) }}" class="position-absolute top-0 start-0 m-3 btn btn-sm bg-white rounded-circle shadow-sm" style="width:38px;height:38px;display:flex;align-items:center;justify-content:center">
                <i class="fa-solid fa-arrow-left" style="font-size:14px"></i>
            </a>
        </div>
    @else
        <div class="position-relative" style="height:200px;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center">
            <i class="fa-solid fa-futbol text-white" style="font-size:48px;opacity:.5"></i>
            <a href="{{ route('player.venue', $venue->slug) }}" class="position-absolute top-0 start-0 m-3 btn btn-sm bg-white rounded-circle shadow-sm" style="width:38px;height:38px;display:flex;align-items:center;justify-content:center">
                <i class="fa-solid fa-arrow-left" style="font-size:14px"></i>
            </a>
        </div>
    @endif

    <div class="px-4 pt-4">
        <div class="d-flex align-items-start justify-content-between mb-2">
            <div>
                <h1 class="fw-800 mb-1" style="font-size:22px">{{ $court->name }}</h1>
                <div class="text-muted" style="font-size:13px">{{ $venue->name }} · {{ $venue->canton }}</div>
            </div>
            <div class="text-end">
                <div class="fw-800" style="font-size:20px">₡{{ number_format($court->price_per_hour, 0, ',', '.') }}</div>
                <div class="text-muted" style="font-size:11px">por hora</div>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap mb-4">
            <span class="badge" style="background:#111;color:#fff;border-radius:20px;padding:6px 14px">{{ \App\Models\Court::sportLabel($court->sport) }}</span>
            <span class="badge" style="background:#f0f0f0;color:#333;border-radius:20px;padding:6px 14px">{{ $court->slot_duration }} min/slot</span>
            @foreach(($court->features ?? []) as $feature)
                <span class="badge" style="background:#f0f0f0;color:#555;border-radius:20px;padding:6px 14px;font-size:11px">{{ $feature }}</span>
            @endforeach
        </div>

        {{-- Date selector --}}
        <div class="mb-4">
            <label class="fw-700 mb-2" style="font-size:14px">Elegí la fecha</label>
            <input type="date" id="dateInput" class="form-control" style="border-radius:14px;border:1.5px solid #e0e0e0;padding:12px;font-weight:600"
                   value="{{ date('Y-m-d') }}" min="{{ date('Y-m-d') }}" max="{{ date('Y-m-d', strtotime('+30 days')) }}">
        </div>

        {{-- Slots --}}
        <div class="mb-4">
            <label class="fw-700 mb-2" style="font-size:14px">Horarios disponibles</label>
            <div id="slotsContainer" class="d-flex flex-wrap gap-2">
                <span class="text-muted" style="font-size:13px">Seleccioná una fecha para ver horarios.</span>
            </div>
        </div>

        {{-- Active promotions --}}
        @if($promotions->count())
        <div class="mb-4">
            <label class="fw-700 mb-2" style="font-size:14px">🏷 Promociones activas</label>
            @foreach($promotions as $promo)
            <div class="d-flex align-items-center justify-content-between p-3 rounded-3 mb-2"
                 style="background:#fff8e1;border:1.5px solid #ffe082">
                <div>
                    <div class="fw-700" style="font-size:13px">{{ $promo->name }}</div>
                    @if($promo->description)
                        <div class="text-muted" style="font-size:12px">{{ $promo->description }}</div>
                    @endif
                    <div style="font-size:11px;color:#888">Válida hasta {{ $promo->ends_at->format('d/m/Y') }}</div>
                </div>
                <span class="fw-800" style="font-size:15px;color:#e65100;white-space:nowrap;margin-left:12px">{{ $promo->display_label }}</span>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Extra services --}}
        @if($extraServices->count())
        <div class="mb-4">
            <label class="fw-700 mb-2" style="font-size:14px">🛎 Servicios adicionales</label>
            <div id="extraServicesContainer" class="d-flex flex-column gap-2">
                @foreach($extraServices as $svc)
                <div class="d-flex align-items-center justify-content-between p-3 rounded-3" style="border:1.5px solid #f0f0f0">
                    <div>
                        <div class="fw-600" style="font-size:13px">{{ $svc->name }}</div>
                        @if($svc->description)
                            <div class="text-muted" style="font-size:12px">{{ $svc->description }}</div>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="fw-700" style="font-size:13px">₡{{ number_format($svc->price,0,',','.') }}</span>
                        <div class="d-flex align-items-center gap-1">
                            <button type="button" class="btn btn-sm" style="border:1.5px solid #e0e0e0;border-radius:8px;width:28px;height:28px;padding:0;display:flex;align-items:center;justify-content:center"
                                    onclick="adjustQty('{{ $svc->id }}', -1)">–</button>
                            <span id="qty_{{ $svc->id }}" style="min-width:24px;text-align:center;font-weight:700;font-size:13px">0</span>
                            <button type="button" class="btn btn-sm" style="border:1.5px solid #e0e0e0;border-radius:8px;width:28px;height:28px;padding:0;display:flex;align-items:center;justify-content:center"
                                    onclick="adjustQty('{{ $svc->id }}', 1)">+</button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Reserve button --}}
        <div id="reserveSection" class="d-none">
            <div class="bg-white rounded-4 p-4 shadow-sm mb-4" style="border:1.5px solid #f0f0f0">
                <div class="d-flex justify-content-between mb-3">
                    <span class="fw-600" style="font-size:14px">Resumen</span>
                </div>
                <div class="d-flex justify-content-between text-muted mb-1" style="font-size:13px">
                    <span>Fecha:</span><span id="summaryDate" class="fw-600 text-dark"></span>
                </div>
                <div class="d-flex justify-content-between text-muted mb-1" style="font-size:13px">
                    <span>Horario:</span><span id="summaryTime" class="fw-600 text-dark"></span>
                </div>
                <div class="d-flex justify-content-between text-muted mb-1" style="font-size:13px">
                    <span>Subtotal:</span><span id="summaryBasePrice" class="fw-600 text-dark"></span>
                </div>
                <div id="summaryDiscountRow" class="d-none d-flex justify-content-between text-muted mb-1" style="font-size:13px">
                    <span id="summaryDiscountLabel" style="color:#e65100">Descuento:</span>
                    <span id="summaryDiscount" class="fw-600" style="color:#e65100"></span>
                </div>
                <div id="summaryExtrasRow" class="d-none d-flex justify-content-between text-muted mb-1" style="font-size:13px">
                    <span>Extras:</span><span id="summaryExtras" class="fw-600 text-dark"></span>
                </div>
                <hr style="margin:8px 0;border-color:#f0f0f0">
                <div class="d-flex justify-content-between text-muted" style="font-size:13px">
                    <span>Total:</span><span id="summaryPrice" class="fw-700 text-dark" style="font-size:16px"></span>
                </div>
            </div>
            <textarea id="reserveNotes" class="form-control mb-3" placeholder="Notas adicionales (opcional)" style="border-radius:14px;border:1.5px solid #e0e0e0;font-size:14px;resize:none" rows="2"></textarea>
            <button id="btnReserve" class="btn-black btn w-100 py-3 mb-5" style="font-size:15px;border-radius:16px">
                Reservar esta cancha
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const COURT_ID   = '{{ $court->id }}';
const SLOTS_URL  = '{{ route("player.slots", $court->id) }}';
const RESERVE_URL = '{{ route("player.bookings.store") }}';

const PROMOTIONS = @json($promotions);
const EXTRA_SERVICES = @json($extraServices);
const extraPrices = {};
EXTRA_SERVICES.forEach(s => extraPrices[s.id] = parseFloat(s.price));

let selectedSlot = null;
let activePromoId = null;
let discountAmount = 0;

function adjustQty(id, delta) {
    const el = document.getElementById('qty_' + id);
    if (!el) return;
    const cur = parseInt(el.textContent) || 0;
    const next = Math.max(0, cur + delta);
    el.textContent = next;
    updateSummaryTotals();
}

function getExtrasTotal() {
    let total = 0;
    EXTRA_SERVICES.forEach(s => {
        const qty = parseInt(document.getElementById('qty_' + s.id)?.textContent || 0);
        total += s.price * qty;
    });
    return total;
}

function updateSummaryTotals() {
    if (!selectedSlot) return;
    const base   = selectedSlot.price;
    const extras = getExtrasTotal();
    const total  = base - discountAmount + extras;

    document.getElementById('summaryBasePrice').textContent = '₡' + Number(base).toLocaleString('es-CR',{maximumFractionDigits:0});
    document.getElementById('summaryPrice').textContent     = '₡' + Number(total).toLocaleString('es-CR',{maximumFractionDigits:0});

    const discRow = document.getElementById('summaryDiscountRow');
    if (discountAmount > 0) {
        discRow.classList.remove('d-none');
        document.getElementById('summaryDiscount').textContent = '-₡' + Number(discountAmount).toLocaleString('es-CR',{maximumFractionDigits:0});
    } else {
        discRow.classList.add('d-none');
    }

    const extRow = document.getElementById('summaryExtrasRow');
    if (extras > 0) {
        extRow.classList.remove('d-none');
        document.getElementById('summaryExtras').textContent = '₡' + Number(extras).toLocaleString('es-CR',{maximumFractionDigits:0});
    } else {
        extRow.classList.add('d-none');
    }
}

document.getElementById('dateInput').addEventListener('change', loadSlots);

async function loadSlots() {
    const date = document.getElementById('dateInput').value;
    const container = document.getElementById('slotsContainer');
    container.innerHTML = '<div class="text-muted" style="font-size:13px">Cargando...</div>';
    selectedSlot = null;
    document.getElementById('reserveSection').classList.add('d-none');

    try {
        const res = await axios.get(SLOTS_URL, { params: { date } });
        const slots = res.data;

        if (!slots.length) {
            container.innerHTML = '<div class="text-muted" style="font-size:13px">No hay horarios configurados para este día.</div>';
            return;
        }

        container.innerHTML = '';
        slots.forEach(slot => {
            const btn = document.createElement('button');
            btn.className = 'slot-btn' + (!slot.available ? ' unavailable' : '');
            btn.textContent = slot.start + ' - ' + slot.end;
            btn.disabled = !slot.available;
            btn.dataset.slot = JSON.stringify(slot);
            if (slot.available) {
                btn.addEventListener('click', () => selectSlot(btn, slot, date));
            }
            container.appendChild(btn);
        });
    } catch(e) {
        container.innerHTML = '<div class="text-danger" style="font-size:13px">Error cargando horarios.</div>';
    }
}

function selectSlot(btn, slot, date) {
    document.querySelectorAll('.slot-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    selectedSlot = { ...slot, date };

    // Find best applicable promo
    activePromoId = null;
    discountAmount = 0;
    const applicable = PROMOTIONS.filter(p => !p.court_ids || p.court_ids.includes(COURT_ID));
    if (applicable.length) {
        const best = applicable.reduce((a, b) => {
            const da = a.type==='percentage' ? slot.price*(a.value/100) : Math.min(a.value, slot.price);
            const db = b.type==='percentage' ? slot.price*(b.value/100) : Math.min(b.value, slot.price);
            return da > db ? a : b;
        });
        discountAmount = best.type==='percentage'
            ? Math.round(slot.price * (best.value/100) * 100) / 100
            : Math.min(parseFloat(best.value), slot.price);
        activePromoId = best.id;
    }

    document.getElementById('summaryDate').textContent = new Date(date + 'T00:00:00').toLocaleDateString('es-CR', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
    document.getElementById('summaryTime').textContent = slot.start + ' – ' + slot.end;
    document.getElementById('reserveSection').classList.remove('d-none');
    document.getElementById('reserveSection').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    updateSummaryTotals();
}

document.getElementById('btnReserve').addEventListener('click', async () => {
    if (!selectedSlot) return;

    const { isConfirmed } = await Swal.fire({
        title: 'Confirmar reserva',
        html: `<p style="font-size:14px">Vas a reservar <b>${selectedSlot.start} - ${selectedSlot.end}</b><br>para el <b>${selectedSlot.date}</b></p>
               <p style="font-size:13px;color:#888">Después vas a subir el comprobante de pago SINPE.</p>`,
        icon: 'question', showCancelButton: true,
        confirmButtonText: 'Sí, reservar', cancelButtonText: 'Cancelar',
        confirmButtonColor: '#000',
    });

    if (!isConfirmed) return;

    try {
        // Build extra services array
        const extraServices = [];
        EXTRA_SERVICES.forEach(s => {
            const qty = parseInt(document.getElementById('qty_' + s.id)?.textContent || 0);
            if (qty > 0) extraServices.push({ id: s.id, quantity: qty });
        });

        const res = await axios.post(RESERVE_URL, {
            court_id:         COURT_ID,
            reservation_date: selectedSlot.date,
            start_time:       selectedSlot.start,
            end_time:         selectedSlot.end,
            notes:            document.getElementById('reserveNotes').value,
            promotion_id:     activePromoId,
            extra_services:   extraServices.length ? extraServices : null,
        });

        await Swal.fire({
            icon: 'success', title: '¡Reserva creada!',
            text: 'Ahora subí el comprobante de pago SINPE para confirmar.',
            confirmButtonColor: '#000',
        });

        window.location.href = '{{ route("player.bookings.index") }}';
    } catch(e) {
        Toast.fire({ icon: 'error', title: e.response?.data?.message || 'Error al reservar.' });
    }
});

// Load today's slots on page load
loadSlots();
</script>
@endpush
