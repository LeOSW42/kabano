<?

require_once($config['models_folder']."d.poi.php");
require_once($config['models_folder']."d.users.php");

$head['css'] = "d.index.css;d.poi.css";

$poi = new Kabano\Poi();

switch ($controller->splitted_url[1]) {
	case "new":
		if($user->rankIsHigher("registered")) {
			if(isset($_POST['submit'])) {
				$poi->name = $_POST['name'];
				$poi->locale = $_POST['locale'];
				$poi->poi_type = $_POST['poi_type'];
				$poi->lat = $_POST['lat'];
				$poi->lon = $_POST['lon'];
				$poi->ele = $_POST['ele'];
				$poi->author = $user->id;
				$poi->source = "kab";
				$poi->is_commentable = isset($_POST['is_commentable'])?'t':'f';
				$poi->parameters = new \stdClass();
				if(!$poi->checkPermalink($_POST['permalink'],1)) {
					$poi->permalink = $_POST['permalink'];
					$poi->insert();
					header('Location: '.$config['rel_root_folder']."poi/".$poi->permalink);
				}
				else {
					$head['title'] = $poi->name;
					$error = "permalink";
				}
			}
			else {
				$head['title'] = "Nouvel hébergement";
			}

			$locales = new Kabano\Locales();
			$locales->getAll();

			$head['third'] = "leaflet/leaflet.js;leaflet-fullscreen/Leaflet.fullscreen.min.js;leaflet-easybutton/easy-button.js";
			$head['css'] .= ";../third/leaflet/leaflet.css;../third/leaflet-fullscreen/leaflet.fullscreen.css;../third/leaflet-easybutton/easy-button.css";
			$head['js'] = "d.poi_map.js";

			$poi->lat = ""; $poi->lon = ""; $poi->ele = "";

			$new = 1;
			include ($config['views_folder']."d.poi.edit.html");
			break;
		}
		else {
			$notfound = 1;	
		}
	case "elevation_proxy":
		if(isset($_GET['location'])) {
			header("Content-Type: application/json;charset=utf-8");
			echo(file_get_contents("https://api.opentopodata.org/v1/mapzen?locations=".$_GET['location']));
			break;
		}
		else {
			$notfound = 1;	
		}
	default:
		// If the page exists
		if ($poi->checkPermalink($controller->splitted_url[1],$user->rankIsHigher("premium"))) {
			// if (isset($controller->splitted_url[2]) && $controller->splitted_url[2] == "delete" && $user->rankIsHigher("moderator")) {
			// 	$poi->delete();
			// 	header('Location: '.$config['rel_root_folder']."blog/".$poi->permalink);
			// }
			// else if (isset($controller->splitted_url[2]) && $controller->splitted_url[2] == "restore" && $user->rankIsHigher("moderator")) {
			// 	$poi->restore();
			// 	header('Location: '.$config['rel_root_folder']."blog/".$poi->permalink);
			// }
			// else if (isset($controller->splitted_url[2]) && $controller->splitted_url[2] == "edit" && $user->rankIsHigher("moderator")) {
			// 	if(isset($_POST['submit'])) {
			// 		$poi->content = $_POST['content'];
			// 		$poi->locale = $_POST['locale'];
			// 		$poi->name = $_POST['name'];
			// 		$poi->is_commentable = $_POST['is_commentable'];
			// 		$poi->author = $user->id;
			// 		$poi->update();
			// 		header('Location: '.$config['rel_root_folder']."blog/".$poi->permalink);
			// 	}
			// 	else {
			// 		$locales = new Kabano\Locales();
			// 		$locales->getAll();

			// 		$head['title'] = $poi->name;
			// 		include ($config['views_folder']."d.blog.edit.html");
			// 	}
			// }
			// else {
				// Manage history of an article
				if($user->rankIsHigher("premium")) {
					$blogHistory = new Kabano\pois();
					$blogHistory->getHistory($controller->splitted_url[1]);
				}
				if (isset($controller->splitted_url[2]) && is_numeric($controller->splitted_url[2]))
					$poi->checkPermalink($controller->splitted_url[1],$user->rankIsHigher("premium"),$controller->splitted_url[2]);

				// Manage comment creation
				if (isset($controller->splitted_url[2]) && $controller->splitted_url[2]=="new_comment") {
					if (isset($_POST['submit']) && $user->rankIsHigher("registered")) {
						$blogComment = new Kabano\BlogComment();
						$blogComment->locale = $user->locale;
						$blogComment->author = $user->id;
						$blogComment->content = $poi->content_id;
						$blogComment->comment = $_POST['comment'];
						$blogComment->insert();
					}
				}

				// Manage comment deletion
				if (isset($controller->splitted_url[2]) && $controller->splitted_url[2]=="delete_comment") {
					if (isset($controller->splitted_url[3]) && is_numeric($controller->splitted_url[3])) {
						$blogComment = new Kabano\BlogComment();
						if($blogComment->checkId($controller->splitted_url[3]))
							if ($user->rankIsHigher("moderator") || $user->id == $blogComment->author)
								$blogComment->delete();
					}
				}

				// Manage comment restoration
				if (isset($controller->splitted_url[2]) && $controller->splitted_url[2]=="restore_comment") {
					if (isset($controller->splitted_url[3]) && is_numeric($controller->splitted_url[3])) {
						$blogComment = new Kabano\BlogComment();
						if($blogComment->checkId($controller->splitted_url[3]))
							if ($user->rankIsHigher("moderator") || $user->id == $blogComment->author)
								$blogComment->restore();
					}
				}

				$poi->md2html();

				// Manage comments
				if ($poi->is_commentable == "t") {
					$pois_comments = new Kabano\BlogComments();
					$pois_comments->listComments($poi->content_id, ($user->rankIsHigher("premium")));

					$i = 0;
					foreach ($pois_comments->objs as $comment) {
						$comment->md2html();
						$comment->author_obj = new Kabano\User();
						$comment->author_obj->checkId($comment->author);
					}
				}


				$tempUser = new Kabano\User();
				$tempUser->checkId($poi->author);
				$poi->author_name = $tempUser->name;
				unset($tempUser);

				$head['title'] = $poi->name;
				include ($config['views_folder']."d.blog.view.html");
			// }
		}
		else {
			$notfound = 1;	
		}
		break;
}

?>