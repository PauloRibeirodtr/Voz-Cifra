<div class="admin-theme-control">
    <button
        type="button"
        class="admin-theme-toggle inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-700 shadow-sm transition hover:bg-gray-50"
        data-theme-toggle
        data-theme-menu-toggle
        aria-label="Escolher tema da interface"
        aria-haspopup="menu"
        aria-expanded="false"
        title="Tema da interface"
    >
        <i class="fa-solid fa-desktop" data-theme-toggle-icon aria-hidden="true"></i>
    </button>

    <div class="admin-theme-menu" data-theme-menu role="menu" hidden>
        <button type="button" role="menuitemradio" data-theme-option="system">
            <i class="fa-solid fa-desktop" aria-hidden="true"></i>
            <span>Automático</span>
            <i class="fa-solid fa-check admin-theme-menu__check" aria-hidden="true"></i>
        </button>
        <button type="button" role="menuitemradio" data-theme-option="light">
            <i class="fa-solid fa-sun" aria-hidden="true"></i>
            <span>Claro</span>
            <i class="fa-solid fa-check admin-theme-menu__check" aria-hidden="true"></i>
        </button>
        <button type="button" role="menuitemradio" data-theme-option="dark">
            <i class="fa-solid fa-moon" aria-hidden="true"></i>
            <span>Escuro</span>
            <i class="fa-solid fa-check admin-theme-menu__check" aria-hidden="true"></i>
        </button>
    </div>
</div>
