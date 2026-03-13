<div class="px-4 pt-4 pb-2 d-flex align-items-center justify-content-between">
    <div>
        <span class="text-muted fw-600" style="font-size:13px">
            Hola, {{ auth()->user()->name }} 👋
        </span>
        <h1 class="fw-800 mb-0" style="font-size:22px;letter-spacing:-0.5px">
            @yield('header_title', 'Encuentra tu cancha')
        </h1>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <button class="btn p-0 border-0 bg-white rounded-circle shadow-sm d-flex align-items-center justify-content-center"
                style="width:42px;height:42px">
            <i class="fa-regular fa-bell" style="font-size:16px"></i>
        </button>
    </div>
</div>
