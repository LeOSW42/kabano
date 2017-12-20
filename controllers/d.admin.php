<?

if(isset($controller->splitted_url[1]) && $user->role >= 800) {
	switch ($controller->splitted_url[1]) {
		case '': case 'admin':
			$head['title'] = "Administration";
			include ($config['views_folder']."d.admin.html");
			break;
		case 'git-pull':
			if ($user->role >= 1000) {
				$head['title'] = "Mise à jour";

				$output = array();
				chdir($config['abs_root_folder']);
				exec("git pull origin master", $output);

				include ($config['views_folder']."d.admin.git-pull.html");
			}
			else {
				$notfound = 1;
			}
			break;
		case 'logs':
			if ($user->role >= 800) {
				$head['title'] = "Logs";

				$files_list = scandir($config['logs_folder']);

				if (isset($controller->splitted_url[2]) && is_numeric($controller->splitted_url[2]) && intval($controller->splitted_url[2]) < count($files_list)-2) {
					$filenb = $controller->splitted_url[2];
				}
				else {
					$filenb = 0;
				}

				chdir($config['logs_folder']);
				exec("tail -n 200 ".$files_list[$filenb+2]." | tac", $output);

				include ($config['views_folder']."d.admin.logs.html");
			}
			else {
				$notfound = 1;
			}
			break;
		default:
			$notfound = 1;
			break;
	}
}
else if($user->role >= 800) {
	$head['title'] = "Administration";
	include ($config['views_folder']."d.admin.html");
}
else {
	$notfound = 1;
}

?>