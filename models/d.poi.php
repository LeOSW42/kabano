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
	public $author_name;

	/*****
	** Checks if a page at this URL exists and return the ID
	*****/
	public function checkPermalink($permalink, $withArchive=0, $elementNb=0) {
	    global $config;

		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

	    $query = "SELECT
            content_versions.id AS version_id,
            content_versions.version,
            content_versions.update_date,
            content_versions.is_archive,
            content_versions.name,
            content_versions.content AS parameters,

            contents.id AS content_id,
            contents.permalink,
            contents.creation_date,
            contents.is_public,
            contents.is_commentable,
            contents.type,
            contents.poi_type,

            content_locales.id AS locale_id,
            content_locales.locale,
            content_locales.author,

            specs.source_id,
            specs.remote_source_id,
            ST_X(specs.geom) AS lon,
            ST_Y(specs.geom) AS lat,
            ST_Z(specs.geom) AS ele,

            sources.display_name AS source

	        FROM contents
	        INNER JOIN content_locales
	            ON contents.id = content_locales.content_id
	        INNER JOIN content_versions
	            ON content_locales.id = content_versions.locale_id
	        LEFT JOIN content_version_poi_specifications specs
	            ON specs.content_version_id = content_versions.id
	        LEFT JOIN sources
	            ON sources.id = specs.source_id

	        WHERE contents.permalink = $1
	          AND contents.type = 'poi'";

	    if ($withArchive == 0) {
	        $query .= " AND content_versions.is_archive = FALSE AND contents.is_public = TRUE";
	    }

	    $query .= " ORDER BY content_versions.update_date DESC LIMIT 1 OFFSET $2";

	    pg_prepare($con, "poi_check_permalink", $query)
	        or die ("Cannot prepare statement\n");

	    $result = pg_execute($con, "poi_check_permalink", [$permalink, $elementNb])
	        or die ("Cannot execute statement\n");

	    pg_close($con);

	    if (pg_num_rows($result) == 1) {
	        $row = pg_fetch_assoc($result);
	        $this->populate($row);
	        return 1;
	    }

	    return 0;
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
		$result = pg_execute($con, "prepare4", array($this->version_id, $this->lon, $this->lat, $this->ele ,$this->source_id, $this->remote_source_id))
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
			$config['logs_folder'].'poi.log');
	}

	/*****
	** Edit a POI by archiving the current version and inserting a new one
	*****/
	public function update() {
		global $config;
		global $user;

		if ($this->content_id == 0 || $this->locale_id == 0 || $this->version_id == 0)
			die("Cannot update entry without giving ID");

		$this->version++;

		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		pg_query($con, "BEGIN");

		// 1) Archive old versions
		$query = "UPDATE content_versions SET is_archive = TRUE WHERE locale_id = $1";
		pg_prepare($con, "poi_update_archive", $query);
		pg_execute($con, "poi_update_archive", array($this->locale_id));

		// 2) Insert new version
		$query = "INSERT INTO content_versions (version, update_date, is_archive, name, content, locale_id) VALUES
			($1, $2, FALSE, $3, $4, $5) RETURNING id";

		pg_prepare($con, "poi_update_newversion", $query);

		$result = pg_execute($con, "poi_update_newversion", array($this->version, date('r'), $this->name, json_encode($this->parameters), $this->locale_id));

		$this->version_id = pg_fetch_assoc($result)['id'];

		// 3) Insert new geometry + source info for this new version
		$query = "INSERT INTO content_version_poi_specifications (content_version_id, geom, source_id, remote_source_id) VALUES
			($1, ST_SetSRID(ST_MakePoint($2, $3, $4), 4326), $5, $6)";

		pg_prepare($con, "poi_insert_specs_update", $query);
		pg_execute($con, "poi_insert_specs_update", array( $this->version_id, $this->lon, $this->lat, $this->ele, $this->source, $this->remote_source_id	));

		// 4) Update is_commentable
		$query = "UPDATE contents SET is_commentable = $1 WHERE id = $2";
		pg_prepare($con, "poi_update_commentable", $query);
		pg_execute($con, "poi_update_commentable", array( $this->is_commentable ? 't' : 'f', $this->content_id));

		// 5) Add contributor
		$query = "INSERT INTO content_contributors (content, contributor)
				  VALUES ($1, $2) ON CONFLICT (content, contributor) DO NOTHING";

		pg_prepare($con, "poi_update_contrib", $query);
		pg_execute($con, "poi_update_contrib", array($this->content_id, $user->id));

		pg_query($con, "COMMIT");
		pg_close($con);

		error_log(
			date('r')." \t".$user->name." (".$user->id.") \tUPDATE \tEdit POI '".$this->permalink."'\r\n",
			3,
			$config['logs_folder'].'poi.log'
		);
	}

	/*****
	** Archive a POI
	*****/
	public function delete() {
		global $config;
		global $user;

		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "UPDATE contents SET is_public = FALSE WHERE id = $1";

		pg_prepare($con, "poi_delete", $query);
		pg_execute($con, "poi_delete", array($this->content_id));

		pg_close($con);

		error_log(
			date('r')." \t".$user->name." (".$user->id.") \tDELETE \tArchive POI '".$this->permalink."'\r\n",
			3,
			$config['logs_folder'].'poi.log'
		);
	}

	/*****
	** Restore a POI
	*****/
	public function restore() {
		global $config;
		global $user;

		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "UPDATE contents SET is_public = TRUE WHERE id = $1";

		pg_prepare($con, "poi_restore", $query);
		pg_execute($con, "poi_restore", array($this->content_id));

		pg_close($con);

		error_log(
			date('r')." \t".$user->name." (".$user->id.") \tRESTORE \tPublish POI '".$this->permalink."'\r\n",
			3,
			$config['logs_folder'].'poi.log'
		);
	}
}

class Pois
{
	public $objs = [];
	public $number = 0;

	public function listPois($first, $count, $archive=0) {
		global $config;

		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT
            content_versions.id AS version_id,
            content_versions.version,
            content_versions.update_date,
            content_versions.is_archive,
            content_versions.name,
            content_versions.content AS parameters,

            contents.id AS content_id,
            contents.permalink,
            contents.creation_date,
            contents.is_public,
            contents.is_commentable,
            contents.type,
            contents.poi_type,

            content_locales.id AS locale_id,
            content_locales.locale,
            content_locales.author,

            specs.source_id,
            specs.remote_source_id,
            ST_X(specs.geom) AS lon,
            ST_Y(specs.geom) AS lat,
            ST_Z(specs.geom) AS ele,

            sources.display_name AS source

	        FROM contents
	        INNER JOIN content_locales
	            ON contents.id = content_locales.content_id
	        INNER JOIN content_versions
	            ON content_locales.id = content_versions.locale_id
	        LEFT JOIN content_version_poi_specifications specs
	            ON specs.content_version_id = content_versions.id
	        LEFT JOIN sources
	            ON sources.id = specs.source_id

	        WHERE contents.type = 'poi'
	          AND content_versions.is_archive = FALSE";

		if ($archive != 1)
			$query .= " AND contents.is_public=TRUE ";

		$query .= " ORDER BY content_versions.update_date DESC LIMIT $1 OFFSET $2";

		pg_prepare($con, "pois_list", $query);
		$result = pg_execute($con, "pois_list", array($count, $first));

		pg_close($con);

		for ($i = 0; $i < pg_num_rows($result); $i++) {
			$row = pg_fetch_assoc($result, $i);
			$this->objs[$i] = new Poi;
			$this->objs[$i]->populate($row);
		}
	}

	public function getHistory($permalink) {
		global $config;

		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT
            content_versions.id AS version_id,
            content_versions.version,
            content_versions.update_date,
            content_versions.is_archive,
            content_versions.name,
            content_versions.content AS parameters,

            contents.id AS content_id,
            contents.permalink,
            contents.creation_date,
            contents.is_public,
            contents.is_commentable,
            contents.type,
            contents.poi_type,

            content_locales.id AS locale_id,
            content_locales.locale,
            content_locales.author,

            specs.source_id,
            specs.remote_source_id,
            ST_X(specs.geom) AS lon,
            ST_Y(specs.geom) AS lat,
            ST_Z(specs.geom) AS ele,

            sources.display_name AS source

	        FROM contents
	        INNER JOIN content_locales
	            ON contents.id = content_locales.content_id
	        INNER JOIN content_versions
	            ON content_locales.id = content_versions.locale_id
	        LEFT JOIN content_version_poi_specifications specs
	            ON specs.content_version_id = content_versions.id
	        LEFT JOIN sources
	            ON sources.id = specs.source_id

	        WHERE contents.permalink = $1
	          AND contents.type = 'poi'

			ORDER BY content_versions.update_date DESC
		";

		pg_prepare($con, "poi_history", $query);
		$result = pg_execute($con, "poi_history", array($permalink));

		pg_close($con);

		$this->number = pg_num_rows($result);

		for ($i = 0; $i < $this->number; $i++) {
			$row = pg_fetch_assoc($result, $i);
			$this->objs[$i] = new Poi;
			$this->objs[$i]->populate($row);
		}
	}
}

?>