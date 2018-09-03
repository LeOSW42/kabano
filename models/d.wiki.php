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
	private $id = 0;
	private $permalink = 0;
	private $version = 0;
	private $locale = NULL;
	private $creation_date = NULL;
	private $update_date = NULL;
	private $author = NULL;
	private $is_public = NULL;
	private $is_archive = NULL;
	private $is_commentable = NULL;
	private $type = "wiki";
	public $name = NULL;
	public $content = NULL;

	/*****
	** Checks if a page at this URL exists and return the populated element
	*****/
	public function checkUrl($url, $withArchive=0, $elementNb=0) {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT * FROM contents WHERE permalink=$1";
		if($withArchive==0) {
			$query .= " AND is_archive=FALSE AND is_public=TRUE";
		}
		$query .= " ORDER BY update_date DESC LIMIT 1 OFFSET $2";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($url, $elementNb))
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
	** Populate the object using raw data from SQL
	*****/
	private function populate($row) {
		$this->permalink = $row['permalink'];
		$this->version = $row['version'];
		$this->locale = $row['locale'];
		$this->creation_date = $row['creation_date'];
		$this->update_date = $row['update_date'];
		$this->author = $row['author'];
		$this->is_public = $row['is_public'];
		$this->is_archive = $row['is_archive'];
		$this->is_commentable = $row['is_commentable'];
		$this->type = $row['type'];
		$this->name = $row['name'];
		$this->content = $row['content'];
	}

	/*****
	** Return archive status
	*****/
	public function is_archive() {
		return $this->is_archive;
	}

	/*****
	** Return archive status
	*****/
	public function update_date() {
		return $this->update_date;
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