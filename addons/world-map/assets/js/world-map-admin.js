; (($) => {
    "use strict";

    window.WorldMapClass = {
        data: {},
        world: null,
        names: null,
        width: 532,
        height: 300,
        tooltip: {
            currentCountry: null
        },
        init: () => {

            const _this = window.WorldMapClass;

            if (_this.world != null && _this.names != null) {
                _this.ready([_this.world, _this.names]);
                return;
            }

            if (!_this.data.hasOwnProperty('world_map_110m2')) {
                return;
            }

            if (_this.world == null) {
                Promise.all([
                    d3.json(_this.data["world_map_110m2"]),
                    d3.csv(_this.data["world_country_names"])
                ]).then(_this.ready);
            } else {
                _this.ready();
            }

        },
        ready: (values) => {

            const _this = window.WorldMapClass;

            _this.world = values[0];

            _this.names = values[1];

            _this.build();

        },
        build: (center = [0, 0], rotate = [0, 0], scale = null, translate = null) => {

            const _this = window.WorldMapClass;

            const container = $("#statisticsWorldMapDataContainer");

            _this.width = container.width();

            _this.height = container.height();

            if (scale == null) {
                scale = [_this.width / 532 * 100]
            }

            if (translate == null) {
                translate = [_this.width / 2, _this.height / 2];
            }



            $("#statisticsWorldMapDataContainer svg").empty();


            var projection = d3.geoNaturalEarth1()
                .center(center)
                .rotate(rotate)
                .scale(scale)
                .translate(translate);

            var path = d3.geoPath()
                .projection(projection);

            var svg = d3.select("#statisticsWorldMapDataContainer svg")
                .append("g");

            var tooltip = d3.select("#statisticsWorldMapDataContainer div.tooltip");

            var countries1 = topojson.feature(_this.world, _this.world.objects.countries);


            let countries = countries1.features.filter(function (d) {
                return _this.names.some(function (n) {
                    if (d.id == n.id) return d.name = n.name;
                })
            });

            svg.selectAll("path")
                .data(countries)
                .enter()
                .append("path")
                .attr("stroke", "gray")
                .attr("stroke-width", 1)
                .attr("fill", function (d) {
                    return window.WorldMapClass.getColor(d.name);
                })
                .attr("d", path)
                .on("change", function (d) {
                    d.attr("data-cname", d.name);
                })
                .on("mouseover", function (d, i) {

                    //d3.select(this).attr("fill", "grey").attr("stroke-width", 2);

                    const data = _this.data['world_map_data'].find((x => { return x.country_name == d.name }));

                    if (typeof data == "undefined" || data["records"] == 0) {
                        return tooltip.classed("hidden", true)
                            .attr("data-cname", d.name);
                    }


                    const hitText = data["records"] <= 1 ? data["records"] + " hit" : data["records"] + " hits";

                    window.WorldMapClass.tooltip.currentCountry = d.name;

                    return tooltip.classed("hidden", false)
                        .attr("data-cname", d.name)
                        .html(`${d.name} - ${hitText}`);
                })
                .on("mousemove", function (d) {

                    const dm = d3.mouse(this);

                    const cattr = tooltip.attr("data-cname");

                    if (cattr == window.WorldMapClass.tooltip.currentCountry && cattr != null) {
                        tooltip.classed("hidden", false);
                    } else {
                        tooltip.classed("hidden", true);
                    }

                    tooltip.style("top", (dm[1]) + "px")
                        .style("left", (dm[0] + 10) + "px");

                })
                .on("mouseout", function (d, i) {
                    //d3.select(this).attr("fill", "white").attr("stroke-width", 1);
                    tooltip.classed("hidden", true);
                });

            container.parents('.busy').removeClass('busy');
        },
        getColor: (name) => {

            const _this = window.WorldMapClass;

            const max = parseInt(_this.data['world_map_max']);

            const data = _this.data['world_map_data'].find((x => { return x.country_name == name }));

            if (typeof data == "undefined") {
                return "white";
            }

            const recs = parseInt(data["records"] || 0);

            if (recs == 0) {
                return "white";
            }

            let value = recs / max;

            let minHue = 120 / 255;

            let maxHue = 0;

            let hue = value * maxHue + (1 - value) * minHue;

            let color = _this.HSVtoRGB(hue, 1, 1);

            return `rgb(${color.r} , ${color.g} , ${color.b})`;

        },
        HSVtoRGB: (h, s, v) => {
            var r, g, b, i, f, p, q, t;
            i = Math.floor(h * 6);
            f = h * 6 - i;
            p = v * (1 - s);
            q = v * (1 - f * s);
            t = v * (1 - (1 - f) * s);
            switch (i % 6) {
                case 0: r = v, g = t, b = p; break;
                case 1: r = q, g = v, b = p; break;
                case 2: r = p, g = v, b = t; break;
                case 3: r = p, g = q, b = v; break;
                case 4: r = t, g = p, b = v; break;
                case 5: r = v, g = p, b = q; break;
            }
            return {
                r: Math.round(r * 255),
                g: Math.round(g * 255),
                b: Math.round(b * 255)
            };
        }
    }

    $(document).ready(() => {
        $.post(heimdall['ajaxurl'], {
            'action': 'heimdall_world_map',
            '_wpnonce': heimdall['ajaxnonce']
        }, (res) => {
            WorldMapClass.data = res.data;
            WorldMapClass.init();
        });
    });

    $(window).on("resize", WorldMapClass.init);

    $(document).on("heimdall--dashboard-tab-is-countries", WorldMapClass.init);

})(jQuery);