var mymap;

$( document ).ready(function() {
	// Differents layers for the map
	var	osmfr   = L.tileLayer('//{s}.tile.openstreetmap.fr/osmfr/{z}/{x}/{y}.png', {maxZoom: 20, attribution: 'Maps © <a href="http://www.openstreetmap.fr">OpenSreetMap France</a>, Data © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>'});
	var	wikimedia  = L.tileLayer('//maps.wikimedia.org/osm-intl/{z}/{x}/{y}.png', {maxZoom: 18, attribution: 'Maps © <a href="http://wikimedia.org">Wikimedia</a>, Data © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>'});

	// Base layers
	var baseLayers = {
		"OSM France": osmfr,
		"OSM Wikimedia": wikimedia,
	};

	mymap = L.map('mapid', {
		zoomControl: false,
		layers: [wikimedia],
	}).setView([47.018, 3.142], 6);
	$("#map-credits").html(wikimedia.getAttribution());

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
});
