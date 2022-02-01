<?

require_once($config['models_folder']."d.users.php");

ini_set("session.cookie_lifetime",60*60*24*30);
session_start();

$user = new Kabano\User();

if(isset($_SESSION['userid'])) {
	if ($user->checkID($_SESSION['userid'])) {
		$user->updateLoginDate();
		$config['locale'] = $user->locale;
		$config['timezone'] = $user->timezone;
	}
	else {
		session_destroy();
		$config['locale'] = "fr_FR";
		$config['timezone'] = "Europe/Paris";
		$user->rank = "visitor"; // All users are visitors
	}
}
else {
	$config['locale'] = "fr_FR";
	$config['timezone'] = "Europe/Paris";
	$user->rank = "visitor"; // All users are visitors
}

if (PHP_VERSION_ID < 80000) {
	$user->date_format = new IntlDateFormatter($config['locale'], IntlDateFormatter::LONG, IntlDateFormatter::NONE, $config['timezone']);
} else {
	$user->date_format = new IntlDateFormatter($config['locale'], IntlDateFormatter::RELATIVE_LONG, IntlDateFormatter::NONE, $config['timezone']);
}
$user->datetime_format = new IntlDateFormatter($config['locale'], IntlDateFormatter::LONG, IntlDateFormatter::SHORT, $config['timezone']);
$user->datetimeshort_format = new IntlDateFormatter($config['locale'], IntlDateFormatter::SHORT, IntlDateFormatter::SHORT, $config['timezone']);


?>