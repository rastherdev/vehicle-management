<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h1 class="h4 fw-semibold mb-1">Vehículos</h1>
        <p class="text-secondary small mb-0">Registra el consumo de combustible de tu flota.</p>
    </div>
    <div class="position-relative">
        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-secondary"></i>
        <input type="search" id="buscarVehiculo" class="form-control form-control-sm ps-5"
               placeholder="Buscar por placa o modelo…" autocomplete="off">
    </div>
</div>

<div class="row g-3" id="gridVehiculos"></div>

<p id="sinResultados" class="text-secondary small d-none">No se encontraron vehículos que coincidan con la búsqueda.</p>

<div class="card panel-card" id="sinVehiculos">
    <div class="card-body text-center py-5">
        <div class="empty-icon"><i class="bi bi-car-front"></i></div>
        <h2 class="h5 fw-semibold mt-3">Sin vehículos</h2>
        <p class="text-secondary mb-0">Registra vehículos en Configuración para comenzar.</p>
    </div>
</div>

<!-- Modal registrar carga de combustible -->
<div class="modal fade" id="modalCarga" tabindex="-1" aria-labelledby="tituloCarga" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title" id="tituloCarga">Registrar combustible</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="formErroresCarga" class="alert alert-danger py-2 small d-none" role="alert"></div>
                <form id="formCarga" novalidate>
                    <input type="hidden" name="vehiculo_id" id="carga-vehiculo-id">

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="carga-fecha" class="form-label small fw-medium">Fecha de carga</label>
                            <input type="date" class="form-control form-control-sm" id="carga-fecha" name="fecha" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="carga-odometro" class="form-label small fw-medium">Odómetro (km)</label>
                            <input type="number" class="form-control form-control-sm" id="carga-odometro" name="odometro"
                                   min="0" placeholder="Lectura del tablero" required>
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="carga-litros" class="form-label small fw-medium">Litros</label>
                            <input type="number" class="form-control form-control-sm" id="carga-litros" name="litros"
                                   step="0.01" min="0" placeholder="0.00" required>
                        </div>
                        <div class="col-6 col-md-4">
                            <label for="carga-monto" class="form-label small fw-medium">Monto ($)</label>
                            <input type="number" class="form-control form-control-sm" id="carga-monto" name="monto"
                                   step="0.01" min="0" placeholder="0.00" required>
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-end">
                            <div class="form-check form-switch mb-1">
                                <input class="form-check-input" type="checkbox" id="carga-tanque" name="tanque_lleno" value="1">
                                <label class="form-check-label small fw-medium" for="carga-tanque">Tanque lleno</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="carga-notas" class="form-label small fw-medium">Notas</label>
                            <textarea class="form-control form-control-sm" id="carga-notas" name="notas" rows="2"
                                      maxlength="500" placeholder="Opcional"></textarea>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="carga-foto-bomba" class="form-label small fw-medium">Foto bomba / gasolinera</label>
                            <input type="file" class="form-control form-control-sm" id="carga-foto-bomba"
                                   name="foto_bomba_gasolineria" accept="image/*">
                            <img class="foto-previa mt-2 d-none" data-previa="foto_bomba_gasolineria" alt="Vista previa">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="carga-foto-odometro" class="form-label small fw-medium">Foto odómetro</label>
                            <input type="file" class="form-control form-control-sm" id="carga-foto-odometro"
                                   name="foto_odometro" accept="image/*">
                            <img class="foto-previa mt-2 d-none" data-previa="foto_odometro" alt="Vista previa">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="carga-foto-ticket" class="form-label small fw-medium">Foto ticket de pago</label>
                            <input type="file" class="form-control form-control-sm" id="carga-foto-ticket"
                                   name="foto_ticket_pago" accept="image/*">
                            <img class="foto-previa mt-2 d-none" data-previa="foto_ticket_pago" alt="Vista previa">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-pine btn-sm" id="btnGuardarCarga">
                    <i class="bi bi-fuel-pump me-1"></i>Registrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal historial -->
<div class="modal fade" id="modalHistorial" tabindex="-1" aria-labelledby="tituloHistorial" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title" id="tituloHistorial">Historial de combustible</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive dt-scroll">
                    <table class="table table-dt w-100" id="tablaCargas">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Odómetro</th>
                                <th>Litros</th>
                                <th>Monto</th>
                                <th>Tanque</th>
                                <th>Evidencia</th>
                                <th>Notas</th>
                            </tr>
                        </thead>
                        <tbody id="cargasBody"></tbody>
                    </table>
                </div>
                <p class="small text-secondary mb-0 mt-2" id="cargasResumen"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal registrar gasto -->
<div class="modal fade" id="modalGasto" tabindex="-1" aria-labelledby="tituloGasto" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title" id="tituloGasto">Registrar gasto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="formErroresGasto" class="alert alert-danger py-2 small d-none" role="alert"></div>
                <form id="formGasto" novalidate>
                    <input type="hidden" name="vehiculo_id" id="gasto-vehiculo-id">

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="gasto-categoria" class="form-label small fw-medium">Categoría</label>
                            <select class="form-select form-select-sm" id="gasto-categoria" name="categoria" required>
                                <option value="">Selecciona…</option>
                                <option value="arreglo">Arreglo</option>
                                <option value="refacciones">Refacciones</option>
                                <option value="mantenimiento">Mantenimiento</option>
                                <option value="seguro">Seguro</option>
                                <option value="tenencia">Tenencia</option>
                                <option value="lavado">Lavado</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="gasto-fecha" class="form-label small fw-medium">Fecha del gasto</label>
                            <input type="date" class="form-control form-control-sm" id="gasto-fecha" name="fecha" required>
                        </div>
                        <div class="col-12">
                            <label for="gasto-concepto" class="form-label small fw-medium">Concepto</label>
                            <input type="text" class="form-control form-control-sm" id="gasto-concepto" name="concepto"
                                   maxlength="100" placeholder="Ej. Cambio de aceite" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="gasto-monto" class="form-label small fw-medium">Monto ($)</label>
                            <input type="number" class="form-control form-control-sm" id="gasto-monto" name="monto"
                                   step="0.01" min="0" placeholder="0.00" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="gasto-foto" class="form-label small fw-medium">Foto comprobante</label>
                            <input type="file" class="form-control form-control-sm" id="gasto-foto"
                                   name="foto_comprobante" accept="image/*">
                            <img class="foto-previa mt-2 d-none" alt="Vista previa">
                        </div>
                        <div class="col-12">
                            <label for="gasto-notas" class="form-label small fw-medium">Notas</label>
                            <textarea class="form-control form-control-sm" id="gasto-notas" name="notas" rows="2"
                                      maxlength="500" placeholder="Opcional"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-pine btn-sm" id="btnGuardarGasto">
                    <i class="bi bi-check-lg me-1"></i>Registrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal historial de gastos -->
<div class="modal fade" id="modalHistorialGastos" tabindex="-1" aria-labelledby="tituloHistorialGastos" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title" id="tituloHistorialGastos">Historial de gastos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive dt-scroll">
                    <table class="table table-dt w-100" id="tablaGastos">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Categoría</th>
                                <th>Concepto</th>
                                <th>Monto</th>
                                <th>Comprobante</th>
                                <th>Notas</th>
                            </tr>
                        </thead>
                        <tbody id="gastosBody"></tbody>
                    </table>
                </div>
                <p class="small text-secondary mb-0 mt-2" id="gastosResumen"></p>
            </div>
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
