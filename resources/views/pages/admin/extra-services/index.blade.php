@extends('layouts.admin')
@section('title', 'Servicios adicionales')
@section('page_title', 'Servicios adicionales')

@section('content')
<div class="stat-card">
    <div class="table-responsive">
        <table class="table table-modern">
            <thead>
                <tr><th>Servicio</th><th>Sede</th><th>Owner</th><th>Precio</th><th>Estado</th><th>Creado</th></tr>
            </thead>
            <tbody>
                @forelse($services as $svc)
                <tr>
                    <td>
                        <div class="fw-600" style="font-size:13px">{{ $svc->name }}</div>
                        @if($svc->description)
                            <div class="text-muted" style="font-size:11px">{{ $svc->description }}</div>
                        @endif
                    </td>
                    <td style="font-size:13px">{{ $svc->venue->name }}</td>
                    <td style="font-size:13px">{{ $svc->venue->owner->name }}</td>
                    <td class="fw-700" style="font-size:13px">₡{{ number_format($svc->price,0,',','.') }}</td>
                    <td>
                        <span class="badge" style="{{ $svc->active ? 'background:#e8f5e9;color:#2e7d32' : 'background:#fafafa;color:#999' }};border-radius:20px;font-size:10px;padding:3px 10px">
                            {{ $svc->active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </td>
                    <td class="text-muted" style="font-size:12px">{{ $svc->created_at->format('d/m/Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-4">Sin servicios registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $services->links() }}
</div>
@endsection
