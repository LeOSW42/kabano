// Carte principale
var mymap;
var poiLayer;
var vectorLayer;
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
	// Styles des POIs pour les vector tiles
	// ---------------------------------------------------------

	// Fonction qui génère le SVG du point pour les calques en pbf
	function svgDataUriFor(color, size, shape, strokeOpacity = 0.2) {
	  const w = size, h = size, strokeWidth = 1, r = Math.max(1, Math.round(size * 0.15));
	  const inset = 2 + strokeWidth / 2, innerW = w - inset * 2, innerH = h - inset * 2;
	  let shapeSvg;

	  if (shape === 'triangle') {
	    const p1 = `${w/2},${inset}`, p2 = `${w-inset},${h-inset}`, p3 = `${inset},${h-inset}`, points = `${p1} ${p2} ${p3}`;
	    shapeSvg = `<g>
	      <polygon points='${points}' fill='${color}' stroke='none'/>
	      <polygon points='${points}' fill='none' stroke='#fff' stroke-opacity='${strokeOpacity}' stroke-width='${strokeWidth}' stroke-linejoin='round'/>
	    </g>`;
	  } else {
	    shapeSvg = `<g>
	      <rect x='${inset}' y='${inset}' rx='${r}' ry='${r}' width='${innerW}' height='${innerH}' fill='${color}' stroke='none'/>
	      <rect x='${inset}' y='${inset}' rx='${r}' ry='${r}' width='${innerW}' height='${innerH}' fill='none' stroke='#fff' stroke-opacity='${strokeOpacity}' stroke-width='${strokeWidth}' stroke-linejoin='round'/>
	    </g>`;
	  }

	  const svg = `<svg xmlns='http://www.w3.org/2000/svg' width='${w}' height='${h}' viewBox='0 0 ${w} ${h}'>${shapeSvg}</svg>`;
	  return `data:image/svg+xml;utf8,${encodeURIComponent(svg)}`;
	}

	var poiStyleMap = {
		"wilderness_hut": { color: "#d73a3a", size: 16, shape: "square" },
		"basic_hut":     { color: "#d73a3a", size: 14,  shape: "square" },
		"alpine_hut":    { color: "#d73a3a", size: 18, shape: "square" },
		"halt":          { color: "#4caf50", size: 18, shape: "square" },
		"bivouac":       { color: "#d73a3a", size: 16, shape: "triangle" },
		"campsite":      { color: "#4caf50", size: 16, shape: "triangle" }
	};

	function buildPoiVectorIcon(properties) {
		var type = (properties && (properties.type || properties.poi_type || properties.poiType)) || "default";
		var style = poiStyleMap[type] || { color: "#d73a3a", size: 10, shape: "square" };
		var iconUrl = svgDataUriFor(style.color, style.size, style.shape);

		return L.icon({
			iconUrl: iconUrl,
			iconSize: [style.size, style.size],
			iconAnchor: [Math.floor(style.size / 2), style.size],
			popupAnchor: [0, -style.size]
		});
	}

	// Couche vectorielle utilisée pour les faibles zooms
	vectorLayer = L.vectorGrid.protobuf(
		root + "poi/tile/{z}/{x}/{y}.pbf",
		{
			interactive: true,
			maxZoom: 9,
			rendererFactory: L.svg.tile,
			vectorTileLayerStyles: {
				pois: function(properties, zoom) {
					var icon = buildPoiVectorIcon(properties);

					return {
						icon: icon,
						interactive: true
					};
				}
			}
		}
	);

	// Clic sur un POI vectoriel
	vectorLayer.on("click", function(e) {
		if (
			e.layer &&
			e.layer.properties &&
			e.layer.properties.permalink
		) {
			window.location =
				root +
				"poi/" +
				e.layer.properties.permalink;
		}
	});

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
		if (mymap.getZoom() < 10)
			return;

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
							className: "poi-tooltip"
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
	// Bascule entre le calque vectoriel et geojson
	// ---------------------------------------------------------

	function switchLayers() {
		var z = mymap.getZoom();

		if (z < 10) {
			poiLayer.clearLayers();

			if (!mymap.hasLayer(vectorLayer)) {
				mymap.addLayer(vectorLayer);
			}
		} else {
			if (mymap.hasLayer(vectorLayer)) {
				mymap.removeLayer(vectorLayer);
			}

			loadVisiblePois();
		}
	}

	// ---------------------------------------------------------
	// Rechargement automatique
	// ---------------------------------------------------------

	mymap.on("zoomend", switchLayers);
	mymap.on("moveend", function() {
		if (mymap.getZoom() >= 10) {
			loadVisiblePois();
		}
	});

	switchLayers();
});