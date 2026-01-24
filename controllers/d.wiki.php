<?

require_once($config['models_folder']."d.wiki.php");

$head['css'] = "d.index.css;d.wiki.css";

$wikiPage = new Kabano\WikiPage();
// Page doesn't exists
if(isset($controller->splitted_url[1]) && !$wikiPage->checkPermalink($controller->splitted_url[1],$user->rankIsHigher('premium')) && $controller->splitted_url[1]!="") {
	if($user->rankIsHigher('moderator')) {
		$wikiPage->permalink = $controller->splitted_url[1];
		// Create new page
		if(isset($_POST['submit'])) {
			$wikiPage->content = $_POST['content'];
			$wikiPage->locale = $_POST['locale'];
			$wikiPage->name = $_POST['name'];
			$wikiPage->insert();

			header('Location: '.$config['rel_root_folder']."wiki/".$wikiPage->permalink);
		}
		else {
			$locales = new Kabano\Locales();
			$locales->getAll();

			$head['title'] = "Nouvelle page";
			include ($config['views_folder']."d.wiki.edit.html");
		}
	}
	else {
		$notfound = 1;
	}
}
// Page exists
else if(isset($controller->splitted_url[1]) && $wikiPage->checkPermalink($controller->splitted_url[1],$user->rankIsHigher('premium'))) {
	$wikiPage->permalink = $controller->splitted_url[1];
	if (isset($controller->splitted_url[2]) && $controller->splitted_url[2]=="edit" && $user->rankIsHigher('moderator')) {
		// Edit page
		if(isset($_POST['submit'])) {
			$wikiPage->content = $_POST['content'];
			$wikiPage->locale = $_POST['locale'];
			$wikiPage->name = $_POST['name'];
			$wikiPage->update();

			header('Location: '.$config['rel_root_folder']."wiki/".$wikiPage->permalink);
		}
		else {
			$locales = new Kabano\Locales();
			$locales->getAll();
			
			$head['title'] = $wikiPage->name;
			include ($config['views_folder']."d.wiki.edit.html");
		}
	} else if (isset($controller->splitted_url[2]) && $controller->splitted_url[2]=="delete" && $user->rankIsHigher('moderator')) {
		// Delete page
		$wikiPage->delete();
		header('Location: '.$config['rel_root_folder']."wiki/".$wikiPage->permalink);
	} else if (isset($controller->splitted_url[2]) && $controller->splitted_url[2]=="restore" && $user->rankIsHigher('moderator')) {
		// Restore page
		$wikiPage->restore();
		header('Location: '.$config['rel_root_folder']."wiki/".$wikiPage->permalink);
	} else {
		// Display page
		if($user->rankIsHigher('premium')) {
			$wikiHistory = new Kabano\WikiPages();
			$wikiHistory->getHistory($controller->splitted_url[1]);
		}
		if (isset($controller->splitted_url[2]) && is_numeric($controller->splitted_url[2]))
			$wikiPage->checkPermalink($controller->splitted_url[1], $user->rankIsHigher('premium'), $controller->splitted_url[2]);

		$wikiPage->md2html();
		$head['title'] = $wikiPage->name;
		$head['css'] .= ";../third/simplelightbox/simple-lightbox.min.css";
		$head['third'] = "simplelightbox/simple-lightbox.min.js";
		include ($config['views_folder']."d.wiki.view.html");
	}
}
else {
	$notfound = 1;
}
