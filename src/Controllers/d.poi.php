<?php

/**
 * Contrôleur des points d'intérêt (POI) : création, affichage, API.
 */

require_once($config['models_folder']."d.poi.php");
require_once($config['models_folder']."d.comments.php");
require_once($config['models_folder']."d.users.php");
require_once($config['includes_folder']."poi_types.struct.php");

$head['css'] = "d.index.css;d.poi.css";

$poi = new Kabano\Poi();

// Routage des actions POI.
switch ($controller->splitted_url[1]) {
	case "new":
		if ($user->rankIsHigher("registered")) {
			if (isset($_POST['submit'])) {
				$poi->name = $_POST['name'];
				$poi->locale = $_POST['locale'];
				$poi->poi_type = $_POST['poi_type'];
				$poi->lat = $_POST['lat'];
				$poi->lon = $_POST['lon'];
				$poi->ele = empty($_POST['ele']) ? 0 : (int) $_POST['ele'];
				$poi->author = $user->id;
				$poi->source = "kab";
				$poi->is_commentable = 't';

				// Paramètres selon le type de POI.
				$definition = $poi_types[$poi->poi_type][5];
				$params = [];

				foreach ($definition as $key => $label) {

				    if (isset($_POST[$key])) {
				        $value = $_POST[$key];

						if (str_starts_with($key, 'b_')) {
						    if ($value === "0" || $value === "1" || $value === "2") {
						        $params[$key] = intval($value);
						    } else {
						        $params[$key] = -1; // non renseigné
						    }
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
		// Proxy vers l'API d'élévation.
		if (isset($_GET['location'])) {
			if (!preg_match('/^[0-9,\.\|\-]+$/', $_GET['location'])) {
				$notfound = 1;
				break;
			}
			$location = urlencode($_GET['location']);
			header("Content-Type: application/json;charset=utf-8");
			echo(file_get_contents("https://api.opentopodata.org/v1/mapzen?locations=".$location));
			break;
		} else {
			$notfound = 1;
		}
		break;

	case "api_bbox":
	    // API JSON pour la liste des POIs (carte).
	    header("Content-Type: application/json; charset=utf-8");

	    $north = (float)$_GET['north'];
	    $south = (float)$_GET['south'];
	    $east  = (float)$_GET['east'];
	    $west  = (float)$_GET['west'];

	    $pois = new Kabano\Pois();
		$pois->listPoisBBOX(
	        $north,
	        $south,
	        $east,
	        $west,
	        $user->rankIsHigher("premium") ? 1 : 0);

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
					$poi->ele = empty($_POST['ele']) ? 0 : (int) $_POST['ele'];
					$poi->is_commentable = 't';

					$definition = $poi_types[$poi->poi_type][5];
					$params = [];

					foreach ($definition as $key => $label) {

					    if (isset($_POST[$key])) {
					        $value = $_POST[$key];

							if (str_starts_with($key, 'b_')) {
							    if ($value === "0" || $value === "1" || $value === "2") {
							        $params[$key] = intval($value);
							    } else {
							        $params[$key] = -1;
							    }
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

						if (!empty($_FILES['photo']['tmp_name'])) {

						    $tmp = $_FILES['photo']['tmp_name'];
						    $info = getimagesize($tmp);
						    if ($info === false) {
						        die("Fichier non valide");
						    }

						    $mime = $info['mime'];
						    switch ($mime) {
						        case 'image/jpeg':
						            $src = imagecreatefromjpeg($tmp);
						            break;
						        case 'image/png':
						            $src = imagecreatefrompng($tmp);
						            break;
						        case 'image/webp':
						            $src = imagecreatefromwebp($tmp);
						            break;
						        default:
						            die("Format non supporté");
						    }

						    $width  = imagesx($src);
						    $height = imagesy($src);

						    // Taille max
						    $max = 1500;

						    $ratio = min($max / $width, $max / $height, 1);
						    $newWidth  = (int)($width * $ratio);
						    $newHeight = (int)($height * $ratio);
						    $dst = imagecreatetruecolor($newWidth, $newHeight);
						    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

						    // --- Watermark ---
						    $watermarkPath = $config['medias_folder'] . '/watermark.png';

						    if (file_exists($watermarkPath)) {
						        $wm = imagecreatefrompng($watermarkPath);

						        imagealphablending($wm, true);
						        imagesavealpha($wm, true);

						        $wmWidth  = imagesx($wm);
						        $wmHeight = imagesy($wm);

						        $x = $newWidth - $wmWidth - 20;
						        $y = 20;

						        // Fusion
						        imagecopy($dst, $wm, $x, $y, 0, 0, $wmWidth, $wmHeight);
						    }

						    $filename = uniqid('comment_') . '.jpg';
						    $destination = $config['medias_folder'] . 'comment_photos/' . $filename;

						    imagejpeg($dst, $destination, 70);

						    $Comment->photo = $filename;
						}

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

				$head['third'] = "leaflet/leaflet.js;leaflet-fullscreen/Leaflet.fullscreen.min.js;leaflet-easybutton/easy-button.js;simplelightbox/simple-lightbox.min.js";
				$head['css'] .= ";../third/leaflet/leaflet.css;../third/leaflet-fullscreen/leaflet.fullscreen.css;../third/leaflet-easybutton/easy-button.css;../third/simplelightbox/simple-lightbox.min.css";
				$head['js'] = "d.poi_map.js";

				$head['title'] = $poi->name;
				include ($config['views_folder']."d.poi.view.html");
			}
		} else {
			$notfound = 1;
		}
		break;
}
