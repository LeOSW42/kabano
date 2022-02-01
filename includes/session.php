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
		$config['locale'] = locale_get_default();
		$config['timezone'] = date_default_timezone_get();
		$user->rank = "visitor"; // All users are visitors
	}
}
else {
	$config['locale'] = locale_get_default();
	$config['timezone'] = date_default_timezone_get();
	$user->rank = "visitor"; // All users are visitors
}

$user->date_format = new IntlDateFormatter($config['locale'], IntlDateFormatter::LONG, IntlDateFormatter::NONE, $config['timezone']);
$user->datetime_format = new IntlDateFormatter($config['locale'], IntlDateFormatter::LONG, IntlDateFormatter::SHORT, $config['timezone']);
$user->datetimeshort_format = new IntlDateFormatter($config['locale'], IntlDateFormatter::SHORT, IntlDateFormatter::SHORT, $config['timezone']);


?>