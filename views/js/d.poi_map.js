var mymap;
var poi_layer;

$( document ).ready(function() {
	// Differents layers for the map
	var	topo_maptiler = L.tileLayer('https://api.maptiler.com/maps/topographique/{z}/{x}/{y}.png?key=Sm8M7mJ53GtYdl773rpi', {tms: false, attribution: 'Carte © <a href="https://www.maptiler.com/copyright/" target="_blank">MapTiler</a>, Données © <a href="http://www.openstreetmap.org/copyright" target="_blank">Contributeurs OpenStreetMap</a>', tileSize: 512, zoomOffset: -1, minZoom: 1});
	var	ign = L.tileLayer('https://wxs.ign.fr/5sf7ych7ecsa575btea2ln17/geoportail/wmts?service=WMTS&request=GetTile&version=1.0.0&tilematrixset=PM&tilematrix={z}&tilecol={x}&tilerow={y}&layer=ORTHOIMAGERY.ORTHOPHOTOS&format=image/jpeg&style=normal', {attribution: 'Carte & Connées © <a href="http://ign.fr/" target="_blank">IGN-F/Géoportail</a>'});
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

	L.control.layers(baseLayers,null,{
		position: "bottomright"
	}).addTo(mymap);

	mymap.removeControl(mymap.attributionControl);

	mymap.on('baselayerchange', function(e) {
		$("#map-credits").html(e.layer.getAttribution());
	});

	poi_layer = L.marker([47, 3], {draggable: true}).addTo(mymap);
	poi_layer.bindTooltip("Glissez moi au bon endroit.", {permanent: true, direction: 'auto'}).openTooltip();

	mymap.on('click', function(e){
		poi_layer.unbindTooltip();
		poi_layer.setLatLng(e.latlng);
		$("#lat").val(e.latlng.lat.toFixed(6));
		$("#lon").val(e.latlng.lng.toFixed(6));
	})
	poi_layer.on('move', function(e){
		poi_layer.unbindTooltip();
		$("#lat").val(e.latlng.lat.toFixed(6));
		$("#lon").val(e.latlng.lng.toFixed(6));
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