/**
 * TUBI 2026 - Interfaz 0 (Intro Cinematográfica)
 */

(function() {
    'use strict';

    const PHASE_1_DURATION = 2000;
    const PHASE_2_DURATION = 800;
    const PHASE_3_DURATION = 1000;
    const FADE_DURATION = 500;
    const REDIRECT_URL = window.introRedirectUrl || 'selector.php';

    let phaseTimers = [];
    let hasRedirected = false;

    function redirectToNext() {
        if (hasRedirected) return;
        hasRedirected = true;

        const container = document.getElementById('introContainer');
        if (container) {
            container.classList.add('fade-out');
        }

        setTimeout(function() {
            window.location.href = REDIRECT_URL;
        }, FADE_DURATION);
    }

    function skipIntro() {
        phaseTimers.forEach(clearTimeout);
        phaseTimers = [];
        redirectToNext();
    }

    function startCinematicSequence() {
        const container = document.getElementById('introContainer');
        if (!container) return;

        phaseTimers.push(setTimeout(function() {
            container.classList.add('phase-split');
        }, PHASE_1_DURATION));

        phaseTimers.push(setTimeout(function() {
            container.classList.add('phase-logo');
        }, PHASE_1_DURATION + PHASE_2_DURATION));

        phaseTimers.push(setTimeout(function() {
            redirectToNext();
        }, PHASE_1_DURATION + PHASE_2_DURATION + PHASE_3_DURATION));
    }

    function init() {
        const skipBtn = document.getElementById('introSkip');
        if (skipBtn) {
            skipBtn.addEventListener('click', skipIntro);
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' || e.key === 'Enter' || e.key === ' ') {
                skipIntro();
            }
        });

        startCinematicSequence();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
