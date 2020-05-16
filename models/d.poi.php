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

// This array is related to the defined SQL enum, do not touch.
$poi_types = array(
	"basic_hut"			=> array("Abri sommaire", "Abri", "#ef2929", "basic_hut"),
	"wilderness_hut" 	=> array("Cabane non gardée", "Cabane", "#ef2929", "wilderness_hut"),
	"alpine_hut" 		=> array("Refuge gardé", "Refuge", "#ef2929", "alpine_hut"),
	"halt"				=> array("Gîte d'étape", "Gîte", "#4e9a06", "halt"),
	"bivouac"			=> array("Zone de bivouac", "Bivouac", "#ef2929", "bivouac"),
	"campsite"			=> array("Camping", "Camping", "#4e9a06", "campsite")
);

class Poi
{
	public $poi_id = NULL;
	public $locale_id = NULL;
	public $source_id = NULL;
	public $version_id = NULL;
	public $is_public = NULL;
	public $permalink = NULL;
	public $creation_date = NULL;
	public $name = NULL;
	public $position = NULL;
	public $type = NULL;
	public $locale = NULL;
	public $source = NULL;
	public $remote_source_id = NULL;
	public $author = NULL;
	public $version = NULL;
	public $update_date = NULL;
	public $is_archive = NULL;
	public $alt_type = NULL;
	public $is_destroyed = NULL;
	public $alt_name = NULL;
	public $alt_position = NULL;
	public $parameters = NULL;

	/*****
	** Checks if a page at this URL exists and return the ID
	*****/
	public function checkPermalink($permalink, $withArchive=0, $elementNb=0) {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT poi_versions.id AS version_id, * FROM pois INNER JOIN poi_locales ON pois.id = poi_locales.poi_id INNER JOIN poi_sources ON poi_locales.id = poi_sources.locale_id INNER JOIN poi_versions ON poi_sources.id = poi_versions.source_id WHERE permalink=$1";
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
		$this->$poi_id = $row['poi_id'];
		$this->$locale_id = $row['locale_id'];
		$this->$source_id = $row['source_id'];
		$this->$version_id = $row['version_id'];
		$this->$is_public = $row['is_public'];
		$this->$permalink = $row['permalink'];
		$this->$creation_date = $row['creation_date'];
		$this->$name = $row['name'];
		$this->$position = $row['position'];
		$this->$type = $row['type'];
		$this->$locale = $row['locale'];
		$this->$source = $row['source'];
		$this->$remote_source_id = $row['remote_source_id'];
		$this->$author = $row['author'];
		$this->$version = $row['version'];
		$this->$update_date = $row['update_date'];
		$this->$is_archive = $row['is_archive'];
		$this->$alt_type = $row['alt_type'];
		$this->$is_destroyed = $row['is_destroyed'];
		$this->$alt_name = $row['alt_name'];
		$this->$alt_position = $row['alt_position'];
		$this->$parameters = $row['parameters'];
	}

	/*****
	** Create a new poi, all field required except alt_*, *_id
	*****/
	public function insert() {
		global $config;
		global $user;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		// Because it is the first insert.
		$this->alt_type = $this->type;
		$this->alt_name = $this->name;
		$this->alt_position = $this->position;

		$query = "INSERT INTO pois (is_public, permalink, creation_date, name, position, type) VALUES
			(TRUE, $1, $2, $3, $4, $5) RETURNING id";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($this->permalink, date('r'), $this->name, $this->position, $this->type))
			or die ("Cannot execute statement\n");

		$this->poi_id = pg_fetch_assoc($result)['id'];

		$query = "INSERT INTO poi_locales (locale, poi_id) VALUES
			($1, $2) RETURNING id";

		pg_prepare($con, "prepare2", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare2", array($this->locale, $this->poi_id))
			or die ("Cannot execute statement\n");

		$this->locale_id = pg_fetch_assoc($result)['id'];

		$query = "INSERT INTO poi_sources (source, remote_source_id, author, locale_id) VALUES
			($1, $2, $3, $4) RETURNING id";

		pg_prepare($con, "prepare3", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare3", array($this->source, $this->remote_source_id, $this->author, $this->locale_id))
			or die ("Cannot execute statement\n");

		$this->source_id = pg_fetch_assoc($result)['id'];

		$query = "INSERT INTO poi_versions (version, update_date, is_archive, alt_type, is_destroyed, alt_name, alt_position, parameters, source_id) VALUES
			('0', $1, FALSE, $2, $3, $4, $5, $6, $7) RETURNING id";

		pg_prepare($con, "prepare4", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare4", array(date('r'), $this->alt_type, $this->is_destroyed, $this->alt_name, $this->alt_position, $this->parameters, $this->source_id))
			or die ("Cannot execute statement\n");

		$this->version_id = pg_fetch_assoc($result)['id'];

		$query = "INSERT INTO poi_contributors (poi, contributor) VALUES
			($1, $2)";

		pg_prepare($con, "prepare5", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare5", array($this->source_id, $user->id))
			or die ("Cannot execute statement\n");

		pg_close($con);

		error_log(
			date('r')." \t".$user->name." (".$user->id.") \tINSERT \tCreate new poi '".$this->permalink."'\r\n",
			3,
			$config['logs_folder'].'wiki.log');
	}

}

?>