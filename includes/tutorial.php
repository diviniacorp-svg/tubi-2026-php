<?php
/**
 * TUBI 2026 - Sistema de Tutoriales
 * Panel de ayuda contextual para cada rol
 */

// Tutoriales por rol
$tutoriales = [
    'alumno' => [
        'titulo' => 'Bienvenido a TuBi',
        'subtitulo' => 'Tu asistente para aprovechar al máximo tu bicicleta',
        'pasos' => [
            [
                'icono' => '🏠',
                'titulo' => 'Mi TuBi',
                'descripcion' => 'Acá podés ver el estado de tu bicicleta, tus puntos acumulados y los logros que has desbloqueado.',
                'color' => '#354393'
            ],
            [
                'icono' => '📚',
                'titulo' => 'Aprendé Jugando',
                'descripcion' => 'Completá los módulos de aprendizaje: mirá los videos y respondé las preguntas para ganar puntos.',
                'color' => '#4aacc4'
            ],
            [
                'icono' => '🎮',
                'titulo' => 'Retos Diarios',
                'descripcion' => 'Cada día tenés nuevos retos según la hora: matutinos, de tarde y nocturnos. ¡Completalos para ganar más puntos!',
                'color' => '#354393'
            ],
            [
                'icono' => '🛡️',
                'titulo' => 'Recordá',
                'descripcion' => '¡Usá SIEMPRE el casco! Es obligatorio y puede salvar tu vida. Circulá por la derecha y respetá las señales.',
                'color' => '#22c55e'
            ]
        ]
    ],
    'proveedor' => [
        'titulo' => 'Panel de Proveedor',
        'subtitulo' => 'Gestión de armado y suministro de bicicletas',
        'pasos' => [
            [
                'icono' => '📦',
                'titulo' => 'Recepción',
                'descripcion' => 'Recibí las bicicletas del depósito y escaneá el código QR para registrar la recepción en el sistema.',
                'color' => '#4aacc4'
            ],
            [
                'icono' => '🔧',
                'titulo' => 'Armado',
                'descripcion' => 'Armá la bicicleta completamente: manubrio, pedales, asiento, ruedas. Verificá frenos y dirección.',
                'color' => '#354393'
            ],
            [
                'icono' => '✅',
                'titulo' => 'Control de Calidad',
                'descripcion' => 'Realizá el control de calidad: frenos, cambios, dirección, ruedas infladas. Todo debe funcionar perfectamente.',
                'color' => '#22c55e'
            ],
            [
                'icono' => '📱',
                'titulo' => 'Registro',
                'descripcion' => 'Escaneá el QR de armado completado para registrar la bici como lista para suministro.',
                'color' => '#4aacc4'
            ],
            [
                'icono' => '🚚',
                'titulo' => 'Suministro',
                'descripcion' => 'Coordiná la entrega a las escuelas asignadas. Verificá las órdenes de suministro pendientes.',
                'color' => '#354393'
            ]
        ]
    ],
    'escuela' => [
        'titulo' => 'Panel Escolar',
        'subtitulo' => 'Gestión de entregas y seguimiento de alumnos',
        'pasos' => [
            [
                'icono' => '📋',
                'titulo' => 'Dashboard',
                'descripcion' => 'Visualizá el resumen de bicicletas recibidas, entregadas y pendientes de tu institución.',
                'color' => '#354393'
            ],
            [
                'icono' => '👥',
                'titulo' => 'Alumnos',
                'descripcion' => 'Gestioná la lista de alumnos beneficiarios. Verificá documentación y asigná bicicletas.',
                'color' => '#4aacc4'
            ],
            [
                'icono' => '🚲',
                'titulo' => 'Asignación',
                'descripcion' => 'Para asignar una bici: verificá DNI del alumno y tutor, escaneá el QR de la bicicleta y completá el acta.',
                'color' => '#22c55e'
            ],
            [
                'icono' => '📝',
                'titulo' => 'Documentación',
                'descripcion' => 'Generá las actas de entrega y reportes de gestión para la Secretaría de Transporte.',
                'color' => '#354393'
            ],
            [
                'icono' => '📊',
                'titulo' => 'Reportes',
                'descripcion' => 'Consultá estadísticas de entregas, estado de bicicletas y progreso de los alumnos en los módulos.',
                'color' => '#4aacc4'
            ]
        ]
    ],
    'admin' => [
        'titulo' => 'Centro de Control',
        'subtitulo' => 'Administración general del programa TuBi',
        'pasos' => [
            [
                'icono' => '📈',
                'titulo' => 'Dashboard',
                'descripcion' => 'Visualizá métricas en tiempo real: total de bicicletas, entregas, distribución por zona y rendimiento.',
                'color' => '#354393'
            ],
            [
                'icono' => '👤',
                'titulo' => 'Usuarios',
                'descripcion' => 'Gestioná todos los usuarios del sistema: alumnos, tutores, escuelas, proveedores y administradores.',
                'color' => '#4aacc4'
            ],
            [
                'icono' => '⚙️',
                'titulo' => 'Configuración',
                'descripcion' => 'Ajustá parámetros del sistema, API keys, y configuración general del programa.',
                'color' => '#354393'
            ],
            [
                'icono' => '📊',
                'titulo' => 'Reportes',
                'descripcion' => 'Generá reportes detallados para la Secretaría de Transporte y autoridades provinciales.',
                'color' => '#4aacc4'
            ]
        ]
    ],
    'tutor' => [
        'titulo' => 'Panel de Tutor',
        'subtitulo' => 'Seguimiento de tu hijo/a en el programa TuBi',
        'pasos' => [
            [
                'icono' => '👁️',
                'titulo' => 'Estado',
                'descripcion' => 'Consultá el estado de la bicicleta asignada a tu representado/a y el proceso de entrega.',
                'color' => '#4aacc4'
            ],
            [
                'icono' => '📊',
                'titulo' => 'Progreso',
                'descripcion' => 'Mirá el avance en los módulos de aprendizaje y puntos acumulados.',
                'color' => '#354393'
            ],
            [
                'icono' => '📄',
                'titulo' => 'Documentación',
                'descripcion' => 'Accedé a la documentación del programa y las responsabilidades como tutor.',
                'color' => '#22c55e'
            ],
            [
                'icono' => '🔔',
                'titulo' => 'Notificaciones',
                'descripcion' => 'Recibí alertas sobre el uso de la bicicleta y recordatorios importantes.',
                'color' => '#4aacc4'
            ],
        ]
    ]
];

// Obtener tutorial del rol actual
$rolActual = isset($user['role']) ? $user['role'] : 'alumno';
$tutorial = isset($tutoriales[$rolActual]) ? $tutoriales[$rolActual] : $tutoriales['alumno'];

// Verificar si el usuario ya vio el tutorial
$tutorialKey = 'tubi_tutorial_' . $rolActual;
$tutorialVisto = isset($_COOKIE[$tutorialKey]);
?>

<!-- Modal de Tutorial -->
<div class="tutorial-overlay" id="tutorialOverlay" style="<?= $tutorialVisto ? 'display:none;' : '' ?>">
    <div class="tutorial-modal">
        <div class="tutorial-header">
            <div class="tutorial-logo"></div>
            <button class="tutorial-close" onclick="closeTutorial()" title="Cerrar">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <div class="tutorial-content">
            <h2 class="tutorial-title"><?= e($tutorial['titulo']) ?></h2>
            <p class="tutorial-subtitle"><?= e($tutorial['subtitulo']) ?></p>

            <div class="tutorial-steps" id="tutorialSteps">
                <?php foreach ($tutorial['pasos'] as $index => $paso): ?>
                <div class="tutorial-step <?= $index === 0 ? 'active' : '' ?>" data-step="<?= $index ?>">
                    <div class="step-icon" style="background: <?= $paso['color'] ?>;">
                        <?= $paso['icono'] ?>
                    </div>
                    <h3 class="step-title"><?= e($paso['titulo']) ?></h3>
                    <p class="step-description"><?= e($paso['descripcion']) ?></p>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="tutorial-dots">
                <?php foreach ($tutorial['pasos'] as $index => $paso): ?>
                <span class="dot <?= $index === 0 ? 'active' : '' ?>" data-step="<?= $index ?>"></span>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="tutorial-footer">
            <button class="btn-tutorial-secondary" id="btnPrev" onclick="prevStep()" disabled>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                Anterior
            </button>
            <button class="btn-tutorial-primary" id="btnNext" onclick="nextStep()">
                Siguiente
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<!-- Botón para abrir tutorial -->
<button class="tutorial-trigger" onclick="openTutorial()" title="Ver tutorial">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
        <line x1="12" y1="17" x2="12.01" y2="17"/>
    </svg>
    Ayuda
</button>

<style>
/* Tutorial Modal */
.tutorial-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(4px);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.tutorial-modal {
    background: var(--bg-card);
    border-radius: var(--border-radius-xl);
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    animation: slideUp 0.3s ease;
    border: 1px solid var(--border-color);
}

@keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.tutorial-header {
    padding: 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid var(--border-color);
}

.tutorial-logo svg {
    width: 80px;
    height: auto;
    color: var(--text-primary);
}

.tutorial-close {
    background: none;
    border: none;
    color: var(--text-muted);
    cursor: pointer;
    padding: 0.5rem;
    border-radius: var(--border-radius);
    transition: all var(--transition);
}

.tutorial-close:hover {
    background: var(--bg-card-hover);
    color: var(--text-primary);
}

.tutorial-content {
    padding: 1.5rem;
    flex: 1;
    overflow-y: auto;
    text-align: center;
}

.tutorial-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.tutorial-subtitle {
    color: var(--text-secondary);
    margin-bottom: 2rem;
}

.tutorial-steps {
    position: relative;
    min-height: 200px;
}

.tutorial-step {
    display: none;
    animation: fadeInStep 0.3s ease;
}

.tutorial-step.active {
    display: block;
}

@keyframes fadeInStep {
    from { opacity: 0; transform: translateX(10px); }
    to { opacity: 1; transform: translateX(0); }
}

.step-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    margin: 0 auto 1.5rem;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.step-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.75rem;
}

.step-description {
    color: var(--text-secondary);
    line-height: 1.6;
    max-width: 350px;
    margin: 0 auto;
}

.tutorial-dots {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 2rem;
}

.dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--border-color);
    cursor: pointer;
    transition: all var(--transition);
}

.dot.active {
    background: linear-gradient(135deg, #354393, #4aacc4);
    width: 24px;
    border-radius: 5px;
}

.tutorial-footer {
    padding: 1.25rem;
    display: flex;
    gap: 1rem;
    border-top: 1px solid var(--border-color);
}

.btn-tutorial-primary,
.btn-tutorial-secondary {
    flex: 1;
    padding: 0.875rem 1.5rem;
    border-radius: var(--border-radius);
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-family: 'Ubuntu', sans-serif;
}

.btn-tutorial-primary {
    background: linear-gradient(135deg, #354393, #4aacc4);
    color: white;
    border: none;
}

.btn-tutorial-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
}

.btn-tutorial-secondary {
    background: transparent;
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
}

.btn-tutorial-secondary:hover:not(:disabled) {
    background: var(--bg-card-hover);
    color: var(--text-primary);
}

.btn-tutorial-secondary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Botón trigger de tutorial */
.tutorial-trigger {
    position: fixed;
    bottom: 100px;
    right: 1rem;
    background: linear-gradient(135deg, #354393, #4aacc4);
    color: white;
    border: none;
    padding: 0.75rem 1rem;
    border-radius: var(--border-radius-full);
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
    transition: all var(--transition);
    z-index: 999;
    font-family: 'Ubuntu', sans-serif;
}

.tutorial-trigger:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.5);
}

@media (max-width: 640px) {
    .tutorial-modal {
        max-height: 95vh;
    }

    .tutorial-content {
        padding: 1rem;
    }

    .step-icon {
        width: 64px;
        height: 64px;
        font-size: 2rem;
    }

    .tutorial-trigger {
        padding: 0.625rem;
    }

    .tutorial-trigger span {
        display: none;
    }
}
</style>

<script>
var currentStep = 0;
var totalSteps = <?php echo count($tutorial['pasos']); ?>;

function openTutorial() {
    document.getElementById('tutorialOverlay').style.display = 'flex';
    currentStep = 0;
    updateStep();
}

function closeTutorial() {
    document.getElementById('tutorialOverlay').style.display = 'none';
    document.cookie = '<?php echo $tutorialKey; ?>=1; path=/; max-age=31536000';
}

function nextStep() {
    if (currentStep < totalSteps - 1) {
        currentStep++;
        updateStep();
    } else {
        closeTutorial();
    }
}

function prevStep() {
    if (currentStep > 0) {
        currentStep--;
        updateStep();
    }
}

function goToStep(step) {
    currentStep = step;
    updateStep();
}

function updateStep() {
    var steps = document.querySelectorAll('.tutorial-step');
    var dots = document.querySelectorAll('.dot');
    var i;

    for (i = 0; i < steps.length; i++) {
        if (i === currentStep) {
            steps[i].className = steps[i].className.replace(' active', '') + ' active';
        } else {
            steps[i].className = steps[i].className.replace(' active', '');
        }
    }

    for (i = 0; i < dots.length; i++) {
        if (i === currentStep) {
            dots[i].className = dots[i].className.replace(' active', '') + ' active';
        } else {
            dots[i].className = dots[i].className.replace(' active', '');
        }
    }

    document.getElementById('btnPrev').disabled = currentStep === 0;

    var btnNext = document.getElementById('btnNext');
    if (currentStep === totalSteps - 1) {
        btnNext.innerHTML = '&iexcl;Entendido! <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>';
    } else {
        btnNext.innerHTML = 'Siguiente <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>';
    }
}

// Click en dots
var allDots = document.querySelectorAll('.dot');
for (var d = 0; d < allDots.length; d++) {
    (function(dot) {
        dot.addEventListener('click', function() {
            goToStep(parseInt(dot.getAttribute('data-step'), 10));
        });
    })(allDots[d]);
}
</script>
