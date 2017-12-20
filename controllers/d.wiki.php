<?

require_once($config['models_folder']."d.wiki.php");

$head['css'] = "d.index.css;d.wiki.css";

$wikiPage = new WikiPage();
// Page doesn't exists
if(isset($controller->splitted_url[1]) && !$wikiPage->checkUrl($controller->splitted_url[1],$user->role >= 600) && $controller->splitted_url[1]!="") {
	if($user->role >= 800) {
		// Create new page
		if(isset($_POST['submit'])) {
			$wikiPage->content = $_POST['content'];
			$wikiPage->locale = $_POST['locale'];
			$wikiPage->title = $_POST['title'];
			$wikiPage->insert();

			header('Location: '.$config['rel_root_folder']."wiki/".$wikiPage->url);
		}
		else {
			$head['title'] = "Nouvelle page";
			include ($config['views_folder']."d.wiki.edit.html");
		}
	}
	else {
		$notfound = 1;
	}
}
// Page exists
else if(isset($controller->splitted_url[1]) && $wikiPage->checkUrl($controller->splitted_url[1],$user->role >= 600)) {
	if (isset($controller->splitted_url[2]) && $controller->splitted_url[2]=="edit" && $user->role >= 800) {
		// Edit page
		if(isset($_POST['submit'])) {
			$wikiPage->content = $_POST['content'];
			$wikiPage->locale = $_POST['locale'];
			$wikiPage->title = $_POST['title'];
			$wikiPage->update();

			header('Location: '.$config['rel_root_folder']."wiki/".$wikiPage->url);
		}
		else {
			$wikiPage->populate();
			$head['title'] = $wikiPage->title;
			include ($config['views_folder']."d.wiki.edit.html");
		}
	} else if (isset($controller->splitted_url[2]) && $controller->splitted_url[2]=="delete" && $user->role >= 800) {
		// Delete page
		$wikiPage->delete();
		header('Location: '.$config['rel_root_folder']."wiki/".$wikiPage->url);
	} else {
		// Display page
		if($user->role >= 600) {
			$wikiHistory = new WikiPages();
			$wikiHistory->getHistory($controller->splitted_url[1]);

			$i = 0;
			foreach ($wikiHistory->ids as $row) {
				$wikiHistory_list[$i] = new WikiPage();
				$wikiHistory_list[$i]->id = $row;
				$wikiHistory_list[$i]->populate();
				$i++;
			}
		}
		if (isset($controller->splitted_url[2]) && is_numeric($controller->splitted_url[2]))
			$wikiPage->checkUrl($controller->splitted_url[1],$user->role>=600, $controller->splitted_url[2]);

		$wikiPage->populate();
		$wikiPage->md2html();
		$head['title'] = $wikiPage->title;
		include ($config['views_folder']."d.wiki.view.html");
	}
}
else {
	$notfound = 1;
}

?>