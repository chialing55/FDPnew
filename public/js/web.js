


//名錄篩選
    $("#spTable").tablesorter({
        widgets: ['filter'],
        widgetOptions : {
            filter_functions: {
                // 自定義篩選函數，這個例子是針對帶有 Font Awesome 圖標的列
                3: customFilterFunction,
                4: customFilterFunction,
                5: customFilterFunction
                
            }
        },
        textExtraction: {
            3: function(node, table, cellIndex) {
                // 提取 data-value 屬性的值
                return $(node).attr('data-value') || $(node).text();
            },
            4: function(node, table, cellIndex) {
                // 提取 data-value 屬性的值
                return $(node).attr('data-value') || $(node).text();
            },
            5: function(node, table, cellIndex) {
                // 提取 data-value 屬性的值
                return $(node).attr('data-value') || $(node).text();
            }
        },
    });

// 共用的自定義篩選函數
function customFilterFunction(e, n, f, i, $r, c, data) {
    // 檢查 filter input 的值是否為 "1" 或 "0"
    
    return $r.find('td:eq(' + i + ')').data('value') == f;
    
    // 如果 filter input 的值不是 "1" 或 "0"，使用默認的篩選邏輯
    return $.tablesorter.filterFormatter.ui(e, n, f, i, $r, c, data);
}

    $('input[data-column=0]').attr('placeholder', 'keyword');
    $('input[data-column=1]').css('width', '200px').attr('placeholder', 'keyword');
    $('input[data-column=2]').css('width', '100px').attr('placeholder', 'keyword');
    $('input[data-column="3"], input[data-column="4"], input[data-column="5"]')
    	.css('width', '40px').attr('placeholder', '1/0');


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

function figtoggle(k){
    $(`.fig${k}creat`).hide();
    $(`.fig${k}show`).show();
    $(`.fig${k}`).show();

    for (let i = 1; i < 7; i++) {
        let fig = eval(`fig${i}`);
        if (i == k) continue;
        if (fig == 'yes') {
            $(`.fig${i}creat`).hide();
            $(`.fig${i}show`).show();
            $(`.fig${i}`).show();
            // console.log(i);
        }
    }
}

window.addEventListener('fig1', event => {

    const censusA=event.detail.censusA;
    const censusR=event.detail.censusR;
    const censusD=event.detail.censusD;  
    console.log(censusA);
    drawChart1(censusA, censusR, censusD);
    figtoggle(1);

});


//各次調查植株數量圖
function drawChart1(censusA, censusR, censusD){

    var ctx1 = $('#myChartFig1');
    var previousChart = Chart.getChart(ctx1);
    if (previousChart) {
        previousChart.destroy();
    }

       const config={
            type: 'bar',
            data: {
                // labels: ['1', '2', '3', '4'],
                datasets: [{
                    label: 'Alive',
                    data: censusA,
                },{
                    label: 'Recruit',
                    data: censusR,
                },{
                    label: 'Dead',
                    data: censusD,
                }
                ],
            },
            options :{
                plugins :{
                    legend: {
                        align: "end",
                    },
                }
            }
            
        };


        var scatterChart = new Chart(
          ctx1,
          config
        )
        fig1='yes';
}


window.addEventListener('fig2', event => {

    const groupedCounts=event.detail.groupedCounts;

    drawChart2(groupedCounts);
    figtoggle(2);
});


//第四次調查徑級結構
function drawChart2(groupedCounts){

    var ctx2 = $('#myChartFig2');
    var previousChart = Chart.getChart(ctx2);
    if (previousChart) {
        previousChart.destroy();
    }
       const config={
            type: 'bar',
            data: {
                // labels: ['1', '2', '3', '4'],
                datasets: [{
                    // label: '',
                    data: groupedCounts,
                }]
            },
            options: {
              plugins: {
                legend: {
                        display: false // 禁用图例
                },
              },
              barThickness: 50,   //barwidth

            }
        };


        var scatterChart = new Chart(
          ctx2,
          config
        )
        fig2='yes';
}


window.addEventListener('fig3', event => {

    const census4A=event.detail.census4A;
    const group=event.detail.group;

    drawChart3(census4A, group);
    figtoggle(3);

});


//第四次調查植株位置分布
function drawChart3(census4A, group){

    var ctx3 = $('#myChartFig3');
    var previousChart = Chart.getChart(ctx3);
    if (previousChart) {
        previousChart.destroy();
    }


    var group1 = {};
    var data = [];
    var scatterDataLabels =[];

    var pointRadiusIncrement = 1; // 遞增的圓點大小

    Object.keys(group).forEach(function(groupName) {
        if (group[groupName].length !== 0) {
            group1[groupName] = group[groupName].map(function(item) {
                return {
                    x: item.plotx/20,
                    y: item.ploty/20,
                    tag: item.tag,
                    dbh: item.dbh
                };
            });

            scatterDataLabels[groupName] = group[groupName].map(function(item) {
                return [
                    item.dbh
                ];
            });
            // 將數據集添加到數據集陣列中
            data.push({
                label: groupName, // 使用 groupName 作為標籤
                data: group1[groupName],
                pointStyle: 'circle',
                pointRadius: pointRadiusIncrement // 設置 pointRadius
            });
            pointRadiusIncrement++; // 增加圓點大小

        }
    });

      const mapimg = new Image();
      mapimg.src = `/images/web/fs.elev.jpg`;

    // Chart.defaults.borderColor = 'blue'; 
        const chartAreaPlugins = {


          id: 'chartAreaPlugins',

          beforeDraw: (chart, args, options) => {

            if (mapimg.complete) {
              const ctx = chart.ctx;
              const {top, left, width, height} = chart.chartArea;

              ctx.drawImage(mapimg, left, top, width, height);
            } else {
              mapimg.onload = () => chart.draw();
            }


          },
        };

    // console.log(group1);

       const config={
            type: 'scatter',
            data: {
                // labels: scatterDataLabels,
                datasets: data
            },
            plugins:[chartAreaPlugins],
            options: {
              aspectRatio:1,
              maintainAspectRatio: true,
              scales: {
                  x: {
                      // display: false, // 禁用 x 轴
                      border: {
                        // 框線繪製
                        display: true,
                        drawOnChartArea: false,
                        drawTicks: true,
                        // 框線顏色
                        color: '#cccccc',
                        // 框線寬度
                        width: 1,
                        z: 1,

                      },
                      // min: 0,
                      // max: 500,
                  },
                  y: {
                      // display: false, // 禁用 x 轴
                      border: {
                        // 框線繪製
                        display: true,
                        drawOnChartArea: false,
                        drawTicks: true,
                        // 框線顏色
                        color: '#cccccc',
                        // 框線寬度
                        width: 1,
                        z: 1,
                      },
                      // min: 0,
                      // max: 200,
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
                  }
                },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            let label = '';

                            if (ctx.parsed && ctx.dataset.data) {
                                label += 'DBH : '+ctx.dataset.data[ctx.dataIndex].dbh;
                            }
                            return label;
                        }
                    }
                }
              },
            },

        };


        var scatterChart = new Chart(
          ctx3,
          config
        )
        fig3='yes';
}

//開花量時間變化
window.addEventListener('fig4', event => {

    const flowerSeries=event.detail.flowerSeries;
    // const dateSeries=event.detail.dateSeries;

    drawChart4(flowerSeries);
    figtoggle(4);

});


//chart作圖
function drawChart4(flowerSeries){

    var ctx4 = $('#myChartFig4');
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
       const config={
            type: 'line',
            data: {
                // labels: dateSeries,
                datasets: [{
                    // label: '',
                    data: flowerSeries,
                    pointStyle: false,
                    borderColor: '#f4a261',
                }],
                

            },
            options: {
              plugins: {
                legend: {
                    display: false // 禁用图例
                },
              },
              scales:{
                y: {
                    title: {
                        display: true,
                        text: '開花強度(網次比例/月)', // y 轴的标签
                    },
                },
              }
            },

        };


        var scatterChart = new Chart(
          ctx4,
          config
        )
        fig4='yes';
}


//結果量時間變化
window.addEventListener('fig5', event => {

    const fruitsSeries=event.detail.fruitsSeries;
    // const dateSeries=event.detail.dateSeries;

    drawChart5(fruitsSeries);
    figtoggle(5);

});


//chart作圖
function drawChart5(fruitsSeries){

    var ctx5 = $('#myChartFig5');
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
       const config={
            type: 'line',
            data: {
                // labels: dateSeries,
                datasets: [{
                    // label: '',
                    data: fruitsSeries,
                    pointStyle: false,
                    borderColor: '#6d597a',
                }],
                

            },
            options: {
              plugins: {
                legend: {
                    display: false // 禁用图例
                },
              },
              scales:{
                y: {
                    title: {
                        display: true,
                        text: '種子密度(種子數/m2)', // y 轴的标签
                    },
                },
              }
            },

        };


        var scatterChart = new Chart(
          ctx5,
          config
        )
        fig5='yes';
}

//結果量時間變化
window.addEventListener('fig6', event => {

    const seedlingSeries=event.detail.seedlingSeries;
    // const dateSeries=event.detail.dateSeries;

    drawChart6(seedlingSeries);
    figtoggle(6);

});


//chart作圖
function drawChart6(seedlingSeries){

    var ctx6 = $('#myChartFig6');
    var previousChart = Chart.getChart(ctx6);
    if (previousChart) {
        previousChart.destroy();
    }

       const config={
            type: 'line',
            data: {
                // labels: dateSeries,
                datasets: [{
                    // label: '',
                    data: seedlingSeries,
                    pointStyle: false,
                    borderColor: '#a7c957',
                }],
                

            },
            options: {
              plugins: {
                legend: {
                    display: false // 禁用图例
                },
              },
              scales:{
                y: {
                    title: {
                        display: true,
                        text: '小苗密度(小苗數/m2)', // y 轴的标签
                    },
                },
              }
            },

        };


        var scatterChart = new Chart(
          ctx6,
          config
        )
        fig6='yes';
}