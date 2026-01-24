<?php

/**
 * Contrôleur de la page carte (map).
 */

$head['css'] = "d.index.css";

// Gestion des routes secondaires de la carte.
if(isset($controller->splitted_url[1]) && $controller->splitted_url[1] != '') {
	switch ($controller->splitted_url[1]) {
		default:
			$notfound = 1;
			break;
	}
}
else {
	// Chargement de la carte et des dépendances Leaflet.
	$head['title'] = "Carte";
	$head['third'] = "leaflet/leaflet.js;leaflet-fullscreen/Leaflet.fullscreen.min.js;leaflet-easybutton/easy-button.js";
	$head['css'] .= ";d.map.css;../third/leaflet/leaflet.css;../third/leaflet-fullscreen/leaflet.fullscreen.css;../third/leaflet-easybutton/easy-button.css";
	$head['js'] = "d.map.js";

	require_once($config['includes_folder']."poi_types.struct.php");
	include ($config['views_folder']."d.map.html");
}
