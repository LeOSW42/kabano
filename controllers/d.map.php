<?

$head['css'] = "d.index.css";

if(isset($controller->splitted_url[1]) && $controller->splitted_url[1] != '') {
	switch ($controller->splitted_url[1]) {
		default:
			$notfound = 1;
			break;
	}
}
else {
	$head['title'] = "Carte";
	$head['third'] = "leaflet/leaflet.js;leaflet-fullscreen/Leaflet.fullscreen.min.js;leaflet-easybutton/easy-button.js";
	$head['css'] .= ";d.map.css;../third/leaflet/leaflet.css;../third/leaflet-fullscreen/leaflet.fullscreen.css;../third/leaflet-easybutton/easy-button.css";
	$head['js'] = "d.map.js";

	require_once($config['includes_folder']."poi_types.struct.php");
	include ($config['views_folder']."d.map.html");
}

?>