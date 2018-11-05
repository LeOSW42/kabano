<?

namespace Kabano;

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
	public $id = NULL;
	public $permalink = NULL;
	public $version = 0;
	public $locale = NULL;
	public $creation_date = NULL;
	public $update_date = NULL;
	public $author = NULL;
	public $is_public = NULL;
	public $is_archive = NULL;
	public $is_commentable = NULL;
	public $type = "wiki";
	public $name = NULL;
	public $content = NULL;

	/*****
	** Checks if a page at this ermalink exists and return the populated element
	*****/
	public function checkPermalink($permalink, $withArchive=0, $elementNb=0) {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT * FROM contents WHERE permalink=$1 AND type='wiki'";
		if($withArchive==0) {
			$query .= " AND is_archive=FALSE AND is_public=TRUE";
		}
		$query .= " ORDER BY update_date DESC LIMIT 1 OFFSET $2";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($permalink, $elementNb))
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
	public function populate($row) {
		$this->id = $row['id'];
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
	** Edit a page by archiving the current one and inserting a new one ID
	*****/
	public function update() {
		global $config;
		global $user;

		if($this->id == 0)
			die("Cannot update entry without giving ID");

		$oldId = $this->id;
		
		$this->version++;

		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "UPDATE contents SET is_archive = TRUE WHERE permalink = $1 AND type='wiki'";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($this->permalink))
			or die ("Cannot execute statement\n");

		$query = "INSERT INTO contents (permalink, version, locale, creation_date, update_date, author, is_public, is_archive, is_commentable, type, name, content) VALUES
			($1, $2, $3, $4, $5, $6, TRUE, FALSE, FALSE, 'wiki', $7, $8) RETURNING id";

		pg_prepare($con, "prepare2", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare2", array($this->permalink, $this->version, $this->locale, $this->creation_date, date('r'), $this->author, $this->name, $this->content))
			or die ("Cannot execute statement\n");

		$this->id = pg_fetch_assoc($result)['id'];

		$query = "INSERT INTO content_contributors (content, contributor) SELECT $1, contributor FROM content_contributors AS old WHERE old.content = $2";

		pg_prepare($con, "prepare3", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare3", array($this->id, $oldId))
			or die ("Cannot execute statement\n");

		$query = "INSERT INTO content_contributors (content, contributor) VALUES
			($1, $2) ON CONFLICT (content, contributor) DO NOTHING";

		pg_prepare($con, "prepare4", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare4", array($this->id, $user->id))
			or die ("Cannot execute statement\n");

		pg_close($con);

		error_log(
			date('r')." \t".$user->name." (".$user->id.") \tUPDATE \tEdit wiki page '".$this->permalink."'\r\n",
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

		$query = "UPDATE contents SET is_public=FALSE WHERE permalink=$1 AND type='wiki'";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($this->permalink))
			or die ("Cannot execute statement\n");

		pg_close($con);

		error_log(
			date('r')." \t".$user->name." (".$user->id.") \tDELETE \tUnpublish wiki page '".$this->permalink."'\r\n",
			3,
			$config['logs_folder'].'wiki.log');
	}

	/*****
	** Restore a page from unpublishing it
	*****/
	public function restore() {
		global $config;
		global $user;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "UPDATE contents SET is_public=TRUE WHERE permalink=$1 AND type='wiki'";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($this->permalink))
			or die ("Cannot execute statement\n");

		pg_close($con);

		error_log(
			date('r')." \t".$user->name." (".$user->id.") \tRESTORE \tPublish wiki page '".$this->permalink."'\r\n",
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

		$query = "INSERT INTO contents (permalink, version, locale, creation_date, update_date, author, is_public, is_archive, is_commentable, type, name, content) VALUES
			($1, '0', $2, $3, $4, $5, TRUE, FALSE, FALSE, 'wiki', $6, $7) RETURNING id";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($this->permalink, $this->locale, date('r'), date('r'), $user->id, $this->name, $this->content))
			or die ("Cannot execute statement\n");

		$this->id = pg_fetch_assoc($result)['id'];

		$query = "INSERT INTO content_contributors (content, contributor) VALUES
			($1, $2)";

		pg_prepare($con, "prepare2", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare2", array($this->id, $user->id))
			or die ("Cannot execute statement\n");

		pg_close($con);

		error_log(
			date('r')." \t".$user->name." (".$user->id.") \tINSERT \tCreate new wiki page '".$this->permalink."'\r\n",
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

/**********************************************************
***********************************************************
**  
**  This class is to manage a wiki page object
**  
***********************************************************
**********************************************************/

class WikiPages
{
	public $objs = array();
	public $number = NULL;

	/*****
	** Checks if a page at this URL exists and return the ID
	*****/
	public function getHistory($url) {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT * FROM contents WHERE permalink=$1 AND type='wiki' ORDER BY update_date DESC";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($url))
			or die ("Cannot execute statement\n");

		pg_close($con);

		$this->number = pg_num_rows($result);

		for($i = 0; $i < $this->number; $i++) {
			$row = pg_fetch_assoc($result, $i);
			$this->objs[$i] = new WikiPage;
			$this->objs[$i]->populate($row);
		}
	}
}

?>