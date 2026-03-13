@extends('layouts.admin')
@section('title', 'Usuarios')
@section('page_title', 'Usuarios')

@section('content')
<div class="stat-card">
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr><th>Usuario</th><th>Rol</th><th>Suscripción</th><th>Registro</th><th>Acciones</th></tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            @if($u->avatar)
                                <img src="{{ $u->avatar }}" class="rounded-circle" width="36" height="36">
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px;height:36px;background:{{ $u->role==='admin' ? '#6C63FF' : ($u->role==='owner' ? '#000' : '#e0e0e0') }};color:{{ in_array($u->role,['admin','owner']) ? '#fff' : '#555' }};font-weight:700;font-size:13px">
                                    {{ strtoupper(substr($u->name,0,1)) }}
                                </div>
                            @endif
                            <div>
                                <div class="fw-600" style="font-size:13px">{{ $u->name }}</div>
                                <div class="text-muted" style="font-size:11px">{{ $u->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge" style="{{ $u->role==='admin' ? 'background:#ede7f6;color:#4527a0' : ($u->role==='owner' ? 'background:#e8eaf6;color:#283593' : 'background:#f5f5f5;color:#555') }};border-radius:20px;font-size:11px;padding:4px 10px">
                            {{ ucfirst($u->role) }}
                        </span>
                    </td>
                    <td>
                        @if($u->subscription)
                            {!! $u->subscription->status_badge !!}
                            <div class="text-muted" style="font-size:11px">{{ $u->subscription->plan?->name }}</div>
                        @else
                            <span class="text-muted" style="font-size:12px">—</span>
                        @endif
                    </td>
                    <td style="font-size:12px;color:#888">{{ $u->created_at->format('d/m/Y') }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <select onchange="changeRole('{{ $u->id }}', this.value)" class="form-select form-select-sm" style="border-radius:8px;font-size:11px;max-width:100px">
                                @foreach(['user','owner','admin'] as $role)
                                <option value="{{ $role }}" {{ $u->role===$role?'selected':'' }}>{{ ucfirst($role) }}</option>
                                @endforeach
                            </select>
                            @if($u->id !== auth()->id())
                            <button onclick="deleteUser('{{ $u->id }}')" class="btn btn-sm" style="background:#ffebee;color:#c62828;border-radius:8px;font-size:11px">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
</div>
@endsection

@push('scripts')
<script>
async function changeRole(id, role) {
    try {
        await axios.put(`/admin/usuarios/${id}`, { role, name: '' });
        Toast.fire({ icon:'success', title:'Rol actualizado.' });
    } catch(e) { Toast.fire({ icon:'error', title:'Error.' }); }
}

async function deleteUser(id) {
    const { isConfirmed } = await Swal.fire({ title:'¿Eliminar usuario?', icon:'warning', showCancelButton:true, confirmButtonColor:'#c62828', confirmButtonText:'Eliminar' });
    if (!isConfirmed) return;
    try {
        await axios.delete(`/admin/usuarios/${id}`);
        Toast.fire({ icon:'success', title:'Usuario eliminado.' });
        setTimeout(() => location.reload(), 1000);
    } catch(e) { Toast.fire({ icon:'error', title: e.response?.data?.message || 'Error.' }); }
}
</script>
@endpush
