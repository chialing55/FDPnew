//名錄篩選
$("#spTable").tablesorter({
    widgets: ["filter"],
    widgetOptions: {
        filter_functions: {
            // 自定義篩選函數，這個例子是針對帶有 Font Awesome 圖標的列
            3: customFilterFunction,
            4: customFilterFunction,
            5: customFilterFunction,
            6: customFilterFunction,
        },
    },
    textExtraction: {
        3: function (node, table, cellIndex) {
            // 提取 data-value 屬性的值
            return $(node).attr("data-value") || $(node).text();
        },
        4: function (node, table, cellIndex) {
            // 提取 data-value 屬性的值
            return $(node).attr("data-value") || $(node).text();
        },
        5: function (node, table, cellIndex) {
            // 提取 data-value 屬性的值
            return $(node).attr("data-value") || $(node).text();
        },
        6: function (node, table, cellIndex) {
            // 提取 data-value 屬性的值
            return $(node).attr("data-value") || $(node).text();
        },
    },
});

// 共用的自定義篩選函數
function customFilterFunction(e, n, f, i, $r, c, data) {
    // 檢查 filter input 的值是否為 "1" 或 "0"

    return $r.find("td:eq(" + i + ")").data("value") == f;

    // 如果 filter input 的值不是 "1" 或 "0"，使用默認的篩選邏輯
    return $.tablesorter.filterFormatter.ui(e, n, f, i, $r, c, data);
}

$("input[data-column=0]").attr("placeholder", "keyword");
$("input[data-column=1]").css("width", "200px").attr("placeholder", "keyword");
$("input[data-column=2]").css("width", "100px").attr("placeholder", "keyword");
$('input[data-column="3"], input[data-column="4"], input[data-column="5"], input[data-column="6"]')
    .css("width", "40px")
    .attr("placeholder", "1/0");

//照片顯示
Fancybox.bind('[data-fancybox="gallery"]', {
    // Your custom options
});

let fig1;
let fig2;
let fig3;
let fig4;
let fig5;
let fig6;
let fig7;
let fig8;
let fig9;
let fig10;

const speciesDistributionPalette = [
    // DBH classes form a continuous visual sequence from small to large.
    // Saturation and opacity stay close to the stem-count chart palette.
    { background: "#F0D84F", border: "#BCA72B" },
    { background: "#B6C84A", border: "#869A2D" },
    { background: "#79B85C", border: "#518D3C" },
    { background: "#3FA67B", border: "#287E59" },
    { background: "#3186A3", border: "#24637C" },
    { background: "#405A96", border: "#2E406E" },
];

function speciesDistributionColor(index) {
    return speciesDistributionPalette[index % speciesDistributionPalette.length];
}

const speciesChartAreaBorder = {
    id: 'speciesChartAreaBorder',
    afterDraw(chart) {
        const { ctx, chartArea } = chart;
        if (!chartArea) return;

        const { left, top, right, bottom } = chartArea;
        ctx.save();
        ctx.strokeStyle = '#d1d5db';
        ctx.lineWidth = 1;
        ctx.strokeRect(
            left + 0.5,
            top + 0.5,
            Math.max(0, right - left - 1),
            Math.max(0, bottom - top - 1),
        );
        ctx.restore();
    },
};

function figtoggle(k) {
    $(`.fig${k}`).show().addClass("is-ready");
}

function restoreSpeciesChartReadyStates() {
    if (!window.Chart) {
        return;
    }

    for (let k = 1; k <= 10; k += 1) {
        const canvas = document.getElementById(`myChartFig${k}`);
        const frame = canvas?.closest(".species-chart-frame");

        if (canvas && frame && Chart.getChart(canvas)) {
            frame.classList.add("is-ready");
        }
    }
}

async function startSpeciesChartQueue() {
    const root = document.querySelector("[data-species-chart-root]");
    if (!root || root.dataset.chartQueueStarted === "yes" || !window.Livewire) {
        return;
    }

    const componentEl = root.closest("[wire\\:id]");
    if (!componentEl) {
        return;
    }

    let chartMethods = [];

    try {
        chartMethods = JSON.parse(root.dataset.chartMethods || "[]");
    } catch (error) {
        chartMethods = [];
    }

    if (chartMethods.length === 0) {
        return;
    }

    const wire = Livewire.find(componentEl.getAttribute("wire:id"));
    if (!wire) {
        return;
    }

    root.dataset.chartQueueStarted = "yes";

    for (const method of chartMethods) {
        try {
            if (typeof wire[method] === "function") {
                await wire[method]();
                restoreSpeciesChartReadyStates();
            }
        } catch (error) {
            console.error(`Failed to load chart method: ${method}`, error);
        }
    }

    requestAnimationFrame(restoreSpeciesChartReadyStates);
}

document.addEventListener("livewire:initialized", () => {
    startSpeciesChartQueue();
});

document.addEventListener("DOMContentLoaded", () => {
    setTimeout(startSpeciesChartQueue, 0);
});

document.addEventListener('livewire:init', () => {
  if (window.__boundFig1Event) return;
  window.__boundFig1Event = true;

  Livewire.on('fig1', ({ censusA, censusR, censusD }) => {
    drawChart1(censusA, censusR, censusD);
    figtoggle(1);
  });
});

document.addEventListener('livewire:init', () => {
  if (window.__boundFig7Event) return;
  window.__boundFig7Event = true;

  Livewire.on('fig7', ({ seedlingSeries }) => {
    drawChart7(seedlingSeries);
    figtoggle(7);
  });
});

function drawChart7(seedlingSeries) {
    const canvas = document.getElementById("myChartFig7");
    if (!canvas) {
        return;
    }

    const previousChart = Chart.getChart(canvas);
    if (previousChart) {
        previousChart.destroy();
    }

    fig7 = new Chart(canvas, {
        type: "line",
        data: {
            datasets: [{
                data: seedlingSeries,
                pointStyle: false,
                borderColor: "#a7c957",
            }],
        },
        options: {
            animation: false,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: "小苗密度(小苗數/m²)",
                    },
                },
            },
        },
    });
}

document.addEventListener('livewire:init', () => {
  if (window.__boundShoushanSpeciesCharts) return;
  window.__boundShoushanSpeciesCharts = true;

  Livewire.on('fig8', ({ censusA, censusR, censusD }) => {
    fig8 = drawSpeciesBarChart('myChartFig8', [
      { label: 'Alive', data: censusA, backgroundColor: '#87CEEB' },
      { label: 'Recruit', data: censusR, backgroundColor: '#ff9eb5' },
      { label: 'Dead', data: censusD, backgroundColor: '#ffc98f' },
    ]);
    figtoggle(8);
  });

  Livewire.on('fig9', ({ groupedCounts }) => {
    fig9 = drawSpeciesBarChart('myChartFig9', [{
      data: groupedCounts,
      backgroundColor: '#87CEEB',
    }], false);
    figtoggle(9);
  });

  Livewire.on('fig10', ({ points }) => {
    const canvas = document.getElementById('myChartFig10');
    if (!canvas) return;
    Chart.getChart(canvas)?.destroy();

    const dbhGroups = [
      { label: '<5', min: 0, max: 5, radius: 2 },
      { label: '5-10', min: 5, max: 10, radius: 3 },
      { label: '10-20', min: 10, max: 20, radius: 4 },
      { label: '>20', min: 20, max: Infinity, radius: 5 },
    ];

    const datasets = dbhGroups.map((group, index) => {
      const color = speciesDistributionColor(dbhGroups.length - 1 - index);

      return {
        label: group.label,
        data: points.filter((point) => point.dbh >= group.min && point.dbh < group.max),
        backgroundColor: color.background,
        borderColor: color.border,
        pointStyle: 'circle',
        pointRadius: group.radius,
      };
    }).filter((dataset) => dataset.data.length > 0);

    const shoushanMap = new Image();
    shoushanMap.src = '/images/web/Shoushan_map.jpg';

    const shoushanMapPlugin = {
      id: 'shoushanMapBackground',
      beforeDraw(chart) {
        if (!shoushanMap.complete) {
          shoushanMap.onload = () => chart.draw();
          return;
        }

        const { ctx, chartArea } = chart;
        if (!chartArea) return;

        const { left, top, width, height } = chartArea;
        ctx.save();
        ctx.beginPath();
        ctx.rect(left, top, width, height);
        ctx.clip();
        ctx.drawImage(shoushanMap, left, top, width, height);
        ctx.restore();
      },
    };

    fig10 = new Chart(canvas, {
      type: 'scatter',
      data: { datasets },
      plugins: [shoushanMapPlugin],
      options: {
        animation: false,
        maintainAspectRatio: true,
        // Canvas 稍微預留圖例與軸標題空間後，實際繪圖區接近 150:70，
        // 讓 X、Y 軸每 10 m 的網格尺寸一致且刻度軸保持貼齊。
        aspectRatio: 1.9,
        plugins: {
          legend: {
            display: true,
            align: 'end',
            labels: {
              usePointStyle: true,
              usePointRadius: true,
            },
          },
          tooltip: {
            callbacks: {
              label: (context) => {
                const point = context.raw;
                return `${point.tag} | DBH: ${point.dbh} cm | (${point.x}, ${point.y})`;
              },
            },
          },
        },
        scales: {
          x: {
            min: 0,
            max: 150,
            ticks: { stepSize: 10 },
          },
          y: {
            min: 0,
            max: 70,
            ticks: { stepSize: 10 },
          },
        },
      },
    });
    figtoggle(10);
  });
});

function drawSpeciesBarChart(canvasId, datasets, showLegend = true) {
    const canvas = document.getElementById(canvasId);
    if (!canvas) return null;
    Chart.getChart(canvas)?.destroy();

    return new Chart(canvas, {
        type: 'bar',
        data: { datasets },
        plugins: [speciesChartAreaBorder],
        options: {
            animation: false,
            plugins: {
                legend: { display: showLegend, align: 'end' },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                },
            },
        },
    });
}


//各次調查植株數量圖
function drawChart1(censusA, censusR, censusD) {
    var ctx1 = $("#myChartFig1");
    var previousChart = Chart.getChart(ctx1);
    if (previousChart) {
        previousChart.destroy();
    }

    const config = {
        type: "bar",
        plugins: [speciesChartAreaBorder],
        data: {
            // labels: ['1', '2', '3', '4'],
            datasets: [
                {
                    label: "Alive",
                    data: censusA,
                },
                {
                    label: "Recruit",
                    data: censusR,
                },
                {
                    label: "Dead",
                    data: censusD,
                },
            ],
        },
        options: {
            animation: false,
            plugins: {
                legend: {
                    align: "end",
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                },
            },
        },
    };

    var scatterChart = new Chart(ctx1, config);
    fig1 = "yes";
}



document.addEventListener('livewire:init', () => {
  if (window.__boundFig2Event) return;
  window.__boundFig2Event = true;

  Livewire.on('fig2', ({ groupedCounts }) => {
    drawChart2(groupedCounts);
    figtoggle(2);
  });
});


// 最新一次調查徑級結構
function drawChart2(groupedCounts) {
    var ctx2 = $("#myChartFig2");
    var previousChart = Chart.getChart(ctx2);
    if (previousChart) {
        previousChart.destroy();
    }
    const config = {
        type: "bar",
        plugins: [speciesChartAreaBorder],
        data: {
            // labels: ['1', '2', '3', '4'],
            datasets: [
                {
                    // label: '',
                    data: groupedCounts,
                },
            ],
        },
        options: {
            animation: false,
            plugins: {
                legend: {
                    display: false, // 禁用图例
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                },
            },
            barThickness: 50, //barwidth
        },
    };

    var scatterChart = new Chart(ctx2, config);
    fig2 = "yes";
}


document.addEventListener('livewire:init', () => {
  if (window.__boundFig3Event) return;
  window.__boundFig3Event = true;

  Livewire.on('fig3', ({ group }) => {
    drawChart3(group);
    figtoggle(3);
  });
});

// 最新一次調查植株位置分布
function drawChart3(group) {
    var ctx3 = $("#myChartFig3");
    var previousChart = Chart.getChart(ctx3);
    if (previousChart) {
        previousChart.destroy();
    }

    var group1 = {};
    var data = [];
    var scatterDataLabels = [];

    var pointRadiusIncrement = 1; // 遞增的圓點大小

    const groupNames = Object.keys(group);

    groupNames.forEach(function (groupName, groupIndex) {
        if (group[groupName].length !== 0) {
            group1[groupName] = group[groupName].map(function (item) {
                return {
                    x: item.plotx / 20,
                    y: item.ploty / 20,
                    tag: item.tag,
                    dbh: item.dbh,
                    qx: item.qx,
                    qy: item.qy,
                    sqx: item.sqx,
                    sqy: item.sqy,
                };
            });

            scatterDataLabels[groupName] = group[groupName].map(function (
                item
            ) {
                return [item.dbh];
            });
            // 將數據集添加到數據集陣列中
            const reverseIndex = groupNames.length - 1 - groupIndex;
            const color = speciesDistributionColor(reverseIndex);
            data.push({
                label: groupName, // 使用 groupName 作為標籤
                data: group1[groupName],
                backgroundColor: color.background,
                borderColor: color.border,
                pointStyle: "circle",
                pointRadius: pointRadiusIncrement, // 設置 pointRadius
            });
            pointRadiusIncrement++; // 增加圓點大小
        }
    });

    const mapimg = new Image();
    mapimg.src = `/images/web/fs.elev.jpg`;

    // Chart.defaults.borderColor = 'blue';
    const chartAreaPlugins = {
        id: "chartAreaPlugins",

        beforeDraw: (chart, args, options) => {
            if (mapimg.complete) {
                const ctx = chart.ctx;
                const { top, left, width, height } = chart.chartArea;

                ctx.drawImage(mapimg, left, top, width, height);
            } else {
                mapimg.onload = () => chart.draw();
            }
        },
    };

    // console.log(group1);

    const config = {
        type: "scatter",
        data: {
            // labels: scatterDataLabels,
            datasets: data,
        },
        plugins: [chartAreaPlugins],
        options: {
            animation: false,
            aspectRatio: 1,
            maintainAspectRatio: true,
            scales: {
                x: {
                    // 福山 500 × 500 m 樣區；座標除以 20 後固定為 0–25。
                    // 固定完整範圍，避免依單一物種分布自動縮放而裁切底圖。
                    min: 0,
                    max: 25,
                    border: {
                        // 框線繪製
                        display: true,
                        drawOnChartArea: false,
                        drawTicks: true,
                        // 框線顏色
                        color: "#cccccc",
                        // 框線寬度
                        width: 1,
                        z: 1,
                    },
                },
                y: {
                    min: 0,
                    max: 25,
                    border: {
                        // 框線繪製
                        display: true,
                        drawOnChartArea: false,
                        drawTicks: true,
                        // 框線顏色
                        color: "#cccccc",
                        // 框線寬度
                        width: 1,
                        z: 1,
                    },
                },
            },
            plugins: {
                legend: {
                    display: true,
                    // position: "right",
                    align: "end",
                    labels: {
                        // boxWidth: 20,
                        usePointStyle: true,
                        usePointRadius: true,
                    },
                },
                tooltip: {
                    callbacks: {
                        label: function (ctx) {
                            if (ctx.dataset?.data?.[ctx.dataIndex]) {
                                const point = ctx.dataset.data[ctx.dataIndex];
                                return `(${point.qx},${point.qy})(${point.sqx},${point.sqy}) | ${point.tag} | DBH: ${point.dbh}`;
                            }
                            return "";
                        },
                    },
                },
            },
        },
    };

    var scatterChart = new Chart(ctx3, config);
    fig3 = "yes";
}

//開花量時間變化

document.addEventListener('livewire:init', () => {
  if (window.__boundFig4Event) return;
  window.__boundFig4Event = true;

  Livewire.on('fig4', ({ flowerSeries }) => {
    drawChart4(flowerSeries);
    figtoggle(4);
  });
});

//chart作圖
function drawChart4(flowerSeries) {
    var ctx4 = $("#myChartFig4");
    var previousChart = Chart.getChart(ctx4);
    if (previousChart) {
        previousChart.destroy();
    }
    // const formattedData = Object.keys(flowerSeries).map(date => {
    //     return {
    //         x: new Date(date),
    //         y: flowerSeries[date]
    //     };
    // });
    // console.log(formattedData);
    const config = {
        type: "line",
        data: {
            // labels: dateSeries,
            datasets: [
                {
                    // label: '',
                    data: flowerSeries,
                    pointStyle: false,
                    borderColor: "#f4a261",
                },
            ],
        },
        options: {
            animation: false,
            plugins: {
                legend: {
                    display: false, // 禁用图例
                },
            },
            scales: {
                y: {
                    title: {
                        display: true,
                        text: "開花強度(網次比例/月)", // y 轴的标签
                    },
                },
            },
        },
    };

    var scatterChart = new Chart(ctx4, config);
    fig4 = "yes";
}

//結果量時間變化

document.addEventListener('livewire:init', () => {
  if (window.__boundFig5Event) return;
  window.__boundFig5Event = true;

  Livewire.on('fig5', (payload) => {
    drawChart5(payload?.fruitsSeries || {});
    figtoggle(5);
  });
});

//chart作圖
function drawChart5(fruitsSeries) {
    var ctx5 = $("#myChartFig5");
    var previousChart = Chart.getChart(ctx5);
    if (previousChart) {
        previousChart.destroy();
    }
    // const formattedData = Object.keys(flowerSeries).map(date => {
    //     return {
    //         x: new Date(date),
    //         y: flowerSeries[date]
    //     };
    // });
    // console.log(formattedData);
    const config = {
        type: "line",
        data: {
            // labels: dateSeries,
            datasets: [
                {
                    // label: '',
                    data: fruitsSeries,
                    pointStyle: false,
                    borderColor: "#6d597a",
                },
            ],
        },
        options: {
            animation: false,
            plugins: {
                legend: {
                    display: false, // 禁用图例
                },
            },
            scales: {
                y: {
                    title: {
                        display: true,
                        text: "種子密度(種子數/m²)", // y 轴的标签
                    },
                },
            },
        },
    };

    var scatterChart = new Chart(ctx5, config);
    fig5 = "yes";
}

//結果量時間變化

document.addEventListener('livewire:init', () => {
  if (window.__boundFig6Event) return;
  window.__boundFig6Event = true;

  Livewire.on('fig6', ({ seedlingSeries }) => {
    drawChart6(seedlingSeries);
    figtoggle(6);
  });
});

//chart作圖
function drawChart6(seedlingSeries) {
    var ctx6 = $("#myChartFig6");
    var previousChart = Chart.getChart(ctx6);
    if (previousChart) {
        previousChart.destroy();
    }

    const config = {
        type: "line",
        data: {
            // labels: dateSeries,
            datasets: [
                {
                    // label: '',
                    data: seedlingSeries,
                    pointStyle: false,
                    borderColor: "#a7c957",
                },
            ],
        },
        options: {
            animation: false,
            plugins: {
                legend: {
                    display: false, // 禁用图例
                },
            },
            scales: {
                y: {
                    title: {
                        display: true,
                        text: "小苗密度(小苗數/m²)", // y 轴的标签
                    },
                },
            },
        },
    };

    var scatterChart = new Chart(ctx6, config);
    fig6 = "yes";
}
