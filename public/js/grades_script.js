/**
 * grades_script.js — v5 FINAL
 * Mapa confirmado por consola:
 * td[0] = nombre (student-name-fixed) → saltar
 * td[1..n-2] = notas → leer
 * td[n-1] = bonus (badge-warning) → saltar
 * td[n] = total (unit-result-*) → saltar
 */
$(function () {

    if ($('[data-toggle="popover"]').length) {
        $('[data-toggle="popover"]').popover();
    }

    const CAP = 100;

    function calcularResultados() {
        if (!window._unidades || !window._unidades.length) return;

        const studentIds = [];
        $('.student-row').each(function () {
            const id = $(this).data('student-id');
            if (id !== undefined && id !== '' && studentIds.indexOf(id) === -1) {
                studentIds.push(id);
            }
        });

        studentIds.forEach(function (insId) {
            let sumFinalUnidades = 0;
            let countUnid = 0;

            window._unidades.forEach(function (u) {
                const uid = u.id;
                const row = $(`#pane-${uid} .student-row[data-student-id="${insId}"]`);
                if (!row.length) return;

                let sumaPonderada = 0;
                let sumaPesos = 0;

                // ── MODO EDICIÓN ───────────────────────────────
                const inputs = row.find('input.grade-input');
                if (inputs.length > 0) {
                    inputs.each(function () {
                        const peso = parseFloat($(this).data('weight'));
                        const val  = parseFloat($(this).val());
                        if (!isNaN(peso) && !isNaN(val) && peso > 0) {
                            sumaPonderada += val * (peso / 100);
                            sumaPesos     += peso;
                        }
                    });

                // ── MODO LECTURA ───────────────────────────────
                } else {
                    // Obtener pesos del thead en orden
                    const pesos = [];
                    $(`#table-${uid} thead tr:last-child th`).each(function () {
                        const pond = $(this).find('.badge-pond');
                        if (pond.length) {
                            pesos.push(parseFloat(pond.text()) || 0);
                        }
                    });

                    // Recolectar todos los td de la fila
                    const tds = row.find('td');
                    // td[0] = nombre, td[last] = total, td[last-1] = bonus
                    const totalCols = tds.length;

                    let pesoIdx = 0;
                    // Desde td[1] hasta el ante-penúltimo (totalCols - 3)
                    for (let i = 1; i <= totalCols - 3; i++) {
                        const td  = $(tds[i]);
                        const span = td.find('span:not(.badge)').first();
                        const txt  = span.text().trim();

                        if (txt !== '-' && txt !== '' && pesoIdx < pesos.length) {
                            const val = parseFloat(txt);
                            if (!isNaN(val) && pesos[pesoIdx] > 0) {
                                sumaPonderada += val * (pesos[pesoIdx] / 100);
                                sumaPesos     += pesos[pesoIdx];
                            }
                        }
                        pesoIdx++;
                    }
                }

                // Cálculo proyectado al 100% de la unidad (regla de tres)
                const baseUnidad = sumaPesos > 0
                    ? Math.round((sumaPonderada * 100) / sumaPesos)
                    : 0;

                // ── Bonus ──────────────────────────────────────
                let bVal = 0;
                const bInp = row.find('.bonus-input');
                if (bInp.length) {
                    bVal = parseInt(bInp.val()) || 0;
                    const bMax = parseInt(bInp.data('bonus-max')) || u.bonus_max || 10;
                    if (bVal > bMax) { bInp.val(bMax); bVal = bMax; }
                    if (bVal < 0)   { bInp.val(0);    bVal = 0;    }
                } else {
                    // Modo lectura: td[last-1] tiene el badge de bonus
                    const tds = row.find('td');
                    const bonusTd = $(tds[tds.length - 2]);
                    const badge = bonusTd.find('.badge-warning');
                    if (badge.length) {
                        const match = badge.text().match(/\d+/);
                        bVal = match ? parseInt(match[0]) : 0;
                    }
                }

                const finalUnidad = Math.min(CAP, baseUnidad + bVal);

                // Actualizar spans de resultado (unidad y resumen global)
                $(`.unit-result-${uid}-${insId}`).each(function () {
                    $(this)
                        .text(finalUnidad)
                        .removeClass('text-success text-warning text-danger text-muted')
                        .addClass(
                            finalUnidad >= 70 ? 'text-success' :
                            finalUnidad >= 60 ? 'text-warning' : 'text-danger'
                        );
                });

                sumFinalUnidades += finalUnidad;
                countUnid++;
            });

            // Promedio global
            const avgG = countUnid > 0 ? Math.round(sumFinalUnidades / countUnid) : 0;
            $(`.global-avg-${insId}`)
                .text(avgG)
                .css('color',
                    avgG >= 70 ? '#27ae60' :
                    avgG >= 60 ? '#f39c12' : '#e74c3c'
                );
        });

        actualizarBadgePesos();
    }

    function actualizarBadgePesos() {
        const activePane = $('.tab-pane.show.active');
        if (!activePane.length) return;

        const uid = (activePane.attr('id') || '').replace('pane-', '');
        if (!uid || uid === 'global') {
            $('#unitBadgeContainer').html('');
            return;
        }

        let pesosUnidad = 0;
        $(`#table-${uid} thead .badge-pond`).each(function () {
            pesosUnidad += parseFloat($(this).text()) || 0;
        });

        const color = pesosUnidad === 100 ? 'badge-success'
                    : pesosUnidad > 100   ? 'badge-danger'
                    : 'badge-warning';

        $('#unitBadgeContainer').html(
            `<span class="badge ${color} px-3 py-2 border">
                <i class="fas fa-weight-hanging mr-1"></i>
                Ponderaciones: <strong>${pesosUnidad}%</strong>
                ${pesosUnidad === 100 ? ' ✓' : ' (debe sumar 100%)'}
            </span>`
        );
    }

    // ── EVENTOS ───────────────────────────────────────────────
    $(document).on('input change', '.grade-input', function () {
        let v = parseFloat($(this).val());
        if (!isNaN(v)) {
            if (v > 100) $(this).val(100);
            if (v < 0)   $(this).val(0);
        }
        calcularResultados();
    });

    $(document).on('input change', '.bonus-input', function () {
        const input = $(this);
        const val   = parseInt(input.val()) || 0;
        const row   = input.closest('.student-row');
        const uid   = input.data('unidad-id');
        const hJust = row.find(`.hidden-just-field[data-uid="${uid}"]`);

        if (val > 0 && hJust.length && !hJust.val()) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Justificaci&#243;n de Bonus',
                    text: `Bonus de ${val} pts — &#191;Cu&#225;l es el motivo?`,
                    input: 'textarea',
                    inputPlaceholder: 'Ingresa el motivo...',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Confirmar',
                    cancelButtonText: 'Luego',
                    inputValidator: (v) => { if (!v) return 'El motivo es necesario'; }
                }).then((r) => { if (r.isConfirmed) hJust.val(r.value); });
            }
        }
        calcularResultados();
    });

    $('[data-toggle="tab"]').on('shown.bs.tab', function () {
        actualizarBadgePesos();
        calcularResultados();
    });

    window.filtrarAlumnos = function () {
        const term = ($('#globalSearch').val() || '').toLowerCase();
        $('.student-row').each(function () {
            const name = ($(this).data('name') || '').toLowerCase();
            $(this).toggle(name.includes(term));
        });
    };

    window.ordenarLista = function (type) {
        $('#sortBtns button').removeClass('active');
        $(`#sortBtns button[onclick*="'${type}'"]`).addClass('active');

        $('.sortable-students').each(function () {
            const container = $(this);
            const rows = container.find('.student-row').get();

            rows.sort(function (a, b) {
                if (type === 'default') {
                    return (parseInt($(a).data('orig-index')) || 0) - (parseInt($(b).data('orig-index')) || 0);
                }
                const na = ($(a).data('name') || '').toLowerCase();
                const nb = ($(b).data('name') || '').toLowerCase();
                return type === 'asc' ? na.localeCompare(nb, 'es') : nb.localeCompare(na, 'es');
            });

            $.each(rows, function (i, row) { container.append(row); });
        });
    };

    // Init
    $('.sortable-students').each(function () {
        $(this).find('.student-row').each(function (i) {
            $(this).attr('data-orig-index', i);
        });
    });

    calcularResultados();
});
