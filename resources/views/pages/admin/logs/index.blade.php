@extends('layouts.admin')
@section('title', 'Logs del sistema')
@section('page_title', 'Logs del sistema')

@section('content')
{{-- Level summary chips --}}
<div class="d-flex flex-wrap gap-2 mb-4">
    @php
    $levels = [
        'info'         => ['label'=>'Info',         'bg'=>'#f5f5f5',  'color'=>'#555'],
        'auth'         => ['label'=>'Auth',         'bg'=>'#f3e5f5',  'color'=>'#6a1b9a'],
        'payment'      => ['label'=>'Pagos',        'bg'=>'#e8f5e9',  'color'=>'#2e7d32'],
        'subscription' => ['label'=>'Suscripciones','bg'=>'#e8eaf6',  'color'=>'#283593'],
        'warning'      => ['label'=>'Warnings',     'bg'=>'#fff3e0',  'color'=>'#e65100'],
        'error'        => ['label'=>'Errores',      'bg'=>'#ffebee',  'color'=>'#c62828'],
    ];
    @endphp
    <a href="{{ route('admin.logs.index') }}"
       class="text-decoration-none d-flex align-items-center gap-2 px-3 py-2 rounded-3 fw-600"
       style="background:{{ request('level') ? '#f5f5f5' : '#111' }};color:{{ request('level') ? '#555' : '#fff' }};font-size:13px">
        Todos <span style="opacity:.7">{{ $counts->sum() }}</span>
    </a>
    @foreach($levels as $key => $lv)
    <a href="{{ route('admin.logs.index', ['level'=>$key]) }}"
       class="text-decoration-none d-flex align-items-center gap-2 px-3 py-2 rounded-3 fw-600"
       style="background:{{ request('level')===$key ? '#111' : $lv['bg'] }};color:{{ request('level')===$key ? '#fff' : $lv['color'] }};font-size:13px">
        {{ $lv['label'] }} <span style="opacity:.7">{{ $counts[$key] ?? 0 }}</span>
    </a>
    @endforeach
</div>

{{-- Filters --}}
<div class="stat-card mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <input type="hidden" name="level" value="{{ request('level') }}">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" style="border-radius:12px"
                   placeholder="Buscar en descripción..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <input type="text" name="type" class="form-control" style="border-radius:12px"
                   placeholder="Tipo (ej: login_failed)" value="{{ request('type') }}">
        </div>
        <div class="col-md-2">
            <input type="date" name="from" class="form-control" style="border-radius:12px" value="{{ request('from') }}">
        </div>
        <div class="col-md-2">
            <input type="date" name="to" class="form-control" style="border-radius:12px" value="{{ request('to') }}">
        </div>
        <div class="col-md-1">
            <button class="btn btn-dark w-100" style="border-radius:12px"><i class="fa-solid fa-search"></i></button>
        </div>
    </form>
</div>

{{-- Logs table --}}
<div class="stat-card">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <span class="text-muted" style="font-size:13px">{{ $logs->total() }} registros</span>
        <button onclick="clearOldLogs()" class="btn btn-sm" style="border:1.5px solid #e0e0e0;border-radius:10px;font-size:12px;color:#888">
            <i class="fa-solid fa-trash me-1"></i>Limpiar +30 días
        </button>
    </div>

    <div class="table-responsive">
        <table class="table" style="font-size:13px">
            <thead>
                <tr style="color:#888;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px">
                    <th style="border:none;padding:8px 12px">Fecha</th>
                    <th style="border:none;padding:8px 12px">Nivel</th>
                    <th style="border:none;padding:8px 12px">Tipo</th>
                    <th style="border:none;padding:8px 12px">Descripción</th>
                    <th style="border:none;padding:8px 12px">Usuario</th>
                    <th style="border:none;padding:8px 12px">IP</th>
                    <th style="border:none;padding:8px 12px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr style="border-bottom:1px solid #f5f5f5">
                    <td style="border:none;padding:10px 12px;white-space:nowrap;color:#888">
                        {{ $log->created_at->format('d/m H:i:s') }}
                    </td>
                    <td style="border:none;padding:10px 12px">
                        <span class="badge" style="{{ $log->level_badge }};border-radius:20px;font-size:10px;padding:3px 9px;font-weight:700">
                            {{ strtoupper($log->level) }}
                        </span>
                    </td>
                    <td style="border:none;padding:10px 12px">
                        <code style="background:#f5f5f5;padding:2px 6px;border-radius:4px;font-size:11px">{{ $log->type }}</code>
                    </td>
                    <td style="border:none;padding:10px 12px;max-width:300px">
                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:block">{{ $log->subject }}</span>
                    </td>
                    <td style="border:none;padding:10px 12px;white-space:nowrap">
                        {{ $log->user?->name ?? '<span class="text-muted">—</span>' }}
                    </td>
                    <td style="border:none;padding:10px 12px;color:#aaa;font-size:11px">{{ $log->ip }}</td>
                    <td style="border:none;padding:10px 12px">
                        @if($log->context)
                        <button onclick="showContext('{{ $log->id }}')"
                                class="btn btn-sm" style="border:1.5px solid #e0e0e0;border-radius:8px;font-size:11px;padding:2px 8px">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-5">No hay logs con estos filtros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links() }}
</div>

{{-- Context modal --}}
<div class="modal fade" id="contextModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:20px;border:none">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <div>
                    <h6 class="fw-700 mb-0" id="ctx_subject"></h6>
                    <div class="text-muted" style="font-size:12px" id="ctx_meta"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <pre id="ctx_json" class="p-3 rounded-3" style="background:#f9f9f9;font-size:12px;overflow:auto;max-height:400px;white-space:pre-wrap"></pre>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const ctxModal = new bootstrap.Modal(document.getElementById('contextModal'));

async function showContext(id) {
    try {
        const res = await axios.get(`/admin/logs/${id}`);
        const d   = res.data;
        document.getElementById('ctx_subject').textContent = d.subject;
        document.getElementById('ctx_meta').textContent    =
            `${d.level.toUpperCase()} · ${d.type} · ${d.created_at} · IP: ${d.ip}`;
        document.getElementById('ctx_json').textContent =
            JSON.stringify(d.context, null, 2);
        ctxModal.show();
    } catch(e) { Toast.fire({ icon:'error', title:'Error cargando detalle.' }); }
}

async function clearOldLogs() {
    const { isConfirmed } = await Swal.fire({
        title: '¿Limpiar logs de más de 30 días?',
        icon: 'warning', showCancelButton: true,
        confirmButtonColor: '#c62828', confirmButtonText: 'Limpiar',
        cancelButtonText: 'Cancelar'
    });
    if (!isConfirmed) return;
    try {
        const res = await axios.delete('/admin/logs/clear');
        Toast.fire({ icon: 'success', title: res.data.message });
        setTimeout(() => location.reload(), 1000);
    } catch(e) { Toast.fire({ icon:'error', title:'Error.' }); }
}
</script>
@endpush
