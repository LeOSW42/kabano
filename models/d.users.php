<?php

namespace Kabano;

/**********************************************************
***********************************************************
**  
**  This class is to manage User object
**  
***********************************************************
**********************************************************/

require_once($config['models_folder']."d.locales.php");

// This array is related to the defined SQL enum, do not touch.
$ranks = array(
	"administrator"	=> array(1000,"Administrateur", "red", "administrator"),
	"moderator" 	=> array(800,"Modérateur", "orangered", "moderator"),
	"premium" 		=> array(600,"Membre premium", "orange", "premium"),
	"registered"	=> array(400,"Utilisateur", "green", "registered"),
	"blocked"		=> array(200,"Membre archivé", "#aaa", "blocked"),
	"visitor"		=> array(0,"Visiteur", "black", "visitor")
);

class User
{
    public $id = 0;
    public $name = NULL;
    public $version = NULL;
    public $email = NULL;
    public $password = NULL;
    public $website = NULL;
    public $is_avatar_present = NULL;
    public $is_archive = NULL;
    public $rank = NULL;
    public $locale = NULL;
    public $timezone = NULL;
    public $visit_date = NULL;
    public $register_date = NULL;

    public $date_format;
    public $datetime_format;
    public $datetimeshort_format;
    public $locale_obj;
    public $locale_loaded = false;


	/*****
	** Connect to correct account using ID and stores its ID
	*****/
	public function checkID($id) {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT * FROM users WHERE id=$1";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($id))
			or die ("Cannot execute statement\n");

		pg_close($con);

		if(pg_num_rows($result) == 1) {
			$row = pg_fetch_assoc($result);
			$this->populate($row);
			return 1;
		}
		else {
			return 0;
		}
	}

	/*****
	** Connect to correct account using user/pass and stores its ID
	*****/
	public function login($login, $pass) {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT * FROM users WHERE name=$1 AND password=$2";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($login, sha1($pass)))
			or die ("Cannot execute statement\n");

		pg_close($con); 

		if(pg_num_rows($result) == 1) {
			$row = pg_fetch_assoc($result);
			$this->populate($row);
			return 1;
		}
	}

	/*****
	** Populate the object using raw data from SQL
	*****/
	public function populate($row) {
		if (!is_array($row)) {
			return;
		}

		if (array_key_exists('id', $row)) {
			$this->id = $row['id'];
		}
		if (array_key_exists('name', $row)) {
			$this->name = $row['name'];
		}
		if (array_key_exists('version', $row)) {
			$this->version = $row['version'];
		}
		if (array_key_exists('email', $row)) {
			$this->email = $row['email'];
		}
		if (array_key_exists('password', $row)) {
			$this->password = $row['password'];
		}
		if (array_key_exists('website', $row)) {
			$this->website = $row['website'];
		}
		if (array_key_exists('is_avatar_present', $row)) {
			$this->is_avatar_present = $row['is_avatar_present'];
		}
		if (array_key_exists('is_archive', $row)) {
			$this->is_archive = $row['is_archive'];
		}
		if (array_key_exists('rank', $row)) {
			$this->rank = $row['rank'];
		}
		if (array_key_exists('locale', $row)) {
			$this->locale = $row['locale'];
		}
		if (array_key_exists('timezone', $row)) {
			$this->timezone = $row['timezone'];
		}
		if (array_key_exists('visit_date', $row)) {
			$this->visit_date = $row['visit_date'];
		}
		if (array_key_exists('register_date', $row)) {
			$this->register_date = $row['register_date'];
		}
	}

	/*****
	** Simple return only functions
	*****/
	public function get_rank() {
		global $ranks;

		return '<span class="userrole" style="color: '.$ranks[$this->rank][2].';">'.$ranks[$this->rank][1].'</span>';
	}
	public function get_locale() {
		if( $this->locale_loaded) {
			return $this->locale_obj->display_name;
		}
		else {
			$this->locale_obj = new Locale;
			$this->locale_loaded = true;
			if( $this->locale_obj->checkName($this->locale) )
				return $this->locale_obj->display_name;
			else
				return false;
		}
	}

	/*****
	** Returns true if user permissions are higher than $rank
	*****/
	public function rankIsHigher($rank) {
		global $ranks;

		return $ranks[$this->rank][0] >= $ranks[$rank][0];
	}

	/*****
	** Checks if the user's name is available or not
	*****/
	public function availableName() {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT * FROM users WHERE lower(name)=$1";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array(strtolower($this->name)))
			or die ("Cannot execute statement\n");

		pg_close($con);

		if(pg_num_rows($result) < 1) {
			return 1;
		}
		else {
			if(pg_num_rows($result)==1) {
				$row = pg_fetch_assoc($result);
				$this->populate($row);
			}
			return 0;
		}
	}

	/*****
	** Checks if the user's mail address exists in the database
	*****/
	public function availableMail() {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT * FROM users WHERE lower(email)=$1";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array(strtolower($this->email)))
			or die ("Cannot execute statement\n");

		pg_close($con);

		if(pg_num_rows($result) < 1) {
			return 1;
		}
		else {
			if(pg_num_rows($result)==1) {
				$row = pg_fetch_assoc($result);
				$this->populate($row);
			}
			return 0;
		}
	}

	/*****
	** Creates a new user giving a sha1 password
	*****/
	public function create($password) {
		global $config;

		$regex = '/^(https?:\/\/)/';
		if (!preg_match($regex, $this->website) && $this->website!="")
			$this->website = "http://".$this->website;
		$this->visit_date = date('r');
		$this->register_date = date('r');
		$this->locale = "fr_FR";
		$this->timezone = "Europe/Paris";
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "INSERT INTO users (name, version, email, password, website, is_avatar_present, is_archive, rank, locale, timezone, visit_date, register_date) VALUES
			($1, '0', $2, $3, $4, FALSE, FALSE, 'registered', $5, $6, $7, $8)";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		pg_execute($con, "prepare1", array($this->name, $this->email, $password, $this->website, $this->locale, $this->timezone, $this->visit_date, $this->register_date))
			or die ("Cannot execute statement\n");

		pg_close($con);
	}

	/*****
	** Update the user profile
	*****/
	public function update() {
		global $config;
		global $user;

		$regex = '/^(https?:\/\/)/';
		if (!preg_match($regex, $this->website) && $this->website!="")
			$this->website = "http://".$this->website;
		$this->version++;

		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		if($this->password=='') {
			$query = "UPDATE users SET version = $1, name = $2, is_avatar_present = $3, locale = $4, rank = $5, email = $6, website = $7, timezone = $8 WHERE id = $9";
			pg_prepare($con, "prepare1", $query) 
				or die ("Cannot prepare statement\n");
			pg_execute($con, "prepare1", array($this->version, $this->name, $this->is_avatar_present, $this->locale, $this->rank, $this->email, $this->website, $this->timezone, $this->id))
				or die ("Cannot execute statement\n");
		}
		else {
			$query = "UPDATE users SET name = $1, is_avatar_present = $2, locale = $3, rank = $4, email = $5, website = $6, password = $7, timezone = $8, version = $9 WHERE id = $10";
			pg_prepare($con, "prepare1", $query) 
				or die ("Cannot prepare statement\n");
			pg_execute($con, "prepare1", array($this->name, $this->is_avatar_present, $this->locale, $this->rank, $this->email, $this->website, $this->password, $this->timezone, $this->version, $this->id))
				or die ("Cannot execute statement\n");
		}

		pg_close($con);

		error_log(
			date('r')." \t".$user->name." (".$user->id.") \tUPDATE \tEdit user ".$this->name." (".$this->id.")\r\n",
			3,
			$config['logs_folder'].'users.log');
	}

	/*****
	** Generates a random passwords, update the base and send the new password by mail.
	*****/
	public function sendPassword() {
		global $config;

		$newPass = randomPassword();
		$this->password = sha1($newPass);

		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "UPDATE users SET password = $1 WHERE email = $2";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		pg_execute($con, "prepare1", array($this->password, $this->email))
			or die ("Cannot execute statement\n");

		pg_close($con);

		$this->availableMail(); // Retreive user data from email

		$url = "http://".$_SERVER['SERVER_NAME'].$config['rel_root_folder'];

		$message = "Bonjour ".$this->name.",<br>\r\n";
		$message .= "<br>\r\n";
		$message .= "Voici votre nouveau mot de passe <a href='".$url."'>Kabano</a> : <b>".$newPass."</b><br>\r\n";
		$message .= "<br>\r\n";
		$message .= "Cordialement,<br>\r\n";
		$message .= "<br>\r\n";
		$message .= "L'équipe Kabano.<br>\r\n";
		$message .= "<small style='color:#777;'><i>Fait avec ♥ en Ariège.</i></small><br>\r\n";

		$headers = 'From: '. $config['bot_mail'] . "\r\n" .
		'Reply-To: '. $config['bot_mail'] . "\r\n" .
		'X-Mailer: PHP/' . phpversion() . "\r\n" .
		'MIME-Version: 1.0' . "\r\n" .
		'Content-type: text/html; charset=UTF-8' . "\r\n"; 

		mail($this->email, 'Kabano - Nouveau mot de passe', $message, $headers);
	}

	/*****
	** Update the last login date
	*****/
	public function updateLoginDate() {
		global $config;

		$this->visit_date = date('r');

		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "UPDATE users SET visit_date = $1 WHERE id = $2";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		pg_execute($con, "prepare1", array($this->visit_date, $this->id))
			or die ("Cannot execute statement\n");

		pg_close($con);
	}

	/*****
	** Sends an email to the user from an other user
	*****/
	public function sendMail($content, $from) {
		global $config;
		global $user;

		$url = "http://".$_SERVER['SERVER_NAME'].$config['rel_root_folder'];

		$message = "Bonjour ".$this->name.",<br>\r\n";
		$message .= "<br>\r\n";
		$message .= "Vous venez de recevoir un message de <b>".$from->name."</b> envoyé depuis <a href='".$url."'>Kabano</a>.<br>\r\n";
		$message .= "<br>\r\n";
		$message .= "<pre style='padding: 10px; background: #ccc;'>".strip_tags($content)."</pre><br>\r\n";
		$message .= "<br>\r\n";
		$message .= "Vous pouvez simplement répondre à cet email.<br>\r\n";
		$message .= "<br>\r\n";
		$message .= "L'équipe Kabano.<br>\r\n";
		$message .= "<small style='color:#777;'><i>Fait avec ♥ depuis Toulouse.</i></small><br>\r\n";

		$headers = 'From: '. $from->email . "\r\n" .
		'Reply-To: '. $from->email . "\r\n" .
		'X-Mailer: PHP/' . phpversion() . "\r\n" .
		'MIME-Version: 1.0' . "\r\n" .
		'Content-type: text/html; charset=UTF-8' . "\r\n"; 

		mail($this->email, 'Kabano - Nouveau message privé', $message, $headers);

		error_log(
			date('r')." \t".$user->name." (".$user->id.") \tMAIL \tMail sent to ".$this->name." (".$this->id.")\r\n",
			3,
			$config['logs_folder'].'users.log');
	}
}

function randomPassword() {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = random_int(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

/**********************************************************
***********************************************************
**  
**  This class is to manage Users list object
**  
***********************************************************
**********************************************************/

class Users
{
	public $objs = array();
	public $number = NULL;

	/*****
	** Get the users number and return the value
	*****/
	public function number() {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT id FROM users";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array())
			or die ("Cannot execute statement\n");

		pg_close($con);

		$this->number = pg_num_rows($result);
	}

	/*****
	** Get a list of users if according to the arguments
	*****/
	public function list_users($first, $count, $orderby = "id", $order = "ASC") {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$orders=array("id","name","lastlogin","registered","website","role");
		$key=array_search($orderby,$orders);
		$orderbysafe=$orders[$key];

		if ($order == 'ASC')
			$query = "SELECT * FROM users ORDER BY $orderbysafe ASC LIMIT $1 OFFSET $2";
		else
			$query = "SELECT * FROM users ORDER BY $orderbysafe DESC LIMIT $1 OFFSET $2";
		

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($count, $first))
			or die ("Cannot execute statement\n");

		pg_close($con);

		for($i = 0; $i < pg_num_rows($result); $i++) {
			$row = pg_fetch_assoc($result, $i);
			$this->objs[$i] = new User;
			$this->objs[$i]->populate($row);
		}
	}
}
