(function ($) {
    'use strict';

    const BASE = $('meta[name="base-url"]').attr('content');

    $(document).ajaxSend(function (e, xhr) {
        const m = document.cookie.match(/(?:^|;\s*)csrf_cookie_name=([^;]+)/);
        const token = m ? decodeURIComponent(m[1]) : $('meta[name="csrf-token"]').attr('content');
        xhr.setRequestHeader('X-CSRF-TOKEN', token);
    });

    const esc = (v) => $('<span>').text(v ?? '').html();
    const fmt = (v, dec = 2) => Number(v ?? 0).toLocaleString('es-CO', { minimumFractionDigits: dec, maximumFractionDigits: dec });

    const TIPOS = { automovil: 'Automóvil', camioneta: 'Camioneta', camion: 'Camión', motocicleta: 'Motocicleta', maquinaria: 'Maquinaria' };
    const CATEGORIAS = { arreglo: 'Arreglo', refacciones: 'Refacciones', mantenimiento: 'Mantenimiento', seguro: 'Seguro', tenencia: 'Tenencia', lavado: 'Lavado', otro: 'Otro' };

    function toast(mensaje) {
        $('#toastCrudMensaje').text(mensaje);
        bootstrap.Toast.getOrCreateInstance(document.getElementById('toastCrud')).show();
    }

    function badgeEstado(v) {
        return v == 1
            ? '<span class="badge-estado activo">Activo</span>'
            : '<span class="badge-estado inactivo">Inactivo</span>';
    }

    // ---------- Cards de vehículos ----------
    function cargarVehiculos() {
        $.getJSON(BASE + 'admin/configuracion/vehiculos?activos=1')
            .done((r) => {
                const grid = $('#gridVehiculos').empty();
                const lista = r.data || [];

                $('#sinVehiculos').toggleClass('d-none', lista.length > 0);

                lista.forEach((v) => {
                    const detalle = [v.marca, v.modelo, v.color].filter(Boolean).join(' · ');

                    grid.append(`
                        <div class="col-12 col-md-6 col-xl-4" data-filtro="${esc((v.placa + ' ' + v.modelo).toLowerCase())}">
                            <div class="card panel-card vehicle-card h-100">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                        <div>
                                            <h2 class="h6 fw-semibold mb-0">
                                                <i class="bi bi-car-front-fill text-pine me-1"></i>${esc(v.placa)}
                                            </h2>
                                            <p class="small text-secondary mb-0">${esc(detalle)}</p>
                                        </div>
                                        ${badgeEstado(v.estado)}
                                    </div>
                                    <p class="small text-secondary mb-3">
                                        <i class="bi bi-building me-1"></i>${esc(v.area || 'Sin área')}
                                        · ${esc(TIPOS[v.tipo_vehiculo] || v.tipo_vehiculo || '')}
                                    </p>
                                    <div class="d-flex flex-column gap-2 mt-auto">
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-pine btn-sm flex-fill" data-accion="cargar"
                                                    data-id="${v.id}" data-placa="${esc(v.placa)}">
                                                <i class="bi bi-fuel-pump me-1"></i>Registrar combustible
                                            </button>
                                            <button type="button" class="btn btn-outline-pine btn-sm" data-accion="historial"
                                                    data-id="${v.id}" data-placa="${esc(v.placa)}" title="Historial de combustible">
                                                <i class="bi bi-clock-history"></i>
                                            </button>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-pine btn-sm flex-fill" data-accion="gasto"
                                                    data-id="${v.id}" data-placa="${esc(v.placa)}">
                                                <i class="bi bi-receipt me-1"></i>Registrar gasto
                                            </button>
                                            <button type="button" class="btn btn-outline-pine btn-sm" data-accion="historial-gastos"
                                                    data-id="${v.id}" data-placa="${esc(v.placa)}" title="Historial de gastos">
                                                <i class="bi bi-list-ul"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>`);
                });

                filtrar();
            })
            .fail(() => toast('No se pudieron cargar los vehículos.'));
    }

    function filtrar() {
        const texto = ($('#buscarVehiculo').val() || '').trim().toLowerCase();
        let visibles = 0;

        $('#gridVehiculos [data-filtro]').each(function () {
            const coincide = texto === '' || $(this).data('filtro').includes(texto);
            $(this).toggleClass('d-none', !coincide);
            if (coincide) {
                visibles++;
            }
        });

        $('#sinResultados').toggleClass('d-none', visibles > 0 || texto === '');
    }

    $('#buscarVehiculo').on('input', filtrar);

    // ---------- Registrar carga ----------
    function abrirCarga(id, placa) {
        $('#formCarga')[0].reset();
        $('#formErroresCarga').addClass('d-none').empty();
        $('#carga-vehiculo-id').val(id);
        $('#carga-fecha').val(new Date().toISOString().slice(0, 10));
        $('#tituloCarga').text('Registrar combustible · ' + placa);
        $('.foto-previa').addClass('d-none').removeAttr('src');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCarga')).show();
    }

    $(document).on('change', '#formCarga input[type="file"], #formGasto input[type="file"]', function () {
        const archivo = this.files && this.files[0];
        const previa = $(this).closest('div').find('.foto-previa');

        if (archivo) {
            const lector = new FileReader();
            lector.onload = (e) => previa.attr('src', e.target.result).removeClass('d-none');
            lector.readAsDataURL(archivo);
        } else {
            previa.addClass('d-none').removeAttr('src');
        }
    });

    $('#btnGuardarCarga').on('click', function () {
        const form = document.getElementById('formCarga');

        if (! form.reportValidity()) {
            return;
        }

        const data = new FormData(form);

        $.ajax({
            url: BASE + 'admin/vehiculos/cargas',
            method: 'POST',
            data,
            processData: false,
            contentType: false,
        })
            .done(() => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalCarga')).hide();
                toast('Carga de combustible registrada correctamente.');
            })
            .fail((xhr) => {
                const errs = (xhr.responseJSON && xhr.responseJSON.errors) || null;

                if (! errs) {
                    toast((xhr.responseJSON && xhr.responseJSON.error) || 'Ocurrió un error inesperado.');
                    return;
                }

                const box = $('#formErroresCarga').removeClass('d-none').empty();
                Object.values(errs).forEach((e) => box.append($('<div>').text(e)));
            });
    });

    // ---------- Historial ----------
    function abrirHistorial(id, placa) {
        $('#tituloHistorial').text('Historial de combustible · ' + placa);

        $.getJSON(BASE + 'admin/vehiculos/cargas?vehiculo_id=' + id)
            .done((r) => {
                const cuerpo = $('#cargasBody').empty();
                const lista = r.data || [];
                let litros = 0;
                let monto = 0;

                lista.forEach((c) => {
                    litros += Number(c.litros ?? 0);
                    monto += Number(c.monto ?? 0);

                    const foto = (nombre) => nombre
                        ? `<a href="${BASE}admin/vehiculos/fotos/${esc(nombre)}" target="_blank" title="Ver foto">
                             <img src="${BASE}admin/vehiculos/fotos/${esc(nombre)}" class="foto-mini" alt="Foto">
                           </a>`
                        : '';

                    cuerpo.append(`
                        <tr>
                            <td class="text-nowrap">${esc(c.fecha)}</td>
                            <td>${fmt(c.odometro, 0)}</td>
                            <td>${fmt(c.litros)}</td>
                            <td>$${fmt(c.monto)}</td>
                            <td>${c.tanque_lleno == 1 ? '<span class="badge-estado activo">Lleno</span>' : '<span class="text-secondary">—</span>'}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    ${foto(c.foto_bomba_gasolineria)}
                                    ${foto(c.foto_odometro)}
                                    ${foto(c.foto_ticket_pago)}
                                </div>
                            </td>
                            <td class="text-secondary small">${c.notas ? esc(c.notas) : '—'}</td>
                        </tr>`);
                });

                $('#cargasResumen').text(
                    lista.length === 0
                        ? 'Sin registros de combustible.'
                        : `${lista.length} registro(s) · Total: ${fmt(litros)} L · $${fmt(monto)}`
                );

                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalHistorial')).show();
            })
            .fail(() => toast('No se pudo cargar el historial.'));
    }

    // ---------- Gastos ----------
    function abrirGasto(id, placa) {
        $('#formGasto')[0].reset();
        $('#formErroresGasto').addClass('d-none').empty();
        $('#gasto-vehiculo-id').val(id);
        $('#gasto-fecha').val(new Date().toISOString().slice(0, 10));
        $('#tituloGasto').text('Registrar gasto · ' + placa);
        $('#modalGasto .foto-previa').addClass('d-none').removeAttr('src');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalGasto')).show();
    }

    $('#btnGuardarGasto').on('click', function () {
        const form = document.getElementById('formGasto');

        if (! form.reportValidity()) {
            return;
        }

        const data = new FormData(form);

        $.ajax({
            url: BASE + 'admin/vehiculos/gastos',
            method: 'POST',
            data,
            processData: false,
            contentType: false,
        })
            .done(() => {
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalGasto')).hide();
                toast('Gasto registrado correctamente.');
            })
            .fail((xhr) => {
                const errs = (xhr.responseJSON && xhr.responseJSON.errors) || null;

                if (! errs) {
                    toast((xhr.responseJSON && xhr.responseJSON.error) || 'Ocurrió un error inesperado.');
                    return;
                }

                const box = $('#formErroresGasto').removeClass('d-none').empty();
                Object.values(errs).forEach((e) => box.append($('<div>').text(e)));
            });
    });

    function abrirHistorialGastos(id, placa) {
        $('#tituloHistorialGastos').text('Historial de gastos · ' + placa);

        $.getJSON(BASE + 'admin/vehiculos/gastos?vehiculo_id=' + id)
            .done((r) => {
                const cuerpo = $('#gastosBody').empty();
                const lista = r.data || [];
                let monto = 0;

                lista.forEach((g) => {
                    monto += Number(g.monto ?? 0);

                    const foto = g.foto_comprobante
                        ? `<a href="${BASE}admin/vehiculos/gastos/fotos/${esc(g.foto_comprobante)}" target="_blank" title="Ver comprobante">
                             <img src="${BASE}admin/vehiculos/gastos/fotos/${esc(g.foto_comprobante)}" class="foto-mini" alt="Comprobante">
                           </a>`
                        : '';

                    cuerpo.append(`
                        <tr>
                            <td class="text-nowrap">${esc(g.fecha)}</td>
                            <td><span class="badge-estado activo">${esc(CATEGORIAS[g.categoria] || g.categoria)}</span></td>
                            <td>${esc(g.concepto)}</td>
                            <td>$${fmt(g.monto)}</td>
                            <td>${foto}</td>
                            <td class="text-secondary small">${g.notas ? esc(g.notas) : '—'}</td>
                        </tr>`);
                });

                $('#gastosResumen').text(
                    lista.length === 0
                        ? 'Sin registros de gastos.'
                        : `${lista.length} registro(s) · Total: $${fmt(monto)}`
                );

                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalHistorialGastos')).show();
            })
            .fail(() => toast('No se pudo cargar el historial de gastos.'));
    }

    // ---------- Eventos ----------
    $(document).on('click', '[data-accion="cargar"]', function () {
        abrirCarga($(this).data('id'), $(this).data('placa'));
    });

    $(document).on('click', '[data-accion="historial"]', function () {
        abrirHistorial($(this).data('id'), $(this).data('placa'));
    });

    $(document).on('click', '[data-accion="gasto"]', function () {
        abrirGasto($(this).data('id'), $(this).data('placa'));
    });

    $(document).on('click', '[data-accion="historial-gastos"]', function () {
        abrirHistorialGastos($(this).data('id'), $(this).data('placa'));
    });

    cargarVehiculos();
})(jQuery);
