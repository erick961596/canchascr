@extends('layouts.owner')
@section('title', 'Reservas')
@section('page_title', 'Gestión de reservas')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="stat-card">
            <div id="calendar"></div>
        </div>
    </div>
</div>

{{-- Filter + Table --}}
<div class="stat-card">
    <form method="GET" class="row g-2 mb-4">
        <div class="col-md-3">
            <select name="status" class="form-select form-select-sm" style="border-radius:10px" onchange="this.form.submit()">
                <option value="">Todos los estados</option>
                <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pendiente</option>
                <option value="confirmed" {{ request('status')=='confirmed'?'selected':'' }}>Confirmada</option>
                <option value="cancelled" {{ request('status')=='cancelled'?'selected':'' }}>Cancelada</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="date" name="date" class="form-control form-control-sm" style="border-radius:10px" value="{{ request('date') }}" onchange="this.form.submit()">
        </div>
        @if(request()->hasAny(['status','date']))
        <div class="col-md-2">
            <a href="{{ route('owner.reservations.index') }}" class="btn btn-sm w-100" style="border:1px solid #e0e0e0;border-radius:10px">Limpiar</a>
        </div>
        @endif
    </form>

    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr><th>Usuario</th><th>Cancha</th><th>Fecha</th><th>Hora</th><th>Total</th><th>Estado pago</th><th>Comprobante</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @forelse($reservations as $r)
                <tr>
                    <td>
                        <div class="fw-600" style="font-size:13px">{{ $r->user->name }}</div>
                        <div class="text-muted" style="font-size:11px">{{ $r->user->phone ?? $r->user->email }}</div>
                    </td>
                    <td style="font-size:13px">{{ $r->court->name }}<br><span class="text-muted" style="font-size:11px">{{ $r->court->venue->name }}</span></td>
                    <td style="font-size:13px">{{ $r->reservation_date->format('d/m/Y') }}</td>
                    <td style="font-size:12px">{{ $r->start_time }} - {{ $r->end_time }}</td>
                    <td class="fw-700" style="font-size:13px">₡{{ number_format($r->total_price,0,',','.') }}</td>
                    <td>
                        <span class="badge" style="{{ $r->payment_status==='verified' ? 'background:#e8f5e9;color:#2e7d32' : ($r->payment_status==='rejected' ? 'background:#ffebee;color:#c62828' : 'background:#fff3e0;color:#e65100') }};border-radius:20px;font-size:10px;padding:3px 10px">
                            {{ match($r->payment_status){'verified'=>'Verificado','rejected'=>'Rechazado',default=>'Pendiente'} }}
                        </span>
                    </td>
                    <td>
                        @if($r->payment_proof)
                            <a href="{{ $r->proof_url }}" target="_blank" class="btn btn-sm" style="background:#f0f0f0;border-radius:8px;font-size:11px;padding:4px 10px">
                                <i class="fa-solid fa-eye me-1"></i>Ver
                            </a>
                        @else
                            <span class="text-muted" style="font-size:12px">—</span>
                        @endif
                    </td>
                    <td>
                        @if($r->status === 'pending')
                        <div class="d-flex gap-1">
                            <button onclick="confirmRes('{{ $r->id }}')" class="btn btn-sm" style="background:#e8f5e9;color:#2e7d32;border-radius:8px;font-size:11px;padding:4px 10px;font-weight:600">✓</button>
                            <button onclick="rejectRes('{{ $r->id }}')" class="btn btn-sm" style="background:#ffebee;color:#c62828;border-radius:8px;font-size:11px;padding:4px 10px;font-weight:600">✗</button>
                        </div>
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
@endsection

@push('scripts')
<script>
const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
    initialView: 'dayGridMonth',
    locale: 'es',
    headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek' },
    height: 500,
    events: async (info, success) => {
        const res = await axios.get('/owner/reservas/calendario-data', {
            params: { start: info.startStr, end: info.endStr }
        });
        success(res.data);
    },
    eventClick: info => {
        const p = info.event.extendedProps;
        Swal.fire({
            title: info.event.title,
            html: `<p style="font-size:13px">Estado: <b>${p.status}</b><br>Pago: <b>${p.payment_status}</b><br>Usuario: <b>${p.user}</b></p>
                   ${p.proof_url ? '<a href="'+p.proof_url+'" target="_blank" class="btn btn-sm btn-dark rounded-3">Ver comprobante</a>' : ''}`,
            showCloseButton: true, showConfirmButton: false,
        });
    }
});
calendar.render();

async function confirmRes(id) {
    const { isConfirmed } = await Swal.fire({ title:'¿Confirmar reserva?', icon:'question', showCancelButton:true, confirmButtonColor:'#000', confirmButtonText:'Confirmar' });
    if (!isConfirmed) return;
    await axios.patch(`/owner/reservas/${id}/confirmar`);
    Toast.fire({ icon:'success', title:'Confirmada.' });
    setTimeout(() => location.reload(), 1000);
}

async function rejectRes(id) {
    const { isConfirmed } = await Swal.fire({ title:'¿Rechazar reserva?', icon:'warning', showCancelButton:true, confirmButtonColor:'#c62828', confirmButtonText:'Rechazar' });
    if (!isConfirmed) return;
    await axios.patch(`/owner/reservas/${id}/rechazar`);
    Toast.fire({ icon:'success', title:'Rechazada.' });
    setTimeout(() => location.reload(), 1000);
}
</script>
@endpush
