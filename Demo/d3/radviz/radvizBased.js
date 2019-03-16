var utils = {
    merge: function(obj1, obj2) {
        for (var p in obj2) {
            if (obj2[p] && obj2[p].constructor == Object) {
                if (obj1[p]) {
                    this.merge(obj1[p], obj2[p]);
                    continue;
                }
            }
            obj1[p] = obj2[p];
        }
    },
    mergeAll: function() {
        var newObj = {};
        var objs = arguments;
        for (var i = 0; i < objs.length; i++) {
            this.merge(newObj, objs[i]);
        }
        return newObj;
    },
    htmlToNode: function(htmlString, parent) {
        while (parent.lastChild) {
            parent.removeChild(parent.lastChild);
        }
        return this.appendHtmlToNode(htmlString, parent);
    },
    appendHtmlToNode: function(htmlString, parent) {
        return parent.appendChild(document.importNode(new DOMParser().parseFromString(htmlString, "text/html").body.childNodes[0], true));
    }
};

var radvizComponent = function() {
    var config = {
        el: null,
        size: 400,
        margin: 50,
        colorScale: d3.scale.ordinal().range(["skyblue", "orange", "lime"]),
        colorAccessor: null,
        dimensions: [],
        testDimensions: [],
        drawLinks: true,
        zoomFactor: 1,
        dotRadius: 6,
        useRepulsion: false,
        useTooltip: true,
        tooltipFormatter: function(d) {
            return d;
        }
    };
    var events = d3.dispatch("panelEnter", "panelLeave", "dotEnter", "dotLeave");
    var force = d3.layout.force().chargeDistance(0).charge(-60).friction(0.5);

    var render = function(data) {
        var svg = d3.select(config.el).append("svg").attr({
            width: config.size,
            height: config.size
        });
        renderDimension(config, svg, config.testDimensions);

        data = addNormalizedValues(data);
        var normalizeSuffix = "_normalized";
        var dimensionNamesNormalized = config.dimensions.map(function(d) {
            return d + normalizeSuffix;
        });
        var thetaScale = d3.scale.linear().domain([0, dimensionNamesNormalized.length]).range([0, Math.PI * 2]);
        var chartRadius = config.size / 2 - config.margin;
        var nodeCount = data.length;
        var panelSize = config.size - config.margin * 2;
        var dimensionNodes = config.dimensions.map(function(d, i) {
            var angle = thetaScale(i);
            var x = chartRadius + Math.cos(angle - thetaScale(1)/2) * chartRadius * config.zoomFactor;
            var y = chartRadius + Math.sin(angle- thetaScale(1)/2) * chartRadius * config.zoomFactor;
            return {
                index: nodeCount + i,
                x: x,
                y: y,
                fixed: true,
                name: d
            };
        });

        var linksData = [];
        data.forEach(function(d, i) {
            dimensionNamesNormalized.forEach(function(dB, iB) {
                linksData.push({
                    source: i,
                    target: nodeCount + iB,
                    value: d[dB]
                });
            });
        });

        force.size([panelSize, panelSize]).linkStrength(function(d) {
            return d.value;
        }).nodes(data.concat(dimensionNodes)).links(linksData).start();

        // svg.append("rect").classed("bg", true).attr({
        //     width: config.size,
        //     height: config.size
        // });

        var root = svg.append("g").attr({
            transform: "translate(" + [config.margin, config.margin] + ")"
        });

        // var panel = root.append("circle").classed("panel", true).attr({
        //     r: chartRadius,
        //     cx: chartRadius,
        //     cy: chartRadius
        // });

        if (config.useRepulsion) {
            root.on("mouseenter", function(d) {
                force.chargeDistance(80).alpha(0.2);
                events.panelEnter();
            });
            root.on("mouseleave", function(d) {
                force.chargeDistance(0).resume();
                events.panelLeave();
            });
        }

        if (config.drawLinks) {
            var links = root.selectAll(".link").data(linksData).enter().append("line").classed("link", true);
        }

        var nodes = root.selectAll("circle.dot").data(data)
            .enter().append("circle")
            .classed("dot", true)
            .attr({
                r: config.dotRadius,
                fill: function(d) {
                    return config.colorScale(config.colorAccessor(d));
            }
            }).on("mouseenter", function(d) {
                if (config.useTooltip) {
                    var mouse = d3.mouse(config.el);
                    tooltip.setText(config.tooltipFormatter(d)).setPosition(mouse[0], mouse[1]).show();
                }
                events.dotEnter(d);
                this.classList.add("active");
            }).on("mouseout", function(d) {
                if (config.useTooltip) {
                    tooltip.hide();
                }
                events.dotLeave(d);
                this.classList.remove("active");
            });

        var labelNodes = root.selectAll("circle.label-node")
            .data(dimensionNodes)
            .enter().append("circle")
            .classed("label-node", true).attr({
                cx: function(d) {
                    return d.x;
                },
                cy: function(d) {
                    return d.y;
                },
                r: 4
            });
        //
        // var labels = root.selectAll("text.label")
        //     .data(dimensionNodes)
        //     .enter().append("text")
        //     .classed("label", true).attr({
        //         x: function(d) {
        //             return d.x;
        //         },
        //         y: function(d) {
        //             return d.y;
        //         },
        //         "text-anchor": function(d) {
        //             if (d.x > panelSize * 0.4 && d.x < panelSize * 0.6) {
        //                 return "middle";
        //             } else {
        //                 return d.x > panelSize / 2 ? "start" : "end";
        //             }
        //         },
        //         "dominant-baseline": function(d) {
        //             return d.y > panelSize * 0.6 ? "hanging" : "auto";
        //         },
        //         dx: function(d) {
        //             return d.x > panelSize / 2 ? "6px" : "-6px";
        //         },
        //         dy: function(d) {
        //             return d.y > panelSize * 0.6 ? "6px" : "-6px";
        //         }
        //     }).text(function(d) {
        //         return d.name;
        //     });

        force.on("tick", function() {
            if (config.drawLinks) {
                links.attr({
                    x1: function(d) {
                        return d.source.x;
                    },
                    y1: function(d) {
                        return d.source.y;
                    },
                    x2: function(d) {
                        return d.target.x;
                    },
                    y2: function(d) {
                        return d.target.y;
                    }
                });
            }
            nodes.attr({
                cx: function(d) {
                    return d.x;
                },
                cy: function(d) {
                    return d.y;
                }
            });
        });

        var tooltipContainer = d3.select(config.el).append("div").attr({
            id: "radviz-tooltip"
        });

        var tooltip = tooltipComponent(tooltipContainer.node());

        return this;
    };

    var setConfig = function(_config) {
        config = utils.mergeAll(config, _config);
        return this;
    };

    var addNormalizedValues = function(data) {
        data.forEach(function(d) {
            config.dimensions.forEach(function(dimension) {
                d[dimension] = +d[dimension];
            });
        });
        var normalizationScales = {};
        config.dimensions.forEach(function(dimension) {
            normalizationScales[dimension] = d3.scale.linear().domain(d3.extent(data.map(function(d, i) {
                return d[dimension];
            }))).range([0, 1]);
        });
        data.forEach(function(d) {
            config.dimensions.forEach(function(dimension) {
                d[dimension + "_normalized"] = normalizationScales[dimension](d[dimension]);
            });
        });
        return data;
    };

    var exports = {
        config: setConfig,
        render: render
    };

    d3.rebind(exports, events, "on");
    return exports;
};

var renderDimension = function(config, svg, data){
    var radius = config.size / 2 - config.margin;
    var color = d3.scale.category20();

    var pie = d3.layout.pie()
    	.sort(null)
    	.value(function(d) {
    		return 10;
    	});

    var arc = d3.svg.arc()
    	.outerRadius(function(t){
            return radius + t.data.value*20;
        })
    	.innerRadius(radius-5);

    var outerArc = d3.svg.arc()
    	.innerRadius(radius * 1.2)
    	.outerRadius(radius * 1.2);

    var key = function(d){ return d.data.name; };

    svg.append("g")
        .attr({transform: "translate(" + [config.size/2, config.size/2] + ")"})
        .attr("class", "slices");
    svg.append("g")
        .attr({transform: "translate(" + [config.size/2, config.size/2] + ")"})
        .attr("class", "labels");
    svg.append("g")
        .attr({transform: "translate(" + [config.size/2, config.size/2] + ")"})
        .attr("class", "lines");

    var slice = svg.select(".slices").selectAll("path.slice")
		.data(pie(data), key);

	slice.enter()
		.insert("path")
		.style("fill", function(d, i) { return color(i); })
		.attr("class", "slice")
        .attr("name", function(d){return d.data.name;});

	slice
		.transition().duration(1000)
		.attrTween("d", function(d) {
			this._current = this._current || d;
			var interpolate = d3.interpolate(this._current, d);
			this._current = interpolate(0);
			return function(t) {
				return arc(interpolate(t));
			};
		});

	slice.exit()
		.remove();

    /* ------- TEXT LABELS -------*/

    var text = svg.select(".labels").selectAll("text")
        .data(pie(data), key);

    text.enter()
        .append("text")
        .attr("dy", ".35em")
        .text(function(d) {
            return d.data.name;
        });

    function midAngle(d) {
        return d.startAngle + (d.endAngle - d.startAngle) / 2;
    }

    text.transition().duration(1000)
        .attrTween("transform", function(d) {
            this._current = this._current || d;
            var interpolate = d3.interpolate(this._current, d);
            this._current = interpolate(0);
            return function(t) {
                var d2 = interpolate(t);
                var pos = outerArc.centroid(d2);
                pos[0] = radius*1.05 * (midAngle(d2) < Math.PI ? 1 : -1);
                return "translate(" + pos + ")";
            };
        })
        .styleTween("text-anchor", function(d) {
            this._current = this._current || d;
            var interpolate = d3.interpolate(this._current, d);
            this._current = interpolate(0);
            return function(t) {
                var d2 = interpolate(t);
                return midAngle(d2) < Math.PI ? "start" : "end";
            };
        });

    text.exit()
        .remove();

    /* ------- SLICE TO TEXT POLYLINES -------*/

    var polyline = svg.select(".lines").selectAll("polyline")
        .data(pie(data), key);

    polyline.enter()
        .append("polyline");

    polyline.transition().duration(1000)
        .attrTween("points", function(d) {
            this._current = this._current || d;
            var interpolate = d3.interpolate(this._current, d);
            this._current = interpolate(0);
            return function(t) {
                var d2 = interpolate(t);
                var pos = outerArc.centroid(d2);
                pos[0] = radius * (midAngle(d2) < Math.PI ? 1 : -1);
                return [arc.centroid(d2), outerArc.centroid(d2), pos];
            };
        });

    polyline.exit()
        .remove();
};

var tooltipComponent = function(tooltipNode) {
    var root = d3.select(tooltipNode).style({
        position: "absolute",
        "pointer-events": "none"
    });
    var setText = function(html) {
        root.html(html);
        return this;
    };
    var position = function(x, y) {
        root.style({
            left: x + "px",
            top: y + "px"
        });
        return this;
    };
    var show = function() {
        root.style({
            display: "block"
        });
        return this;
    };
    var hide = function() {
        root.style({
            display: "none"
        });
        return this;
    };
    return {
        setText: setText,
        setPosition: position,
        show: show,
        hide: hide
    };
};
