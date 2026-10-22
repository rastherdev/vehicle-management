<div class="mb-4">
    <h1 class="h4 fw-semibold mb-1">Reportes y gráficas</h1>
    <p class="text-secondary small mb-0">Analiza gastos, consumo de combustible y mantenimientos de tu flota.</p>
</div>

<div class="accordion" id="acordeonReportes">

    <!-- 1. Reportes generales -->
    <div class="accordion-item panel-card">
        <h2 class="accordion-header">
            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseGastos" aria-expanded="true" aria-controls="collapseGastos">
                <i class="bi bi-receipt-cutoff me-2"></i>Reportes generales
            </button>
        </h2>
        <div id="collapseGastos" class="accordion-collapse collapse show" data-bs-parent="#acordeonReportes">
            <div class="accordion-body">
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-medium">Desde</label>
                        <input type="date" class="form-control form-control-sm" id="fGastosDesde">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-medium">Hasta</label>
                        <input type="date" class="form-control form-control-sm" id="fGastosHasta">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-medium">Vehículo</label>
                        <select class="form-select form-select-sm" id="fGastosVehiculo"></select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-medium">Categoría</label>
                        <select class="form-select form-select-sm" id="fGastosCategoria">
                            <option value="">Todas</option>
                            <option value="arreglo">Arreglo</option>
                            <option value="refacciones">Refacciones</option>
                            <option value="mantenimiento">Mantenimiento</option>
                            <option value="seguro">Seguro</option>
                            <option value="tenencia">Tenencia</option>
                            <option value="lavado">Lavado</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                        <button type="button" class="btn btn-pine btn-sm flex-fill" id="btnAplicarGastos">
                            <i class="bi bi-funnel me-1"></i>Aplicar
                        </button>
                        <button type="button" class="btn btn-outline-pine btn-sm flex-fill" id="btnExportarXlsx" title="Exportar a Excel">
                            <i class="bi bi-file-earmark-excel me-1"></i>XLSX
                        </button>
                    </div>
                </div>

                <div class="row g-3 mb-3" id="kpiGastos">
                    <div class="col-6 col-lg-3">
                        <div class="card kpi-card h-100">
                            <div class="card-body">
                                <span class="kpi-label">Total gastos</span>
                                <div class="kpi-valor" id="kpiTotalMonto">$0</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="card kpi-card h-100">
                            <div class="card-body">
                                <span class="kpi-label">Registros</span>
                                <div class="kpi-valor" id="kpiTotalRegistros">0</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="card kpi-card h-100">
                            <div class="card-body">
                                <span class="kpi-label">Categoría más costosa</span>
                                <div class="kpi-valor kpi-small" id="kpiCategoriaTop">—</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="card kpi-card h-100">
                            <div class="card-body">
                                <span class="kpi-label">Vehículo con más gasto</span>
                                <div class="kpi-valor kpi-small" id="kpiVehiculoTop">—</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive dt-scroll">
                    <table class="table table-dt w-100" id="tablaGastosReporte">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Vehículo</th>
                                <th>Categoría</th>
                                <th>Concepto</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Gráficas de consumo de combustible -->
    <div class="accordion-item panel-card">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseConsumo" aria-expanded="false" aria-controls="collapseConsumo">
                <i class="bi bi-fuel-pump me-2"></i>Gráficas de consumo de combustible
            </button>
        </h2>
        <div id="collapseConsumo" class="accordion-collapse collapse" data-bs-parent="#acordeonReportes">
            <div class="accordion-body">
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-medium">Desde</label>
                        <input type="date" class="form-control form-control-sm" id="fCargasDesde">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-medium">Hasta</label>
                        <input type="date" class="form-control form-control-sm" id="fCargasHasta">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-medium">Área</label>
                        <select class="form-select form-select-sm" id="fCargasArea"></select>
                    </div>
                    <div class="col-12 col-md-5">
                        <label class="form-label small fw-medium">Vehículos</label>
                        <select class="form-select form-select-sm" id="fCargasVehiculos" multiple size="3"></select>
                    </div>
                </div>

                <div class="row g-3 mb-3" id="kpiConsumo">
                    <div class="col-6 col-lg-3">
                        <div class="card kpi-card h-100">
                            <div class="card-body">
                                <span class="kpi-label">Litros consumidos</span>
                                <div class="kpi-valor" id="kpiLitros">0 L</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="card kpi-card h-100">
                            <div class="card-body">
                                <span class="kpi-label">Dinero en combustible</span>
                                <div class="kpi-valor" id="kpiDineroCargas">$0</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="card kpi-card h-100">
                            <div class="card-body">
                                <span class="kpi-label">Precio promedio por litro</span>
                                <div class="kpi-valor" id="kpiPrecioLitro">$0</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="card kpi-card h-100">
                            <div class="card-body">
                                <span class="kpi-label">Cargas registradas</span>
                                <div class="kpi-valor" id="kpiCargas">0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-lg-6">
                        <div class="card chart-card h-100">
                            <div class="card-body">
                                <h3 class="chart-titulo">Litros por vehículo</h3>
                                <canvas id="graficaLitros"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="card chart-card h-100">
                            <div class="card-body">
                                <h3 class="chart-titulo">Dinero en combustible por mes</h3>
                                <canvas id="graficaMensual"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="card chart-card h-100">
                            <div class="card-body">
                                <h3 class="chart-titulo">Precio por litro por vehículo</h3>
                                <canvas id="graficaPrecio"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="card chart-card h-100">
                            <div class="card-body">
                                <h3 class="chart-titulo">Kilómetros recorridos por vehículo</h3>
                                <canvas id="graficaKm"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Impacto de combustible -->
    <div class="accordion-item panel-card">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseImpacto" aria-expanded="false" aria-controls="collapseImpacto">
                <i class="bi bi-activity me-2"></i>Impacto de combustible
            </button>
        </h2>
        <div id="collapseImpacto" class="accordion-collapse collapse" data-bs-parent="#acordeonReportes">
            <div class="accordion-body">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary"></i>
                        <input type="search" id="buscarImpacto" class="form-control form-control-sm ps-5"
                               placeholder="Buscar por placa o modelo…" autocomplete="off">
                    </div>
                    <button type="button" class="btn btn-outline-pine btn-sm" id="btnExportarPdf">
                        <i class="bi bi-file-earmark-pdf me-1"></i>Exportar PDF
                    </button>
                </div>

                <div class="row g-3" id="gridImpacto"></div>
                <p id="sinImpacto" class="text-secondary small d-none">No hay vehículos que coincidan con la búsqueda.</p>
            </div>
        </div>
    </div>

    <!-- 4. Gráficas de mantenimientos -->
    <div class="accordion-item panel-card">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseMant" aria-expanded="false" aria-controls="collapseMant">
                <i class="bi bi-tools me-2"></i>Gráficas de mantenimientos
            </button>
        </h2>
        <div id="collapseMant" class="accordion-collapse collapse" data-bs-parent="#acordeonReportes">
            <div class="accordion-body">
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-medium">Desde</label>
                        <input type="date" class="form-control form-control-sm" id="fMantDesde">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-medium">Hasta</label>
                        <input type="date" class="form-control form-control-sm" id="fMantHasta">
                    </div>
                    <div class="col-6 col-md-4">
                        <label class="form-label small fw-medium">Vehículo</label>
                        <select class="form-select form-select-sm" id="fMantVehiculo"></select>
                    </div>
                </div>

                <div class="row g-3 mb-3" id="kpiMant">
                    <div class="col-6 col-lg-4">
                        <div class="card kpi-card h-100">
                            <div class="card-body">
                                <span class="kpi-label">Mantenimientos completados</span>
                                <div class="kpi-valor" id="kpiMantTotal">0</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-4">
                        <div class="card kpi-card h-100">
                            <div class="card-body">
                                <span class="kpi-label">Costo total real</span>
                                <div class="kpi-valor" id="kpiMantCosto">$0</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-4">
                        <div class="card kpi-card h-100">
                            <div class="card-body">
                                <span class="kpi-label">Tipo más frecuente</span>
                                <div class="kpi-valor kpi-small" id="kpiMantTipo">—</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-lg-6">
                        <div class="card chart-card h-100">
                            <div class="card-body">
                                <h3 class="chart-titulo">Completados por mes</h3>
                                <canvas id="graficaMantMensual"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="card chart-card h-100">
                            <div class="card-body">
                                <h3 class="chart-titulo">Distribución por tipo</h3>
                                <canvas id="graficaMantTipos"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card chart-card h-100">
                            <div class="card-body">
                                <h3 class="chart-titulo">Costo por vehículo</h3>
                                <canvas id="graficaMantCosto"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toastCrud" class="toast align-items-center" role="status" aria-live="polite" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body small" id="toastCrudMensaje"></div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
    </div>
</div>
