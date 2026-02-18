/**
 * TUBI 2026 - Interfaz 0 (Intro)
 * Muestra estadísticas en vivo y botón para ingresar
 * Compatible ES5
 */

(function() {
    'use strict';

    // Configuración
    var LOADING_DONE_AT = 4500;       // ms cuando termina la barra de carga (1.8s delay + 2.5s anim)
    var SHOW_BUTTON_DELAY = 4800;     // ms para mostrar botón "Ingresar"
    var STATS_REFRESH_INTERVAL = 15000; // Refrescar stats cada 15 seg
    var FADE_DURATION = 500;
    var REDIRECT_URL = window.introRedirectUrl || 'selector.php';
    var STATS_URL = window.introStatsUrl || 'index.php';

    var refreshTimer = null;

    // ========================================
    // Funciones de estadísticas en vivo
    // ========================================

    function refreshStats() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', STATS_URL, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                try {
                    var resp = JSON.parse(xhr.responseText);
                    if (resp.success && resp.stats) {
                        updateStatsDisplay(resp.stats);
                    }
                } catch (e) {
                    // Silenciar errores de parse
                }
            }
        };
        xhr.send();
    }

    function updateStatsDisplay(stats) {
        var total = parseInt(stats.total_bicicletas, 10) || 0;
        var entregadas = parseInt(stats.entregadas, 10) || 0;
        var progreso = total > 0 ? Math.round((entregadas / total) * 1000) / 10 : 0;

        // Actualizar valores con efecto
        animateValue('statHoy', stats.entregas_hoy);
        animateValue('statSemana', stats.entregas_semana);
        animateValue('statMes', stats.entregas_mes);
        animateValue('statTasa', stats.tasa_entrega + '%');

        // Barra de progreso
        var fillEl = document.getElementById('progresoFill');
        if (fillEl) fillEl.style.width = progreso + '%';

        // Texto de detalle
        var detalleEl = document.getElementById('statDetalle');
        if (detalleEl) detalleEl.textContent = entregadas + '/' + total + ' bicicletas \u2014 ' + progreso + '%';
    }

    function animateValue(elementId, newValue) {
        var el = document.getElementById(elementId);
        if (!el) return;

        var current = el.textContent;
        var newStr = String(newValue);

        if (current !== newStr) {
            // Efecto de destaque al cambiar
            el.className = 'intro-stat-value updating';
            el.textContent = newStr;
            setTimeout(function() {
                el.className = 'intro-stat-value';
            }, 400);
        }
    }

    // ========================================
    // Navegación
    // ========================================

    function skipToSelector() {
        if (refreshTimer) {
            clearInterval(refreshTimer);
            refreshTimer = null;
        }

        var container = document.getElementById('introContainer');
        if (container) {
            container.className = container.className + ' fade-out';
        }

        setTimeout(function() {
            window.location.href = REDIRECT_URL;
        }, FADE_DURATION);
    }

    function showEnterButton() {
        var btn = document.getElementById('introEnterBtn');
        if (btn) {
            btn.className = btn.className + ' visible';
        }
        // Ocultar barra de carga después de que aparezca el botón
        var loadingBar = document.getElementById('introLoadingBar');
        if (loadingBar) {
            loadingBar.style.opacity = '0';
            loadingBar.style.transition = 'opacity 0.3s';
        }
    }

    // ========================================
    // Inicialización
    // ========================================

    function init() {
        // Botón saltar → va directo al selector
        var skipBtn = document.getElementById('introSkip');
        if (skipBtn) {
            skipBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                skipToSelector();
            });
        }

        // Tecla Escape para saltar
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                skipToSelector();
            }
        });

        // Mostrar botón "Ingresar al Sistema" después de la carga
        setTimeout(showEnterButton, SHOW_BUTTON_DELAY);

        // Auto-refresh de estadísticas cada 15 segundos
        refreshTimer = setInterval(refreshStats, STATS_REFRESH_INTERVAL);
    }

    // Arrancar
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
