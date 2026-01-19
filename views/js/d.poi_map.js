var mymap;
var poi_layer;

$(document).ready(function() {
    // Mode : edit (formulaire) ou view (affichage)
    var isEdit = (typeof poi_mode === "undefined" || poi_mode === "edit");

    // Differents layers for the map
    var topo_maptiler = L.tileLayer(
        'https://api.maptiler.com/maps/topographique/{z}/{x}/{y}.png?key=Sm8M7mJ53GtYdl773rpi',
        {
            tms: false,
            attribution: 'Carte © <a href="https://www.maptiler.com/copyright/" target="_blank">MapTiler</a>, Données © <a href="http://www.openstreetmap.org/copyright" target="_blank">Contributeurs OpenStreetMap</a>',
            tileSize: 512,
            zoomOffset: -1,
            minZoom: 1
        }
    );
    var ign = L.tileLayer(
        'https://data.geopf.fr/private/wmts?&REQUEST=GetTile&SERVICE=WMTS&VERSION=1.0.0&STYLE=normal&TILEMATRIXSET=PM&FORMAT=image/jpeg&LAYER=GEOGRAPHICALGRIDSYSTEMS.MAPS&TILEMATRIX={z}&TILEROW={y}&TILECOL={x}&apikey=ign_scan_ws',
        {
            attribution: 'Carte & Connées © <a href="http://ign.fr/" target="_blank">IGN-F/Géoportail</a>'
        }
    );
    // Base layers
    var baseLayers = {
        "OpenStreetMap": topo_maptiler,
        "IGN France": ign
    };

    mymap = L.map('mapid', {
        zoomControl: false,
        layers: [topo_maptiler]
    }).setView([47, 3], 6);
    $("#map-credits").html(topo_maptiler.getAttribution());

    L.control.scale({
        position: "bottomleft",
        imperial: false
    }).addTo(mymap);

    L.control.fullscreen({
        position: "bottomleft"
    }).addTo(mymap);

    L.control.zoom({
        zoomOutText: "<i class=\"fa fa-minus\" aria-hidden=\"true\"></i>",
        zoomInText: "<i class=\"fa fa-plus\" aria-hidden=\"true\"></i>",
        position: "bottomleft"
    }).addTo(mymap);

    L.control.layers(baseLayers, null, {
        position: "bottomright"
    }).addTo(mymap);

    mymap.removeControl(mymap.attributionControl);

    mymap.on('baselayerchange', function(e) {
        $("#map-credits").html(e.layer.getAttribution());
    });

    // Position initiale : soit celle du POI, soit le centre par défaut
    var startLat = (typeof poi_lat !== "undefined" && poi_lat) ? poi_lat : 47;
    var startLon = (typeof poi_lon !== "undefined" && poi_lon) ? poi_lon : 3;

    // Icône : si poi_type défini, on l’utilise, sinon icône Leaflet par défaut
    var poiicon = null;
    if (typeof poi_type !== "undefined" && poi_type) {
        poiicon = L.icon({
            iconSize: [24, 24],
            iconAnchor: [12, 12],
 			iconUrl: root + "views/img/" + poi_type + ".svg"
         });
    }

    if (poiicon) {
        poi_layer = L.marker([startLat, startLon], {draggable: isEdit, icon: poiicon}).addTo(mymap);
    } else {
        poi_layer = L.marker([startLat, startLon], {draggable: isEdit}).addTo(mymap);
    }

    if (isEdit) {
        poi_layer.bindTooltip("Glissez moi au bon endroit.", {permanent: true, direction: 'auto'}).openTooltip();
    }

    // Interactions uniquement en mode édition
    if (isEdit) {
        mymap.on('click', function(e){
            poi_layer.setLatLng(e.latlng);
        });

        poi_layer.on('move', function(e){
            poi_layer.unbindTooltip();
            $("#lat").val(+e.latlng.lat.toFixed(6));
            $("#lon").val(+e.latlng.lng.toFixed(6));
            $("#elevation_icon").show();
        });

        $("#lat,#lon").change(function() { // If the user changes the lat/lon input values manualy
            if(isNaN($("#lat").val()) || isNaN($("#lon").val()) || $("#lat").val().length==0 || $("#lon").val()==null)
                $("#elevation_icon").hide();
            else {
                $("#elevation_icon").show();
                poi_layer.setLatLng([$("#lat").val(),$("#lon").val()]);
            }
        });

        var editPoiIcon = L.icon({
            iconSize: [24, 24],
            iconAnchor: [12, 12]
        });

        $("#type_selector label").click(function(e) {
            poi_layer.unbindTooltip();
            editPoiIcon.options.iconUrl = e.currentTarget.firstChild.currentSrc;
            poi_layer.setIcon(editPoiIcon);
        });

        $("#elevation_icon").click(function(e) {
            $(this).find($(".fas")).removeClass('fa-search-location').addClass('fa-spinner').addClass('fa-spin');
            $.get(root+"poi/elevation_proxy", {location:$("#lat").val()+","+$("#lon").val()}, function(result){
                $("#ele").val(result.results[0].elevation);
                $("#elevation_icon").find($(".fas")).removeClass('fa-spinner').removeClass('fa-spin').addClass('fa-search-location');
            });
        });
    } else {
        // Mode affichage : centrer sur le POI si coordonnées présentes
        if (typeof poi_lat !== "undefined" && typeof poi_lon !== "undefined") {
            mymap.setView([poi_lat, poi_lon], 14);
        }
    }
});
