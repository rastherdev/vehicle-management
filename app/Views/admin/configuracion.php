<div class="mb-4">
    <h1 class="h4 fw-semibold mb-1">Configuración</h1>
    <p class="text-secondary small mb-0">Gestiona las áreas, conductores y vehículos de la empresa.</p>
</div>

<div class="accordion" id="acordeonConfig">
    <!-- Áreas -->
    <div class="accordion-item panel-card">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseAreas" aria-expanded="false" aria-controls="collapseAreas">
                <i class="bi bi-building me-2"></i>Áreas
            </button>
        </h2>
        <div id="collapseAreas" class="accordion-collapse collapse" data-bs-parent="#acordeonConfig" data-entidad="areas">
            <div class="accordion-body">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                    <span class="small text-secondary">Áreas de la empresa donde se asignan conductores y vehículos.</span>
                    <button type="button" class="btn btn-pine btn-sm text-nowrap" data-nuevo="areas">
                        <i class="bi bi-plus-lg me-1"></i>Nueva área
                    </button>
                </div>
                <div class="table-responsive dt-scroll">
                    <table id="tablaAreas" class="table table-dt w-100"></table>
                </div>
            </div>
        </div>
    </div>

    <!-- Conductores -->
    <div class="accordion-item panel-card">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseConductores" aria-expanded="false" aria-controls="collapseConductores">
                <i class="bi bi-people me-2"></i>Conductores
            </button>
        </h2>
        <div id="collapseConductores" class="accordion-collapse collapse" data-bs-parent="#acordeonConfig" data-entidad="conductores">
            <div class="accordion-body">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                    <span class="small text-secondary">Conductores asignables a vehículos y a un área.</span>
                    <button type="button" class="btn btn-pine btn-sm text-nowrap" data-nuevo="conductores">
                        <i class="bi bi-plus-lg me-1"></i>Nuevo conductor
                    </button>
                </div>
                <div class="table-responsive dt-scroll">
                    <table id="tablaConductores" class="table table-dt w-100"></table>
                </div>
            </div>
        </div>
    </div>

    <!-- Vehículos -->
    <div class="accordion-item panel-card">
        <h2 class="accordion-header">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                    data-bs-target="#collapseVehiculos" aria-expanded="false" aria-controls="collapseVehiculos">
                <i class="bi bi-car-front me-2"></i>Vehículos
            </button>
        </h2>
        <div id="collapseVehiculos" class="accordion-collapse collapse" data-bs-parent="#acordeonConfig" data-entidad="vehiculos">
            <div class="accordion-body">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                    <span class="small text-secondary">Vehículos asignables a conductores y a un área.</span>
                    <button type="button" class="btn btn-pine btn-sm text-nowrap" data-nuevo="vehiculos">
                        <i class="bi bi-plus-lg me-1"></i>Nuevo vehículo
                    </button>
                </div>
                <div class="table-responsive dt-scroll">
                    <table id="tablaVehiculos" class="table table-dt w-100"></table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modales de formulario -->
<div class="modal fade" id="modalFormAreas" tabindex="-1" aria-labelledby="tituloFormAreas" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title" id="tituloFormAreas">Nueva área</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="formErroresAreas" class="alert alert-danger py-2 small d-none" role="alert"></div>
                <form id="formAreas" novalidate></form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-pine btn-sm" data-guardar="areas">
                    <i class="bi bi-check-lg me-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalFormConductores" tabindex="-1" aria-labelledby="tituloFormConductores" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title" id="tituloFormConductores">Nuevo conductor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="formErroresConductores" class="alert alert-danger py-2 small d-none" role="alert"></div>
                <form id="formConductores" novalidate></form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-pine btn-sm" data-guardar="conductores">
                    <i class="bi bi-check-lg me-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalFormVehiculos" tabindex="-1" aria-labelledby="tituloFormVehiculos" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title" id="tituloFormVehiculos">Nuevo vehículo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="formErroresVehiculos" class="alert alert-danger py-2 small d-none" role="alert"></div>
                <form id="formVehiculos" novalidate></form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-pine btn-sm" data-guardar="vehiculos">
                    <i class="bi bi-check-lg me-1"></i>Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modales de detalle -->
<div class="modal fade" id="modalDetalleAreas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title">Detalle del área</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="detalleAreas"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalleConductores" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title">Detalle del conductor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="detalleConductores"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalleVehiculos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title">Detalle del vehículo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="detalleVehiculos"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación de borrado -->
<div class="modal fade" id="modalConfirmar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-3">
                <h5 class="modal-title">Eliminar registro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0 small">¿Deseas eliminar este registro? Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger btn-sm" id="btnConfirmarBorrar">
                    <i class="bi bi-trash me-1"></i>Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast de notificaciones -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toastCrud" class="toast align-items-center" role="status" aria-live="polite" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body small" id="toastCrudMensaje"></div>
            <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
    </div>
</div>
