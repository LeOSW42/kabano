var mymap;

$( document ).ready(function() {
	// Differents layers for the map
	var	topo_maptiler = L.tileLayer('https://api.maptiler.com/maps/topographique/{z}/{x}/{y}.png?key=Sm8M7mJ53GtYdl773rpi', {tms: false, attribution: 'Carte © <a href="https://www.maptiler.com/copyright/" target="_blank">MapTiler</a>, Données © <a href="http://www.openstreetmap.org/copyright" target="_blank">Contributeurs OpenStreetMap</a>', tileSize: 512, zoomOffset: -1, minZoom: 1});
	var	ign = L.tileLayer('https://data.geopf.fr/private/wmts?&REQUEST=GetTile&SERVICE=WMTS&VERSION=1.0.0&STYLE=normal&TILEMATRIXSET=PM&FORMAT=image/jpeg&LAYER=GEOGRAPHICALGRIDSYSTEMS.MAPS&TILEMATRIX={z}&TILEROW={y}&TILECOL={x}&apikey=ign_scan_ws', {attribution: 'Carte & Connées © <a href="http://ign.fr/" target="_blank">IGN-F/Géoportail</a>'});

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
