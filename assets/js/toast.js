/**
 * TUBI 2026 - Sistema de Notificaciones Toast
 * Colores institucionales: Azul #2563eb, Turquesa #06b6d4
 * Compatible ES5
 */

var TubiToast = {
    container: null,

    init: function() {
        if (this.container) return;

        this.container = document.createElement('div');
        this.container.className = 'toast-container';
        document.body.appendChild(this.container);
    },

    show: function(options) {
        this.init();

        var type = options.type || 'success';
        var title = options.title || '';
        var message = options.message || '';
        var duration = options.duration !== undefined ? options.duration : 4000;

        var toast = document.createElement('div');
        toast.className = 'toast toast-' + type;

        var icons = {
            success: '\u2713',
            error: '\u2715',
            warning: '!',
            info: 'i',
            action: '\u2192'
        };

        var html = '<div class="toast-icon">' + (icons[type] || '\u2713') + '</div>' +
            '<div class="toast-content">';
        if (title) html += '<div class="toast-title">' + title + '</div>';
        if (message) html += '<div class="toast-message">' + message + '</div>';
        html += '</div>' +
            '<button class="toast-close" aria-label="Cerrar">' +
            '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
            '<line x1="18" y1="6" x2="6" y2="18"/>' +
            '<line x1="6" y1="6" x2="18" y2="18"/>' +
            '</svg></button>';

        toast.innerHTML = html;

        var self = this;
        var closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', function() { self.hide(toast); });

        this.container.appendChild(toast);

        requestAnimationFrame(function() {
            toast.className = toast.className + ' show';
        });

        if (duration > 0) {
            setTimeout(function() { self.hide(toast); }, duration);
        }

        return toast;
    },

    hide: function(toast) {
        toast.className = toast.className.replace(' show', '') + ' hide';

        setTimeout(function() {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    },

    success: function(title, message, duration) {
        return this.show({ type: 'success', title: title, message: message, duration: duration });
    },

    error: function(title, message, duration) {
        return this.show({ type: 'error', title: title, message: message, duration: duration });
    },

    warning: function(title, message, duration) {
        return this.show({ type: 'warning', title: title, message: message, duration: duration });
    },

    info: function(title, message, duration) {
        return this.show({ type: 'info', title: title, message: message, duration: duration });
    },

    action: function(title, message, duration) {
        return this.show({ type: 'action', title: title, message: message, duration: duration });
    }
};

window.TubiToast = TubiToast;

document.addEventListener('DOMContentLoaded', function() { TubiToast.init(); });
