(function ($) {
    'use strict';

    const BASE = $('meta[name="base-url"]').attr('content');

    $(document).ajaxSend(function (e, xhr) {
        const m = document.cookie.match(/(?:^|;\s*)csrf_cookie_name=([^;]+)/);
        const token = m ? decodeURIComponent(m[1]) : $('meta[name="csrf-token"]').attr('content');
        xhr.setRequestHeader('X-CSRF-TOKEN', token);
    });

    const esc = (v) => $('<span>').text(v ?? '').html();

    const TIPOS = { aceite: 'Cambio de aceite', llantas: 'Llantas', frenos: 'Frenos', filtros: 'Filtros', revision_general: 'Revisión general', otro: 'Otro' };
    const ESTADOS = {
        pendiente: ['Pendiente', 'pendiente'],
        en_proceso: ['En proceso', 'en-proceso'],
        completado: ['Completado', 'completado'],
        cancelado: ['Cancelado', 'cancelado'],
        vencido: ['Vencido', 'vencido'],
    };
    const PRIORIDADES = {
        baja: ['Baja', 'prioridad-baja'],
        media: ['Media', 'prioridad-media'],
        alta: ['Alta', 'prioridad-alta'],
        critica: ['Crítica', 'prioridad-critica'],
    };

    let filtroEstado = '';
    let mantCompletar = null;

    function toast(mensaje) {
        $('#toastCrudMensaje').text(mensaje);
        bootstrap.Toast.getOrCreateInstance(document.getElementById('toastCrud')).show();
    }

    function errorGlobal(xhr) {
        toast((xhr.responseJSON && xhr.responseJSON.error) || 'Ocurrió un error inesperado.');
    }

    function badgeEstado(estado) {
        const [texto, clase] = ESTADOS[estado] || [estado, ''];
        return `<span class="badge-estado mant-${clase}">${esc(texto)}</span>`;
    }

    function badgePrioridad(prioridad) {
        const [texto, clase] = PRIORIDADES[prioridad] || PRIORIDADES.media;
        return `<span class="badge-estado ${clase}">${esc(texto)}</span>`;
    }

    function disparador(m) {
        const partes = [];
        if (m.fecha_programada) partes.push('Fecha: ' + esc(m.fecha_programada));
        if (m.odometro_programado) partes.push('Km: ' + esc(m.odometro_programado));
        if (m.intervalo_km) partes.push('Cada ' + esc(m.intervalo_km) + ' km');
        if (m.intervalo_dias) partes.push('Cada ' + esc(m.intervalo_dias) + ' días');
        return partes.join(' · ') || '—';
    }

    function acciones(m) {
        const botones = [];

        botones.push(`<button type="button" class="btn-icon" data-accion="ver" data-id="${m.id}" title="Ver detalle"><i class="bi bi-eye"></i></button>`);

        if (m.estado === 'pendiente' || m.estado === 'vencido') {
            botones.push(`<button type="button" class="btn-icon" data-accion="iniciar" data-id="${m.id}" title="Iniciar"><i class="bi bi-play-circle"></i></button>`);
        }

        if (m.estado !== 'completado' && m.estado !== 'cancelado') {
            botones.push(`<button type="button" class="btn-icon" data-accion="completar" data-id="${m.id}" title="Completar"><i class="bi bi-check2-circle"></i></button>`);
        }

        if (m.estado !== 'completado' && m.estado !== 'cancelado') {
            botones.push(`<button type="button" class="btn-icon danger" data-accion="cancelar" data-id="${m.id}" title="Cancelar"><i class="bi bi-x-circle"></i></button>`);
        }

        return `<div class="d-flex gap-1">${botones.join('')}</div>`;
    }

    function cargarLista() {
        const url = BASE + 'admin/mantenimientos/lista' + (filtroEstado ? '?estado=' + filtroEstado : '');

        $.getJSON(url)
            .done((r) => {
                const cuerpo = $('#mantBody').empty();
                const lista = r.data || [];

                if (lista.length === 0) {
                    cuerpo.append('<tr><td colspan="6" class="text-center text-secondary small py-4">Sin mantenimientos.</td></tr>');
                    return;
                }

                lista.forEach((m) => {
                    cuerpo.append(`
                        <tr>
                            <td>
                                <div class="fw-medium">${esc(m.placa)}</div>
                                <div class="text-secondary small">${esc([m.marca, m.modelo].filter(Boolean).join(' '))}</div>
                            </td>
                            <td>${esc(TIPOS[m.tipo] || m.tipo)}</td>
                            <td>${badgePrioridad(m.prioridad)}</td>
                            <td class="small">${disparador(m)}</td>
                            <td>${badgeEstado(m.estado)}</td>
                            <td class="dt-acciones">${acciones(m)}</td>
                        </tr>`);
                });
            })
            .fail(errorGlobal);
    }

    // ---------- Filtros ----------
    $('#chipsEstado').on('click', '[data-estado]', function () {
        $('#chipsEstado .chip-estado').removeClass('active');
        $(this).addClass('active');
        filtroEstado = $(this).data('estado') || '';
        cargarLista();
    });

    // ---------- Nuevo ----------
    function cargarVehiculosSelect() {
        const select = $('#mant-vehiculo').empty();
        select.append($('<option value="">Selecciona…</option>'));

        $.getJSON(BASE + 'admin/configuracion/vehiculos?activos=1')
            .done((r) => {
                (r.data || []).forEach((v) => {
                    select.append($('<option>', { value: String(v.id) }).text(`${v.placa} · ${v.marca} ${v.modelo}`));
                });
            });
    }

    function abrirNuevo() {
        $('#formMant')[0].reset();
        $('#formErroresMant').addClass('d-none').empty();
        cargarVehiculosSelect();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalMant')).show();
    }

    $('#btnGuardarMant').on('click', function () {
        const form = document.getElementById('formMant');

        if (! form.reportValidity()) {
            return;
        }

        const payload = Object.fromEntries(new FormData(form).entries());

        $.ajax({
            url: BASE + 'admin/mantenimientos/crear',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
        })
            .done(() => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalMant')).hide();
                toast('Mantenimiento programado correctamente.');
                cargarLista();
            })
            .fail((xhr) => {
                const errs = (xhr.responseJSON && xhr.responseJSON.errors) || null;

                if (! errs) {
                    errorGlobal(xhr);
                    return;
                }

                const box = $('#formErroresMant').removeClass('d-none').empty();
                Object.values(errs).forEach((e) => box.append($('<div>').text(e)));
            });
    });

    // ---------- Iniciar / Cancelar ----------
    function accionSimple(id, accion, mensaje) {
        $.ajax({ url: `${BASE}admin/mantenimientos/${id}/${accion}`, method: 'POST' })
            .done(() => {
                toast(mensaje);
                cargarLista();
            })
            .fail(errorGlobal);
    }

    // ---------- Completar ----------
    function abrirCompletar(id) {
        mantCompletar = id;
        $('#formCompletar')[0].reset();
        $('#formErroresCompletar').addClass('d-none').empty();
        $('#compCamposGasto').addClass('d-none');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCompletar')).show();
    }

    $('#comp-gasto').on('change', function () {
        $('#compCamposGasto').toggleClass('d-none', !this.checked);
    });

    $('#btnConfirmarCompletar').on('click', function () {
        if (!mantCompletar) {
            return;
        }

        const form = document.getElementById('formCompletar');
        const payload = Object.fromEntries(new FormData(form).entries());
        payload.registrar_gasto = $('#comp-gasto').is(':checked') ? '1' : '0';

        $.ajax({
            url: `${BASE}admin/mantenimientos/${mantCompletar}/completar`,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
        })
            .done(() => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCompletar')).hide();
                toast('Mantenimiento completado correctamente.');
                mantCompletar = null;
                cargarLista();
            })
            .fail((xhr) => {
                const errs = (xhr.responseJSON && xhr.responseJSON.errors) || null;

                if (! errs) {
                    errorGlobal(xhr);
                    return;
                }

                const box = $('#formErroresCompletar').removeClass('d-none').empty();
                Object.values(errs).forEach((e) => box.append($('<div>').text(e)));
            });
    });

    // ---------- Ver detalle ----------
    function ver(id) {
        $.getJSON(BASE + 'admin/mantenimientos/' + id)
            .done((m) => {
                const filas = [
                    ['Vehículo', `${m.placa} · ${m.marca} ${m.modelo}`],
                    ['Tipo', TIPOS[m.tipo] || m.tipo],
                    ['Prioridad', (PRIORIDADES[m.prioridad] || PRIORIDADES.media)[0]],
                    ['Disparador', disparador(m)],
                    ['Costo estimado', m.costo_estimado ? '$' + esc(m.costo_estimado) : '—'],
                    ['Estado', (ESTADOS[m.estado] || [m.estado])[0]],
                    ['Descripción', m.descripcion ? esc(m.descripcion) : '—'],
                    ['Notas', m.notas ? esc(m.notas) : '—'],
                ];

                if (m.estado === 'completado') {
                    filas.push(
                        ['Completado el', esc(m.fecha_completado)],
                        ['Odómetro al completar', m.odometro_completado ? esc(m.odometro_completado) + ' km' : '—']
                    );
                }

                const cuerpo = $('#detalleMantBody').empty();

                filas.forEach(([k, v]) => {
                    cuerpo.append(
                        $('<div class="row detalle-fila"></div>').append(
                            $('<div class="col-5 detalle-label"></div>').text(k),
                            $('<div class="col-7 detalle-valor"></div>').html(v)
                        )
                    );
                });

                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDetalleMant')).show();
            })
            .fail(errorGlobal);
    }

    // ---------- Eventos ----------
    $(document).on('click', '[data-accion="nuevo"]', abrirNuevo);

    $(document).on('click', '[data-accion]', function () {
        const accion = $(this).data('accion');
        const id = $(this).data('id');

        if (accion === 'ver') {
            ver(id);
        } else if (accion === 'iniciar') {
            accionSimple(id, 'iniciar', 'Mantenimiento en proceso.');
        } else if (accion === 'completar') {
            abrirCompletar(id);
        } else if (accion === 'cancelar') {
            accionSimple(id, 'cancelar', 'Mantenimiento cancelado.');
        }
    });

    cargarLista();
})(jQuery);
