<?

/**********************************************************
***********************************************************
**  
**  This class is to manage a wiki page object
**  
***********************************************************
**********************************************************/

require_once($config['third_folder']."Md/MarkdownExtra.inc.php");

class WikiPage
{
	public $id = 0;
	public $title = NULL;
	public $url = NULL;
	public $locale = NULL;
	public $lastedit = NULL;
	public $archive = NULL;
	public $content = NULL;

	/*****
	** Checks if a page at this URL exists and return the ID
	*****/
	public function checkUrl($url, $withArchive=0, $elementNb=0) {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT id FROM wiki WHERE url=$1";
		if($withArchive==0) {
			$query .= " AND archive=FALSE";
		}
		$query .= " ORDER BY lastedit DESC LIMIT 1 OFFSET $2";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($url, $elementNb))
			or die ("Cannot execute statement\n");

		pg_close($con);

		if(pg_num_rows($result) == 1) {
			$wiki = pg_fetch_assoc($result);
			$this->id = $wiki['id'];
			$this->url = $url;
			return 1;
		}
		else {
			$this->url = $url;
			return 0;
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

			$query = "SELECT * FROM wiki WHERE id=$1";

			pg_prepare($con, "prepare1", $query) 
				or die ("Cannot prepare statement\n");
			$result = pg_execute($con, "prepare1", array($this->id))
				or die ("Cannot execute statement\n");

			pg_close($con);

			$wiki = pg_fetch_assoc($result);

			$this->title = $wiki['title'];
			$this->url = $wiki['url'];
			$this->locale = $wiki['locale'];
			$this->lastedit = $wiki['lastedit'];
			$this->archive = $wiki['archive'];
			$this->content = $wiki['content'];
		}
		else {
			die("Cannot populate a wiki page without ID");
		}
	}

	/*****
	** Edit a page by archiving the current one and inserting a new one ID
	*****/
	public function update() {
		global $config;
		global $user;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "UPDATE wiki SET archive = TRUE WHERE url = $1";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($this->url))
			or die ("Cannot execute statement\n");


		$query = "INSERT INTO wiki (url, title, content, lastedit, archive, locale) VALUES
			($1, $2, $3, $4, FALSE, $5)";

		pg_prepare($con, "prepare2", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare2", array($this->url, $this->title, $this->content, date('r'), $this->locale))
			or die ("Cannot execute statement\n");

		pg_close($con);

		error_log(
			date('r')." \t".$user->name." (".$user->id.") \tUPDATE \tEdit wiki page '".$this->url."'\r\n",
			3,
			$config['logs_folder'].'wiki.log');
	}

	/*****
	** Delete a page by archiving it
	*****/
	public function delete() {
		global $config;
		global $user;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "UPDATE wiki SET archive = TRUE WHERE url = $1";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($this->url))
			or die ("Cannot execute statement\n");

		pg_close($con);

		error_log(
			date('r')." \t".$user->name." (".$user->id.") \tDELETE \tArchive wiki page '".$this->url."'\r\n",
			3,
			$config['logs_folder'].'wiki.log');
	}

	/*****
	** Create a page by archiving the current one and inserting a new one ID
	*****/
	public function insert() {
		global $config;
		global $user;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "INSERT INTO wiki (url, title, content, lastedit, archive, locale) VALUES
			($1, $2, $3, $4, FALSE, $5)";

		pg_prepare($con, "prepare2", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare2", array($this->url, $this->title, $this->content, date('r'), $this->locale))
			or die ("Cannot execute statement\n");

		pg_close($con);

		error_log(
			date('r')." \t".$user->name." (".$user->id.") \tINSERT \tCreate new wiki page '".$this->url."'\r\n",
			3,
			$config['logs_folder'].'wiki.log');
	}

	/*****
	** Converts the Markdown content to HTML
	*****/
	public function md2html() {
		$this->content_html = \Michelf\MarkdownExtra::defaultTransform($this->content);
	}
}

class WikiPages
{
	public $ids = array();
	public $number = NULL;

	/*****
	** Checks if a page at this URL exists and return the ID
	*****/
	public function getHistory($url) {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT id FROM wiki WHERE url=$1 ORDER BY lastedit DESC";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($url))
			or die ("Cannot execute statement\n");

		pg_close($con);

		$this->number = pg_num_rows($result);

		for($i = 0; $i < $this->number; $i++) {
			$row = pg_fetch_assoc($result, $i);
			$this->ids[$i] = $row['id'];
		}
	}
}

?>