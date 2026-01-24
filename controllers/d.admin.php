<?php

if(isset($controller->splitted_url[1]) && $user->rankIsHigher("moderator")) {
	switch ($controller->splitted_url[1]) {
		case '': case 'admin':
			$head['title'] = "Administration";
			include ($config['views_folder']."d.admin.html");
			break;
		case 'git-pull':
			if ($user->rankIsHigher("administrator")) {
				$head['title'] = "Mise à jour";

				$output = array();
				chdir($config['abs_root_folder']);
				exec("git pull 2>&1", $output);

				include ($config['views_folder']."d.admin.git-pull.html");
			}
			else {
				$notfound = 1;
			}
			break;
		case 'logs':
			if ($user->rankIsHigher("moderator")) {
			$head['title'] = "Logs";

			$output = array();
			$files_list = scandir($config['logs_folder']);
			$logs_folder = realpath($config['logs_folder']);
			$logs_folder_root = $logs_folder !== false ? rtrim($logs_folder, DIRECTORY_SEPARATOR) : null;

				if (isset($controller->splitted_url[2]) && is_numeric($controller->splitted_url[2]) && intval($controller->splitted_url[2]) < count($files_list)-2) {
					$filenb = $controller->splitted_url[2];
				}
				else {
					$filenb = 0;
				}

			$log_file = $files_list[$filenb+2] ?? null;
			if ($logs_folder_root && $log_file) {
				$log_file = basename($log_file);
				$log_path = $logs_folder_root . DIRECTORY_SEPARATOR . $log_file;
				$real_log_path = realpath($log_path);
				if ($real_log_path && str_starts_with($real_log_path, $logs_folder_root . DIRECTORY_SEPARATOR)) {
					exec("tail -n 200 ".escapeshellarg($real_log_path)." | tac", $output);
				}
			}

				include ($config['views_folder']."d.admin.logs.html");
			}
			else {
				$notfound = 1;
			}
			break;
		case 'wiki-files':
			if ($user->rankIsHigher("moderator")) {
				$head['css'] = "d.index.css;d.admin.css";
				$head['title'] = "Fichiers attachés au wiki";
				$rows_per_pages = 50;
			$files_folder = $config['medias_folder']."wiki/";
			$files_folder_real = realpath($files_folder);
			$files_folder_root = $files_folder_real !== false ? rtrim($files_folder_real, DIRECTORY_SEPARATOR) : rtrim($files_folder, DIRECTORY_SEPARATOR);

					// Delete a file
				if ($user->rankIsHigher("administrator")) {
				if(isset($controller->splitted_url[2]) && $controller->splitted_url[2]=='delete' && isset($controller->splitted_url[3])) {
					$safe_name = basename($controller->splitted_url[3]);
					$filename = $files_folder_root . DIRECTORY_SEPARATOR . $safe_name;
					$real_filename = realpath($filename);
					if ($real_filename && str_starts_with($real_filename, $files_folder_root . DIRECTORY_SEPARATOR)) {
						unlink($real_filename);
						error_log(date('r')." \t".$user->name." (".$user->id.") \tDELETE \tDelete wiki file '".$safe_name."'\r\n",3,$config['logs_folder'].'wiki-files.log');
					}
				}
				}

					// Add a file
				if(isset($controller->splitted_url[2]) && $controller->splitted_url[2]=='upload' && isset($_FILES['file'])) {
					$safe_name = basename($_FILES['file']['name']);
					$filename = $files_folder_root . DIRECTORY_SEPARATOR . $safe_name;
					if($safe_name !== '' && move_uploaded_file($_FILES['file']['tmp_name'], $filename)) {
						error_log(date('r')." \t".$user->name." (".$user->id.") \tUPLOAD Upload wiki file '".$safe_name."'\r\n",3,$config['logs_folder'].'wiki-files.log');
					}

				}

					// Get the file list
			$files_list = scandir($files_folder_root);
					// Populate table
				foreach ($files_list as $file) {
					$file_path = $files_folder_root . DIRECTORY_SEPARATOR . $file;

					if (is_file($file_path)) {
						$file_info = [
							'name' => $file,
							'type' => mime_content_type($file_path),
							'creation_date' => date("Y-m-d H:i:s", filectime($file_path)),
							'size' => filesize($file_path),
						];

						$files[] = $file_info;
					}
				}
				$filenb = count($files);

					// Manage sorting
				if(isset($_GET['orderby']))
					$orderby = $_GET['orderby'];
				else
					$orderby = 'name';
				if(isset($_GET['order']) && $_GET['order']=='ASC') {
					$order = 'ASC';
					usort($files, function ($a, $b) use ($orderby) { return $a[$orderby] <=> $b[$orderby]; });
				}
				else {
					$order = 'DESC';
					usort($files, function ($a, $b) use ($orderby) { return $b[$orderby] <=> $a[$orderby]; });
				}

					// Get the correct page number
				if (!isset($controller->splitted_url[2]) OR $controller->splitted_url[2]=="" OR $controller->splitted_url[2]=="0" OR !is_numeric($controller->splitted_url[2])) {
					$page = 0;
				} else {
					$page = $controller->splitted_url[2] - 1;
				}
					// In case the wanted page is too big
				if($rows_per_pages * $page >= $filenb)
					$page = 0;

				$first = $page*$rows_per_pages+1;
				$last = (($page+1)*$rows_per_pages > $filenb ? $filenb : ($page+1)*$rows_per_pages);
				include ($config['views_folder']."d.admin.wiki-files.html");
			}
			else {
				$notfound = 1;
			}
		break;
		case 'stats':
			if ($user->rankIsHigher("moderator")) {
				$head['title'] = "Statistiques";

				$report = $config['abs_root_folder'].'tmp/report.html';

				$files = glob('/var/log/nginx/kabano.org-access.log*.gz');

				$parts = [];

				if (!empty($files)) {
				    $parts[] = 'zcat ' . implode(' ', array_map('escapeshellarg', $files));
				}

				if (file_exists('/var/log/nginx/kabano.org-access.log.1')) {
				    $parts[] = 'cat /var/log/nginx/kabano.org-access.log.1';
				}

				$parts[] = 'cat /var/log/nginx/kabano.org-access.log';

				$command = '/bin/bash -c \'(' . implode(' && ', $parts) . ')'
				         . ' | goaccess --log-format=COMBINED --no-progress -o '
				         . escapeshellarg($report)
				         . ' -\' 2>&1';

				$output = shell_exec($command);

				include ($config['views_folder']."d.admin.stats.html");
			}
			else {
				$notfound = 1;
			}
			break;
		case 'sql-backup':
			if ($user->rankIsHigher("administrator")) {
				$head['title'] = "Export SQL";

			if(isset($controller->splitted_url[2]) && $controller->splitted_url[2]=='delete' && isset($controller->splitted_url[3])) {
				$tmp_folder = realpath($config['abs_root_folder'].'tmp');
				if ($tmp_folder !== false) {
					$safe_name = basename($controller->splitted_url[3]);
					$tmp_folder_root = rtrim($tmp_folder, DIRECTORY_SEPARATOR);
					$delete_path = $tmp_folder_root . DIRECTORY_SEPARATOR . $safe_name;
					$real_delete_path = realpath($delete_path);
					if ($real_delete_path && str_starts_with($real_delete_path, $tmp_folder_root . DIRECTORY_SEPARATOR)) {
						unlink($real_delete_path);
					}
				}
				$output = Array();
				$backup_file = Array();
			}
				else {
					// Nom du fichier de sauvegarde
					$timestamp = date('Ymd_His');
					$backup_filename[0] = $timestamp.'_backup.sql';
					$backup_file[0] = $config['abs_root_folder'].'tmp/'.$backup_filename[0];

					// Construction de la commande pg_dump
					$cmd = 'PGPASSWORD="'.$config['SQL_pass'].'" pg_dump -h '.$config['SQL_host'].' -U '.$config['SQL_user'].' -F c -b -v -f "'.$backup_file[0].'" '.$config['SQL_db'].' 2>&1';

					$output = [];
					$return_var = 0;
					exec($cmd, $output, $return_var);
				}

				$backup_files = glob($config['abs_root_folder'].'tmp/*.sql');

				include ($config['views_folder']."d.admin.backup.html");
			}
			else {
				$notfound = 1;
			}
			break;
		case 'files-backup':
			if ($user->rankIsHigher("administrator")) {
				$head['title'] = "Export des fichiers";
				$output = Array();
				$backup_file = Array();

			if(isset($controller->splitted_url[2]) && $controller->splitted_url[2]=='delete' && isset($controller->splitted_url[3])) {
				$tmp_folder = realpath($config['abs_root_folder'].'tmp');
				if ($tmp_folder !== false) {
					$safe_name = basename($controller->splitted_url[3]);
					$tmp_folder_root = rtrim($tmp_folder, DIRECTORY_SEPARATOR);
					$delete_path = $tmp_folder_root . DIRECTORY_SEPARATOR . $safe_name;
					$real_delete_path = realpath($delete_path);
					if ($real_delete_path && str_starts_with($real_delete_path, $tmp_folder_root . DIRECTORY_SEPARATOR)) {
						unlink($real_delete_path);
					}
				}
			}
				else {
					// Nom du fichier de sauvegarde
					$timestamp = date('Ymd_His');
					$backup_source[0] = $config['abs_root_folder'].'medias/avatars';
					$backup_source[1] = $config['abs_root_folder'].'medias/wiki';
					$backup_filename[0] = $timestamp.'_avatar_files.zip';
					$backup_filename[1] = $timestamp.'_wiki_files.zip';

					for($i=0;$i<2;$i++) {
						$backup_file[$i] = $config['abs_root_folder'].'tmp/'.$backup_filename[$i];

						$backup[$i] = new ZipArchive();
						if ($backup[$i]->open($backup_file[$i], ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
						    $files = new RecursiveIteratorIterator(
						        new RecursiveDirectoryIterator($backup_source[$i]),
						        RecursiveIteratorIterator::LEAVES_ONLY
						    );

						    foreach ($files as $name => $file) {
						        if (!$file->isDir()) {
						            $filePath = $file->getRealPath();
						            $relativePath = substr($filePath, strlen(realpath($backup_source[$i])) + 1);
						            $backup[$i]->addFile($filePath, $relativePath);
						        }
						    }

						    $backup[$i]->close();
						} else {
							$output[0] = "Erreur lors de la création de l'archive $backup_filename[$i] avec les avatars de $backup_source[$i]";
						}
					}
				}

				$backup_files = glob($config['abs_root_folder'].'tmp/*.zip');

				include ($config['views_folder']."d.admin.backup.html");
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
else if($user->rankIsHigher("moderator")) {
	$head['title'] = "Administration";
	include ($config['views_folder']."d.admin.html");
}
else {
	$notfound = 1;
}

// Fonctions de mise en forme

function getFontAwesomeIcon($mimeType) {
	$icons = [
		'application/pdf' => 'fa-file-pdf',
		'image/jpeg' => 'fa-file-image',
		'image/png' => 'fa-file-image',
		'application/zip' => 'fa-file-archive',
		'text/plain' => 'fa-file-alt',
		'application/vnd.ms-excel' => 'fa-file-excel',
		'application/msword' => 'fa-file-word',
		'video/mp4' => 'fa-file-video',
		'audio/mpeg' => 'fa-file-audio',
	];

    return $icons[$mimeType] ?? 'fa-file'; // Default
}

function formatBytes($bytes, $locale = 'en', $precision = 2) {
	$unitMap = [
		'en' => ['B', 'KB', 'MB', 'GB', 'TB', 'PB'],
		'fr' => ['o', 'Ko', 'Mo', 'Go', 'To', 'Po']
	];

	$locale = explode('_', $locale)[0];
	$units = $unitMap[$locale] ?? $unitMap['en'];

	if ($bytes == 0) {
		return '0 ' . $units[0];
	}

	$power = floor(log($bytes, 1024));
	$formatted = round($bytes / pow(1024, $power), $precision);

	return $formatted . ' ' . $units[$power];
}
