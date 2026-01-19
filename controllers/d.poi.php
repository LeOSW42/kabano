<?php

require_once($config['models_folder']."d.poi.php");
require_once($config['models_folder']."d.comments.php");
require_once($config['models_folder']."d.users.php");
require_once($config['includes_folder']."poi_types.struct.php");

$head['css'] = "d.index.css;d.poi.css";

$poi = new Kabano\Poi();

switch ($controller->splitted_url[1]) {
	case "new":
		if ($user->rankIsHigher("registered")) {
			if (isset($_POST['submit'])) {
				$poi->name = $_POST['name'];
				$poi->locale = $_POST['locale'];
				$poi->poi_type = $_POST['poi_type'];
				$poi->lat = $_POST['lat'];
				$poi->lon = $_POST['lon'];
				$poi->ele = $_POST['ele'];
				$poi->author = $user->id;
				$poi->source = "kab";
				$poi->is_commentable = isset($_POST['is_commentable']) ? 't' : 'f';

				$definition = $poi_types[$poi->poi_type][5];
				$params = [];

				foreach ($definition as $key => $label) {

				    if (isset($_POST[$key])) {
				        $value = $_POST[$key];

				        if (str_starts_with($key, 'b_')) {
				            // 3 états : 1 = oui, 0 = non, -1 = non renseigné
				            $params[$key] = ($value === "1" ? 1 :
				                             ($value === "0" ? 0 : -1));
				        }
				        elseif (str_starts_with($key, 'n_')) {
				            $params[$key] = is_numeric($value) ? (0 + $value) : null;
				        }
				        elseif (str_starts_with($key, 't_') || str_starts_with($key, 'l_')) {
				            $params[$key] = trim($value);
				        }
				        else {
				            $params[$key] = $value;
				        }

				    } else {
				        // Champ absent → booléen = -1 (non renseigné)
				        if (str_starts_with($key, 'b_')) {
				            $params[$key] = -1;
				        } else {
				            $params[$key] = null;
				        }
				    }
				}

				$poi->parameters = json_encode($params, JSON_UNESCAPED_UNICODE);

				if (!$poi->checkPermalink($_POST['permalink'], 1)) {
					$poi->permalink = $_POST['permalink'];
					$poi->insert();
					header('Location: '.$config['rel_root_folder']."poi/".$poi->permalink);
				} else {
					$head['title'] = $poi->name;
					$error = "permalink";
				}
			} else {
				$head['title'] = "Nouvel hébergement";
			}

			$locales = new Kabano\Locales();
			$locales->getAll();

			$head['third'] = "leaflet/leaflet.js;leaflet-fullscreen/Leaflet.fullscreen.min.js;leaflet-easybutton/easy-button.js";
			$head['css'] .= ";../third/leaflet/leaflet.css;../third/leaflet-fullscreen/leaflet.fullscreen.css;../third/leaflet-easybutton/easy-button.css";
			$head['js'] = "d.poi_map.js";

			$poi->lat = "";
			$poi->lon = "";
			$poi->ele = "";

			$new = 1;
			include ($config['views_folder']."d.poi.edit.html");
			break;
		} else {
			$notfound = 1;
		}
		break;

	case "elevation_proxy":
		if (isset($_GET['location'])) {
			header("Content-Type: application/json;charset=utf-8");
			echo(file_get_contents("https://api.opentopodata.org/v1/mapzen?locations=".$_GET['location']));
			break;
		} else {
			$notfound = 1;
		}
		break;

	case "api_list":
	    header("Content-Type: application/json; charset=utf-8");

	    $pois = new Kabano\Pois();
	    $pois->listPois();

	    $out = [];

	    foreach ($pois->objs as $poi) {
	        $out[] = [
	            "id"        => $poi->content_id,
	            "name"      => $poi->name,
	            "lat"       => floatval($poi->lat),
	            "lon"       => floatval($poi->lon),
	            "type"      => $poi->poi_type,
	            "permalink" => $poi->permalink
	        ];
	    }

	    echo json_encode($out, JSON_UNESCAPED_UNICODE);
		break;

	default:
		// Affichage / édition / suppression d’un POI
		if ($poi->checkPermalink($controller->splitted_url[1], $user->rankIsHigher("premium"))) {

			// Suppression
			if (isset($controller->splitted_url[2]) && $controller->splitted_url[2] == "delete" && $user->rankIsHigher("moderator")) {
				$poi->delete();
				header('Location: '.$config['rel_root_folder']."poi/".$poi->permalink);
			}

			// Restauration
			else if (isset($controller->splitted_url[2]) && $controller->splitted_url[2] == "restore" && $user->rankIsHigher("moderator")) {
				$poi->restore();
				header('Location: '.$config['rel_root_folder']."poi/".$poi->permalink);
			}

			// Édition
			else if (isset($controller->splitted_url[2]) && $controller->splitted_url[2] == "edit" && $user->rankIsHigher("moderator")) {
				if (isset($_POST['submit'])) {
					$poi->name = $_POST['name'];
					$poi->locale = $_POST['locale'];
					$poi->poi_type = $_POST['poi_type'];
					$poi->lat = $_POST['lat'];
					$poi->lon = $_POST['lon'];
					$poi->ele = $_POST['ele'];
					$poi->author = $user->id;
					$poi->source = "kab";
					$poi->is_commentable = isset($_POST['is_commentable']) ? 't' : 'f';

					$definition = $poi_types[$poi->poi_type][5];
					$params = [];

					foreach ($definition as $key => $label) {

					    if (isset($_POST[$key])) {
					        $value = $_POST[$key];

					        if (str_starts_with($key, 'b_')) {
					            // 3 états : 1 = oui, 0 = non, -1 = non renseigné
					            $params[$key] = ($value === "1" ? 1 :
					                             ($value === "0" ? 0 : -1));
					        }
					        elseif (str_starts_with($key, 'n_')) {
					            $params[$key] = is_numeric($value) ? (0 + $value) : null;
					        }
					        elseif (str_starts_with($key, 't_') || str_starts_with($key, 'l_')) {
					            $params[$key] = trim($value);
					        }
					        else {
					            $params[$key] = $value;
					        }

					    } else {
					        // Champ absent → booléen = -1 (non renseigné)
					        if (str_starts_with($key, 'b_')) {
					            $params[$key] = -1;
					        } else {
					            $params[$key] = null;
					        }
					    }
					}

					$poi->parameters = json_encode($params, JSON_UNESCAPED_UNICODE);
					$poi->update();
					header('Location: '.$config['rel_root_folder']."poi/".$poi->permalink);
				} else {
					$locales = new Kabano\Locales();
					$locales->getAll();

					$head['third'] = "leaflet/leaflet.js;leaflet-fullscreen/Leaflet.fullscreen.min.js;leaflet-easybutton/easy-button.js";
					$head['css'] .= ";../third/leaflet/leaflet.css;../third/leaflet-fullscreen/leaflet.fullscreen.css;../third/leaflet-easybutton/easy-button.css";
					$head['js'] = "d.poi_map.js";

					$head['title'] = $poi->name;
					include ($config['views_folder']."d.poi.edit.html");
				}
			}

			// Affichage
			else {
				// Historique
				if ($user->rankIsHigher("premium")) {
					$PoiHistory = new Kabano\Pois();
					$PoiHistory->getHistory($controller->splitted_url[1]);
				}
				if (isset($controller->splitted_url[2]) && is_numeric($controller->splitted_url[2])) {
					$poi->checkPermalink($controller->splitted_url[1], $user->rankIsHigher("premium"), $controller->splitted_url[2]);
				}

				// Création d’un commentaire
				if (isset($controller->splitted_url[2]) && $controller->splitted_url[2] == "new_comment") {
					if (isset($_POST['submit']) && $user->rankIsHigher("registered")) {
						$Comment = new Kabano\Comment();
						$Comment->locale = $user->locale;
						$Comment->author = $user->id;
						$Comment->content = $poi->content_id;
						$Comment->comment = $_POST['comment'];
						$Comment->insert();
					}
				}

				// Suppression d’un commentaire
				if (isset($controller->splitted_url[2]) && $controller->splitted_url[2] == "delete_comment") {
					if (isset($controller->splitted_url[3]) && is_numeric($controller->splitted_url[3])) {
						$Comment = new Kabano\Comment();
						if ($Comment->checkId($controller->splitted_url[3]))
							if ($user->rankIsHigher("moderator") || $user->id == $Comment->author)
								$Comment->delete();
					}
				}

				// Restauration d’un commentaire
				if (isset($controller->splitted_url[2]) && $controller->splitted_url[2] == "restore_comment") {
					if (isset($controller->splitted_url[3]) && is_numeric($controller->splitted_url[3])) {
						$Comment = new Kabano\Comment();
						if ($Comment->checkId($controller->splitted_url[3]))
							if ($user->rankIsHigher("moderator") || $user->id == $Comment->author)
								$Comment->restore();
					}
				}

				// Commentaires
				if ($poi->is_commentable == "t") {
					$poi_comments = new Kabano\Comments();
					$poi_comments->listComments($poi->content_id, ($user->rankIsHigher("premium")));

					foreach ($poi_comments->objs as $comment) {
						$comment->md2html();
						$comment->author_obj = new Kabano\User();
						$comment->author_obj->checkId($comment->author);
					}
				}

				// Auteur
				$tempUser = new Kabano\User();
				$tempUser->checkId($poi->author);
				$poi->author_name = $tempUser->name;
				unset($tempUser);

				$head['third'] = "leaflet/leaflet.js;leaflet-fullscreen/Leaflet.fullscreen.min.js;leaflet-easybutton/easy-button.js";
				$head['css'] .= ";../third/leaflet/leaflet.css;../third/leaflet-fullscreen/leaflet.fullscreen.css;../third/leaflet-easybutton/easy-button.css";
				$head['js'] = "d.poi_map.js";

				$head['title'] = $poi->name;
				include ($config['views_folder']."d.poi.view.html");
			}
		} else {
			$notfound = 1;
		}
		break;
}

?>
