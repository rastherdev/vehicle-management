(function ($) {
    'use strict';

    const BASE = $('meta[name="base-url"]').attr('content');

    const fmt = (v, dec = 2) => Number(v ?? 0).toLocaleString('es-CO', { minimumFractionDigits: dec, maximumFractionDigits: dec });
    const esc = (v) => $('<span>').text(v ?? '').html();

    const TIPOS_MANT = { aceite: 'Cambio de aceite', llantas: 'Llantas', frenos: 'Frenos', filtros: 'Filtros', revision_general: 'Revisión general', otro: 'Otro' };
    const PRIORIDADES = { baja: ['Baja', 'prioridad-baja'], media: ['Media', 'prioridad-media'], alta: ['Alta', 'prioridad-alta'], critica: ['Crítica', 'prioridad-critica'] };
    const SEMAFORO_LABEL = { verde: 'Óptimo', amarillo: 'Atención', rojo: 'Crítico', gris: 'Sin datos' };

    const hoy = new Date();
    $('#dashFecha').text(hoy.toLocaleDateString('es-CO', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }));

    function cargar() {
        $.getJSON(BASE + 'admin/dashboard/resumen')
            .done((r) => {
                // KPIs
                $('#kpiVehiculosActivos').text(r.flota.activos);
                $('#kpiDineroCombustible').text('$' + fmt(r.dinero_mes.combustible));
                $('#kpiDineroGastos').text('$' + fmt(r.dinero_mes.gastos));
                $('#kpiMantAtencion').text(r.mantenimientos.pendientes + r.mantenimientos.vencidos);

                // Gráfica mensual
                new Chart(document.getElementById('graficaMensualDash').getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: r.serie.map((s) => s.mes),
                        datasets: [
                            {
                                label: 'Combustible',
                                data: r.serie.map((s) => s.combustible),
                                borderColor: '#184B44',
                                backgroundColor: '#184B4422',
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#184B44',
                            },
                            {
                                label: 'Otros gastos',
                                data: r.serie.map((s) => s.gastos),
                                borderColor: '#56AD71',
                                backgroundColor: '#56AD7122',
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#56AD71',
                            },
                        ],
                    },
                    options: {
                        maintainAspectRatio: false,
                        scales: { y: { ticks: { callback: (v) => '$' + fmt(v, 0) } } },
                    },
                });

                // Doughnut flota
                new Chart(document.getElementById('graficaFlota').getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Activos', 'Inactivos'],
                        datasets: [{
                            data: [r.flota.activos, r.flota.inactivos],
                            backgroundColor: ['#184B44', '#8D99A6'],
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: { legend: { position: 'bottom' } },
                    },
                });

                // Próximos mantenimientos
                const listaM = $('#listaProximos').empty();

                if ((r.mantenimientos.proximos || []).length === 0) {
                    listaM.append('<p class="small text-secondary mb-0">Todo al día. Sin mantenimientos en los próximos 7 días. 🎉</p>');
                } else {
                    r.mantenimientos.proximos.forEach((m) => {
                        const [prio, clase] = PRIORIDADES[m.prioridad] || PRIORIDADES.media;
                        const dias = Math.max(0, Math.round((new Date(m.fecha_programada + 'T00:00:00') - new Date()) / 86400000));

                        listaM.append(`
                            <div class="dash-fila">
                                <span class="dash-fila-icon"><i class="bi bi-tools"></i></span>
                                <div class="flex-fill">
                                    <div class="fw-medium small">${esc(TIPOS_MANT[m.tipo] || m.tipo)} · ${esc(m.placa)}</div>
                                    <div class="text-secondary small">${esc(m.fecha_programada)} · en ${dias} ${dias === 1 ? 'día' : 'días'}</div>
                                </div>
                                <span class="badge-estado ${clase}">${esc(prio)}</span>
                            </div>`);
                    });
                }

                // Alertas de impacto
                const listaA = $('#listaAlertas').empty();

                if (r.alertas.length === 0) {
                    listaA.append('<p class="small text-secondary mb-0">Sin alertas. Toda la flota dentro de lo esperado. ✅</p>');
                } else {
                    r.alertas.forEach((a) => {
                        listaA.append(`
                            <div class="dash-fila">
                                <span class="semaforo semaforo-${a.semaforo}"></span>
                                <div class="flex-fill">
                                    <div class="fw-medium small">${esc(a.placa)} · ${esc([a.marca, a.modelo].filter(Boolean).join(' '))}</div>
                                    <div class="text-secondary small">
                                        ${a.rendimiento_real != null ? fmt(a.rendimiento_real, 1) + ' km/L real' : 'Sin datos'} ·
                                        ${a.alertas.length ? esc(a.alertas[0]) : SEMAFORO_LABEL[a.semaforo]}
                                    </div>
                                </div>
                            </div>`);
                    });
                }
            })
            .fail(() => {
                $('#listaProximos').html('<p class="small text-danger">No se pudo cargar el resumen.</p>');
            });
    }

    cargar();
})(jQuery);
