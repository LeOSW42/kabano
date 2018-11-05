<?

require_once($config['models_folder']."d.blog.php");
require_once($config['models_folder']."d.users.php");

$head['css'] = "d.index.css;d.blog.css";

$blogArticle = new Kabano\BlogArticle();

// In case we are in the list of articles, we set url to switch with according parameters
if (!isset($controller->splitted_url[1]) OR $controller->splitted_url[1]=="" OR is_numeric($controller->splitted_url[1])) {
	$head['title'] = "Blog";

	// Get the correct page number
	if (!isset($controller->splitted_url[1]) OR $controller->splitted_url[1]=="") {
		$page = 0;
	} else {
		$page = $controller->splitted_url[1] - 1;
	}

	$controller->splitted_url[1] = "list";
	$list = "html";
	$articles_per_pages = 5;
}

switch ($controller->splitted_url[1]) {
	case "rss":
		$page = 0;
		$list = "rss";
		$articles_per_pages = 20;
	case "list":
		$blogArticles = new Kabano\BlogArticles();

		$blogArticles->number(($user->rankIsHigher("premium")));

		// In case the wanted page is too big
		if($articles_per_pages * $page >= $blogArticles->number)
			$page = 0;

		$blogArticles->listArticles($page*$articles_per_pages,$articles_per_pages,($user->rankIsHigher("premium")));

		$i = 0;
		$blogArticles_list = array();
		foreach ($blogArticles->ids as $row) {
			$blogArticles_list[$i] = new Kabano\BlogArticle();
			$blogArticles_list[$i]->id = $row;
			$blogArticles_list[$i]->populate();
			$blogArticles_list[$i]->md2txt();
			$tempUser = new Kabano\User();
			$tempUser->id = $blogArticles_list[$i]->author;
			$tempUser->populate();
			$blogArticles_list[$i]->author_name = $tempUser->name;
			unset($tempUser);
			$i++;
		}

		$first = $page*$articles_per_pages+1;
		$last = (($page+1)*$articles_per_pages > $blogArticles->number ? $blogArticles->number : ($page+1)*$articles_per_pages);

		if ($list == "rss") {
			include ($config['views_folder']."d.blog.list.rss");
		} else {
			include ($config['views_folder']."d.blog.list.html");
		}
		break;
	case "new":
		if($user->rankIsHigher("moderator")) {
			if(isset($_POST['submit'])) {
				$blogArticle->content = $_POST['content'];
				$blogArticle->locale = $_POST['locale'];
				$blogArticle->name = $_POST['name'];
				$blogArticle->is_commentable = isset($_POST['is_commentable'])?'t':'f';
				$blogArticle->author = $user->id;
				if(!$blogArticle->checkPermalink($_POST['permalink'],1)) {
					$blogArticle->permalink = $_POST['permalink'];
					$blogArticle->insert();
					header('Location: '.$config['rel_root_folder']."blog/".$blogArticle->permalink);
				}
				else {
					$head['title'] = $blogArticle->name;
					$error = "permalink";
				}
			}
			else {
				$head['title'] = "Nouvel article";
			}

			$locales = new Kabano\Locales();
			$locales->getAll();

			$new = 1;
			include ($config['views_folder']."d.blog.edit.html");
			break;
		}
	default:
		// If the page exists
		if ($blogArticle->checkPermalink($controller->splitted_url[1],$user->rankIsHigher("premium"))) {
			if (isset($controller->splitted_url[2]) && $controller->splitted_url[2] == "delete" && $user->rankIsHigher("moderator")) {
				$blogArticle->delete();
				header('Location: '.$config['rel_root_folder']."blog/".$blogArticle->permalink);
			}
			else if (isset($controller->splitted_url[2]) && $controller->splitted_url[2] == "edit" && $user->rankIsHigher("moderator")) {
				if(isset($_POST['submit'])) {
					$blogArticle->content = $_POST['content'];
					$blogArticle->locale = $_POST['locale'];
					$blogArticle->name = $_POST['name'];
					$blogArticle->is_commentable = isset($_POST['is_commentable'])?'t':'f';
					$blogArticle->author = $user->id;
					$blogArticle->update();
					header('Location: '.$config['rel_root_folder']."blog/".$blogArticle->permalink);
				}
				else {
					$locales = new Kabano\Locales();
					$locales->getAll();

					$head['title'] = $blogArticle->name;
					include ($config['views_folder']."d.blog.edit.html");
				}
			}
			else {
				// Manage history of an article
				if($user->rankIsHigher("premium")) {
					$blogArticles_history = new Kabano\BlogArticles();
					$blogArticles_history->getHistory($controller->splitted_url[1]);
				}
				if (isset($controller->splitted_url[2]) && is_numeric($controller->splitted_url[2]))
					$blogArticle->checkPermalink($controller->splitted_url[1],$user->rankIsHigher("premium"),$controller->splitted_url[2]);

				// Manage comment creation
				if (isset($controller->splitted_url[2]) && $controller->splitted_url[2]=="new_comment") {
					if (isset($_POST['submit']) && $user->rankIsHigher("registered")) {
						$blogComment = new Kabano\BlogComment();
						$blogComment->locale = $user->locale;
						$blogComment->author = $user->id;
						$blogComment->article = $blogArticle->id;
						$blogComment->content = $_POST['comment'];
						$blogComment->insert();
					}
				}

				// Manage comment deletion
				if (isset($controller->splitted_url[2]) && $controller->splitted_url[2]=="delete_comment") {
					if (isset($controller->splitted_url[3]) && is_numeric($controller->splitted_url[3])) {
						$blogComment = new Kabano\BlogComment();
						$blogComment->id = $controller->splitted_url[3];
						$blogComment->populate();
						if ($user->rankIsHigher("moderator") || $user->id == $blogComment->author)
							$blogComment->delete();
					}
				}

				// Manage comment restoration
				if (isset($controller->splitted_url[2]) && $controller->splitted_url[2]=="restore_comment") {
					if (isset($controller->splitted_url[3]) && is_numeric($controller->splitted_url[3])) {
						$blogComment = new Kabano\BlogComment();
						$blogComment->id = $controller->splitted_url[3];
						$blogComment->populate();
						if ($user->rankIsHigher("moderator") || $user->id == $blogComment->author)
							$blogComment->restore();
					}
				}

				$blogArticle->md2html();

				// Manage comments
				if ($blogArticle->is_commentable == "t") {
					$blogArticles_comments = new Kabano\BlogComments();
					$blogArticles_comments->listComments($blogArticle->id, ($user->rankIsHigher("premium")));

					$i = 0;
					foreach ($blogArticles_comments->ids as $row) {
						$blogArticles_comments_list[$i] = new Kabano\BlogComment();
						$blogArticles_comments_list[$i]->id = $row;
						$blogArticles_comments_list[$i]->populate();
						$blogArticles_comments_list[$i]->md2html();
						$blogArticles_comments_list[$i]->author_obj = new Kabano\User();
						$blogArticles_comments_list[$i]->author_obj->id = $blogArticles_comments_list[$i]->author;
						$blogArticles_comments_list[$i]->author_obj->populate();
						$i++;
					}
				}


				$tempUser = new Kabano\User();
				$tempUser->checkId($blogArticle->author);
				$blogArticle->author_name = $tempUser->name;
				unset($tempUser);

				$head['title'] = $blogArticle->name;
				include ($config['views_folder']."d.blog.view.html");
			}
		}
		else {
			$notfound = 1;	
		}
		break;
}

?>