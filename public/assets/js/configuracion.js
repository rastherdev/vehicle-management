(function ($) {
    'use strict';

    const BASE = $('meta[name="base-url"]').attr('content');

    // CSRF: lee cookie fresca en cada request (token puede regenerarse)
    $(document).ajaxSend(function (e, xhr) {
        const m = document.cookie.match(/(?:^|;\s*)csrf_cookie_name=([^;]+)/);
        const token = m ? decodeURIComponent(m[1]) : $('meta[name="csrf-token"]').attr('content');
        xhr.setRequestHeader('X-CSRF-TOKEN', token);
    });

    const esc = (v) => $('<span>').text(v ?? '').html();
    const cap = (s) => s.charAt(0).toUpperCase() + s.slice(1);

    const LABELS = {
        estado: { 1: 'Activo', 0: 'Inactivo' },
        combustible: { gasolina: 'Gasolina', diesel: 'Diesel' },
        tipoVehiculo: { automovil: 'Automóvil', camioneta: 'Camioneta', camion: 'Camión', motocicleta: 'Motocicleta', maquinaria: 'Maquinaria' },
        cilindraje: { v4: 'V4', v6: 'V6', v8: 'V8', v10: 'V10', v12: 'V12', otro: 'Otro' },
    };

    const OPTIONS = { areas: [], conductores: [] };
    const tablas = {};
    let pendienteBorrar = null;

    const IDIOMA_ES = {
        emptyTable: 'Sin registros',
        info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
        infoEmpty: 'Sin registros',
        infoFiltered: '(filtrado de _MAX_ registros)',
        lengthMenu: 'Mostrar _MENU_',
        loadingRecords: 'Cargando…',
        processing: 'Procesando…',
        search: 'Buscar:',
        zeroRecords: 'Sin resultados',
        paginate: {
            first: '«',
            last: '»',
            next: '›',
            previous: '‹',
        },
    };

    const badgeEstado = (v) => v == 1
        ? '<span class="badge-estado activo">Activo</span>'
        : '<span class="badge-estado inactivo">Inactivo</span>';

    const opcionesEstado = () => [
        { v: '1', t: 'Activo' },
        { v: '0', t: 'Inactivo' },
    ];

    const opcionesAreas = () => OPTIONS.areas.map((a) => ({ v: String(a.id), t: a.nombre }));

    const opcionesConductores = () => OPTIONS.conductores.map((c) => ({
        v: String(c.id),
        t: `${c.nombres} ${c.apellidos}`,
    }));

    const acciones = (entidad, row) => `
        <div class="d-flex gap-1">
            <button type="button" class="btn-icon" data-accion="ver" data-entidad="${entidad}" data-id="${row.id}" title="Ver detalles"><i class="bi bi-eye"></i></button>
            <button type="button" class="btn-icon" data-accion="editar" data-entidad="${entidad}" data-id="${row.id}" title="Editar"><i class="bi bi-pencil"></i></button>
            <button type="button" class="btn-icon danger" data-accion="borrar" data-entidad="${entidad}" data-id="${row.id}" title="Eliminar"><i class="bi bi-trash"></i></button>
        </div>`;

    const CONFIG = {
        areas: {
            titulo: 'área',
            tituloNuevo: 'Nueva área',
            url: 'admin/configuracion/areas',
            columns: [
                { data: 'id', title: 'ID', width: '60px' },
                { data: 'nombre', title: 'Nombre' },
                { data: 'descripcion', title: 'Descripción', render: (d) => d ? esc(d) : '<span class="text-secondary">—</span>' },
                { data: 'estado', title: 'Estado', width: '110px', render: badgeEstado },
                { data: null, title: 'Acciones', orderable: false, searchable: false, width: '120px', render: (d, t, row) => acciones('areas', row) },
            ],
            fields: [
                { name: 'nombre', label: 'Nombre', type: 'text', required: true, maxlength: 100, ph: 'Ej. Operaciones' },
                { name: 'descripcion', label: 'Descripción', type: 'textarea', rows: 3, maxlength: 500, ph: 'Descripción breve del área' },
                { name: 'estado', label: 'Estado', type: 'select', options: opcionesEstado(), required: true },
            ],
            detalle: (r) => [
                ['Nombre', esc(r.nombre)],
                ['Descripción', r.descripcion ? esc(r.descripcion) : '—'],
                ['Estado', badgeEstado(r.estado)],
            ],
        },
        conductores: {
            titulo: 'conductor',
            tituloNuevo: 'Nuevo conductor',
            url: 'admin/configuracion/conductores',
            columns: [
                { data: 'id', title: 'ID', width: '60px' },
                { data: 'nombres', title: 'Nombres' },
                { data: 'apellidos', title: 'Apellidos' },
                { data: 'telefono', title: 'Teléfono' },
                { data: 'area', title: 'Área', render: (d) => d ? esc(d) : '<span class="text-secondary">—</span>' },
                { data: 'estado', title: 'Estado', width: '110px', render: badgeEstado },
                { data: null, title: 'Acciones', orderable: false, searchable: false, width: '120px', render: (d, t, row) => acciones('conductores', row) },
            ],
            fields: [
                { name: 'nombres', label: 'Nombres', type: 'text', required: true, maxlength: 100, ph: 'Ej. Juan Carlos' },
                { name: 'apellidos', label: 'Apellidos', type: 'text', required: true, maxlength: 100, ph: 'Ej. Pérez Gómez' },
                { name: 'telefono', label: 'Teléfono', type: 'text', required: true, maxlength: 20, ph: 'Ej. 300 123 4567' },
                { name: 'area_id', label: 'Área', type: 'select', options: opcionesAreas },
                { name: 'estado', label: 'Estado', type: 'select', options: opcionesEstado(), required: true },
            ],
            detalle: (r) => [
                ['Nombres', esc(r.nombres)],
                ['Apellidos', esc(r.apellidos)],
                ['Teléfono', esc(r.telefono)],
                ['Área', r.area ? esc(r.area) : '—'],
                ['Estado', badgeEstado(r.estado)],
            ],
        },
        vehiculos: {
            titulo: 'vehículo',
            tituloNuevo: 'Nuevo vehículo',
            url: 'admin/configuracion/vehiculos',
            columns: [
                { data: 'id', title: 'ID', width: '60px' },
                { data: 'placa', title: 'Placa' },
                { data: 'marca', title: 'Marca' },
                { data: 'modelo', title: 'Modelo' },
                { data: 'color', title: 'Color' },
                { data: 'area', title: 'Área', render: (d) => d ? esc(d) : '<span class="text-secondary">—</span>' },
                { data: 'tipo_vehiculo', title: 'Tipo', width: '120px', render: (d) => LABELS.tipoVehiculo[d] || esc(d) },
                { data: 'estado', title: 'Estado', width: '110px', render: badgeEstado },
                { data: null, title: 'Acciones', orderable: false, searchable: false, width: '120px', render: (d, t, row) => acciones('vehiculos', row) },
            ],
            fields: [
                { name: 'placa', label: 'Placa', type: 'text', required: true, maxlength: 20, ph: 'Ej. ABC-123' },
                { name: 'marca', label: 'Marca', type: 'text', required: true, maxlength: 60, ph: 'Ej. Toyota' },
                { name: 'modelo', label: 'Modelo', type: 'text', required: true, maxlength: 60, ph: 'Ej. Hilux' },
                { name: 'color', label: 'Color', type: 'text', required: true, maxlength: 60, ph: 'Ej. Blanco' },
                { name: 'tipo_vehiculo', label: 'Tipo de vehículo', type: 'select', required: true, options: [
                    { v: 'automovil', t: 'Automóvil' },
                    { v: 'camioneta', t: 'Camioneta' },
                    { v: 'camion', t: 'Camión' },
                    { v: 'motocicleta', t: 'Motocicleta' },
                    { v: 'maquinaria', t: 'Maquinaria' },
                ]},
                { name: 'tipo_combustible', label: 'Tipo de combustible', type: 'select', required: true, options: [
                    { v: 'gasolina', t: 'Gasolina' },
                    { v: 'diesel', t: 'Diesel' },
                ]},
                { name: 'anio', label: 'Año', type: 'number', min: 1900, max: 2100, ph: 'Ej. 2024' },
                { name: 'cilindraje', label: 'Cilindraje', type: 'select', options: [
                    { v: 'v4', t: 'V4' },
                    { v: 'v6', t: 'V6' },
                    { v: 'v8', t: 'V8' },
                    { v: 'v10', t: 'V10' },
                    { v: 'v12', t: 'V12' },
                    { v: 'otro', t: 'Otro' },
                ]},
                { name: 'rendimiento', label: 'Rendimiento aprox. (km/L)', type: 'number', step: '0.1', min: 0, ph: 'Ej. 12.5', hiddenWhen: { campo: 'tipo_vehiculo', valor: 'maquinaria' } },
                { name: 'rendimiento_hora', label: 'Rendimiento aprox. (por hora)', type: 'number', step: '0.1', min: 0, ph: 'Ej. 8.5', visibleWhen: { campo: 'tipo_vehiculo', valor: 'maquinaria' } },
                { name: 'area_id', label: 'Área', type: 'select', options: opcionesAreas },
                { name: 'conductor_ids', label: 'Conductores asignados', type: 'multiselect', options: opcionesConductores },
                { name: 'estado', label: 'Estado', type: 'select', options: opcionesEstado(), required: true },
            ],
            detalle: (r) => {
                const filas = [
                    ['Placa', esc(r.placa)],
                    ['Marca', esc(r.marca)],
                    ['Modelo', esc(r.modelo)],
                    ['Color', esc(r.color)],
                    ['Tipo de vehículo', LABELS.tipoVehiculo[r.tipo_vehiculo] || esc(r.tipo_vehiculo)],
                    ['Combustible', LABELS.combustible[r.tipo_combustible] || esc(r.tipo_combustible)],
                    ['Año', r.anio ?? '—'],
                    ['Cilindraje', r.cilindraje ? (LABELS.cilindraje[r.cilindraje] || esc(r.cilindraje)) : '—'],
                ];
                if (r.tipo_vehiculo === 'maquinaria') {
                    filas.push(['Rendimiento aprox. (por hora)', r.rendimiento_hora ?? '—']);
                } else {
                    filas.push(['Rendimiento (km/L)', r.rendimiento ?? '—']);
                }
                filas.push(
                    ['Área', r.area ? esc(r.area) : '—'],
                    ['Conductores', (r.conductores || []).map((c) => esc(`${c.nombres} ${c.apellidos}`)).join(', ') || '—'],
                    ['Estado', badgeEstado(r.estado)]
                );
                return filas;
            },
        },
    };

    // ---------- Opciones de selects ----------
    function refrescarOpciones() {
        return $.when(
            $.getJSON(BASE + 'admin/configuracion/areas').done((r) => { OPTIONS.areas = r.data || []; }),
            $.getJSON(BASE + 'admin/configuracion/conductores').done((r) => { OPTIONS.conductores = r.data || []; })
        );
    }

    // ---------- Toast ----------
    function toast(mensaje) {
        $('#toastCrudMensaje').text(mensaje);
        bootstrap.Toast.getOrCreateInstance(document.getElementById('toastCrud')).show();
    }

    function errorGlobal(xhr) {
        const msg = (xhr.responseJSON && xhr.responseJSON.error) || 'Ocurrió un error inesperado.';
        toast(msg);
    }

    // ---------- DataTables ----------
    function initTabla(entidad) {
        if (tablas[entidad]) {
            tablas[entidad].columns.adjust();
            return;
        }

        tablas[entidad] = $('#tabla' + cap(entidad)).DataTable({
            ajax: { url: BASE + CONFIG[entidad].url, dataSrc: 'data' },
            columns: CONFIG[entidad].columns,
            language: IDIOMA_ES,
            pageLength: 10,
            lengthMenu: [10, 25, 50],
            layout: {
                topStart: 'pageLength',
                topEnd: 'search',
                bottomStart: 'info',
                bottomEnd: 'paging',
            },
            order: [[0, 'asc']],
        });
    }

    $('#acordeonConfig').on('shown.bs.collapse', function (e) {
        const entidad = $(e.target).data('entidad');
        if (entidad) {
            initTabla(entidad);
        }
    });

    // ---------- Formularios ----------
    function construirCampos(form, fields, prefijo) {
        const contenedor = form.empty();

        fields.forEach((f) => {
            let input;
            const id = 'campo-' + prefijo + '-' + f.name;

            if (f.type === 'textarea') {
                input = $(`<textarea class="form-control form-control-sm" rows="${f.rows || 3}"></textarea>`);
            } else if (f.type === 'select' || f.type === 'multiselect') {
                input = $('<select class="form-select form-select-sm"></select>');
                if (f.type === 'multiselect') {
                    input.attr('multiple', true).attr('size', 5);
                } else if (!f.required) {
                    input.append($('<option value="">—</option>'));
                }
                const opciones = typeof f.options === 'function' ? f.options() : f.options;
                opciones.forEach((o) => input.append($('<option>', { value: o.v }).text(o.t)));
            } else {
                input = $(`<input class="form-control form-control-sm" type="${f.type}">`);
                if (f.maxlength) input.attr('maxlength', f.maxlength);
                if (f.step) input.attr('step', f.step);
                if (f.min !== undefined) input.attr('min', f.min);
                if (f.max !== undefined) input.attr('max', f.max);
            }

            input.attr('name', f.name).attr('id', id);
            if (f.required) input.prop('required', true);
            if (f.ph) input.attr('placeholder', f.ph);

            contenedor.append(
                $('<div class="mb-3"></div>').attr('data-campo', f.name).append(
                    $('<label class="form-label small fw-medium"></label>')
                        .attr('for', id)
                        .text(f.label),
                    input
                )
            );

            if (f.visibleWhen || f.hiddenWhen) {
                const cond = f.visibleWhen || f.hiddenWhen;
                const aplicar = () => {
                    const valor = form.find(`[name="${cond.campo}"]`).val();
                    const coincide = Array.isArray(cond.valor)
                        ? cond.valor.includes(valor)
                        : valor === cond.valor;
                    const mostrar = f.visibleWhen ? coincide : !coincide;
                    form.find(`[data-campo="${f.name}"]`).toggle(mostrar);
                };
                aplicar();
                form.find(`[name="${cond.campo}"]`).on('change', aplicar);
            }
        });
    }

    function llenarCampos(form, fields, vals) {
        fields.forEach((f) => {
            const $i = form.find(`[name="${f.name}"]`);
            const v = vals[f.name];

            if ($i.is('select[multiple]')) {
                $i.val(v || []);
            } else if ($i.is('select')) {
                $i.val(v == null || v === '' ? '' : String(v));
            } else {
                $i.val(v ?? '');
            }
        });
    }

    function recogerCampos(form) {
        const out = {};
        form.find('[name]').each(function () {
            if ($(this).is('select[multiple]')) {
                out[this.name] = $(this).val() || [];
            } else {
                out[this.name] = $(this).val();
            }
        });
        return out;
    }

    function abrirForm(entidad, id) {
        const cfg = CONFIG[entidad];
        const modal = $('#modalForm' + cap(entidad));
        const form = $('#form' + cap(entidad));

        const mostrar = (vals) => {
            construirCampos(form, cfg.fields, entidad);
            form.data('id', id || null);
            modal.find('.modal-title').text(id ? 'Editar ' + cfg.titulo : cfg.tituloNuevo);
            $('#formErrores' + cap(entidad)).addClass('d-none').empty();
            if (id) {
                llenarCampos(form, cfg.fields, vals);
            }
            bootstrap.Modal.getOrCreateInstance(modal[0]).show();
        };

        if (id) {
            $.getJSON(BASE + cfg.url + '/' + id)
                .done((r) => mostrar(r))
                .fail(errorGlobal);
        } else {
            mostrar({});
        }
    }

    function mostrarErrores(entidad, errs) {
        const box = $('#formErrores' + cap(entidad)).removeClass('d-none').empty();
        Object.values(errs || {}).forEach((e) => box.append($('<div>').text(e)));
        if (box.children().length === 0) {
            box.append($('<div>').text('Ocurrió un error al guardar.'));
        }
    }

    function guardar(entidad) {
        const cfg = CONFIG[entidad];
        const modal = $('#modalForm' + cap(entidad));
        const form = $('#form' + cap(entidad));
        const id = form.data('id');
        const esNuevo = !id;

        $.ajax({
            url: BASE + cfg.url + (esNuevo ? '' : '/' + id),
            method: esNuevo ? 'POST' : 'PUT',
            contentType: 'application/json',
            data: JSON.stringify(recogerCampos(form)),
        })
            .done(() => {
                modal.modal('hide');
                toast(esNuevo ? 'Registro creado correctamente.' : 'Registro actualizado correctamente.');
                tablas[entidad].ajax.reload(null, false);
                refrescarOpciones();
            })
            .fail((xhr) => {
                const errs = (xhr.responseJSON && xhr.responseJSON.errors) || null;
                if (errs) {
                    mostrarErrores(entidad, errs);
                } else {
                    errorGlobal(xhr);
                }
            });
    }

    // ---------- Detalles ----------
    function ver(entidad, id) {
        const cfg = CONFIG[entidad];

        $.getJSON(BASE + cfg.url + '/' + id)
            .done((r) => {
                const cuerpo = $('#detalle' + cap(entidad)).empty();
                cfg.detalle(r).forEach(([k, v]) => {
                    cuerpo.append(
                        $('<div class="row detalle-fila"></div>').append(
                            $('<div class="col-5 detalle-label"></div>').text(k),
                            $('<div class="col-7 detalle-valor"></div>').html(v)
                        )
                    );
                });
                bootstrap.Modal.getOrCreateInstance($('#modalDetalle' + cap(entidad))[0]).show();
            })
            .fail(errorGlobal);
    }

    // ---------- Borrar ----------
    function borrar(entidad, id) {
        pendienteBorrar = { entidad, id };
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalConfirmar')).show();
    }

    $('#btnConfirmarBorrar').on('click', function () {
        if (!pendienteBorrar) {
            return;
        }

        const { entidad, id } = pendienteBorrar;

        $.ajax({ url: BASE + CONFIG[entidad].url + '/' + id, method: 'DELETE' })
            .done(() => {
                toast('Registro eliminado correctamente.');
                tablas[entidad].ajax.reload(null, false);
                refrescarOpciones();
            })
            .fail(errorGlobal)
            .always(() => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalConfirmar')).hide();
                pendienteBorrar = null;
            });
    });

    // ---------- Eventos ----------
    $(document).on('click', '[data-nuevo]', function () {
        abrirForm($(this).data('nuevo'), null);
    });

    $(document).on('click', '[data-guardar]', function () {
        guardar($(this).data('guardar'));
    });

    $(document).on('click', '[data-accion]', function () {
        const entidad = $(this).data('entidad');
        const id = $(this).data('id');
        const accion = $(this).data('accion');

        if (accion === 'ver') {
            ver(entidad, id);
        } else if (accion === 'editar') {
            abrirForm(entidad, id);
        } else if (accion === 'borrar') {
            borrar(entidad, id);
        }
    });

    // Precarga inicial de listas para selects de relación
    refrescarOpciones();
})(jQuery);
