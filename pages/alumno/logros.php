<?php
/**
 * TUBI 2026 - Mis Logros
 * Sistema de logros y achievements
 */
require_once __DIR__ . '/../../config/config.php';

if (!isLoggedIn() || !hasRole('alumno')) {
    redirect('login.php');
}

$user = getCurrentUser();
$pageTitle = 'Mis Logros';

// Obtener logros del sistema
$todosLosLogros = getData('logros');
if (!$todosLosLogros) $todosLosLogros = array();

// Logros obtenidos por el alumno (simulado - en producción vendría de BD)
$logrosObtenidosIds = array(1, 2, 5);

// Estadísticas
$totalLogros = count($todosLosLogros);
$logrosObtenidos = count($logrosObtenidosIds);
$porcentajeCompletado = $totalLogros > 0 ? round(($logrosObtenidos / $totalLogros) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> - TuBi 2026</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚲</text></svg>">
    <style>
        .logros-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .logros-header {
            background: linear-gradient(135deg, #354393 0%, #4aacc4 100%);
            color: white;
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            text-align: center;
        }

        .logros-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
        }

        .logros-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .logros-stat {
            background: rgba(255, 255, 255, 0.15);
            padding: 1rem;
            border-radius: 12px;
            text-align: center;
        }

        .logros-stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .logros-stat-label {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .logros-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .logro-card {
            background: var(--bg-card);
            border: 2px solid var(--border-color);
            border-radius: 16px;
            padding: 1.5rem;
            transition: all var(--transition);
            position: relative;
            overflow: hidden;
        }

        .logro-card.obtenido {
            border-color: #22c55e;
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, var(--bg-card) 100%);
        }

        .logro-card.bloqueado {
            opacity: 0.6;
            filter: grayscale(0.5);
        }

        .logro-card:hover:not(.bloqueado) {
            transform: translateY(-4px);
            border-color: var(--color-primary);
            box-shadow: var(--shadow-lg);
        }

        .logro-icon {
            font-size: 3rem;
            text-align: center;
            margin-bottom: 1rem;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
        }

        .logro-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .logro-description {
            color: var(--text-secondary);
            margin-bottom: 1rem;
            line-height: 1.5;
        }

        .logro-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
        }

        .logro-points {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #f59e0b;
            font-weight: 600;
        }

        .logro-status {
            padding: 0.25rem 0.75rem;
            border-radius: 999px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .logro-status.obtenido {
            background: #22c55e;
            color: white;
        }

        .logro-status.bloqueado {
            background: var(--bg-card-hover);
            color: var(--text-muted);
        }

        .ribbon {
            position: absolute;
            top: 15px;
            right: -30px;
            background: #22c55e;
            color: white;
            padding: 0.25rem 2rem;
            transform: rotate(45deg);
            font-size: 0.75rem;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        .progress-bar-container {
            background: rgba(255, 255, 255, 0.15);
            height: 8px;
            border-radius: 999px;
            overflow: hidden;
            margin-top: 1rem;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #22c55e 0%, #16a34a 100%);
            transition: width 1s ease;
        }
    </style>
</head>
<body class="tubi-bg-pattern" data-theme="dark">
    <div class="page-wrapper">
        <!-- Header simple -->
        <header class="header">
            <div class="header-inner">
                <a href="<?= BASE_URL ?>" class="logo" style="display: flex; align-items: center;">
                </a>
                <a href="<?= BASE_URL ?>pages/alumno/dashboard.php" class="btn btn-secondary btn-sm">← Volver al Panel</a>
            </div>
        </header>

        <main class="main-content">
            <div class="logros-container">
                <!-- Header con estadísticas -->
                <div class="logros-header">
                    <h1>🏆 Mis Logros</h1>
                    <p>Desbloquea logros completando módulos y desafíos</p>

                    <div class="logros-stats">
                        <div class="logros-stat">
                            <div class="logros-stat-value"><?= $logrosObtenidos ?>/<?= $totalLogros ?></div>
                            <div class="logros-stat-label">Logros Obtenidos</div>
                        </div>
                        <div class="logros-stat">
                            <div class="logros-stat-value"><?= $porcentajeCompletado ?>%</div>
                            <div class="logros-stat-label">Completado</div>
                        </div>
                        <div class="logros-stat">
                            <div class="logros-stat-value"><?php $puntosGanados = 0; foreach ($todosLosLogros as $l) { if (in_array($l['id'], $logrosObtenidosIds)) $puntosGanados += $l['puntos']; } echo $puntosGanados; ?></div>
                            <div class="logros-stat-label">Puntos Ganados</div>
                        </div>
                    </div>

                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: <?= $porcentajeCompletado ?>%"></div>
                    </div>
                </div>

                <!-- Grid de logros -->
                <div class="logros-grid">
                    <?php foreach ($todosLosLogros as $logro):
                        $obtenido = in_array($logro['id'], $logrosObtenidosIds);
                    ?>
                    <div class="logro-card <?= $obtenido ? 'obtenido' : 'bloqueado' ?>">
                        <?php if ($obtenido): ?>
                        <div class="ribbon">✓ OBTENIDO</div>
                        <?php endif; ?>

                        <div class="logro-icon"><?= $logro['icono'] ?></div>
                        <h3 class="logro-title"><?= e($logro['titulo']) ?></h3>
                        <p class="logro-description"><?= e($logro['descripcion']) ?></p>

                        <div class="logro-footer">
                            <div class="logro-points">
                                <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                </svg>
                                <span>+<?= $logro['puntos'] ?> pts</span>
                            </div>
                            <div class="logro-status <?= $obtenido ? 'obtenido' : 'bloqueado' ?>">
                                <?= $obtenido ? '✓ Obtenido' : '🔒 Bloqueado' ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>

        <?php include __DIR__ . '/../../includes/footer.php'; ?>
    </div>
</body>
</html>
