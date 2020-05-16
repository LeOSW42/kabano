<?

require_once($config['models_folder']."d.users.php");

ini_set("session.cookie_lifetime",60*60*24*30);
session_start();

$user = new Kabano\User();
$user->rank = "visitor"; // All users are visitors

if(isset($_SESSION['userid'])) {
	$user->checkID($_SESSION['userid']);
	if ($user->checkID($_SESSION['userid'])) {
		$user->updateLoginDate();
		//setlocale(LC_ALL, $config['locales'][$user->locale][4]);
	}
	else {
		session_destroy();
	}
}

?>