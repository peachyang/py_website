var loadData = function (canvas, params) {
    var count = $('.dashboard [data-url]').length;
    $('.dashboard [data-url]').each(function () {
        var o = this;
        $.get($(o).data('url') + params).success(function (xhr) {
            var r = typeof xhr === 'string' ? eval('(' + xhr + ')') : xhr;
            for (var i in r) {
                $(o).find('.data.' + i).text(r[i]);
            }
            var data = [];
            var cat = [];
            for (var i in r['filted']) {
                cat.push(i);
                data.push(r['filted'][i]);
            }
            if (count-- == $('.dashboard [data-url]').length) {
                var axis = $('#canvas').highcharts().xAxis;
                for (var i in axis) {
                    axis[i].remove(false);
                }
                $(canvas).highcharts().addAxis({categories: cat}, true, false);
            }
            $(canvas).highcharts().addSeries({
                name: $(o).data('title'),
                data: data
            }, false);
            if (!count) {
                $(canvas).highcharts().redraw();
            }
        });
    });
};
var drawChart = function (canvas) {
    "use strict";
    var colors = [];
    $('.dashboard [data-url]').each(function () {
        colors.push($(this).data('color'));
    });
    $(canvas).highcharts({
        credits: false,
        colors: colors,
        title: {text: ''},
        subtitle: {text: ''},
        yAxis: {title: ''},
        legend: {align: 'right'},
        series: []
    });
    loadData(canvas, '');
};
$(function () {
    drawChart('#canvas');
    $('.dashboard #filter').change(function () {
        if (this.value !== 'c') {
            var series = $('#canvas').highcharts().series;
            for (var i in series) {
                series[i].remove(false);
            }
            $('.dashboard #filter~*').hide();
            loadData('#canvas', '?filter=' + this.value);
        } else {
            $('.dashboard #filter~*').show();
        }
    });
    $('.dashboard #compare').click(function () {
        var series = $('#canvas').highcharts().series;
        for (var i in series) {
            series[i].remove(false);
        }
        loadData('#canvas', '?filter=c&' + $('.dashboard .filter .date').serialize());
    });
});
