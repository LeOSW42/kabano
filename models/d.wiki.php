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
	public $content_id = NULL;
	public $locale_id = NULL;
	public $version_id = NULL;
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

	public $content_html;

	private function decodeJsonText($value) {
		if ($value === null || $value === '') {
			return '';
		}

		$decoded = json_decode($value, true);
		if (!is_array($decoded)) {
			return '';
		}

		return isset($decoded['text']) ? $decoded['text'] : '';
	}

	/*****
	** Checks if a page at this permalink exists and return the populated element
	*****/
	public function checkPermalink($permalink, $withArchive=0, $elementNb=0) {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT content_versions.id AS version_id, * FROM contents INNER JOIN content_locales ON contents.id = content_locales.content_id INNER JOIN content_versions ON content_locales.id = content_versions.locale_id WHERE permalink=$1 AND type='wiki'";
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
		if (!is_array($row)) {
			return;
		}

		$decodedContent = null;
		if (array_key_exists('content', $row)) {
			$decodedContent = $this->decodeJsonText($row['content']);
		}

		if (array_key_exists('content_id', $row)) {
			$this->content_id = $row['content_id'];
		}
		if (array_key_exists('locale_id', $row)) {
			$this->locale_id = $row['locale_id'];
		}
		if (array_key_exists('version_id', $row)) {
			$this->version_id = $row['version_id'];
		}
		if (array_key_exists('permalink', $row)) {
			$this->permalink = $row['permalink'];
		}
		if (array_key_exists('version', $row)) {
			$this->version = $row['version'];
		}
		if (array_key_exists('locale', $row)) {
			$this->locale = $row['locale'];
		}
		if (array_key_exists('creation_date', $row)) {
			$this->creation_date = $row['creation_date'];
		}
		if (array_key_exists('update_date', $row)) {
			$this->update_date = $row['update_date'];
		}
		if (array_key_exists('author', $row)) {
			$this->author = $row['author'];
		}
		if (array_key_exists('is_public', $row)) {
			$this->is_public = $row['is_public'];
		}
		if (array_key_exists('is_archive', $row)) {
			$this->is_archive = $row['is_archive'];
		}
		if (array_key_exists('is_commentable', $row)) {
			$this->is_commentable = $row['is_commentable'];
		}
		if (array_key_exists('type', $row)) {
			$this->type = $row['type'];
		}
		if (array_key_exists('name', $row)) {
			$this->name = $row['name'];
		}
		if ($decodedContent !== null) {
			$this->content = $decodedContent;
		}
	}

	/*****
	** Edit a page by archiving the current one and inserting a new one ID
	*****/
	public function update() {
		global $config;
		global $user;

		if($this->content_id == 0 || $this->locale_id == 0 || $this->version_id == 0)
			die("Cannot update entry without giving ID");

		$this->version++;

		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		pg_query($con, "BEGIN");

		$query = "UPDATE content_versions SET is_archive = TRUE WHERE locale_id = $1";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($this->locale_id))
			or die ("Cannot execute statement\n");

		$query = "INSERT INTO content_versions (version, update_date, is_archive, name, content, locale_id) VALUES
			($1, $2, FALSE, $3, $4, $5) RETURNING id";

		pg_prepare($con, "prepare2", $query) 
			or die ("Cannot prepare statement\n");
			
		$jsonContent = json_encode(['text' => $this->content]);
		$result = pg_execute($con, "prepare2", array($this->version, date('r'), $this->name, $jsonContent, $this->locale_id))
			or die ("Cannot execute statement\n");

		$this->version_id = pg_fetch_assoc($result)['id'];

		$query = "INSERT INTO content_contributors (content, contributor) VALUES
			($1, $2) ON CONFLICT (content, contributor) DO NOTHING";

		pg_prepare($con, "prepare3", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare3", array($this->locale_id, $user->id))
			or die ("Cannot execute statement\n");

		pg_query($con, "COMMIT");

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
	** Create a new page, fails if permalink already exists
	*****/
	public function insert() {
		global $config;
		global $user;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		pg_query($con, "BEGIN");

		$query = "INSERT INTO contents (permalink, creation_date, is_public, is_commentable, type) VALUES
			($1, $2, TRUE, FALSE, 'wiki') RETURNING id";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($this->permalink, date('r')))
			or die ("Cannot execute statement\n");

		$this->content_id = pg_fetch_assoc($result)['id'];

		$query = "INSERT INTO content_locales (content_id, locale, author) VALUES
			($1, $2, $3) RETURNING id";

		pg_prepare($con, "prepare2", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare2", array($this->content_id, $this->locale, $user->id))
			or die ("Cannot execute statement\n");

		$this->locale_id = pg_fetch_assoc($result)['id'];

		$query = "INSERT INTO content_versions (version, update_date, is_archive, name, content, locale_id) VALUES
			('0', $1, FALSE, $2, $3, $4) RETURNING id";

		pg_prepare($con, "prepare3", $query) 
			or die ("Cannot prepare statement\n");

		$jsonContent = json_encode(['text' => $this->content]);
		$result = pg_execute($con, "prepare3", array(date('r'), $this->name, $jsonContent, $this->locale_id))
			or die ("Cannot execute statement\n");

		$this->version_id = pg_fetch_assoc($result)['id'];

		$query = "INSERT INTO content_contributors (content, contributor) VALUES
			($1, $2)";

		pg_prepare($con, "prepare4", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare4", array($this->locale_id, $user->id))
			or die ("Cannot execute statement\n");

		pg_query($con, "COMMIT");

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

		$query = "SELECT content_versions.id AS version_id, * FROM contents INNER JOIN content_locales ON contents.id = content_locales.content_id INNER JOIN content_versions ON content_locales.id = content_versions.locale_id WHERE permalink=$1 AND type='wiki' ORDER BY update_date DESC";

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
