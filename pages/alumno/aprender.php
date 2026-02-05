<?php
/**
 * TUBI 2026 - Aprende Jugando
 * Sistema de gamificación con videos y trivia
 */
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasRole('alumno')) {
    redirect('login.php');
}

$user = getCurrentUser();
$pageTitle = 'Aprendé Jugando';

// Progreso del estudiante (demo)
$studentProgress = [
    'total_points' => 350,
    'modules_completed' => 3,
    'badges' => ['beginner', 'safe_rider', 'mechanic'],
    'streak_days' => 5
];

// Módulos de aprendizaje con emojis
$modulos = [
    [
        'id' => 1,
        'titulo' => '🚲 Conocé tu Bicicleta',
        'descripcion' => 'Aprendé las partes de tu bici y cómo funciona cada una',
        'icono' => '🚲',
        'color' => '#2563eb',
        'puntos' => 100,
        'completado' => true,
        'preguntas' => 5
    ],
    [
        'id' => 2,
        'titulo' => '🛡️ Seguridad Vial Básica',
        'descripcion' => 'Señales de tránsito y reglas para circular seguro',
        'icono' => '🛡️',
        'color' => '#06b6d4',
        'puntos' => 150,
        'completado' => true,
        'preguntas' => 5
    ],
    [
        'id' => 3,
        'titulo' => '🔧 Mantenimiento Básico',
        'descripcion' => 'Cómo cuidar y mantener tu bicicleta en buen estado',
        'icono' => '🔧',
        'color' => '#2563eb',
        'puntos' => 100,
        'completado' => true,
        'preguntas' => 4
    ],
    [
        'id' => 4,
        'titulo' => '🌙 Seguridad Nocturna',
        'descripcion' => 'Cómo circular de noche de forma segura',
        'icono' => '🌙',
        'color' => '#06b6d4',
        'puntos' => 100,
        'completado' => false,
        'preguntas' => 4
    ],
    [
        'id' => 5,
        'titulo' => '🏥 Primeros Auxilios',
        'descripcion' => 'Qué hacer en caso de accidente o emergencia',
        'icono' => '🏥',
        'color' => '#0891b2',
        'puntos' => 150,
        'completado' => false,
        'preguntas' => 5
    ],
    [
        'id' => 6,
        'titulo' => '🚦 Señales de Tránsito',
        'descripcion' => 'Reconocé todas las señales que encontrás en la vía',
        'icono' => '🚦',
        'color' => '#2563eb',
        'puntos' => 120,
        'completado' => false,
        'preguntas' => 6
    ],
    [
        'id' => 7,
        'titulo' => '🌍 Eco-Ciclismo',
        'descripcion' => 'Cuidá el planeta usando tu bici de forma sustentable',
        'icono' => '🌍',
        'color' => '#06b6d4',
        'puntos' => 80,
        'completado' => false,
        'preguntas' => 4
    ],
    [
        'id' => 8,
        'titulo' => '🏆 Ciclista Experto',
        'descripcion' => 'Desafío final: demostrá todo lo que aprendiste',
        'icono' => '🏆',
        'color' => '#0891b2',
        'puntos' => 200,
        'completado' => false,
        'preguntas' => 10
    ]
];

// Calcular progreso total
$totalModules = count($modulos);
$completedModules = count(array_filter($modulos, fn($m) => $m['completado']));
$progressPercent = ($completedModules / $totalModules) * 100;

include __DIR__ . '/../../includes/header.php';
?>

<style>
/* Estilos de la página de aprendizaje */
.learn-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 1rem;
}

/* Header con stats */
.learn-header {
    text-align: center;
    margin-bottom: 2rem;
}

.learn-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.learn-subtitle {
    color: var(--text-secondary);
    margin-bottom: 1.5rem;
}

/* Stats cards */
.stats-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-lg);
    padding: 1rem;
    text-align: center;
}

.stat-card-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.5rem;
    font-size: 1.25rem;
}

.stat-card-icon.blue {
    background: rgba(37, 99, 235, 0.15);
}

.stat-card-icon.cyan {
    background: rgba(6, 182, 212, 0.15);
}

.stat-card-icon.green {
    background: rgba(34, 197, 94, 0.15);
}

.stat-card-icon.orange {
    background: rgba(249, 115, 22, 0.15);
}

.stat-card-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
}

.stat-card-label {
    font-size: 0.75rem;
    color: var(--text-muted);
}

/* Progress overall */
.progress-card {
    background: linear-gradient(135deg, #1e3a5f 0%, #134e4a 100%);
    border-radius: var(--border-radius-xl);
    padding: 1.5rem;
    margin-bottom: 2rem;
    color: white;
}

.progress-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.progress-card-title {
    font-weight: 600;
    font-size: 1rem;
}

.progress-card-percent {
    font-size: 1.25rem;
    font-weight: 700;
}

.progress-bar-large {
    height: 12px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 6px;
    overflow: hidden;
}

.progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #2563eb, #06b6d4);
    border-radius: 6px;
    transition: width 0.5s ease;
}

/* Badges */
.badges-section {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-xl);
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.badges-title {
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 1rem;
}

.badges-row {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.badge-item {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    position: relative;
}

.badge-item.earned {
    background: linear-gradient(135deg, #2563eb, #06b6d4);
    box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
}

.badge-item.locked {
    background: var(--bg-card-hover);
    opacity: 0.5;
}

.badge-item.locked::after {
    content: '🔒';
    position: absolute;
    font-size: 0.875rem;
    bottom: -5px;
    right: -5px;
}

/* Modules grid */
.modules-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 1rem;
}

.modules-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.module-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--border-radius-xl);
    padding: 1.5rem;
    transition: all var(--transition);
    position: relative;
    overflow: hidden;
}

.module-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.module-card.completed {
    border-color: #22c55e;
}

.module-card.completed::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #22c55e, #16a34a);
}

.module-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1rem;
}

.module-icon {
    width: 56px;
    height: 56px;
    border-radius: var(--border-radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    flex-shrink: 0;
}

.module-info h3 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
}

.module-info p {
    font-size: 0.8125rem;
    color: var(--text-secondary);
    margin: 0;
    line-height: 1.4;
}

.module-meta {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
    font-size: 0.8125rem;
}

.module-meta-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    color: var(--text-muted);
}

.module-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.module-points {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-weight: 600;
    color: #f59e0b;
}

.btn-module {
    padding: 0.625rem 1rem;
    border-radius: var(--border-radius);
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all var(--transition);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
}

.btn-module.primary {
    background: linear-gradient(135deg, #2563eb, #06b6d4);
    color: white;
    border: none;
}

.btn-module.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
}

.btn-module.completed {
    background: rgba(34, 197, 94, 0.15);
    color: #22c55e;
    border: 1px solid #22c55e;
}

/* Responsive */
@media (max-width: 768px) {
    .stats-row {
        grid-template-columns: repeat(2, 1fr);
    }

    .modules-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 480px) {
    .learn-container {
        padding: 0.5rem;
    }

    .badges-row {
        justify-content: center;
    }
}
</style>

<div class="learn-container">
    <!-- Header -->
    <div class="learn-header">
        <h1 class="learn-title">Aprendé Jugando</h1>
        <p class="learn-subtitle">Mirá los videos, respondé las preguntas y ganá puntos</p>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-card-icon blue">⭐</div>
            <div class="stat-card-value"><?= number_format($studentProgress['total_points']) ?></div>
            <div class="stat-card-label">Puntos Totales</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon cyan">📚</div>
            <div class="stat-card-value"><?= $completedModules ?>/<?= $totalModules ?></div>
            <div class="stat-card-label">Módulos</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon green">🏆</div>
            <div class="stat-card-value"><?= count($studentProgress['badges']) ?></div>
            <div class="stat-card-label">Insignias</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon orange">🔥</div>
            <div class="stat-card-value"><?= $studentProgress['streak_days'] ?></div>
            <div class="stat-card-label">Días seguidos</div>
        </div>
    </div>

    <!-- Progress general -->
    <div class="progress-card">
        <div class="progress-card-header">
            <span class="progress-card-title">Tu progreso general</span>
            <span class="progress-card-percent"><?= round($progressPercent) ?>%</span>
        </div>
        <div class="progress-bar-large">
            <div class="progress-bar-fill" style="width: <?= $progressPercent ?>%"></div>
        </div>
    </div>

    <!-- Insignias -->
    <div class="badges-section">
        <h2 class="badges-title">Tus Insignias</h2>
        <div class="badges-row">
            <div class="badge-item earned" title="Principiante">🚴</div>
            <div class="badge-item earned" title="Ciclista Seguro">🛡️</div>
            <div class="badge-item earned" title="Mecánico">🔧</div>
            <div class="badge-item locked" title="Ciclista Nocturno">🌙</div>
            <div class="badge-item locked" title="Socorrista">🏥</div>
            <div class="badge-item locked" title="Experto TuBi">🏆</div>
        </div>
    </div>

    <!-- Módulos -->
    <h2 class="modules-title">Módulos de Aprendizaje</h2>
    <div class="modules-grid">
        <?php foreach ($modulos as $modulo): ?>
        <div class="module-card <?= $modulo['completado'] ? 'completed' : '' ?>">
            <div class="module-header">
                <div class="module-icon" style="background: <?= $modulo['color'] ?>20;">
                    <?= $modulo['icono'] ?>
                </div>
                <div class="module-info">
                    <h3><?= e($modulo['titulo']) ?></h3>
                    <p><?= e($modulo['descripcion']) ?></p>
                </div>
            </div>
            <div class="module-meta">
                <span class="module-meta-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="5 3 19 12 5 21 5 3"/>
                    </svg>
                    1 video
                </span>
                <span class="module-meta-item">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    <?= $modulo['preguntas'] ?> preguntas
                </span>
            </div>
            <div class="module-footer">
                <span class="module-points">
                    ⭐ <?= $modulo['puntos'] ?> pts
                </span>
                <?php if ($modulo['completado']): ?>
                    <a href="<?= BASE_URL ?>pages/alumno/modulo.php?id=<?= $modulo['id'] ?>" class="btn-module completed">
                        ✓ Completado
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>pages/alumno/modulo.php?id=<?= $modulo['id'] ?>" class="btn-module primary">
                        Comenzar
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
