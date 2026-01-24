// Carte principale et marqueurs de POI.
var mymap;
var markers = [];

$( document ).ready(function() {
	// Différentes couches pour la carte.
	var	topo_maptiler = L.tileLayer('https://api.maptiler.com/maps/topographique/{z}/{x}/{y}.png?key=Sm8M7mJ53GtYdl773rpi', {tms: false, attribution: 'Carte © <a href="https://www.maptiler.com/copyright/" target="_blank">MapTiler</a>, Données © <a href="http://www.openstreetmap.org/copyright" target="_blank">Contributeurs OpenStreetMap</a>', tileSize: 512, zoomOffset: -1, minZoom: 1});
	var	ign = L.tileLayer('https://data.geopf.fr/private/wmts?&REQUEST=GetTile&SERVICE=WMTS&VERSION=1.0.0&STYLE=normal&TILEMATRIXSET=PM&FORMAT=image/jpeg&LAYER=GEOGRAPHICALGRIDSYSTEMS.MAPS&TILEMATRIX={z}&TILEROW={y}&TILECOL={x}&apikey=ign_scan_ws', {attribution: 'Carte & Connées © <a href="http://ign.fr/" target="_blank">IGN-F/Géoportail</a>'});

	// Couches de base.
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

	var credits = L.easyButton('fa-info',
		function(control, mymap){
			$("footer").hide();
			$("#footer-credits").show();
			$("#footer-legend").hide();
		}, 'Credits');
	var legend = L.easyButton('fa-question',
		function(control, mymap){
			$("footer").hide();
			$("#footer-credits").hide();
			$("#footer-legend").show();
		}, 'Legend');
	L.easyBar([ credits, legend, ], {position: "bottomleft"}).addTo(mymap);

	L.control.fullscreen({
		position: "bottomleft"
	}).addTo(mymap);

	L.control.zoom({
		zoomOutText: "<i class=\"fa fa-minus\" aria-hidden=\"true\"></i>",
		zoomInText: "<i class=\"fa fa-plus\" aria-hidden=\"true\"></i>",
		position: "bottomleft"
	}).addTo(mymap);

	L.control.layers(baseLayers,null,{
		position: "bottomright"
	}).addTo(mymap);

	mymap.removeControl(mymap.attributionControl);

	$(".close-link").click(function() {
		$("footer").show();
		$("#footer-credits").hide();
		$("#footer-legend").hide();
	});

	mymap.on('baselayerchange', function(e) {
		$("#map-credits").html(e.layer.getAttribution());
	});

	// Ajuste la taille des icônes selon le niveau de zoom.
	mymap.on("zoomend", function () {
	    var z = mymap.getZoom();

	    var size = 32;
	    if (z < 8) size = 20;
	    if (z < 4) size = 12;

	    markers.forEach(function(marker) {
	        var icon = marker.options.icon;
	        icon.options.iconSize = [size, size];
	        icon.options.iconAnchor = [size/2, size];
	        marker.setIcon(icon);
	    });
	});

    // ---------------------------------------------------------
    //  CHARGEMENT DES POIs
    // ---------------------------------------------------------

    $.getJSON(root + "poi/api_list", function(pois) {

        var icons = {};

        // Préparer les icônes
        for (const type in window.poi_icons) {
            icons[type] = L.icon({
                iconUrl: window.poi_icons[type],
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            });
        }

        // Ajouter les POIs
		pois.forEach(function(poi) {

		    var icon = icons[poi.type] || icons["default"];

		    var marker = L.marker([poi.lat, poi.lon], { icon: icon })
		        .addTo(mymap);

		    // Tooltip discret au survol
		    marker.bindTooltip(poi.name, {
		        direction: "top",
		        offset: [0, -10],
		        opacity: 0.9,
		        className: "poi-tooltip"
		    });

		    // Clic → ouvrir la fiche directement
		    marker.on("click", function() {
		        window.location = root + "poi/" + poi.permalink;
		    });

		    markers.push(marker);
		});

        // Ajuster la vue
        if (markers.length > 0) {
            var group = L.featureGroup(markers);
            mymap.fitBounds(group.getBounds(), { padding: [30, 30] });
        }
    });
});
