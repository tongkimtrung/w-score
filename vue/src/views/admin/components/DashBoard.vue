<template>
    <div class="main-view flex1 flex-column position-relative">
        <div class="form-list flex-column h-100 dashboard">
            <div class="flex-row title-box">
                <div class="list-title flex">Tổng quan</div>
            </div>
            <div class="content-container">
                <div class="card-container">
                    <div class="card blue">
                        <div class="header">Ngân hàng đề thi</div>
                        <div class="body">{{ countExamBank }}</div>
                    </div>
                    <div class="card green">
                        <div class="header">Kì thi</div>
                        <div class="body">{{ countExam }}</div>
                    </div>
                    <div class="card purple">
                        <div class="header">Phòng thi</div>
                        <div class="body">{{ countDepartment }}</div>
                    </div>
                    <div class="card orange">
                        <div class="header">Người dùng</div>
                        <div class="body">{{ countUser }}</div>
                    </div>
                </div>
                <div class="flex-column flex-text group-chart">
                    <div class="chart-container" style="margin-bottom: 16px;">
                        <div class="chart flex-column">
                            <div class="header">
                                <div class="title p-text-truncate"> Tổng hợp kết quả điểm</div>
                                <div class="filter-group">
                                    <div class="flex-row">
                                        <Button icon="pi pi-check" aria-label="Filter"
                                                class="ms-button btn btn-filter secondary only-icon">
                                            <div class="icon24 icon left filter"></div>
                                        </Button>
                                    </div>
                                </div>
                            </div>
                            <div class="sub-header p-text-truncate">Theo kì thi</div>
                            <div class="main">
                                <div class="body d-flex">
                                    <div class="w-full d-flex flex1">
                                        <Chart type="doughnut" :data="chartData" :options="chartOptions"
                                               :plugins="plugins"
                                               class="w-full"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="chart flex-column">
                            <div class="header">
                                <div class="title p-text-truncate">Thống kê ngân hàng đề thi</div>
                                <div class="filter-group">
                                    <div class="flex-row">
                                        <Button icon="pi pi-check" aria-label="Filter"
                                                class="ms-button btn btn-filter secondary only-icon">
                                            <div class="icon24 icon left filter"></div>
                                        </Button>
                                    </div>
                                </div>
                            </div>
                            <div class="sub-header p-text-truncate"> Dữ liệu về ngân hàng đề thi</div>
                            <div class="main">
                                <div class="body d-flex">
                                    <div class="w-full d-flex flex1">
                                        <Chart type="bar" :data="chartDataExam" :options="chartOptionsExam"
                                               class="w-full"/>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="chart-container">
                        <div class="chart flex-column">
                            <div class="header">
                                <div class="title p-text-truncate">Thống kê ngân hàng đề thi</div>
                                <div class="filter-group">
                                    <div class="flex-row">
                                        <Button icon="pi pi-check" aria-label="Filter"
                                                class="ms-button btn btn-filter secondary only-icon">
                                            <div class="icon24 icon left filter"></div>
                                        </Button>
                                    </div>
                                </div>
                            </div>
                            <div class="sub-header p-text-truncate"> Dữ liệu về ngân hàng đề thi</div>
                            <div class="main">
                                <div class="body d-flex">
                                    <div class="w-full d-flex flex1">
                                        <Chart type="bar" :data="chartDataExam" :options="chartOptionsExam"
                                               class="w-full"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="chart flex-column">
                            <div class="header">
                                <div class="title p-text-truncate">Thống kê ngân hàng đề thi</div>
                                <div class="filter-group">
                                    <div class="flex-row">
                                        <Button icon="pi pi-check" aria-label="Filter"
                                                class="ms-button btn btn-filter secondary only-icon">
                                            <div class="icon24 icon left filter"></div>
                                        </Button>
                                    </div>
                                </div>
                            </div>
                            <div class="sub-header p-text-truncate"> Dữ liệu về ngân hàng đề thi</div>
                            <div class="main">
                                <div class="body d-flex">
                                    <div class="w-full d-flex flex1">
                                        <Chart type="bar" :data="chartDataExam" :options="chartOptionsExam"
                                               class="w-full"/>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <TheLoadingProgress v-if="isLoading" :useBackground="true"/>
    </div>
</template>

<script>
import Chart from 'primevue/chart';
import Button from "primevue/button";
import ChartDataLabels from 'chartjs-plugin-datalabels';
import TheLoadingProgress from "@/components/LoadingProgress.vue";
import {get} from '/api/dash-board'

export default {
    components: {
        Chart,
        Button,
        TheLoadingProgress
    },
    data() {
        return {
            countExamBank: 0,
            countExam: 0,
            countDepartment: 0,
            countUser: 0,
            isLoading: false,
            plugins: [ChartDataLabels],
            chartData: {
                labels: ['Dưới 5 điểm', 'Từ 5 - 6.5 điểm', 'Từ 6.5 - 8 điểm', 'Từ 8 - 9 điểm', 'Từ 9 - 10 điểm'],
                datasets: [
                    {
                        data: null,
                        backgroundColor: [
                            'rgb(255, 109, 0)',
                            'rgba(255, 159, 64)',
                            'rgb(68, 138, 255)',
                            '#5b4dff',
                            'rgb(53, 189, 123)',
                        ],

                        hoverOffset: 10,
                    }
                ],
            },

            chartOptions: {
                plugins: {
                    datalabels: {
                        color: '#fff',
                        textAlign: 'center',
                        font: {
                            size: 13,
                            align: 'center',
                            family: "GoogleSans",
                        },
                    },

                    tooltip: {
                        enabled: true,
                        callbacks: {
                            label: function (context) {
                                var label = context.dataset.data[context.dataIndex] || '';
                                if (label) {
                                    label += ': ';
                                }
                                var percentage = ((context.raw / context.chart.data.datasets[0].data.reduce((a, b) => a + b)) * 100).toFixed(2);
                                return percentage + '%';
                            }
                        }
                    },
                    legend: {
                        position: 'right',
                        align: 'center',
                        labels: {
                            font: {
                                size: 14,
                                family: "GoogleSans"
                            },
                            useBorderRadius: true,
                            borderRadius: 5
                        },
                        textDirection: 'rtl'
                    },

                },
                cutout: '50%',
                responsive: true,
                cutoutPercentage: 50,
                maintainAspectRatio: false,
                layout: {
                    padding: 20
                },
            },

            chartDataExam: {
                labels: ['Chưa thiết lập', 'Đã thiết lập', 'Không sử dụng' ,'Đang sử dụng'],
                datasets: [
                    {
                        label: '',
                        data: [0, 0],
                        backgroundColor: [
                            'rgb(255, 109, 0)',
                            'rgba(255, 159, 64)',
                            'rgb(68, 138, 255)',
                            '#5b4dff',
                        ],
                        minBarLength: 2,
                        barThickness: 30,
                        hoverOffset: 10,
                    }
                ],
            },

            chartOptionsExam: {
                plugins: {
                    datalabels: {
                        color: '#fff',
                        textAlign: 'center',
                        font: {
                            size: 13,
                            align: 'center',
                            family: "GoogleSans",
                        },
                    },

                    legend: {
                        position: 'top',
                        align: 'center',
                        labels: {
                            font: {
                                size: 14,
                                family: "GoogleSans"
                            },
                            useBorderRadius: true,
                            borderRadius: 5
                        },
                        textDirection: 'rtl'
                    },

                },
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: 20
                },

                scales: {

                    y: {
                        min: 0,
                        max: 10,
                        ticks: {
                            stepSize: 2,
                        }
                    }
                }
            }
        }
    },
    methods: {
        async loadData() {
            this.isLoading = true;
            await get().then(res => {
                let data = res.data;
                this.countUser = data.countUser
                this.countDepartment = data.countDepartment
                this.countExam = data.countExam
                this.countExamBank = data.countExamBank
                this.chartDataExam.datasets[0].data = data.bankStatistics
                this.chartData.datasets[0].data = [data.examStatistics.score_range_0_5, data.examStatistics.score_range_5_6_5, data.examStatistics.score_range_6_5_8, data.examStatistics.score_range_8_9, data.examStatistics.score_range_9_10];
            }).catch(error => {
                console.log(error)
            }).finally(() => {
                setTimeout(() => {
                    this.isLoading = false;
                }, 300);
            })
        }
    },
    async created() {
        await this.loadData();
    }
}
</script>

<style lang="scss">
.dashboard {
    position: relative;
}

.content-container {
    overflow: hidden;
    width: 100%;
    height: 100%;
    overflow-y: auto;
    overflow-x: hidden;
    position: relative;
}

.card-container {
    display: flex;
    margin: 0 -8px 16px;
}

.card-container .card.blue {
    background-color: #448aff !important;
    background-image: url('@public/assets/icons/bg_card_1.6e2cd149.svg');
}

.card-container .card.green {
    background-color: #35bd7b !important;
    background-image: url('@public/assets/icons/bg_card_2.dbda94db.svg');
}

.card-container .card.purple {
    background-color: #5b4dff !important;
    background-image: url('@public/assets/icons/bg_card_3.e0fea051.svg');
}

.card-container .card.orange {
    background-color: #ff6d00 !important;
    background-image: url('@public/assets/icons/bg_card_4.f5d74bf9.svg');
}

.card-container .card {
    width: calc(50% - 16px);
    background-color: #fff !important;
    border-radius: 8px !important;
    border: unset;
    padding: 16px;
    margin: 0 8px;
    color: #fff;
    background-position: bottom;
    background-repeat: no-repeat;
    background-size: cover;
}

.card-container .card .header {
    font-weight: 700;
    font-size: 14px;
}

.card-container .card .body {
    font-weight: 700;
    font-size: 32px;
    text-align: right;
    line-height: 32px;
}

.group-chart {
    height: calc(100% - 100px);
}

.mb-16 {
    margin-bottom: 16px;
}

.chart-container {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -8px;
    height: 50%;
}

.chart-container .chart {
    width: calc(50% - 16px);
    height: 100%;
    background-color: #fff;
    border-radius: 8px;
    padding: 16px;
    margin: 0 8px;
    box-shadow: 0 0 11px rgba(0, 0, 0, .08);
}

.chart-container .chart .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

.chart-container .chart .header .title {
    font-weight: 700;
    font-size: 16px;
    padding-right: 40px;
}

.filter-group .btn-filter {
    z-index: 6;
    border: none;
}

.chart-container .chart .sub-header {
    color: #707070;
    padding-bottom: 4px;
    padding-right: 40px;
}

.p-button.secondary.btn-filter:hover {
    border: none !important;
}

.p-text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.filter-group {
    position: absolute;
    right: -10px;
    top: -12px;
}

.filter-group {
    --primary: #fff;
    --gray: #fbfbfe;
    position: relative;
}

.chart-container .chart .main {
    position: relative;
    height: 100%;
    flex: 1;
}

.chart-container .chart .body {
    height: 100%;
    width: 100%;
}
</style>
