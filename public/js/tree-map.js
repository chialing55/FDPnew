//map

function showFlywhere(event) {
    var x = event.offsetX || event.pageX - $("#pointer_div2").offset().left;
    var y = event.offsetY || event.pageY - $("#pointer_div2").offset().top;

    dx=chartAreaInfo.left;
    dy=chartAreaInfo.top;
    w=chartAreaInfo.width;
    h=chartAreaInfo.height;

    x = Math.max(dx, Math.min(x, dx+w));
    y = Math.max(dy, Math.min(y, dy+h));    
    // Livewire.emit('coordinatesUpdated', { x: x, y: y });
    // 顯示的值
    sx=(x-dx) / w * 10;
    sy=(h - (y-dy)) / h * 10;

    $("#showForm input[name='x']").val(sx.toFixed(2));
    $("#showForm input[name='y']").val(sy.toFixed(2));


    $("#cross").css({
        left: (x),
        top: (y+dy),
        transform: "translateX(-50%)",
        visibility: "visible"
    });
    console.log(x,y,dx,dy);

}

function drawChart(data, tablePlot, mapfile){

    var ctx = $(`#myChart${tablePlot}`)[0].getContext('2d');

    var previousChart = Chart.getChart(ctx);
    if (previousChart) {
        previousChart.destroy();
    }
    // console.log(previousChart);

    var scatterData = data.map(function(item) {
        return {
            x: item.qudx,
            y: item.qudy,
            tag: item.tag,
            status: item.status
        };
    });

    var recruitData = scatterData.filter(function(item) {
        return item.status === '-9';
    });

    var treeData = scatterData.filter(function(item) {
        return item.status != '-9';
    });

    console.log(recruitData);

    var recruitDataLabels = recruitData.map(function(item) {
        return [
            item.tag
        ];
    }); 
    var treeDataLabels = treeData.map(function(item) {
        return [
            item.tag
        ];
    }); 
    var scatterDataLabels = scatterData.map(function(item) {
        return [
            item.tag
        ];
    }); 

      const mapimg = new Image();
      mapimg.src = `/${mapfile}`;

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

       const config={
            type: 'scatter',
            data: {
                // labels: scatterDataLabels,
                datasets: [{
                    labels: treeDataLabels,
                    data: treeData,
                    backgroundColor: 'purple', // 散點的顏色
                    // borderColor: 'rgba(255, 99, 132, 1)', // 線的顏色
                    // borderWidth: 1, // 線的寬度
                    // borderColor: 'rgba(255, 99, 132, 1)', // 外框颜色

                },{
                    labels: recruitDataLabels,
                    data: recruitData,
                    backgroundColor: '#ef5e5efa', // 散點的顏色
                }]
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
                        // // 格線虛線，10為線，5為空格
                        // dash: [10, 5],
                        // // 虛線偏移
                        // dashOffset: 5,
                        // 軸線 Z-index
                        z: 1,

                      },
                      min: 0,
                      max: 10,
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
                        // // 格線虛線，10為線，5為空格
                        // dash: [10, 5],
                        // // 虛線偏移
                        // dashOffset: 5,
                        // 軸線 Z-index
                        z: 1,
                      },
                      min: 0,
                      max: 10,
                  },

              },
              
              plugins: {
                title: {
                  display: false
                },
                legend: {
                  display: false // 禁用图例
                },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            // console.log(ctx);
                            let label = ctx.dataset.labels[ctx.dataIndex];
                            label += " (" + ctx.parsed.x + ", " + ctx.parsed.y + ")";
                            return label;
                        }
                    }
                }
              },
            },
            
        };



        var scatterChart = new Chart(
          ctx,
          config
        )

        // console.log(scatterChart.chartArea.left);
        chartAreaInfo=scatterChart.chartArea;

      $("#showForm input[name='tag']").val('');
      $("#showForm input[name='x']").val('');
      $("#showForm input[name='y']").val('');
      $("#showForm input[name='rtype']").val('');
}


function choiceTag(element, tag, x, y, rtype, tablePlot){
    $("#showForm input[name='tag']").val(tag);
    $("#showForm input[name='x']").val(x);
    $("#showForm input[name='y']").val(y);
    $("#showForm input[name='rtype']").val(rtype);
    $(`.maptr`).removeClass('fontred');
    $(element).addClass('fontred');
    $(".datasavenote").html('');

    Livewire.emit('updateSavenote');

}


function saveData(){
    var tag = $("#showForm input[name='tag']").val();
    var x = $("#showForm input[name='x']").val();
    var y = $("#showForm input[name='y']").val();
    var rtype = $("#showForm input[name='rtype']").val();

    console.log(tag, x, y);

    Livewire.emit('updateCoordinates', {x: x, y: y, tag:tag, rtype:rtype});

    // $(".datasavenote").html('已更新資料');
}