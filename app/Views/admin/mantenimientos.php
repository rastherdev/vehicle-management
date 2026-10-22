<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h1 class="h4 fw-semibold mb-1">Mantenimientos</h1>
        <p class="text-secondary small mb-0">Programa y da seguimiento a los mantenimientos de tu flota.</p>
    </div>
    <button type="button" class="btn btn-pine btn-sm text-nowrap" data-accion="nuevo">
        <i class="bi bi-plus-lg me-1"></i>Nuevo mantenimiento
    </button>
</div>

<div class="d-flex flex-wrap gap-2 mb-3" id="chipsEstado">
    <button type="button" class="btn chip-estado active" data-estado="">Todas</button>
    <button type="button" class="btn chip-estado" data-estado="pendiente">Pendientes</button>
    <button type="button" class="btn chip-estado" data-estado="vencido">Vencidos</button>
    <button type="button" class="btn chip-estado" data-estado="en_proceso">En proceso</button>
    <button type="button" class="btn chip-estado" data-estado="completado">Completados</button>
    <button type="button" class="btn chip-estado" data-estado="cancelado">Cancelados</button>
</div>

<div class="card panel-card">
    <div class="table-responsive dt-scroll">
        <table class="table table-dt w-100" id="tablaMantenimientos">
            <thead>
                <tr>
                    <th>Vehículo</th>
                    <th>Tipo</th>
                    <th>Prioridad</th>
                    <th>Disparador</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="mantBody"></tbody>
        </table>
    </div>
</div>

<!-- Modal nuevo mantenimiento -->
<div class="modal fade" id="modalMant" tabindex="-1" aria-labelledby="tituloMant" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title" id="tituloMant">Nuevo mantenimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="formErroresMant" class="alert alert-danger py-2 small d-none" role="alert"></div>
                <form id="formMant" novalidate>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="mant-vehiculo" class="form-label small fw-medium">Vehículo</label>
                            <select class="form-select form-select-sm" id="mant-vehiculo" name="vehiculo_id" required></select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="mant-tipo" class="form-label small fw-medium">Tipo</label>
                            <select class="form-select form-select-sm" id="mant-tipo" name="tipo" required>
                                <option value="">Selecciona…</option>
                                <option value="aceite">Cambio de aceite</option>
                                <option value="llantas">Llantas</option>
                                <option value="frenos">Frenos</option>
                                <option value="filtros">Filtros</option>
                                <option value="revision_general">Revisión general</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="mant-prioridad" class="form-label small fw-medium">Prioridad</label>
                            <select class="form-select form-select-sm" id="mant-prioridad" name="prioridad">
                                <option value="baja">Baja</option>
                                <option value="media" selected>Media</option>
                                <option value="alta">Alta</option>
                                <option value="critica">Crítica</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="mant-costo" class="form-label small fw-medium">Costo estimado ($)</label>
                            <input type="number" class="form-control form-control-sm" id="mant-costo" name="costo_estimado"
                                   step="0.01" min="0" placeholder="Opcional">
                        </div>
                        <div class="col-12">
                            <p class="form-label small fw-medium mb-2">Disparador — ¿cuándo se debe hacer? <span class="text-secondary">(al menos uno)</span></p>
                            <div class="row g-2">
                                <div class="col-6 col-md-3">
                                    <input type="date" class="form-control form-control-sm" name="fecha_programada" aria-label="Fecha programada">
                                    <div class="form-text">Fecha</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <input type="number" class="form-control form-control-sm" name="odometro_programado" min="0" placeholder="Ej. 50000" aria-label="Odómetro programado">
                                    <div class="form-text">Odómetro (km)</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <input type="number" class="form-control form-control-sm" name="intervalo_km" min="1" placeholder="Ej. 5000" aria-label="Intervalo en km">
                                    <div class="form-text">Cada X km</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <input type="number" class="form-control form-control-sm" name="intervalo_dias" min="1" placeholder="Ej. 180" aria-label="Intervalo en días">
                                    <div class="form-text">Cada X días</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="mant-descripcion" class="form-label small fw-medium">Descripción</label>
                            <textarea class="form-control form-control-sm" id="mant-descripcion" name="descripcion" rows="2"
                                      maxlength="500" placeholder="Detalle de qué se va a hacer"></textarea>
                        </div>
                        <div class="col-12">
                            <label for="mant-notas" class="form-label small fw-medium">Notas</label>
                            <textarea class="form-control form-control-sm" id="mant-notas" name="notas" rows="2"
                                      maxlength="500" placeholder="Opcional"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-pine btn-sm" id="btnGuardarMant">
                    <i class="bi bi-check-lg me-1"></i>Programar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal completar -->
<div class="modal fade" id="modalCompletar" tabindex="-1" aria-labelledby="tituloCompletar" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title" id="tituloCompletar">Completar mantenimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="formErroresCompletar" class="alert alert-danger py-2 small d-none" role="alert"></div>
                <form id="formCompletar" novalidate>
                    <div class="mb-3">
                        <label for="comp-odometro" class="form-label small fw-medium">Odómetro al completar (km)</label>
                        <input type="number" class="form-control form-control-sm" id="comp-odometro"
                               name="odometro_completado" min="0" placeholder="Opcional">
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="comp-gasto" name="registrar_gasto" value="1">
                        <label class="form-check-label small fw-medium" for="comp-gasto">Registrar gasto al completar</label>
                    </div>
                    <div id="compCamposGasto" class="d-none">
                        <div class="mb-2">
                            <label for="comp-monto" class="form-label small fw-medium">Monto ($)</label>
                            <input type="number" class="form-control form-control-sm" id="comp-monto" name="monto"
                                   step="0.01" min="0" placeholder="0.00">
                        </div>
                        <div class="mb-2">
                            <label for="comp-categoria" class="form-label small fw-medium">Categoría del gasto</label>
                            <select class="form-select form-select-sm" id="comp-categoria" name="categoria">
                                <option value="mantenimiento" selected>Mantenimiento</option>
                                <option value="refacciones">Refacciones</option>
                                <option value="arreglo">Arreglo</option>
                                <option value="seguro">Seguro</option>
                                <option value="tenencia">Tenencia</option>
                                <option value="lavado">Lavado</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label for="comp-concepto" class="form-label small fw-medium">Concepto</label>
                            <input type="text" class="form-control form-control-sm" id="comp-concepto" name="concepto"
                                   maxlength="100" placeholder="Se genera automáticamente si se deja vacío">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-pine btn-sm" id="btnConfirmarCompletar">
                    <i class="bi bi-check2-circle me-1"></i>Completar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal detalle -->
<div class="modal fade" id="modalDetalleMant" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title">Detalle del mantenimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="detalleMantBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cerrar</button>
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
