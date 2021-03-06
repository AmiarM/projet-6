$(document).ready(function() {

    //CHARTS
    function gd(year, day, month) {
        return new Date(year, month - 1, day).getTime();
    }

    if ($('#graph-bar').length) {
        var data1 = [
            [gd(2015, 1, 1), 838],
            [gd(2015, 1, 2), 749],
            [gd(2015, 1, 3), 634],
            [gd(2015, 1, 4), 1080],
            [gd(2015, 1, 5), 850],
            [gd(2015, 1, 6), 465],
            [gd(2015, 1, 7), 453],
            [gd(2015, 1, 8), 646],
            [gd(2015, 1, 9), 738],
            [gd(2015, 1, 10), 899],
            [gd(2015, 1, 11), 830],
            [gd(2015, 1, 12), 789]
        ];

        var data2 = [
            [gd(2015, 1, 1), 342],
            [gd(2015, 1, 2), 721],
            [gd(2015, 1, 3), 493],
            [gd(2015, 1, 4), 403],
            [gd(2015, 1, 5), 657],
            [gd(2015, 1, 6), 782],
            [gd(2015, 1, 7), 609],
            [gd(2015, 1, 8), 543],
            [gd(2015, 1, 9), 599],
            [gd(2015, 1, 10), 359],
            [gd(2015, 1, 11), 783],
            [gd(2015, 1, 12), 680]
        ];

        var series = new Array();

        series.push({
            data: data1,
            bars: {
                show: true,
                barWidth: 24 * 60 * 60 * 12000,
                lineWidth: 1,
                fill: 1,
                align: 'center'
            },
            label: 'Revenues'
        });
        series.push({
            data: data2,
            color: '#e84e40',
            lines: {
                show: true,
                lineWidth: 3,
            },
            points: {
                fillColor: "#e84e40",
                fillColor: '#ffffff',
                pointWidth: 1,
                show: true
            },
            label: 'Orders'
        });

        $.plot("#graph-bar", series, {
            colors: ['#03a9f4', '#f1c40f', '#2ecc71', '#3498db', '#9b59b6', '#95a5a6'],
            grid: {
                tickColor: "#f2f2f2",
                borderWidth: 0,
                hoverable: true,
                clickable: true
            },
            legend: {
                noColumns: 1,
                labelBoxBorderColor: "#000000",
                position: "ne"
            },
            shadowSize: 0,
            xaxis: {
                mode: "time",
                tickSize: [1, "month"],
                tickLength: 0,
                // axisLabel: "Date",
                axisLabelUseCanvas: true,
                axisLabelFontSizePixels: 12,
                axisLabelFontFamily: 'Open Sans, sans-serif',
                axisLabelPadding: 10
            }
        });

        var previousPoint = null;
        $("#graph-bar").bind("plothover", function(event, pos, item) {
            if (item) {
                if (previousPoint != item.dataIndex) {

                    previousPoint = item.dataIndex;

                    $("#flot-tooltip").remove();
                    var x = item.datapoint[0],
                        y = item.datapoint[1];

                    showTooltip(item.pageX, item.pageY, item.series.label, y);
                }
            } else {
                $("#flot-tooltip").remove();
                previousPoint = [0, 0, 0];
            }
        });

        function showTooltip(x, y, label, data) {
            $('<div id="flot-tooltip">' + '<b>' + label + ': </b><i>' + data + '</i>' + '</div>').css({
                top: y + 5,
                left: x + 20
            }).appendTo("body").fadeIn(200);
        }
    }

    //WORLD MAP
    $('#world-map').vectorMap({
        map: 'world_merc',
        backgroundColor: '#ffffff',
        zoomOnScroll: false,
        regionStyle: {
            initial: {
                fill: '#e1e1e1',
                stroke: 'none',
                "stroke-width": 0,
                "stroke-opacity": 1
            },
            hover: {
                "fill-opacity": 0.8
            },
            selected: {
                fill: '#8dc859'
            },
            selectedHover: {}
        },
        markerStyle: {
            initial: {
                fill: '#e84e40',
                stroke: '#e84e40'
            }
        },
        markers: [
            { latLng: [38.35, -121.92], name: 'Los Angeles - 23' },
            { latLng: [39.36, -73.12], name: 'New York - 84' },
            { latLng: [50.49, -0.23], name: 'London - 43' },
            { latLng: [36.29, 138.54], name: 'Tokyo - 33' },
            { latLng: [37.02, 114.13], name: 'Beijing - 91' },
            { latLng: [-32.59, 150.21], name: 'Sydney - 22' },
        ],
        series: {
            regions: [{
                values: gdpData,
                scale: ['#6fc4fe', '#2980b9'],
                normalizeFunction: 'polynomial'
            }]
        },
        onRegionLabelShow: function(e, el, code) {
            el.html(el.html() + ' (' + gdpData[code] + ')');
        }
    });

    /* SPARKLINE - graph in header */
    var orderValues = [10, 8, 5, 7, 4, 4, 3, 8, 0, 7, 10, 6, 5, 4, 3, 6, 8, 9];

    $('.spark-orders').sparkline(orderValues, {
        type: 'bar',
        barColor: '#ced9e2',
        height: 25,
        barWidth: 6
    });

    var revenuesValues = [8, 3, 2, 6, 4, 9, 1, 10, 8, 2, 5, 8, 6, 9, 3, 4, 2, 3, 7];

    $('.spark-revenues').sparkline(revenuesValues, {
        type: 'bar',
        barColor: '#ced9e2',
        height: 25,
        barWidth: 6
    });

    /* ANIMATED WEATHER */
    var skycons = new Skycons({ "color": "#03a9f4" });
    // on Android, a nasty hack is needed: {"resizeClear": true}

    // you can add a canvas by it's ID...
    skycons.add("current-weather", Skycons.SNOW);

    // start animation!
    skycons.play();

});