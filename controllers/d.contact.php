<?

function post($index) {
	return isset($_POST[$index]) ? $_POST[$index] : '';
}

$error = "no";

if(isset($_POST['submit'])) {
	$message = "Message reçu depuis Kabano par ".post('name').".<br>\r\n";
	$message .= "<hr>\r\n";
	$message .= "<pre style='padding: 10px; background: #ccc;'>".strip_tags(post('message'))."</pre><br>\r\n";

	$headers = 'From: '. post('mail') . "\r\n" .
	'Reply-To: '. post('mail') . "\r\n" .
	'X-Mailer: PHP/' . phpversion() . "\r\n" .
	'MIME-Version: 1.0' . "\r\n" .
	'Content-type: text/html; charset=UTF-8' . "\r\n"; 

	if(post('ns') == '' && $_POST['captcha'] == -2) {
		$send = true;
		if(post('name') == '') {
			$error = "name";
			$send = false;
		}
		if(post('subject') == '') {
			$error = "subject";
			$send = false;
		}
		if(post('mail') == '') {
			$error = "mail";
			$send = false;
		}
		if(post('message') == '') {
			$error = "message";
			$send = false;
		}
		if($send) {
			if(mail($config['admin_mail'], "Kabano :: ".post('subject'), $message, $headers)) {
				$error = "none";
			} else {
				$error = "unknown";
			}
		}
	}
	else {
		$error = "spam";
	}
}

if(post('name') != '')
	$contact['name'] = post('name');
else if($user->role > 0)
	$contact['name'] = $user->name;
else
	$contact['name'] = '';

if(post('mail') != '')
	$contact['mail'] = post('mail');
else if($user->role > 0)
	$contact['mail'] = $user->mail;
else
	$contact['mail'] = '';

$contact['subject'] = post('subject');
$contact['message'] = post('message');
$contact['ns'] = post('ns');


$head['css'] = "d.index.css;d.user.css";
$head['js'] = "d.captcha.js";
$head['title'] = "Contact";

include ($config['views_folder']."d.contact.html");


?>