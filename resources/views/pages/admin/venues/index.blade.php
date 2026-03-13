@extends('layouts.admin')
@section('title', 'Sedes')
@section('page_title', 'Sedes registradas')

@section('content')
{{-- Filters --}}
<div class="stat-card mb-4">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" style="border-radius:12px"
                   placeholder="Buscar por nombre o provincia..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="active" class="form-select" style="border-radius:12px">
                <option value="">Todas</option>
                <option value="1" {{ request('active')==='1'?'selected':'' }}>Activas</option>
                <option value="0" {{ request('active')==='0'?'selected':'' }}>Inactivas</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-dark w-100" style="border-radius:12px;font-weight:600">Filtrar</button>
            <a href="{{ route('admin.venues.index') }}" class="btn w-100" style="border-radius:12px;border:1.5px solid #e0e0e0">Limpiar</a>
        </div>
    </form>
</div>

<div class="stat-card">
    <table class="table table-modern">
        <thead>
            <tr>
                <th>Sede</th>
                <th>Owner</th>
                <th>Ubicación</th>
                <th>Canchas</th>
                <th>Rating</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($venues as $venue)
            <tr>
                <td>
                    <div class="fw-600" style="font-size:13px">{{ $venue->name }}</div>
                    <div class="text-muted" style="font-size:11px">{{ $venue->phone }}</div>
                </td>
                <td>
                    <div style="font-size:13px">{{ $venue->owner->name }}</div>
                    <div class="text-muted" style="font-size:11px">{{ $venue->owner->email }}</div>
                </td>
                <td style="font-size:12px;color:#555">{{ $venue->canton }}, {{ $venue->province }}</td>
                <td>
                    <span class="badge" style="background:#e8eaf6;color:#283593;border-radius:20px;font-size:11px;padding:4px 10px">
                        {{ $venue->courts_count }} canchas
                    </span>
                </td>
                <td>
                    @php $avg = round($venue->ratings()->avg('rating') ?? 0, 1); @endphp
                    <div class="d-flex align-items-center gap-1">
                        <span style="color:#f59e0b;font-size:13px">★</span>
                        <span class="fw-600" style="font-size:13px">{{ $avg > 0 ? $avg : '—' }}</span>
                        <span class="text-muted" style="font-size:11px">({{ $venue->ratings_count }})</span>
                    </div>
                </td>
                <td>
                    <span class="badge {{ $venue->active ? 'badge-active' : 'badge-inactive' }}">
                        {{ $venue->active ? 'Activa' : 'Inactiva' }}
                    </span>
                </td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="{{ route('admin.venues.show', $venue) }}" class="btn btn-sm" style="border:1.5px solid #e0e0e0;border-radius:8px;font-size:11px;font-weight:600">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        <button onclick="toggleVenue('{{ $venue->id }}', this)"
                                class="btn btn-sm" style="border:1.5px solid #e0e0e0;border-radius:8px;font-size:11px"
                                title="{{ $venue->active ? 'Desactivar' : 'Activar' }}">
                            <i class="fa-solid {{ $venue->active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center text-muted py-4">No hay sedes registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
    {{ $venues->links() }}
</div>
@endsection

@push('scripts')
<script>
async function toggleVenue(id, btn) {
    try {
        const res = await axios.patch(`/admin/venues/${id}/toggle`);
        Toast.fire({ icon: 'success', title: res.data.message });
        setTimeout(() => location.reload(), 800);
    } catch(e) { Toast.fire({ icon: 'error', title: 'Error.' }); }
}
</script>
@endpush
