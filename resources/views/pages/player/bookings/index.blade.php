@extends('layouts.player')
@section('title', 'Mis Reservas - SuperCancha')
@section('header_title', 'Mis reservas')

@section('content')
<div class="px-3 mt-2">
    @forelse($reservations as $res)
    <div class="card mb-3 border-0 shadow-sm" style="border-radius:20px;overflow:hidden">
        <div class="p-4">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div>
                    <div class="fw-700" style="font-size:16px">{{ $res->court->name }}</div>
                    <div class="text-muted" style="font-size:12px">{{ $res->court->venue->name }}</div>
                </div>
                {!! $res->status_badge !!}
            </div>
            <div class="d-flex gap-3 text-muted mb-3" style="font-size:13px">
                <span><i class="fa-regular fa-calendar me-1"></i>{{ $res->reservation_date->format('d/m/Y') }}</span>
                <span><i class="fa-regular fa-clock me-1"></i>{{ $res->start_time }} - {{ $res->end_time }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <div class="fw-800" style="font-size:18px">₡{{ number_format($res->total_price, 0, ',', '.') }}</div>

                @if($res->payment_status === 'pending' && $res->status !== 'cancelled')
                    <button class="btn btn-sm btn-dark" style="border-radius:12px;font-size:12px;font-weight:700"
                            onclick="openProofUpload('{{ $res->id }}')">
                        <i class="fa-solid fa-upload me-1"></i> Subir comprobante
                    </button>
                @elseif($res->payment_status === 'verified')
                    <span style="font-size:12px;font-weight:600;color:#2e7d32"><i class="fa-solid fa-circle-check me-1"></i>Pago verificado</span>
                @elseif($res->payment_status === 'rejected')
                    <span style="font-size:12px;font-weight:600;color:#c62828"><i class="fa-solid fa-circle-xmark me-1"></i>Pago rechazado</span>
                @endif
            </div>

            @if($res->status === 'pending')
            <button class="btn btn-sm w-100 mt-2" style="border:1.5px solid #fee2e2;color:#c62828;border-radius:12px;font-size:12px;font-weight:600"
                    onclick="cancelReservation('{{ $res->id }}')">
                Cancelar reserva
            </button>
            @endif
        </div>
    </div>
    @empty
        <div class="text-center py-5">
            <div style="font-size:56px;margin-bottom:16px">📅</div>
            <p class="fw-700 mb-1">No tenés reservas aún</p>
            <p class="text-muted" style="font-size:13px">Explorá las canchas disponibles y hacé tu primera reserva.</p>
            <a href="{{ route('player.explore') }}" class="btn btn-dark mt-2" style="border-radius:14px;font-weight:600">Explorar canchas</a>
        </div>
    @endforelse

    {{ $reservations->links() }}
</div>

{{-- Proof upload modal --}}
<div class="modal fade" id="proofModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:24px;border:none">
            <div class="modal-body p-4">
                <h5 class="fw-800 mb-1">Subir comprobante SINPE</h5>
                <p class="text-muted mb-4" style="font-size:13px">Subí la captura o foto del comprobante de transferencia.</p>

                <div id="dropArea" class="text-center py-5 mb-3" style="border:2px dashed #e0e0e0;border-radius:16px;cursor:pointer">
                    <i class="fa-solid fa-cloud-arrow-up fa-2x text-muted mb-2 d-block"></i>
                    <span class="text-muted" style="font-size:13px">Tocá para seleccionar imagen</span>
                    <input type="file" id="proofFile" accept="image/*,.pdf" class="d-none">
                </div>
                <div id="proofPreview" class="d-none mb-3"></div>

                <button id="btnUploadProof" class="btn btn-dark w-100 py-3" style="border-radius:14px;font-weight:700" disabled>
                    Enviar comprobante
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let currentReservationId = null;
const proofModal = new bootstrap.Modal(document.getElementById('proofModal'));

document.getElementById('dropArea').addEventListener('click', () => {
    document.getElementById('proofFile').click();
});

document.getElementById('proofFile').addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('proofPreview').innerHTML =
            `<img src="${e.target.result}" class="w-100 rounded-3" style="max-height:200px;object-fit:contain">`;
        document.getElementById('proofPreview').classList.remove('d-none');
    };
    reader.readAsDataURL(file);
    document.getElementById('btnUploadProof').disabled = false;
});

function openProofUpload(reservationId) {
    currentReservationId = reservationId;
    document.getElementById('proofFile').value = '';
    document.getElementById('proofPreview').innerHTML = '';
    document.getElementById('proofPreview').classList.add('d-none');
    document.getElementById('btnUploadProof').disabled = true;
    proofModal.show();
}

document.getElementById('btnUploadProof').addEventListener('click', async () => {
    const file = document.getElementById('proofFile').files[0];
    if (!file || !currentReservationId) return;

    const formData = new FormData();
    formData.append('proof', file);
    formData.append('_method', 'POST');

    const btn = document.getElementById('btnUploadProof');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Subiendo...';

    try {
        await axios.post(`/app/reservas/${currentReservationId}/comprobante`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        proofModal.hide();
        Toast.fire({ icon: 'success', title: '¡Comprobante enviado! El dueño lo verificará pronto.' });
        setTimeout(() => location.reload(), 1500);
    } catch(e) {
        Toast.fire({ icon: 'error', title: e.response?.data?.message || 'Error al subir.' });
        btn.disabled = false;
        btn.innerHTML = 'Enviar comprobante';
    }
});

async function cancelReservation(id) {
    const { isConfirmed } = await Swal.fire({
        title: '¿Cancelar reserva?', text: 'Esta acción no se puede deshacer.',
        icon: 'warning', showCancelButton: true,
        confirmButtonText: 'Sí, cancelar', cancelButtonText: 'No',
        confirmButtonColor: '#c62828',
    });
    if (!isConfirmed) return;

    try {
        await axios.patch(`/app/reservas/${id}/cancelar`);
        Toast.fire({ icon: 'success', title: 'Reserva cancelada.' });
        setTimeout(() => location.reload(), 1200);
    } catch(e) {
        Toast.fire({ icon: 'error', title: 'No se pudo cancelar.' });
    }
}
</script>
@endpush
