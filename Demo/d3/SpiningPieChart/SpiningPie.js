$(function() {
    var width = 500,
        height = 500,
        inner = 20,
        dataSize = 20,
        //产生一个随机序列
        randomData = function(min, max) {
            return Math.floor(Math.random() * (max - min)) + min;
        },

        dataSet = function(dataSize, inner, a) {
            for (var n = [], i = 0; dataSize > i; i++)
                n.push({
                    inner: inner,
                    outer: randomData(a - 60, a)
                });
            return n;
        },
        o = randomData(0, 3);

    var color;
    if (0 === o)
        color = d3.scale.category20();
    else if (1 == o)
        color = d3.scale.category20b();
    else if (2 == o)
        color = d3.scale.category20c();

    var data = dataSet(dataSize, inner, Math.min(width, height) / 2),
        svg = d3.select("#canvas").append("svg").attr("width", width).attr("height", height),
        path = d3.svg.arc().innerRadius(function(t) {
            return t.data.inner;
        }).outerRadius(function(t) {
            return t.data.outer;
        }),

        pie = d3.layout.pie().value(function(t) {
            return randomData(10, 15);
        }).sort(null),

        pathChange = d3.svg.arc().innerRadius(function(t) {
            return t.data.inner;
        }).outerRadius(function(a) {
            return randomData(100, Math.min(width, height) / 2);
        }),

        change = function() {
            var a = randomData(0, 360),
                n = randomData(0, 7),
                i = "cubic-in-out";
            0 === n ? i = "linear" : 1 == n ? i = "quad" : 2 == n ? i = "back" : 3 == n ? i = "elastic" : 4 == n && (i = "bounce"),

            svg.selectAll("path")
                .transition()
                .attr("d", function(t) {
                    return pathChange(t);
                })
                .attr("transform", "translate(" + width / 2 + "," + height / 2 + ") rotate(" + a + ",0,0)")
                .duration(500)
                .ease(i);
        };

    svg.selectAll("path").data(pie(data))
        .enter().append("path")
        .attr("d", function(t) {
            return path(t);
        })
        .attr("stroke-width", 2)
        .attr("stroke", "black")
        .style("stroke-opacity", 0.3)
        .style("stroke-linejoin", "round")
        .attr("fill", function(t, r) {
            return color(r);
        })
        .style("opacity", 0.9)
        .attr("class", "pie")
        .attr("transform", "translate(250, 250)");

        setInterval(change, 1e3);
    });
