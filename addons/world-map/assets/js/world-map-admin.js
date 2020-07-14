; (($) => {
    "use strict";

    function getX(lon, width)
    {
        return (width*(180+lon)/360) % (width + (width/2));
    }

    function getY(lat, height, width)
    {
        let latRad = lat*Math.PI/180;
        let mercN = Math.log(Math.tan((Math.PI/4)+(latRad/2)));
        return (height/2)-(width*mercN/(2*Math.PI));
    }

    function HSVtoRGB(h, s, v) {
        var r, g, b, i, f, p, q, t;
        if (arguments.length === 1) {
            s = h.s, v = h.v, h = h.h;
        }
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

    function updateMapPoints(){

        const container = $("#statisticsWorldMapDataContainer")[0];

        const list = $("#statisticsWorldMapData");


        list.empty();



        const bounds = container.getBoundingClientRect();

        const width = bounds.width - 25;

        const height = bounds.height - 25;

        


        heimdall['world_map_data'].forEach((e,i)=>{

            let recs = parseInt(e["records"]);

            if(recs == 0)
            {
                return;
            }

            let max =  parseInt(heimdall['world_map_max']);

            let value = recs / max ;

            let scale = 1 + value;

            let minHue = 120 / 255; 

            let maxHue = 0;

            let hue = value*maxHue + (1-value)*minHue; 

            let color = HSVtoRGB(hue, 1, 1);

            let lat = parseFloat(e["lat"]);

            let lng = parseFloat(e["lng"]);

            let x = getX(lng , width);

            let y = getY(lat, height, width );

            let bgc = `"rgba(${color.r} , ${color.g} , ${color.b} , .5)"`;

            let size = 10 * scale + "px";

            let item = $('<li>').css({
                left: x + "px",
                top: y + "px",
                width: size,
                height: size,
                "background-color": bgc
            });

            let pulse = $("<div>").addClass("pulse").css({
                width: size,
                height: size,
                "background-color": bgc
            });

            let hitText =  recs <= 1 ? e["records"] + " hit" : e["records"] + " hits";

            let info = $("<p>")
                .addClass("info")
                .html(`<strong>${e["country_name"]}</strong> - <span style="color:#aaa">${hitText}</span>`);

            info.append($("<i>").addClass("arrow-down"));

            item.append(pulse , info);

            list.append(item);


        });

    }


    $(window).on("resize" , function(){

        updateMapPoints();

    });


    $(document).on("heimdall--dashboard-tab-is-countries" , function(){

        $(window).trigger("resize");

    });

    $(document).ready(() => {

        $(window).trigger("resize");

    });


})(jQuery);