<?

require_once($config['models_folder']."d.users.php");

$head['css'] = "d.index.css;d.user.css";

if(isset($controller->splitted_url[1])) {
	switch ($controller->splitted_url[1]) {
		case 'login':
			$head['title'] = "Connexion";
			if ($user->rank == "visitor") {
				if (isset($_POST['submit'])) {
					// PROCESS DATA FROM FORM
					$user = new User();

					if($user->login($_POST['login'], $_POST['password'])) {
						// SUCESSFULL LOGIN
						$_SESSION['userid'] = $user->id;
						header('Location: '.$_SERVER['HTTP_REFERER']);
					}
					else {
						header('Location: '.$config['rel_root_folder'].'user/login?error=1');
					}
				}
				include ($config['views_folder']."d.user.login.html");
			} else {
				header('Location: '.$config['rel_root_folder']);
			}
			break;
		case 'logout':
			session_destroy();
			header('Location: '.$_SERVER['HTTP_REFERER']);
			break;
		case 'signin':
			$head['js'] = "d.captcha.js";
			$head['title'] = "Création de compte";
			if ($user->rank == "visitor") {
				if (isset($_POST['submit'])) {
					// PROCESS DATA FROM FORM
					$user = new User();
					$user->name = $_POST['login'];
					$user->email = strtolower($_POST['email']);
					$user->rank = "registered";

					if($_POST['captcha'] == -2) {
						if($user->availableName()) {
							if($user->availableMail()) {
								if($_POST['password'] AND $user->name != "" AND $user->email != "") {
									$user->create(sha1($_POST['password']));
									header('Location: '.$config['rel_root_folder'].'user/login?status=created');
								}
								else {
									header('Location: '.$config['rel_root_folder'].'user/signin?error=empty');
								}
							}
							else {
								header('Location: '.$config['rel_root_folder'].'user/signin?error=email');
							}
						}
						else {
							header('Location: '.$config['rel_root_folder'].'user/signin?error=name');
						}
					}
					else {
						header('Location: '.$config['rel_root_folder'].'user/signin?error=captcha');
					}
				}
				include ($config['views_folder']."d.user.signin.html");
			} else {
				header('Location: '.$config['rel_root_folder']);
			}
			break;
		case 'password_lost':
			$head['title'] = "Récupération de mot de passe";
			if ($user->rank == "visitor") {
				if (isset($_POST['submit'])) {
					// PROCESS DATA FROM FORM
					$user = new User();
					$user->email = strtolower($_POST['email']);

					if($user->availableMail()) {
						header('Location: '.$config['rel_root_folder'].'user/password_lost?error=1');
					}
					else {
						$user->sendPassword();
						header('Location: '.$config['rel_root_folder'].'user/login?status=password_sent');
					}
				}
				include ($config['views_folder']."d.user.password_lost.html");
			} else {
				header('Location: '.$config['rel_root_folder']);
			}
			break;
		case 'p':
			if ($user->rankIsHigher("registered")) {
				$userProfile = new User();
				if (!isset($controller->splitted_url[2]) OR $controller->splitted_url[2]=="") {
					// WE DISPLAY THE CONNECTED USER PROFILE
					$userProfile = $user;
				} else {
					// WE DISPLAY THE SELECTED USER PROFILE FROM ID
					$userProfile->checkID(intval($controller->splitted_url[2]));
				}
				$head['title'] = "Profil inexistant";
				if($userProfile->id != 0) {
					$head['title'] = "Profil de ".$userProfile->name;
				}

				// If we are editing the profile
				if(isset($controller->splitted_url[3]) && $controller->splitted_url[3]=="edit" && ($user->rankIsHigher("moderator") || $user->id == $userProfile->id)) {
					$locales = new Locales;
					$locales->getAll();
					$head['js'] = "d.avatar.js";
					if (isset($_POST['submit'])) {
						$receivedUser = new User();
						$receivedUser->name = $_POST['name'];
						if($receivedUser->name != $userProfile->name && $receivedUser->availableName())
							$userProfile->name = $receivedUser->name;
						else if($receivedUser->name != $userProfile->name)
							$nameError=1;
						$receivedUser->email = strtolower($_POST['email']);
						if($receivedUser->email != $userProfile->email && $receivedUser->availableMail())
							$userProfile->email = $receivedUser->email;
						else if ($receivedUser->email != $userProfile->email)
							$emailError=1;
						if($_POST['password']!='')
							$userProfile->password=sha1($_POST['password']);
						$userProfile->locale=$_POST['locale'];
						if($user->rankIsHigher("administrator"))
							$userProfile->rank = $_POST['rank'];
						$userProfile->website=$_POST['website'];

						// Is the file correctly sent to the server ?
						$pathToFile = $config['medias_folder']."avatars/".$userProfile->id;
						if(isset($_FILES['avatarfile']['tmp_name']) && $_FILES['avatarfile']['tmp_name']!='' && $_FILES['avatarfile']['size'] < 16000000 && isset($_POST['avatar'])) {

							require_once($config['includes_folder']."images.php");

							if(file_exists($pathToFile)) unlink($pathToFile);
							move_uploaded_file($_FILES['avatarfile']['tmp_name'], $pathToFile);

							if(file_exists($pathToFile."_p.jpg")) unlink($pathToFile."_p.jpg");
							generate_image_thumbnail($pathToFile, $pathToFile."_p.jpg", 220, 240);
							if(file_exists($pathToFile."_s.jpg")) unlink($pathToFile."_s.jpg");
							generate_image_thumbnail($pathToFile, $pathToFile."_s.jpg", 28, 28);

							$userProfile->is_avatar_present = 't';
						}
						elseif (!isset($_POST['avatar'])) {
							if(file_exists($pathToFile)) unlink($pathToFile);
							if(file_exists($pathToFile."_p.jpg")) unlink($pathToFile."_p.jpg");
							if(file_exists($pathToFile."_s.jpg")) unlink($pathToFile."_s.jpg");
							$userProfile->is_avatar_present = 'f';
						}

						$userProfile->update();

						$updated = 1;
					}
					include ($config['views_folder']."d.user.profile.edit.html");

				}
				// If we are displaying the profile
				else {
					if (isset($_POST['submit']) && $user->rankIsHigher("registered")) {
						// PROCESS DATA FROM CONTACT FORM
						$message = $_POST['message'];
						
						$userProfile->sendMail($message, $user);
						$mailsent = 1;
					}
					include ($config['views_folder']."d.user.profile.html");
				}
			}
			else {
				header('Location: '.$config['rel_root_folder']);
			}
			break;
		case 'member_list':
			if ($user->rankIsHigher("registered")) {
				$rows_per_pages = 50;
				// Get the correct page number
				if (!isset($controller->splitted_url[2]) OR $controller->splitted_url[2]=="" OR $controller->splitted_url[2]=="0" OR !is_numeric($controller->splitted_url[2])) {
					$page = 0;
				} else {
					$page = $controller->splitted_url[2] - 1;
				}
				$head['title'] = "Liste des membres";

				$users = new Users();
				$users->number();

				// In case the wanted page is too big
				if($rows_per_pages * $page >= $users->number)
					$page = 0;

				if(isset($_GET['order']))
					$order = $_GET['order'];
				else
					$order = 'ASC';
				if(isset($_GET['orderby']))
					$orderby = $_GET['orderby'];
				else
					$orderby = 'id';

				$users->list_users($page*$rows_per_pages,$rows_per_pages,$orderby,$order);

				$first = $page*$rows_per_pages+1;
				$last = (($page+1)*$rows_per_pages > $users->number ? $users->number : ($page+1)*$rows_per_pages);
				
				include ($config['views_folder']."d.user.member_list.html");
			}
			else {
				header('Location: '.$config['rel_root_folder']);
			}
			break;
		default:
			$notfound = 1;
			break;
	}
}
else {
	$notfound = 1;
}

?>