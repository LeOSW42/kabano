var mymap;

$( document ).ready(function() {
	// Differents layers for the map
	var	topo_maptiler = L.tileLayer('https://api.maptiler.com/maps/topographique/{z}/{x}/{y}.png?key=Sm8M7mJ53GtYdl773rpi', {tms: false, maxZoom: 20, attribution: 'Maps © <a href="https://www.maptiler.com/copyright/" target="_blank">MapTiler</a>, Data © <a href="http://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap contributors</a>'});
	/*var	wikimedia  = L.tileLayer('//maps.wikimedia.org/osm-intl/{z}/{x}/{y}.png', {tms: true, maxZoom: 18, attribution: 'Maps © <a href="http://wikimedia.org">Wikimedia</a>, Data © <a href="http://www.openstreetmap.org/copyright">OpenStreetMap contributors</a>'});*/
	// Base layers
	var baseLayers = {
		"Topo": topo_maptiler,
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
});
