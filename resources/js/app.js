/**
 * Drawer lateral en pantallas pequeñas: abrir con el botón de menú y cerrar
 * con la X, el fondo oscuro, la tecla Escape o al elegir una opción.
 */
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const openButton = document.querySelector('[data-sidebar-open]');

    if (!sidebar || !openButton) {
        return;
    }

    const setOpen = (isOpen) => {
        sidebar.classList.toggle('open', isOpen);
        openButton.setAttribute('aria-expanded', String(isOpen));
        document.body.style.overflow = isOpen ? 'hidden' : '';
    };

    openButton.addEventListener('click', () => setOpen(true));
    document.querySelectorAll('[data-sidebar-close]').forEach((element) => {
        element.addEventListener('click', () => setOpen(false));
    });
    sidebar.querySelectorAll('.nav-item').forEach((link) => {
        link.addEventListener('click', () => setOpen(false));
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && sidebar.classList.contains('open')) {
            setOpen(false);
        }
    });
});

/**
 * Ventana centrada para confirmar el cierre de sesión.
 */
document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.getElementById('logoutDialog');
    if (!dialog) {
        return;
    }
    document.querySelectorAll('[data-logout-open]').forEach((button) => {
        button.addEventListener('click', () => dialog.showModal());
    });
    dialog.querySelector('[data-logout-close]')?.addEventListener('click', () => dialog.close());
    // Cerrar al hacer clic en el fondo oscuro (fuera del recuadro).
    dialog.addEventListener('click', (event) => {
        const box = dialog.getBoundingClientRect();
        const outside = event.clientX < box.left || event.clientX > box.right || event.clientY < box.top || event.clientY > box.bottom;
        if (event.target === dialog && outside) {
            dialog.close();
        }
    });
});
