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

    const CATEGORIAS = { arreglo: 'Arreglo', refacciones: 'Refacciones', mantenimiento: 'Mantenimiento', seguro: 'Seguro', tenencia: 'Tenencia', lavado: 'Lavado', otro: 'Otro' };
    const TIPOS_MANT = { aceite: 'Cambio de aceite', llantas: 'Llantas', frenos: 'Frenos', filtros: 'Filtros', revision_general: 'Revisión general', otro: 'Otro' };

    const COLORES = ['#184B44', '#56AD71', '#A8DDB5', '#8D99A6', '#060712', '#3d7a66', '#7cc292', '#c9a227', '#5b6f82'];

    const OPT = { areas: [], vehiculos: [] };
    let tablaGastos = null;
    const charts = {};
    let impactoData = [];

    function toast(mensaje) {
        $('#toastCrudMensaje').text(mensaje);
        bootstrap.Toast.getOrCreateInstance(document.getElementById('toastCrud')).show();
    }

    function graficar(id, config) {
        if (charts[id]) {
            charts[id].destroy();
        }
        charts[id] = new Chart(document.getElementById(id).getContext('2d'), config);
    }

    function cargarOpciones() {
        return $.getJSON(BASE + 'admin/reportes/opciones').done((r) => {
            OPT.areas = r.areas || [];
            OPT.vehiculos = r.vehiculos || [];
            poblarSelects();
        });
    }

    function poblarSelects() {
        const opts = ['<option value="">Todos</option>'].concat(
            OPT.vehiculos.map((v) => `<option value="${v.id}">${esc(v.placa)} · ${esc(v.marca)} ${esc(v.modelo)}</option>`)
        );
        $('#fGastosVehiculo, #fMantVehiculo').html(opts.join(''));

        $('#fCargasArea').html(
            ['<option value="">Todas las áreas</option>'].concat(
                OPT.areas.map((a) => `<option value="${a.id}">${esc(a.nombre)}</option>`)
            ).join('')
        );

        $('#fCargasVehiculos').html(
            OPT.vehiculos.map((v) => `<option value="${v.id}" selected>${esc(v.placa)} · ${esc(v.marca)} ${esc(v.modelo)}</option>`).join('')
        );
    }

    // =========================================================
    // 1. Reportes generales (gastos)
    // =========================================================
    function cargarGastos() {
        const params = new URLSearchParams({
            desde: $('#fGastosDesde').val() || '',
            hasta: $('#fGastosHasta').val() || '',
            vehiculo_id: $('#fGastosVehiculo').val() || '',
            categoria: $('#fGastosCategoria').val() || '',
        });

        $.getJSON(BASE + 'admin/reportes/gastos?' + params.toString())
            .done((r) => {
                const datos = r.data || [];

                if (tablaGastos) {
                    tablaGastos.clear();
                    tablaGastos.rows.add(datos.map((g) => [
                        esc(g.fecha),
                        esc(g.placa),
                        esc(CATEGORIAS[g.categoria] || g.categoria),
                        esc(g.concepto),
                        '$' + fmt(g.monto),
                    ]));
                    tablaGastos.draw();
                } else {
                    tablaGastos = $('#tablaGastosReporte').DataTable({
                        data: datos.map((g) => [
                            esc(g.fecha),
                            esc(g.placa),
                            esc(CATEGORIAS[g.categoria] || g.categoria),
                            esc(g.concepto),
                            '$' + fmt(g.monto),
                        ]),
                        columns: [
                            { title: 'Fecha' },
                            { title: 'Vehículo' },
                            { title: 'Categoría' },
                            { title: 'Concepto' },
                            { title: 'Monto' },
                        ],
                        language: {
                            emptyTable: 'Sin gastos con los filtros seleccionados',
                            info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                            infoEmpty: 'Sin registros',
                            infoFiltered: '(filtrado de _MAX_ registros)',
                            lengthMenu: 'Mostrar _MENU_',
                            search: 'Buscar:',
                            zeroRecords: 'Sin resultados',
                            paginate: { first: '«', last: '»', next: '›', previous: '‹' },
                        },
                        pageLength: 10,
                        lengthMenu: [10, 25, 50],
                        layout: { topStart: 'pageLength', topEnd: 'search', bottomStart: 'info', bottomEnd: 'paging' },
                    });
                }

                // KPIs
                const total = datos.reduce((s, g) => s + Number(g.monto ?? 0), 0);
                $('#kpiTotalMonto').text('$' + fmt(total));
                $('#kpiTotalRegistros').text(datos.length);

                const porCategoria = {};
                const porVehiculo = {};
                datos.forEach((g) => {
                    porCategoria[g.categoria] = (porCategoria[g.categoria] || 0) + Number(g.monto ?? 0);
                    porVehiculo[g.placa] = (porVehiculo[g.placa] || 0) + Number(g.monto ?? 0);
                });

                const topCat = Object.entries(porCategoria).sort((a, b) => b[1] - a[1])[0];
                const topVeh = Object.entries(porVehiculo).sort((a, b) => b[1] - a[1])[0];

                $('#kpiCategoriaTop').text(topCat ? CATEGORIAS[topCat[0]] || topCat[0] : '—');
                $('#kpiVehiculoTop').text(topVeh ? topVeh[0] : '—');
            })
            .fail(() => toast('No se pudieron cargar los gastos.'));
    }

    $('#btnAplicarGastos').on('click', cargarGastos);

    $('#btnExportarXlsx').on('click', function () {
        const tabla = document.getElementById('tablaGastosReporte');

        if (!tabla || tabla.querySelectorAll('tbody tr').length === 0) {
            toast('No hay datos para exportar.');
            return;
        }

        const wb = XLSX.utils.table_to_book(tabla, { sheet: 'Gastos' });
        XLSX.writeFile(wb, 'reporte_gastos.xlsx');
        toast('Reporte exportado en XLSX.');
    });

    // =========================================================
    // 2. Gráficas de consumo de combustible
    // =========================================================
    function cargarConsumo() {
        const vehiculoIds = $('#fCargasVehiculos').val() || [];
        const params = new URLSearchParams({
            desde: $('#fCargasDesde').val() || '',
            hasta: $('#fCargasHasta').val() || '',
            area_id: $('#fCargasArea').val() || '',
        });
        vehiculoIds.forEach((id) => params.append('vehiculo_ids[]', id));

        $.getJSON(BASE + 'admin/reportes/cargas?' + params.toString())
            .done((r) => {
                const datos = r.data || [];

                const litros = Number(datos.reduce((s, c) => s + Number(c.litros ?? 0), 0));
                const dinero = Number(datos.reduce((s, c) => s + Number(c.monto ?? 0), 0));
                $('#kpiLitros').text(fmt(litros) + ' L');
                $('#kpiDineroCargas').text('$' + fmt(dinero));
                $('#kpiPrecioLitro').text('$' + fmt(litros > 0 ? dinero / litros : 0));
                $('#kpiCargas').text(datos.length);

                const porVehiculo = {};
                const porMes = {};
                const porVehiculoKm = { min: {}, max: {} };

                datos.forEach((c) => {
                    const clave = c.placa;
                    porVehiculo[clave] = (porVehiculo[clave] || 0) + Number(c.litros ?? 0);

                    const mes = String(c.fecha).slice(0, 7);
                    porMes[mes] = (porMes[mes] || 0) + Number(c.monto ?? 0);

                    if (!(clave in porVehiculoKm.min) || Number(c.odometro) < porVehiculoKm.min[clave]) {
                        porVehiculoKm.min[clave] = Number(c.odometro);
                    }
                    if (!(clave in porVehiculoKm.max) || Number(c.odometro) > porVehiculoKm.max[clave]) {
                        porVehiculoKm.max[clave] = Number(c.odometro);
                    }
                });

                const labels = Object.keys(porVehiculo);
                const meses = Object.keys(porMes).sort();

                graficar('graficaLitros', {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                            label: 'Litros',
                            data: labels.map((l) => porVehiculo[l]),
                            backgroundColor: COLORES[1],
                            borderRadius: 6,
                        }],
                    },
                    options: { plugins: { legend: { display: false } }, maintainAspectRatio: false },
                });

                graficar('graficaMensual', {
                    type: 'line',
                    data: {
                        labels: meses.map((m) => m.slice(5) + '/' + m.slice(0, 4)),
                        datasets: [{
                            label: 'Gasto ($)',
                            data: meses.map((m) => porMes[m]),
                            borderColor: COLORES[0],
                            backgroundColor: COLORES[2] + '55',
                            fill: true,
                            tension: 0.35,
                            pointBackgroundColor: COLORES[0],
                        }],
                    },
                    options: { maintainAspectRatio: false },
                });

                const precioPorVeh = {};
                const litrosPorVeh = {};
                datos.forEach((c) => {
                    litrosPorVeh[c.placa] = (litrosPorVeh[c.placa] || 0) + Number(c.litros ?? 0);
                    precioPorVeh[c.placa] = (precioPorVeh[c.placa] || 0) + Number(c.monto ?? 0);
                });
                const precioLabels = Object.keys(precioPorVeh);

                graficar('graficaPrecio', {
                    type: 'bar',
                    data: {
                        labels: precioLabels,
                        datasets: [{
                            label: 'Precio por litro ($)',
                            data: precioLabels.map((p) => litrosPorVeh[p] > 0 ? precioPorVeh[p] / litrosPorVeh[p] : 0),
                            backgroundColor: COLORES[8],
                            borderRadius: 6,
                        }],
                    },
                    options: { plugins: { legend: { display: false } }, maintainAspectRatio: false },
                });

                const kmLabels = Object.keys(porVehiculoKm.min);
                graficar('graficaKm', {
                    type: 'bar',
                    data: {
                        labels: kmLabels,
                        datasets: [{
                            label: 'Km recorridos',
                            data: kmLabels.map((l) => Math.max(0, porVehiculoKm.max[l] - porVehiculoKm.min[l])),
                            backgroundColor: COLORES[3],
                            borderRadius: 6,
                        }],
                    },
                    options: { plugins: { legend: { display: false } }, maintainAspectRatio: false },
                });
            })
            .fail(() => toast('No se pudieron cargar las cargas de combustible.'));
    }

    $('#fCargasArea').on('change', function () {
        const area = $(this).val();
        const deArea = OPT.vehiculos.filter((v) => v.area_id == area);

        if (!area) {
            $('#fCargasVehiculos option').prop('selected', true);
        } else {
            $('#fCargasVehiculos option').prop('selected', false);
            deArea.forEach((v) => $(`#fCargasVehiculos option[value="${v.id}"]`).prop('selected', true));
        }
    });

    $('#fCargasDesde, #fCargasHasta, #fCargasVehiculos').on('change', cargarConsumo);

    // =========================================================
    // 3. Impacto de combustible
    // =========================================================
    const SEMAFORO_LABEL = {
        verde: 'Óptimo',
        amarillo: 'Atención',
        rojo: 'Crítico',
        gris: 'Sin datos',
    };

    function cargarImpacto() {
        $.getJSON(BASE + 'admin/reportes/impacto')
            .done((r) => {
                impactoData = r.data || [];
                pintarImpacto();
            })
            .fail(() => toast('No se pudo cargar el análisis de impacto.'));
    }

    function pintarImpacto() {
        const grid = $('#gridImpacto').empty();
        const texto = ($('#buscarImpacto').val() || '').trim().toLowerCase();

        let visibles = 0;

        impactoData.forEach((d) => {
            const clave = (d.placa + ' ' + (d.modelo || '')).toLowerCase();
            if (texto && !clave.includes(texto)) {
                return;
            }
            visibles++;

            const diff = (d.rendimiento_config != null && d.rendimiento_real != null)
                ? (d.rendimiento_real - d.rendimiento_config)
                : null;

            const alertas = (d.alertas || []).map((a) => `<li class="small"><i class="bi bi-exclamation-circle me-1 text-warning"></i>${esc(a)}</li>`).join('');

            grid.append(`
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card panel-card impacto-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <h3 class="h6 fw-semibold mb-0"><i class="bi bi-car-front-fill text-pine me-1"></i>${esc(d.placa)}</h3>
                                    <p class="small text-secondary mb-0">${esc([d.marca, d.modelo].filter(Boolean).join(' '))}</p>
                                </div>
                                <span class="semaforo semaforo-${d.semaforo}" title="${SEMAFORO_LABEL[d.semaforo]}"></span>
                            </div>

                            <div class="row g-2 text-center mb-3">
                                <div class="col-4">
                                    <div class="mini-metrica">
                                        <div class="mini-valor">${d.rendimiento_config != null ? fmt(d.rendimiento_config, 1) : '—'}</div>
                                        <div class="mini-label">Config km/L</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="mini-metrica">
                                        <div class="mini-valor">${d.rendimiento_real != null ? fmt(d.rendimiento_real, 1) : '—'}</div>
                                        <div class="mini-label">Real km/L</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="mini-metrica">
                                        <div class="mini-valor">${diff != null ? (diff >= 0 ? '+' : '') + fmt(diff, 1) : '—'}</div>
                                        <div class="mini-label">Diferencia</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between small text-secondary mb-2">
                                <span>${fmt(d.total_litros)} L</span>
                                <span>$${fmt(d.total_monto)}</span>
                                <span>$${fmt(d.precio_litro)}/L</span>
                            </div>

                            ${alertas ? `<ul class="list-unstyled mb-0 alerta-lista">${alertas}</ul>` : '<p class="small text-secondary mb-0"><i class="bi bi-check-circle me-1 text-pine"></i>Sin hallazgos.</p>'}
                        </div>
                    </div>
                </div>`);
        });

        $('#sinImpacto').toggleClass('d-none', visibles > 0 || texto === '');
    }

    $('#buscarImpacto').on('input', pintarImpacto);

    $('#btnExportarPdf').on('click', function () {
        if (impactoData.length === 0) {
            toast('No hay datos para exportar.');
            return;
        }

        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        let y = 15;

        doc.setFontSize(16);
        doc.setTextColor(24, 75, 68);
        doc.text('Impacto de combustible · Bloomingtec', 14, y);
        y += 6;
        doc.setFontSize(10);
        doc.setTextColor(100);
        doc.text('Generado: ' + new Date().toLocaleString('es-CO'), 14, y);
        y += 10;

        impactoData.forEach((d) => {
            if (y > 265) {
                doc.addPage();
                y = 15;
            }

            doc.setFontSize(12);
            doc.setTextColor(24, 75, 68);
            doc.text(`${d.placa} · ${d.marca} ${d.modelo}`, 14, y);
            y += 5;

            doc.setFontSize(9);
            doc.setTextColor(60);
            const lineas = [
                `Rendimiento configurado: ${d.rendimiento_config != null ? fmt(d.rendimiento_config, 1) + ' km/L' : 'Sin configurar'}`,
                `Rendimiento real: ${d.rendimiento_real != null ? fmt(d.rendimiento_real, 1) + ' km/L' : 'Sin datos suficientes'}`,
                `Total litros: ${fmt(d.total_litros)} L · Total gasto: $${fmt(d.total_monto)} · Precio por litro: $${fmt(d.precio_litro)}`,
                `Km recorridos: ${fmt(d.km_recorridos, 0)} · Cargas: ${d.n_cargas}`,
                `Estado: ${SEMAFORO_LABEL[d.semaforo]}`,
            ];
            lineas.forEach((l) => { doc.text(l, 18, y); y += 4.5; });

            if ((d.alertas || []).length) {
                doc.setTextColor(180, 120, 0);
                doc.text('Hallazgos:', 18, y);
                y += 4.5;
                doc.setTextColor(60);
                d.alertas.forEach((a) => {
                    const lineasA = doc.splitTextToSize('• ' + a, 170);
                    doc.text(lineasA, 22, y);
                    y += lineasA.length * 4.5;
                });
            }

            y += 6;
        });

        doc.save('impacto_combustible.pdf');
        toast('PDF exportado correctamente.');
    });

    // =========================================================
    // 4. Gráficas de mantenimientos
    // =========================================================
    function cargarMant() {
        const params = new URLSearchParams({
            desde: $('#fMantDesde').val() || '',
            hasta: $('#fMantHasta').val() || '',
            vehiculo_id: $('#fMantVehiculo').val() || '',
        });

        $.getJSON(BASE + 'admin/reportes/mantenimientos?' + params.toString())
            .done((r) => {
                const datos = r.data || [];

                const costoTotal = datos.reduce((s, m) => s + Number(m.gasto_monto ?? m.costo_estimado ?? 0), 0);
                $('#kpiMantTotal').text(datos.length);
                $('#kpiMantCosto').text('$' + fmt(costoTotal));

                const porTipo = {};
                const porMes = {};
                const costoPorVeh = {};

                datos.forEach((m) => {
                    porTipo[m.tipo] = (porTipo[m.tipo] || 0) + 1;
                    const mes = String(m.fecha_completado).slice(0, 7);
                    porMes[mes] = (porMes[mes] || 0) + 1;
                    costoPorVeh[m.placa] = (costoPorVeh[m.placa] || 0) + Number(m.gasto_monto ?? m.costo_estimado ?? 0);
                });

                const topTipo = Object.entries(porTipo).sort((a, b) => b[1] - a[1])[0];
                $('#kpiMantTipo').text(topTipo ? TIPOS_MANT[topTipo[0]] || topTipo[0] : '—');

                const meses = Object.keys(porMes).sort();
                graficar('graficaMantMensual', {
                    type: 'line',
                    data: {
                        labels: meses.map((m) => m.slice(5) + '/' + m.slice(0, 4)),
                        datasets: [{
                            label: 'Completados',
                            data: meses.map((m) => porMes[m]),
                            borderColor: COLORES[0],
                            backgroundColor: COLORES[2] + '55',
                            fill: true,
                            tension: 0.35,
                            pointBackgroundColor: COLORES[0],
                        }],
                    },
                    options: { maintainAspectRatio: false, scales: { y: { ticks: { precision: 0 } } } },
                });

                const tiposLabels = Object.keys(porTipo);
                graficar('graficaMantTipos', {
                    type: 'doughnut',
                    data: {
                        labels: tiposLabels.map((t) => TIPOS_MANT[t] || t),
                        datasets: [{
                            data: tiposLabels.map((t) => porTipo[t]),
                            backgroundColor: COLORES.slice(0, tiposLabels.length),
                            borderWidth: 0,
                        }],
                    },
                    options: { maintainAspectRatio: false, plugins: { legend: { position: 'right' } } },
                });

                const vehLabels = Object.keys(costoPorVeh);
                graficar('graficaMantCosto', {
                    type: 'bar',
                    data: {
                        labels: vehLabels,
                        datasets: [{
                            label: 'Costo ($)',
                            data: vehLabels.map((v) => costoPorVeh[v]),
                            backgroundColor: COLORES[1],
                            borderRadius: 6,
                        }],
                    },
                    options: { plugins: { legend: { display: false } }, maintainAspectRatio: false },
                });
            })
            .fail(() => toast('No se pudieron cargar los mantenimientos.'));
    }

    $('#fMantDesde, #fMantHasta, #fMantVehiculo').on('change', cargarMant);

    // ---------- Lazy init por acordeón ----------
    let inicializado = { gastos: false, consumo: false, impacto: false, mant: false };

    $('#acordeonReportes').on('shown.bs.collapse', function (e) {
        const id = e.target.id;

        if (id === 'collapseGastos' && !inicializado.gastos) {
            inicializado.gastos = true;
            cargarGastos();
        }
        if (id === 'collapseConsumo' && !inicializado.consumo) {
            inicializado.consumo = true;
            cargarConsumo();
        }
        if (id === 'collapseImpacto' && !inicializado.impacto) {
            inicializado.impacto = true;
            cargarImpacto();
        }
        if (id === 'collapseMant' && !inicializado.mant) {
            inicializado.mant = true;
            cargarMant();
        }
    });

    cargarOpciones().done(() => {
        // Sección 1 abierta por defecto
        cargarGastos();
    });
})(jQuery);
