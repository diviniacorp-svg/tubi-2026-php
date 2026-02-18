/**
 * TUBI 2026 - Interfaz 0 (Intro)
 * Muestra estadísticas en vivo y redirige automáticamente al login
 * Compatible ES5
 */

(function() {
    'use strict';

    // Configuración
    var LOADING_DONE_AT = 4200;       // ms cuando termina la barra de carga (1.4s delay + 2.5s anim + margen)
    var REDIRECT_DELAY = 4800;        // ms para redirigir automáticamente al login
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
        if (detalleEl) detalleEl.textContent = entregadas + ' / ' + total + ' bicicletas entregadas';
    }

    function animateValue(elementId, newValue) {
        var el = document.getElementById(elementId);
        if (!el) return;

        var current = el.textContent;
        var newStr = String(newValue);

        if (current !== newStr) {
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

    function goToLogin() {
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

    // ========================================
    // Inicialización
    // ========================================

    function init() {
        // Botón saltar → va directo al selector
        var skipBtn = document.getElementById('introSkip');
        if (skipBtn) {
            skipBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                goToLogin();
            });
        }

        // Tecla Escape para saltar
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                goToLogin();
            }
        });

        // Auto-redirect al login después de que termine la barra de carga
        setTimeout(goToLogin, REDIRECT_DELAY);

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
