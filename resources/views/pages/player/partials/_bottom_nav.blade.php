<nav class="player-bottom-nav">
    <div class="nav-wrapper d-flex justify-content-around align-items-center">
        <a href="{{ route('player.home') }}" class="nav-item {{ request()->routeIs('player.home') ? 'active' : '' }}">
            <i class="fa-solid fa-house"></i>
            <span class="nav-label">Inicio</span>
        </a>
        <a href="{{ route('player.explore') }}" class="nav-item {{ request()->routeIs('player.explore') ? 'active' : '' }}">
            <i class="fa-solid fa-magnifying-glass"></i>
            <span class="nav-label">Explorar</span>
        </a>
        <a href="{{ route('player.bookings.index') }}" class="nav-item {{ request()->routeIs('player.bookings.*') ? 'active' : '' }}">
            <i class="fa-solid fa-calendar-check"></i>
            <span class="nav-label">Reservas</span>
        </a>
        <a href="#" class="nav-item">
            <i class="fa-solid fa-user"></i>
            <span class="nav-label">Perfil</span>
        </a>
    </div>
</nav>

<style>
:root { --player-primary: #000; }
.player-bottom-nav {
    position: fixed; bottom: 20px; left: 0; right: 0; z-index: 1000;
    display: flex; justify-content: center; padding: 0 20px;
}
.player-bottom-nav .nav-wrapper {
    background: rgba(255,255,255,0.9);
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    border: 1px solid rgba(255,255,255,0.4);
    border-radius: 40px; padding: 10px 20px;
    width: 100%; max-width: 420px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
}
.player-bottom-nav .nav-item {
    display: flex; flex-direction: column; align-items: center;
    text-decoration: none; color: #aaa;
    transition: all .3s cubic-bezier(.4,0,.2,1);
    position: relative; padding: 8px 14px;
}
.player-bottom-nav .nav-item i { font-size: 18px; transition: transform .3s; }
.player-bottom-nav .nav-item.active { color: var(--player-primary); }
.player-bottom-nav .nav-item.active i { transform: translateY(-4px); }
.player-bottom-nav .nav-label { font-size: 10px; font-weight: 700; margin-top: 3px; opacity: 0; transform: translateY(4px); transition: all .3s; }
.player-bottom-nav .nav-item.active .nav-label { opacity: 1; transform: translateY(0); }
.player-bottom-nav .nav-item.active::after { content:''; position:absolute; bottom:-2px; width:5px; height:5px; background:var(--player-primary); border-radius:50%; }
</style>
