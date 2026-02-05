/**
 * TUBI 2026 - Sistema de Notificaciones Toast
 * Colores institucionales: Azul #2563eb, Turquesa #06b6d4
 */

const TubiToast = {
    container: null,

    init() {
        if (this.container) return;

        this.container = document.createElement('div');
        this.container.className = 'toast-container';
        document.body.appendChild(this.container);
    },

    /**
     * Mostrar notificación toast
     * @param {Object} options - Configuración del toast
     * @param {string} options.type - Tipo: success, error, warning, info, action
     * @param {string} options.title - Título del toast
     * @param {string} options.message - Mensaje del toast
     * @param {number} options.duration - Duración en ms (default: 4000)
     */
    show(options) {
        this.init();

        const {
            type = 'success',
            title = '',
            message = '',
            duration = 4000
        } = options;

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;

        const icons = {
            success: '✓',
            error: '✕',
            warning: '!',
            info: 'i',
            action: '→'
        };

        toast.innerHTML = `
            <div class="toast-icon">${icons[type] || '✓'}</div>
            <div class="toast-content">
                ${title ? `<div class="toast-title">${title}</div>` : ''}
                ${message ? `<div class="toast-message">${message}</div>` : ''}
            </div>
            <button class="toast-close" aria-label="Cerrar">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        `;

        // Evento para cerrar
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => this.hide(toast));

        // Agregar al container
        this.container.appendChild(toast);

        // Trigger animation
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });

        // Auto-cerrar después de duration
        if (duration > 0) {
            setTimeout(() => this.hide(toast), duration);
        }

        return toast;
    },

    hide(toast) {
        toast.classList.remove('show');
        toast.classList.add('hide');

        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    },

    // Métodos de conveniencia
    success(title, message, duration) {
        return this.show({ type: 'success', title, message, duration });
    },

    error(title, message, duration) {
        return this.show({ type: 'error', title, message, duration });
    },

    warning(title, message, duration) {
        return this.show({ type: 'warning', title, message, duration });
    },

    info(title, message, duration) {
        return this.show({ type: 'info', title, message, duration });
    },

    action(title, message, duration) {
        return this.show({ type: 'action', title, message, duration });
    }
};

// Exponer globalmente
window.TubiToast = TubiToast;

// Auto-inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => TubiToast.init());
