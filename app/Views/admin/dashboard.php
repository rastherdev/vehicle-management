<div class="dash-hero mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <p class="dash-fecha mb-1" id="dashFecha"></p>
            <h1 class="dash-saludo mb-1">Hola, <?= esc($userName) ?> 👋</h1>
            <p class="mb-0 dash-sub">Este es el estado de tu flota hoy.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap" id="accesosRapidos">
            <a class="btn btn-light btn-sm" href="<?= site_url('admin/vehiculos') ?>">
                <i class="bi bi-fuel-pump me-1 text-pine"></i>Cargar combustible
            </a>
            <a class="btn btn-light btn-sm" href="<?= site_url('admin/mantenimientos') ?>">
                <i class="bi bi-tools me-1 text-pine"></i>Programar mantenimiento
            </a>
            <a class="btn btn-light btn-sm" href="<?= site_url('admin/reportes') ?>">
                <i class="bi bi-graph-up me-1 text-pine"></i>Ver reportes
            </a>
        </div>
    </div>
</div>

<div class="row g-3 mb-3" id="kpiDash">
    <div class="col-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon stat-icon-pine"><i class="bi bi-car-front"></i></span>
                <div>
                    <div class="stat-valor" id="kpiVehiculosActivos">—</div>
                    <div class="stat-label">Vehículos activos</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon stat-icon-fern"><i class="bi bi-fuel-pump"></i></span>
                <div>
                    <div class="stat-valor" id="kpiDineroCombustible">—</div>
                    <div class="stat-label">Combustible este mes</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon stat-icon-mint"><i class="bi bi-receipt"></i></span>
                <div>
                    <div class="stat-valor" id="kpiDineroGastos">—</div>
                    <div class="stat-label">Otros gastos este mes</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon stat-icon-stone"><i class="bi bi-exclamation-triangle"></i></span>
                <div>
                    <div class="stat-valor" id="kpiMantAtencion">—</div>
                    <div class="stat-label">Mantenimientos por atender</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-xl-8">
        <div class="card chart-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="chart-titulo mb-0">Dinero por mes</h2>
                    <div class="d-flex gap-3 small">
                        <span><span class="leyenda-dot" style="background:#184B44"></span> Combustible</span>
                        <span><span class="leyenda-dot" style="background:#56AD71"></span> Otros gastos</span>
                    </div>
                </div>
                <canvas id="graficaMensualDash"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card chart-card h-100">
            <div class="card-body">
                <h2 class="chart-titulo">Estado de la flota</h2>
                <canvas id="graficaFlota"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-6">
        <div class="card panel-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="chart-titulo mb-0"><i class="bi bi-calendar-event me-1 text-pine"></i>Próximos mantenimientos</h2>
                    <a href="<?= site_url('admin/mantenimientos') ?>" class="small text-pine text-decoration-none">Ver todos</a>
                </div>
                <div id="listaProximos"></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card panel-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="chart-titulo mb-0"><i class="bi bi-activity me-1 text-pine"></i>Alertas de rendimiento</h2>
                    <a href="<?= site_url('admin/reportes') ?>" class="small text-pine text-decoration-none">Ver impacto</a>
                </div>
                <div id="listaAlertas"></div>
            </div>
        </div>
    </div>
</div>

