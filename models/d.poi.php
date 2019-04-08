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

}

?>