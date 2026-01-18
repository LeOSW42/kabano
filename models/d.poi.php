<?

namespace Kabano;

/**********************************************************
***********************************************************
**  
**  This class is to manage a poi object
**  
***********************************************************
**********************************************************/

require_once($config['third_folder']."Md/MarkdownExtra.inc.php");
require_once($config['includes_folder']."poi_types.struct.php");

class Poi
{
	public $content_id = NULL;
	public $locale_id = NULL;
	public $source_id = NULL;
	public $version_id = NULL;
	public $permalink = NULL;
	public $version = 0;
	public $locale = 0;
	public $creation_date = NULL;
	public $update_date = NULL;
	public $author = NULL;
	public $is_public = NULL;
	public $is_archive = NULL; // Means destroyed for a POI
	public $is_commentable = NULL;
	public $type = "poi";
	public $poi_type = NULL;
	public $name = NULL;
	public $source = NULL;
	public $remote_source_id = NULL;
	public $parameters = NULL;

	public $lat;
	public $lon;
	public $ele;

	/*****
	** Checks if a page at this URL exists and return the ID
	*****/
	public function checkPermalink($permalink, $withArchive=0, $elementNb=0) {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT content_versions.id AS version_id, * FROM contents INNER JOIN content_locales ON contents.id = content_locales.content_id INNER JOIN content_versions ON content_locales.id = content_versions.locale_id WHERE permalink=$1 AND type='poi'";
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
	** Populate the object using its ID
	*****/
	public function populate($row) {
		$this->content_id = $row['content_id'];
		$this->locale_id = $row['locale_id'];
		$this->source_id = $row['source_id'];
		$this->version_id = $row['version_id'];
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
		$this->poi_type = $row['poi_type'];
		$this->name = $row['name'];
		$this->parameters = json_decode($row['parameters'], true);
		$this->lon = $row['lon'];
		$this->lat = $row['lat'];
		$this->ele = $row['ele'];
		$this->source = $row['source'];
		$this->remote_source_id = $row['remote_source_id'];
	}

	/*****
	** Create a new poi
	*****/
	public function insert() {
		global $config;
		global $user;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		pg_query($con, "BEGIN");

		$query = "INSERT INTO contents (is_commentable, is_public, permalink, creation_date, type, poi_type) VALUES
			($1, TRUE, $2, $3, $4, $5) RETURNING id";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($this->is_commentable, $this->permalink, date('r'), $this->type, $this->poi_type))
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

		$result = pg_execute($con, "prepare3", array(date('r'), $this->name, json_encode($this->parameters), $this->locale_id))
			or die ("Cannot execute statement\n");

		$this->version_id = pg_fetch_assoc($result)['id'];

		$query = "INSERT INTO content_version_poi_specifications (content_version_id, geom, source_id, remote_source_id) VALUES
			($1, ST_SetSRID(ST_MakePoint($2, $3, $4), 4326), $5, $6)";

		pg_prepare($con, "prepare4", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare4", array($this->version_id, $this->lon, $this->lat, $this->ele ,$this->source, $this->remote_source_id))
			or die ("Cannot execute statement\n");

		$query = "INSERT INTO content_contributors (content, contributor) VALUES
			($1, $2)";

		pg_prepare($con, "prepare5", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare5", array($this->locale_id, $user->id))
			or die ("Cannot execute statement\n");

		pg_query($con, "COMMIT");

		pg_close($con);

		error_log(
			date('r')." \t".$user->name." (".$user->id.") \tINSERT \tCreate new poi '".$this->permalink."'\r\n",
			3,
			$config['logs_folder'].'wiki.log');
	}

}

?>