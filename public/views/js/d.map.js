// Carte principale
var mymap;
var poiLayer;
var icons = {};

$(document).ready(function() {

    // ---------------------------------------------------------
    // Fonds de carte
    // ---------------------------------------------------------

    var topo_maptiler = L.tileLayer(
        'https://api.maptiler.com/maps/topographique/{z}/{x}/{y}.png?key=Sm8M7mJ53GtYdl773rpi',
        {
            tms: false,
            tileSize: 512,
            zoomOffset: -1,
            minZoom: 1,
            attribution:
                'Carte © MapTiler, Données © OpenStreetMap'
        }
    );

    var ign = L.tileLayer(
        'https://data.geopf.fr/private/wmts?REQUEST=GetTile&SERVICE=WMTS&VERSION=1.0.0&STYLE=normal&TILEMATRIXSET=PM&FORMAT=image/jpeg&LAYER=GEOGRAPHICALGRIDSYSTEMS.MAPS&TILEMATRIX={z}&TILEROW={y}&TILECOL={x}&apikey=ign_scan_ws',
        {
            attribution: 'Carte © IGN-F/Géoportail'
        }
    );

    var baseLayers = {
        "OpenStreetMap": topo_maptiler,
        "IGN France": ign
    };

    // ---------------------------------------------------------
    // Carte
    // ---------------------------------------------------------

    mymap = L.map('mapid', {
        zoomControl: false,
        layers: [topo_maptiler]
    }).setView([47, 3], 6);

    $("#map-credits").html(topo_maptiler.getAttribution());

    poiLayer = L.layerGroup().addTo(mymap);

    // ---------------------------------------------------------
    // Contrôles
    // ---------------------------------------------------------

    L.control.scale({
        position: "bottomleft",
        imperial: false
    }).addTo(mymap);

    var credits = L.easyButton(
        'fa-info',
        function() {
            $("footer").hide();
            $("#footer-credits").show();
            $("#footer-legend").hide();
        },
        'Credits'
    );

    var legend = L.easyButton(
        'fa-question',
        function() {
            $("footer").hide();
            $("#footer-credits").hide();
            $("#footer-legend").show();
        },
        'Legend'
    );

    L.easyBar(
        [credits, legend],
        {
            position: "bottomleft"
        }
    ).addTo(mymap);

    L.control.fullscreen({
        position: "bottomleft"
    }).addTo(mymap);

    L.control.zoom({
        zoomOutText: '<i class="fa fa-minus"></i>',
        zoomInText: '<i class="fa fa-plus"></i>',
        position: "bottomleft"
    }).addTo(mymap);

    L.control.layers(
        baseLayers,
        null,
        {
            position: "bottomright"
        }
    ).addTo(mymap);

    mymap.removeControl(mymap.attributionControl);

    // ---------------------------------------------------------
    // Footer
    // ---------------------------------------------------------

    $(".close-link").click(function() {
        $("footer").show();
        $("#footer-credits").hide();
        $("#footer-legend").hide();
    });

    mymap.on('baselayerchange', function(e) {
        $("#map-credits").html(
            e.layer.getAttribution()
        );
    });

    // ---------------------------------------------------------
    // Icônes
    // ---------------------------------------------------------

    for (const type in window.poi_icons) {

        icons[type] = L.icon({
            iconUrl: window.poi_icons[type],
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
        });

    }

    // ---------------------------------------------------------
    // Chargement des POI visibles
    // ---------------------------------------------------------

    function loadVisiblePois() {
        if (mymap.getZoom() < 10) {

            poiLayer.clearLayers();

            return;
        }

        var b = mymap.getBounds();

        $.getJSON(
            root + "poi/api_bbox",
            {
                north: b.getNorth(),
                south: b.getSouth(),
                east: b.getEast(),
                west: b.getWest()
            },
            function(pois) {

                poiLayer.clearLayers();

                pois.forEach(function(poi) {

                    var icon =
                        icons[poi.type] ||
                        icons["default"];

                    var marker = L.marker(
                        [poi.lat, poi.lon],
                        {
                            icon: icon
                        }
                    );

                    marker.bindTooltip(
                        poi.name,
                        {
                            direction: "top",
                            offset: [0, -10],
                            opacity: 0.9,
                            className:
                                "poi-tooltip"
                        }
                    );

                    marker.on(
                        "click",
                        function() {
                            window.location =
                                root +
                                "poi/" +
                                poi.permalink;
                        }
                    );

                    poiLayer.addLayer(marker);

                });
            }
        );
    }

    // ---------------------------------------------------------
    // Rechargement automatique
    // ---------------------------------------------------------

    mymap.on("moveend", loadVisiblePois);
    mymap.on("zoomend", loadVisiblePois);

    // Premier chargement
    loadVisiblePois();
});