<?php
/**
 * TUBI 2026 - Módulo de Aprendizaje con Videos y Trivia
 * Aprende jugando - Videos de bici + preguntas
 */
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasRole('alumno')) {
    redirect('login.php');
}

$user = getCurrentUser();
$moduloId = intval($_GET['id'] ?? 1);

// Base de datos de módulos con videos y preguntas
$modulosDB = [
    1 => [
        'titulo' => 'Conocé tu Bicicleta',
        'descripcion' => 'Aprendé las partes de tu bici y cómo funciona cada una',
        'icono' => '🚲',
        'color' => '#2563eb',
        'puntos_total' => 100,
        'videos' => [
            [
                'id' => 'v1',
                'titulo' => 'Las partes de la bicicleta',
                'descripcion' => 'Conocé cada parte de tu bici',
                'youtube_id' => 'dQw4w9WgXcQ', // Placeholder - reemplazar con video real
                'duracion' => '3:45'
            ]
        ],
        'preguntas' => [
            [
                'pregunta' => '¿Cuál es la parte de la bicicleta que te permite frenar?',
                'opciones' => ['El pedal', 'El freno', 'El asiento', 'El manubrio'],
                'correcta' => 1,
                'puntos' => 20
            ],
            [
                'pregunta' => '¿Cómo se llama la parte donde te sentás?',
                'opciones' => ['Cuadro', 'Rueda', 'Asiento o sillín', 'Cadena'],
                'correcta' => 2,
                'puntos' => 20
            ],
            [
                'pregunta' => '¿Qué parte transmite la fuerza de tus piernas a la rueda?',
                'opciones' => ['El timbre', 'Los rayos', 'La cadena', 'El reflector'],
                'correcta' => 2,
                'puntos' => 20
            ],
            [
                'pregunta' => '¿Cuántas ruedas tiene una bicicleta normal?',
                'opciones' => ['1', '2', '3', '4'],
                'correcta' => 1,
                'puntos' => 20
            ],
            [
                'pregunta' => '¿Para qué sirve el timbre de la bici?',
                'opciones' => ['Para ir más rápido', 'Para avisar a peatones', 'Para decorar', 'Para frenar'],
                'correcta' => 1,
                'puntos' => 20
            ]
        ]
    ],
    2 => [
        'titulo' => 'Seguridad Vial Básica',
        'descripcion' => 'Señales de tránsito y reglas para circular seguro',
        'icono' => '🛡️',
        'color' => '#06b6d4',
        'puntos_total' => 150,
        'videos' => [
            [
                'id' => 'v2',
                'titulo' => 'Señales de tránsito importantes',
                'descripcion' => 'Las señales que todo ciclista debe conocer',
                'youtube_id' => 'dQw4w9WgXcQ',
                'duracion' => '5:20'
            ]
        ],
        'preguntas' => [
            [
                'pregunta' => '¿De qué lado de la calle debés circular con tu bici?',
                'opciones' => ['Por la izquierda', 'Por la derecha', 'Por el medio', 'Por donde quiera'],
                'correcta' => 1,
                'puntos' => 30
            ],
            [
                'pregunta' => '¿Qué significa una señal PARE?',
                'opciones' => ['Acelerar', 'Detenerse completamente', 'Tocar bocina', 'Girar'],
                'correcta' => 1,
                'puntos' => 30
            ],
            [
                'pregunta' => '¿Es obligatorio usar casco?',
                'opciones' => ['No, es opcional', 'Sí, siempre', 'Solo de noche', 'Solo en autopistas'],
                'correcta' => 1,
                'puntos' => 30
            ],
            [
                'pregunta' => '¿Qué luz de semáforo indica que podés avanzar?',
                'opciones' => ['Roja', 'Amarilla', 'Verde', 'Todas'],
                'correcta' => 2,
                'puntos' => 30
            ],
            [
                'pregunta' => '¿Podés llevar pasajeros en tu bicicleta TuBi?',
                'opciones' => ['Sí, siempre', 'No, es solo para una persona', 'Sí, hasta 3 personas', 'Solo adultos'],
                'correcta' => 1,
                'puntos' => 30
            ]
        ]
    ],
    3 => [
        'titulo' => 'Mantenimiento Básico',
        'descripcion' => 'Cómo cuidar y mantener tu bicicleta en buen estado',
        'icono' => '🔧',
        'color' => '#2563eb',
        'puntos_total' => 100,
        'videos' => [
            [
                'id' => 'v3',
                'titulo' => 'Cuidados básicos de tu bici',
                'descripcion' => 'Mantené tu bici siempre lista',
                'youtube_id' => 'dQw4w9WgXcQ',
                'duracion' => '4:15'
            ]
        ],
        'preguntas' => [
            [
                'pregunta' => '¿Cada cuánto deberías revisar la presión de las ruedas?',
                'opciones' => ['Cada año', 'Cada mes', 'Cada semana', 'Nunca'],
                'correcta' => 2,
                'puntos' => 25
            ],
            [
                'pregunta' => '¿Qué parte necesita lubricación regular?',
                'opciones' => ['El asiento', 'La cadena', 'El manubrio', 'Las luces'],
                'correcta' => 1,
                'puntos' => 25
            ],
            [
                'pregunta' => '¿Dónde es mejor guardar tu bicicleta?',
                'opciones' => ['En la lluvia', 'Bajo techo', 'En el barro', 'En el sol directo'],
                'correcta' => 1,
                'puntos' => 25
            ],
            [
                'pregunta' => '¿Qué hacer si la cadena se sale?',
                'opciones' => ['Dejar la bici', 'Colocarla de nuevo con cuidado', 'Pedalear más fuerte', 'Cortar la cadena'],
                'correcta' => 1,
                'puntos' => 25
            ]
        ]
    ],
    4 => [
        'titulo' => 'Seguridad Nocturna',
        'descripcion' => 'Cómo circular de noche de forma segura',
        'icono' => '🌙',
        'color' => '#06b6d4',
        'puntos_total' => 100,
        'videos' => [
            [
                'id' => 'v4',
                'titulo' => 'Ciclismo nocturno seguro',
                'descripcion' => 'Tips para andar de noche',
                'youtube_id' => 'dQw4w9WgXcQ',
                'duracion' => '3:30'
            ]
        ],
        'preguntas' => [
            [
                'pregunta' => '¿Qué color de luz va adelante en la bici?',
                'opciones' => ['Roja', 'Blanca', 'Azul', 'Verde'],
                'correcta' => 1,
                'puntos' => 25
            ],
            [
                'pregunta' => '¿Qué color de luz va atrás?',
                'opciones' => ['Blanca', 'Amarilla', 'Roja', 'Sin luz'],
                'correcta' => 2,
                'puntos' => 25
            ],
            [
                'pregunta' => '¿Qué ropa es mejor usar de noche?',
                'opciones' => ['Ropa oscura', 'Ropa clara o reflectiva', 'No importa', 'Ropa ajustada'],
                'correcta' => 1,
                'puntos' => 25
            ],
            [
                'pregunta' => '¿Debés ir más rápido o más despacio de noche?',
                'opciones' => ['Más rápido', 'Más despacio', 'Igual velocidad', 'Lo más rápido posible'],
                'correcta' => 1,
                'puntos' => 25
            ]
        ]
    ],
    5 => [
        'titulo' => 'Primeros Auxilios',
        'descripcion' => 'Qué hacer en caso de accidente o emergencia',
        'icono' => '🏥',
        'color' => '#0891b2',
        'puntos_total' => 150,
        'videos' => [
            [
                'id' => 'v5',
                'titulo' => 'Primeros auxilios para ciclistas',
                'descripcion' => 'Cómo actuar en emergencias',
                'youtube_id' => 'dQw4w9WgXcQ',
                'duracion' => '6:00'
            ]
        ],
        'preguntas' => [
            [
                'pregunta' => '¿Qué número llamás en caso de emergencia en Argentina?',
                'opciones' => ['911', '100', '123', '999'],
                'correcta' => 0,
                'puntos' => 30
            ],
            [
                'pregunta' => '¿Qué hacer primero si tenés un accidente leve?',
                'opciones' => ['Seguir andando', 'Detenerte en lugar seguro', 'Llamar a mamá', 'Nada'],
                'correcta' => 1,
                'puntos' => 30
            ],
            [
                'pregunta' => '¿Qué NO debés hacer si alguien está herido grave?',
                'opciones' => ['Llamar al 911', 'Moverlo sin cuidado', 'Esperar ayuda', 'Quedarte con la persona'],
                'correcta' => 1,
                'puntos' => 30
            ],
            [
                'pregunta' => '¿Qué elemento es útil tener siempre?',
                'opciones' => ['Juguetes', 'Un botiquín pequeño', 'Comida', 'Dinero'],
                'correcta' => 1,
                'puntos' => 30
            ],
            [
                'pregunta' => '¿Cuál es la mejor manera de prevenir accidentes?',
                'opciones' => ['No salir nunca', 'Circular con cuidado y atención', 'Ir muy rápido', 'No usar casco'],
                'correcta' => 1,
                'puntos' => 30
            ]
        ]
    ]
];

$modulo = $modulosDB[$moduloId] ?? $modulosDB[1];
$pageTitle = $modulo['titulo'];

include __DIR__ . '/../../includes/header.php';
?>

<style>
/* Estilos específicos del módulo de aprendizaje */
.modulo-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 1rem;
}

.modulo-header {
    text-align: center;
    margin-bottom: 2rem;
}

.modulo-icon {
    width: 80px;
    height: 80px;
    border-radius: var(--border-radius-lg);
    background: <?= $modulo['color'] ?>;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    margin: 0 auto 1rem;
    box-shadow: 0 8px 25px <?= $modulo['color'] ?>40;
}

.modulo-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.modulo-desc {
    color: var(--text-secondary);
}

/* Progress bar */
.progress-container {
    background: var(--bg-card);
    border-radius: var(--border-radius-lg);
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
    border: 1px solid var(--border-color);
}

.progress-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    font-size: 0.875rem;
}

.progress-bar {
    height: 8px;
    background: var(--bg-card-hover);
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #2563eb, #06b6d4);
    border-radius: 4px;
    transition: width 0.5s ease;
    width: 0%;
}

/* Secciones */
.section-card {
    background: var(--bg-card);
    border-radius: var(--border-radius-xl);
    border: 1px solid var(--border-color);
    margin-bottom: 1.5rem;
    overflow: hidden;
}

.section-header {
    padding: 1rem 1.5rem;
    background: var(--bg-card-hover);
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.section-number {
    width: 32px;
    height: 32px;
    background: linear-gradient(135deg, #2563eb, #06b6d4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 0.875rem;
}

.section-title {
    font-weight: 600;
    color: var(--text-primary);
}

.section-content {
    padding: 1.5rem;
}

/* Video player */
.video-container {
    position: relative;
    padding-bottom: 56.25%;
    height: 0;
    border-radius: var(--border-radius);
    overflow: hidden;
    background: #000;
    margin-bottom: 1rem;
}

.video-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: none;
}

.video-placeholder {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #1e3a5f, #134e4a);
    cursor: pointer;
    transition: all var(--transition);
}

.video-placeholder:hover {
    background: linear-gradient(135deg, #2563eb, #06b6d4);
}

.video-placeholder svg {
    width: 64px;
    height: 64px;
    color: white;
    margin-bottom: 1rem;
}

.video-placeholder span {
    color: white;
    font-weight: 500;
}

.video-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background: var(--bg-card-hover);
    border-radius: var(--border-radius);
}

.video-info-icon {
    width: 40px;
    height: 40px;
    background: <?= $modulo['color'] ?>;
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    justify-content: center;
}

.video-info-icon svg {
    width: 20px;
    height: 20px;
    color: white;
}

/* Quiz section */
.quiz-container {
    display: none;
}

.quiz-container.active {
    display: block;
}

.question-card {
    background: var(--bg-card-hover);
    border-radius: var(--border-radius-lg);
    padding: 1.5rem;
    margin-bottom: 1rem;
}

.question-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
}

.question-number {
    width: 28px;
    height: 28px;
    background: linear-gradient(135deg, #2563eb, #06b6d4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.8rem;
}

.question-text {
    font-weight: 600;
    color: var(--text-primary);
    font-size: 1rem;
}

.options-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.option-btn {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem;
    background: var(--bg-card);
    border: 2px solid var(--border-color);
    border-radius: var(--border-radius);
    cursor: pointer;
    transition: all var(--transition);
    text-align: left;
    color: var(--text-primary);
    font-size: 0.9375rem;
}

.option-btn:hover:not(.disabled) {
    border-color: #2563eb;
    background: rgba(37, 99, 235, 0.1);
}

.option-btn.selected {
    border-color: #2563eb;
    background: rgba(37, 99, 235, 0.15);
}

.option-btn.correct {
    border-color: #22c55e;
    background: rgba(34, 197, 94, 0.15);
}

.option-btn.incorrect {
    border-color: #ef4444;
    background: rgba(239, 68, 68, 0.15);
}

.option-btn.disabled {
    cursor: not-allowed;
    opacity: 0.7;
}

.option-letter {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--bg-card-hover);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.8rem;
    flex-shrink: 0;
}

.option-btn.correct .option-letter {
    background: #22c55e;
    color: white;
}

.option-btn.incorrect .option-letter {
    background: #ef4444;
    color: white;
}

/* Resultado */
.quiz-result {
    display: none;
    text-align: center;
    padding: 2rem;
}

.quiz-result.active {
    display: block;
}

.result-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto 1.5rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
}

.result-icon.success {
    background: linear-gradient(135deg, #22c55e, #16a34a);
}

.result-icon.partial {
    background: linear-gradient(135deg, #f59e0b, #d97706);
}

.result-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.result-subtitle {
    color: var(--text-secondary);
    margin-bottom: 1.5rem;
}

.result-stats {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-bottom: 2rem;
}

.stat-item {
    text-align: center;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #2563eb;
}

.stat-label {
    font-size: 0.8rem;
    color: var(--text-muted);
}

/* Botones */
.btn-gradient {
    background: linear-gradient(135deg, #2563eb, #06b6d4);
    color: white;
    border: none;
    padding: 0.875rem 1.5rem;
    border-radius: var(--border-radius);
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
}

.btn-outline {
    background: transparent;
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
    padding: 0.875rem 1.5rem;
    border-radius: var(--border-radius);
    font-weight: 500;
    cursor: pointer;
    transition: all var(--transition);
}

.btn-outline:hover {
    background: var(--bg-card-hover);
    color: var(--text-primary);
}

.actions-row {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

/* Feedback */
.feedback-toast {
    position: fixed;
    bottom: 100px;
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    padding: 1rem 1.5rem;
    border-radius: var(--border-radius);
    font-weight: 600;
    z-index: 1000;
    opacity: 0;
    transition: all 0.3s ease;
}

.feedback-toast.show {
    transform: translateX(-50%) translateY(0);
    opacity: 1;
}

.feedback-toast.success {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    color: white;
}

.feedback-toast.error {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
}

/* Responsive */
@media (max-width: 640px) {
    .modulo-container {
        padding: 0.5rem;
    }

    .section-content {
        padding: 1rem;
    }

    .question-card {
        padding: 1rem;
    }

    .result-stats {
        gap: 1rem;
    }
}
</style>

<div class="modulo-container">
    <!-- Header del módulo -->
    <div class="modulo-header">
        <div class="modulo-icon"><?= $modulo['icono'] ?></div>
        <h1 class="modulo-title"><?= e($modulo['titulo']) ?></h1>
        <p class="modulo-desc"><?= e($modulo['descripcion']) ?></p>
    </div>

    <!-- Barra de progreso -->
    <div class="progress-container">
        <div class="progress-info">
            <span id="progressText">Paso 1 de 2</span>
            <span id="pointsEarned">0 / <?= $modulo['puntos_total'] ?> puntos</span>
        </div>
        <div class="progress-bar">
            <div class="progress-fill" id="progressFill"></div>
        </div>
    </div>

    <!-- Sección 1: Video -->
    <div class="section-card" id="videoSection">
        <div class="section-header">
            <div class="section-number">1</div>
            <span class="section-title">Mirá el video</span>
        </div>
        <div class="section-content">
            <?php foreach ($modulo['videos'] as $video): ?>
            <div class="video-container" id="videoContainer">
                <div class="video-placeholder" onclick="loadVideo('<?= $video['youtube_id'] ?>')">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                    <span>Click para reproducir video</span>
                </div>
            </div>
            <div class="video-info">
                <div class="video-info-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="5 3 19 12 5 21 5 3"/>
                    </svg>
                </div>
                <div>
                    <strong><?= e($video['titulo']) ?></strong>
                    <p style="font-size: 0.875rem; color: var(--text-muted); margin: 0;">
                        <?= e($video['descripcion']) ?> • <?= $video['duracion'] ?>
                    </p>
                </div>
            </div>
            <?php endforeach; ?>

            <div style="text-align: center; margin-top: 1.5rem;">
                <button class="btn-gradient" onclick="showQuiz()">
                    Continuar al Quiz
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Sección 2: Quiz -->
    <div class="section-card quiz-container" id="quizSection">
        <div class="section-header">
            <div class="section-number">2</div>
            <span class="section-title">Respondé las preguntas</span>
        </div>
        <div class="section-content" id="quizContent">
            <?php foreach ($modulo['preguntas'] as $idx => $pregunta): ?>
            <div class="question-card" id="question<?= $idx ?>" data-correct="<?= $pregunta['correcta'] ?>" data-points="<?= $pregunta['puntos'] ?>">
                <div class="question-header">
                    <div class="question-number"><?= $idx + 1 ?></div>
                    <span class="question-text"><?= e($pregunta['pregunta']) ?></span>
                </div>
                <div class="options-list">
                    <?php foreach ($pregunta['opciones'] as $optIdx => $opcion): ?>
                    <button class="option-btn" onclick="selectOption(<?= $idx ?>, <?= $optIdx ?>)" data-option="<?= $optIdx ?>">
                        <span class="option-letter"><?= chr(65 + $optIdx) ?></span>
                        <span><?= e($opcion) ?></span>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <div style="text-align: center; margin-top: 1.5rem;">
                <button class="btn-gradient" onclick="submitQuiz()" id="submitBtn" disabled>
                    Verificar Respuestas
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 6L9 17l-5-5"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Resultado final -->
    <div class="section-card quiz-result" id="resultSection">
        <div class="section-content">
            <div class="result-icon" id="resultIcon">🎉</div>
            <h2 class="result-title" id="resultTitle">¡Felicitaciones!</h2>
            <p class="result-subtitle" id="resultSubtitle">Completaste el módulo exitosamente</p>

            <div class="result-stats">
                <div class="stat-item">
                    <div class="stat-value" id="correctCount">0</div>
                    <div class="stat-label">Correctas</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" id="totalPoints">0</div>
                    <div class="stat-label">Puntos</div>
                </div>
            </div>

            <div class="actions-row">
                <button class="btn-outline" onclick="location.reload()">
                    Repetir módulo
                </button>
                <a href="<?= BASE_URL ?>pages/alumno/aprender.php" class="btn-gradient">
                    Ver más módulos
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Toast de feedback -->
<div class="feedback-toast" id="feedbackToast"></div>

<script>
const totalQuestions = <?= count($modulo['preguntas']) ?>;
const totalPossiblePoints = <?= $modulo['puntos_total'] ?>;
let answers = {};
let earnedPoints = 0;
let correctAnswers = 0;

// Cargar video de YouTube
function loadVideo(videoId) {
    const container = document.getElementById('videoContainer');
    container.innerHTML = `<iframe src="https://www.youtube.com/embed/${videoId}?autoplay=1"
        frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        allowfullscreen></iframe>`;
}

// Mostrar sección de quiz
function showQuiz() {
    document.getElementById('videoSection').style.display = 'none';
    document.getElementById('quizSection').classList.add('active');
    updateProgress(50);
    document.getElementById('progressText').textContent = 'Paso 2 de 2';
}

// Seleccionar opción
function selectOption(questionIdx, optionIdx) {
    const questionCard = document.getElementById('question' + questionIdx);
    if (questionCard.classList.contains('answered')) return;

    // Deseleccionar opciones anteriores
    const options = questionCard.querySelectorAll('.option-btn');
    options.forEach(opt => opt.classList.remove('selected'));

    // Seleccionar nueva opción
    const selectedOption = questionCard.querySelector(`[data-option="${optionIdx}"]`);
    selectedOption.classList.add('selected');

    // Guardar respuesta
    answers[questionIdx] = optionIdx;

    // Habilitar botón de enviar si todas las preguntas tienen respuesta
    if (Object.keys(answers).length === totalQuestions) {
        document.getElementById('submitBtn').disabled = false;
    }
}

// Enviar quiz
function submitQuiz() {
    correctAnswers = 0;
    earnedPoints = 0;

    for (let i = 0; i < totalQuestions; i++) {
        const questionCard = document.getElementById('question' + i);
        const correctAnswer = parseInt(questionCard.dataset.correct);
        const points = parseInt(questionCard.dataset.points);
        const userAnswer = answers[i];

        // Marcar como respondida
        questionCard.classList.add('answered');

        // Deshabilitar opciones
        const options = questionCard.querySelectorAll('.option-btn');
        options.forEach(opt => opt.classList.add('disabled'));

        // Marcar correcta/incorrecta
        const correctBtn = questionCard.querySelector(`[data-option="${correctAnswer}"]`);
        correctBtn.classList.add('correct');

        if (userAnswer === correctAnswer) {
            correctAnswers++;
            earnedPoints += points;
            showFeedback('¡Correcto! +' + points + ' puntos', 'success');
        } else {
            const selectedBtn = questionCard.querySelector(`[data-option="${userAnswer}"]`);
            selectedBtn.classList.add('incorrect');
        }
    }

    // Mostrar resultado después de un momento
    setTimeout(() => {
        showResult();
    }, 1500);
}

// Mostrar resultado final
function showResult() {
    document.getElementById('quizSection').classList.remove('active');
    document.getElementById('resultSection').classList.add('active');

    const resultIcon = document.getElementById('resultIcon');
    const resultTitle = document.getElementById('resultTitle');
    const resultSubtitle = document.getElementById('resultSubtitle');

    if (correctAnswers === totalQuestions) {
        resultIcon.innerHTML = '🏆';
        resultIcon.classList.add('success');
        resultTitle.textContent = '¡Perfecto!';
        resultSubtitle.textContent = 'Respondiste todas las preguntas correctamente';
    } else if (correctAnswers >= totalQuestions / 2) {
        resultIcon.innerHTML = '🎉';
        resultIcon.classList.add('success');
        resultTitle.textContent = '¡Muy bien!';
        resultSubtitle.textContent = 'Completaste el módulo exitosamente';
    } else {
        resultIcon.innerHTML = '💪';
        resultIcon.classList.add('partial');
        resultTitle.textContent = '¡Seguí practicando!';
        resultSubtitle.textContent = 'Podés repetir el módulo para mejorar';
    }

    document.getElementById('correctCount').textContent = correctAnswers + '/' + totalQuestions;
    document.getElementById('totalPoints').textContent = earnedPoints;
    document.getElementById('pointsEarned').textContent = earnedPoints + ' / ' + totalPossiblePoints + ' puntos';

    updateProgress(100);
}

// Actualizar barra de progreso
function updateProgress(percent) {
    document.getElementById('progressFill').style.width = percent + '%';
}

// Mostrar feedback toast
function showFeedback(message, type) {
    const toast = document.getElementById('feedbackToast');
    toast.textContent = message;
    toast.className = 'feedback-toast ' + type + ' show';

    setTimeout(() => {
        toast.classList.remove('show');
    }, 2000);
}

// Inicializar
updateProgress(25);
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
