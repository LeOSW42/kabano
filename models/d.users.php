<?

/**********************************************************
***********************************************************
**  
**  This class is to manage User object
**  
***********************************************************
**********************************************************/

class User
{
	public $id = 0;
	public $name = NULL;
	public $avatar = NULL;
	public $locale = NULL;
	public $role = NULL;
	public $lastlogin = NULL;
	public $mail = NULL;
	public $website = NULL;
	public $password = NULL;
	public $registered = NULL;

	/*****
	** Connect to correct account using ID and stores its ID
	*****/
	public function checkID($id) {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT id FROM users WHERE id=$1";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($id))
			or die ("Cannot execute statement\n");

		pg_close($con);

		if(pg_num_rows($result) == 1) {
			$this->id = $id;
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

		$query = "SELECT id FROM users WHERE name=$1 AND password=$2";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($login, sha1($pass)))
			or die ("Cannot execute statement\n");

		pg_close($con); 

		if(pg_num_rows($result) == 1) {
			$user = pg_fetch_assoc($result);
			$this->id = $user['id'];
		}
	}
	/*****
	** Populate the object using its ID
	*****/
	public function populate() {
		global $config;
		
		if($this->id != 0) {
			$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
				or die ("Could not connect to server\n");

			$query = "SELECT * FROM users WHERE id=$1";

			pg_prepare($con, "prepare1", $query) 
				or die ("Cannot prepare statement\n");
			$result = pg_execute($con, "prepare1", array($this->id))
				or die ("Cannot execute statement\n");

			pg_close($con);

			$user = pg_fetch_assoc($result);

			$this->name = $user['name'];
			$this->avatar = $user['avatar'];
			$this->locale = $user['locale'];
			$this->role = $user['role'];
			$this->lastlogin = $user['lastlogin'];
			$this->mail = $user['mail'];
			$this->website = $user['website'];
			$this->registered = $user['registered'];
		}
		else {
			die("Cannot populate an User without ID");
		}
	}
	/*****
	** Checks if the user's name is available or not
	*****/
	public function availableName() {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT id FROM users WHERE lower(name)=$1";

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
				$user = pg_fetch_assoc($result);
				$this->id = $user['id'];
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

		$query = "SELECT id FROM users WHERE lower(mail)=$1";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array(strtolower($this->mail)))
			or die ("Cannot execute statement\n");

		pg_close($con);

		if(pg_num_rows($result) < 1) {
			return 1;
		}
		else {
			if(pg_num_rows($result)==1) {
				$user = pg_fetch_assoc($result);
				$this->id = $user['id'];
			}
			return 0;
		}
	}
	/*****
	** Creates a new user.
	*****/
	public function create() {
		global $config;

		$regex = '/^(https?:\/\/)/';
		if (!preg_match($regex, $this->website) && $this->website!="")
			$this->website = "http://".$this->website;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "INSERT INTO users (name, password, avatar, locale, role, lastlogin, mail, website, registered) VALUES
			($1, $2, $3, $4, $5, $6, $7, $8, $9)";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		pg_execute($con, "prepare1", array($this->name, $this->password, $this->avatar, $this->locale, $this->role, $this->lastlogin, $this->mail, $this->website, date('r')))
			or die ("Cannot execute statement\n");

		pg_close($con);
	
		$this->updateLoginDate();
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

		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		if($this->password=='') {
			$query = "UPDATE users SET name = $1, avatar = $2, locale = $3, role = $4, mail = $5, website = $6 WHERE id = $7";
			pg_prepare($con, "prepare1", $query) 
				or die ("Cannot prepare statement\n");
			pg_execute($con, "prepare1", array($this->name, $this->avatar, $this->locale, $this->role, $this->mail, $this->website, $this->id))
				or die ("Cannot execute statement\n");
		}
		else {
			$query = "UPDATE users SET name = $1, avatar = $2, locale = $3, role = $4, mail = $5, website = $6, password = $7 WHERE id = $8";
			pg_prepare($con, "prepare1", $query) 
				or die ("Cannot prepare statement\n");
			pg_execute($con, "prepare1", array($this->name, $this->avatar, $this->locale, $this->role, $this->mail, $this->website, $this->password, $this->id))
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

		$query = "UPDATE users SET password = $1 WHERE mail = $2";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		pg_execute($con, "prepare1", array($this->password, $this->mail))
			or die ("Cannot execute statement\n");

		pg_close($con);

		$this->availableMail();
		$this->populate();

		$url = "http://".$_SERVER['SERVER_NAME'].$config['rel_root_folder'];

		$message = "Bonjour ".$this->name.",<br>\r\n";
		$message .= "<br>\r\n";
		$message .= "Voici votre nouveau mot de passe <a href='".$url."'>Kabano</a> : <b>".$newPass."</b><br>\r\n";
		$message .= "<br>\r\n";
		$message .= "Cordialement,<br>\r\n";
		$message .= "<br>\r\n";
		$message .= "L'équipe Kabano.<br>\r\n";
		$message .= "<small style='color:#777;'><i>Fait avec ♥ depuis Toulouse.</i></small><br>\r\n";

		$headers = 'From: '. $config['bot_mail'] . "\r\n" .
		'Reply-To: '. $config['bot_mail'] . "\r\n" .
		'X-Mailer: PHP/' . phpversion() . "\r\n" .
		'MIME-Version: 1.0' . "\r\n" .
		'Content-type: text/html; charset=UTF-8' . "\r\n"; 

		mail($this->mail, 'Kabano - Nouveau mot de passe', $message, $headers);
	}
	/*****
	** Update the last login date
	*****/
	public function updateLoginDate() {
		global $config;

		$this->lastlogin = date('r');

		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "UPDATE users SET lastlogin = $1 WHERE id = $2";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		pg_execute($con, "prepare1", array($this->lastlogin, $this->id))
			or die ("Cannot execute statement\n");

		pg_close($con);
	}
	/*****
	** Outputs the role of the user
	*****/
	public function role() {
		global $config;
		return '<span class="userrole" style="color: '.$config['roles'][$this->role][2].';">'.$config['roles'][$this->role][1].'</span>';
	}
	/*****
	** Sends an email to the user from an other user
	*****/
	public function sendMail($content, $from) {
		global $config;
		global $user;

		$this->populate();
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

		$headers = 'From: '. $from->mail . "\r\n" .
		'Reply-To: '. $from->mail . "\r\n" .
		'X-Mailer: PHP/' . phpversion() . "\r\n" .
		'MIME-Version: 1.0' . "\r\n" .
		'Content-type: text/html; charset=UTF-8' . "\r\n"; 

		mail($this->mail, 'Kabano - Nouveau message privé', $message, $headers);

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
        $n = rand(0, $alphaLength);
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
	public $ids = array();
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
			$query = "SELECT id FROM users ORDER BY $orderbysafe ASC LIMIT $1 OFFSET $2";
		else
			$query = "SELECT id FROM users ORDER BY $orderbysafe DESC LIMIT $1 OFFSET $2";
		

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($count, $first))
			or die ("Cannot execute statement\n");

		pg_close($con);

		for($i = 0; $i < pg_num_rows($result); $i++) {
			$row = pg_fetch_assoc($result, $i);
			$this->ids[$i] = $row['id'];
		}
	}
}

?>