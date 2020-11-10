var mymap;
var poi_layer;

$( document ).ready(function() {
	// Differents layers for the map
/*	var	osmfr   = L.tileLayer('//{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {tms: true, maxZoom: 20, attribution: 'Maps © <a href="http://www.openstreetmap.fr">OpenSreetMap France</a>, Data © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>'});
	var	wikimedia  = L.tileLayer('//maps.wikimedia.org/osm-intl/{z}/{x}/{y}.png', {tms: true, maxZoom: 18, attribution: 'Maps © <a href="http://wikimedia.org">Wikimedia</a>, Data © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>'});*/
	var	sat_cro  = L.tileLayer('/_maps/sat_cro/{z}/{x}/{y}.png', {tms: true, maxZoom: 17, attribution: 'For dev purpose only'});
	var	osm_cro  = L.tileLayer('/_maps/osm_cro/{z}/{x}/{y}.png', {tms: true, maxZoom: 17, attribution: 'For dev purpose only'});
	var	ign_cro  = L.tileLayer('/_maps/ign_cro/{z}/{x}/{y}.jpg.tile', {tms: false, maxZoom: 13, attribution: 'For dev purpose only'});

	// Base layers
	var baseLayers = {
		"Crozet IGN": ign_cro,
		"Crozet satellite": sat_cro,
		"Crozet OSM": osm_cro,
	};

	mymap = L.map('mapid', {
		zoomControl: false,
		layers: [sat_cro],
		maxBounds: [[-46.32,51.60],[-46.50,51.90]]
	}).setView([-46.407, 51.766], 13);
	$("#map-credits").html(sat_cro.getAttribution());

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

	L.control.layers(baseLayers,null,{
		position: "bottomright"
	}).addTo(mymap);

	mymap.removeControl(mymap.attributionControl);

	mymap.on('baselayerchange', function(e) {
		$("#map-credits").html(e.layer.getAttribution());
	});

	poi_layer = L.marker([-46.407, 51.766], {draggable: true}).addTo(mymap);
	poi_layer.bindTooltip("Glissez moi au bon endroit.", {permanent: true, direction: 'top'}).openTooltip();

	mymap.on('click', function(e){
		poi_layer.unbindTooltip();
		poi_layer.setLatLng(e.latlng);
		$("#lat").val(e.latlng.lat);
		$("#lon").val(e.latlng.lng);
	})
	poi_layer.on('move', function(e){
		poi_layer.unbindTooltip();
		$("#lat").val(e.latlng.lat);
		$("#lon").val(e.latlng.lng);
	})

	var poiicon = L.icon({
		iconSize: [24, 24],
		iconAnchor: [12, 12]
	});
	$("#type_selector label").click(function(e) {
		poi_layer.unbindTooltip();
		poiicon.options.iconUrl = e.currentTarget.firstChild.currentSrc;
		poi_layer.setIcon(poiicon);
	})
});