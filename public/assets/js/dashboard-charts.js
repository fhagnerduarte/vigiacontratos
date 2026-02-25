/**
 * Dashboard Executivo — Charts ApexCharts
 * Depende da variavel global `dashboardData` definida no Blade.
 */
(function () {
    'use strict';

    if (typeof dashboardData === 'undefined') return;

    // ============================================
    // 0. RadialBar — Score de Gestao (RN-075 a RN-077)
    // ============================================
    if (document.getElementById('chartScoreGestao') && dashboardData.score) {
        var scoreData = dashboardData.score;
        var optionsScore = {
            series: [scoreData.score],
            chart: {
                type: 'radialBar',
                height: 200,
                fontFamily: 'inherit'
            },
            plotOptions: {
                radialBar: {
                    hollow: { size: '60%' },
                    track: { background: '#e5e7eb', strokeWidth: '100%' },
                    dataLabels: {
                        name: {
                            show: true,
                            fontSize: '13px',
                            fontWeight: 600,
                            offsetY: -8,
                            color: scoreData.cor_hex
                        },
                        value: {
                            show: true,
                            fontSize: '28px',
                            fontWeight: 700,
                            offsetY: 4,
                            formatter: function (val) { return Math.round(val); }
                        }
                    }
                }
            },
            labels: [scoreData.classificacao],
            colors: [scoreData.cor_hex],
            stroke: { lineCap: 'round' }
        };

        new ApexCharts(document.getElementById('chartScoreGestao'), optionsScore).render();
    }

    // ============================================
    // 1. Donut — Mapa de Risco (RN-062 a RN-065)
    // ============================================
    if (document.getElementById('chartMapaRisco') && dashboardData.risco) {
        var riscoData = dashboardData.risco;
        var totalRisco = riscoData.baixo + riscoData.medio + riscoData.alto;

        if (totalRisco > 0) {
            var optionsRisco = {
                series: [riscoData.baixo, riscoData.medio, riscoData.alto],
                chart: {
                    type: 'donut',
                    height: 260,
                    fontFamily: 'inherit',
                    events: {
                        dataPointSelection: function (event, chartContext, config) {
                            var niveis = ['baixo', 'medio', 'alto'];
                            var nivel = niveis[config.dataPointIndex];
                            if (nivel) {
                                window.location.href = window.location.pathname.replace(/\/$/, '')
                                    .replace(/\/[^\/]*$/, '') + '/contratos?nivel_risco=' + nivel;
                            }
                        }
                    }
                },
                labels: ['Baixo', 'Medio', 'Alto'],
                colors: ['#22c55e', '#f59e0b', '#ef4444'],
                legend: {
                    position: 'bottom',
                    fontSize: '13px',
                    markers: { width: 10, height: 10, radius: 2 }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '14px',
                                    fontWeight: 600,
                                    formatter: function () {
                                        return totalRisco;
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return Math.round(val) + '%';
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val + ' contrato(s) — clique para filtrar';
                        }
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: { height: 220 },
                        legend: { position: 'bottom' }
                    }
                }]
            };

            new ApexCharts(document.getElementById('chartMapaRisco'), optionsRisco).render();
        }
    }

    // ============================================
    // 2. Bar — Vencimentos por Janela (RN-066/067)
    // ============================================
    if (document.getElementById('chartVencimentos') && dashboardData.vencimentos) {
        var venc = dashboardData.vencimentos;

        var optionsVenc = {
            series: [{
                name: 'Contratos',
                data: [venc['0_30d'], venc['31_60d'], venc['61_90d'], venc['91_120d'], venc['120p']]
            }],
            chart: {
                type: 'bar',
                height: 300,
                fontFamily: 'inherit',
                toolbar: { show: false },
                events: {
                    dataPointSelection: function (event, chartContext, config) {
                        var janelas = ['0-30', '31-60', '61-90', '91-120', '120+'];
                        var janela = janelas[config.dataPointIndex];
                        if (janela) {
                            window.location.href = window.location.pathname.replace(/\/$/, '')
                                .replace(/\/[^\/]*$/, '') + '/painel-risco';
                        }
                    }
                }
            },
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '50%',
                    distributed: true
                }
            },
            colors: ['#ef4444', '#f59e0b', '#3b82f6', '#22c55e', '#6b7280'],
            xaxis: {
                categories: ['0-30 dias', '31-60 dias', '61-90 dias', '91-120 dias', '> 120 dias'],
                labels: { style: { fontSize: '12px' } }
            },
            yaxis: {
                labels: { style: { fontSize: '12px' } },
                forceNiceScale: true
            },
            legend: { show: false },
            dataLabels: {
                enabled: true,
                style: { fontSize: '13px', fontWeight: 'bold' }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return val + ' contrato(s) — clique para ver detalhes';
                    }
                }
            },
            responsive: [{
                breakpoint: 480,
                options: { chart: { height: 250 } }
            }]
        };

        new ApexCharts(document.getElementById('chartVencimentos'), optionsVenc).render();
    }

    // ============================================
    // 3. Mixed — Tendencias 12 Meses (RN-078)
    // ============================================
    if (document.getElementById('chartTendencias') && dashboardData.tendencias && dashboardData.tendencias.length > 0) {
        var tend = dashboardData.tendencias;
        var labels = tend.map(function (t) { return t.label; });
        var contratos = tend.map(function (t) { return t.contratos_ativos; });
        var volume = tend.map(function (t) { return t.volume_financeiro; });
        var risco = tend.map(function (t) { return t.risco_medio; });

        var optionsTend = {
            series: [
                {
                    name: 'Volume Financeiro (R$)',
                    type: 'column',
                    data: volume
                },
                {
                    name: 'Contratos Ativos',
                    type: 'line',
                    data: contratos
                },
                {
                    name: 'Risco Medio',
                    type: 'line',
                    data: risco
                }
            ],
            chart: {
                height: 350,
                type: 'line',
                fontFamily: 'inherit',
                toolbar: { show: false },
                stacked: false
            },
            colors: ['#3b82f6', '#22c55e', '#ef4444'],
            stroke: {
                width: [0, 3, 3],
                curve: 'smooth'
            },
            plotOptions: {
                bar: {
                    borderRadius: 3,
                    columnWidth: '40%'
                }
            },
            fill: {
                opacity: [0.85, 1, 1]
            },
            xaxis: {
                categories: labels,
                labels: { style: { fontSize: '11px' }, rotate: -45 }
            },
            yaxis: [
                {
                    title: { text: 'Volume (R$)' },
                    labels: {
                        style: { fontSize: '11px' },
                        formatter: function (val) {
                            if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M';
                            if (val >= 1000) return (val / 1000).toFixed(0) + 'K';
                            return val;
                        }
                    }
                },
                {
                    opposite: true,
                    title: { text: 'Qtd / Score' },
                    labels: { style: { fontSize: '11px' } }
                }
            ],
            legend: {
                position: 'top',
                horizontalAlign: 'left',
                fontSize: '13px'
            },
            tooltip: {
                shared: true,
                intersect: false,
                y: {
                    formatter: function (val, opts) {
                        if (opts.seriesIndex === 0) {
                            return 'R$ ' + val.toLocaleString('pt-BR', { minimumFractionDigits: 2 });
                        }
                        return val;
                    }
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: { height: 280 },
                    legend: { position: 'bottom' }
                }
            }]
        };

        new ApexCharts(document.getElementById('chartTendencias'), optionsTend).render();
    }

})();
