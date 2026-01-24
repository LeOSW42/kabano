<?php

namespace Kabano;

/**********************************************************
***********************************************************
**  
**  This class is to manage a blog article object
**  
***********************************************************
**********************************************************/

require_once($config['third_folder']."Md/MarkdownExtra.inc.php");

class BlogArticle
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
	public $type = "blog";
	public $name = NULL;
	public $content = NULL;
    public $content_html = NULL;
    public $content_txt = NULL;
    public $author_name = NULL;

	/*****
	** Checks if a page at this URL exists and return the ID
	*****/
	public function checkPermalink($permalink, $withArchive=0, $elementNb=0) {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT content_versions.id AS version_id, * FROM contents INNER JOIN content_locales ON contents.id = content_locales.content_id INNER JOIN content_versions ON content_locales.id = content_versions.locale_id WHERE permalink=$1 AND type='blog'";
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
		$json = json_decode($row['content'], true);

		$this->content_id = $row['content_id'];
		$this->locale_id = $row['locale_id'];
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
		$this->name = $row['name'];
		$this->content = isset($json['text']) ? $json['text'] : '';
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

		$query = "UPDATE contents SET is_commentable = $1 WHERE id = $2";
		pg_prepare($con, "prepare4", $query)
			or die ("Cannot prepare statement\n");
		pg_execute($con, "prepare4", array($this->is_commentable ? 't' : 'f', $this->content_id))
			or die ("Cannot prepare statement\n");

		pg_query($con, "COMMIT");

		pg_close($con);

		error_log(
			date('r')." \t".$user->name." (".$user->id.") \tUPDATE \tEdit blog article '".$this->permalink."'\r\n",
			3,
			$config['logs_folder'].'blog.articles.log');
	}

	/*****
	** Delete an article by archiving it
	*****/
	public function delete() {
		global $config;
		global $user;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "UPDATE contents SET is_public=FALSE WHERE permalink=$1 AND type='blog'";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($this->permalink))
			or die ("Cannot execute statement\n");

		pg_close($con);

		error_log(
			date('r')." \t".$user->name." (".$user->id.") \tDELETE \tArchive blog article '".$this->permalink."'\r\n",
			3,
			$config['logs_folder'].'blog.articles.log');
	}

	/*****
	** Restore a page from unpublishing it
	*****/
	public function restore() {
		global $config;
		global $user;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "UPDATE contents SET is_public=TRUE WHERE permalink=$1 AND type='blog'";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($this->permalink))
			or die ("Cannot execute statement\n");

		pg_close($con);

		error_log(
			date('r')." \t".$user->name." (".$user->id.") \tRESTORE \tPublish blog article '".$this->permalink."'\r\n",
			3,
			$config['logs_folder'].'blog.articles.log');
	}

	/*****
	** Create an article
	*****/
	public function insert() {
		global $config;
		global $user;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		pg_query($con, "BEGIN");

		$query = "INSERT INTO contents (permalink, creation_date, is_public, is_commentable, type) VALUES
			($1, $2, TRUE, $3, 'blog') RETURNING id";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($this->permalink, date('r'), $this->is_commentable))
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
			date('r')." \t".$user->name." (".$user->id.") \tINSERT \tCreate new blog article '".$this->permalink."'\r\n",
			3,
			$config['logs_folder'].'blog.articles.log');
	}

	/*****
	** Converts the Markdown content to HTML
	*****/
	public function md2html() {
		$this->content_html = \Michelf\MarkdownExtra::defaultTransform($this->content);
	}

	/*****
	** Converts the Markdown content to text
	*****/
	public function md2txt() {
		$this->md2html();
		$this->content_txt = strip_tags($this->content_html);
	}
}


/**********************************************************
***********************************************************
**  
**  This class is to manage a list of blog articles
**  
***********************************************************
**********************************************************/

class BlogArticles
{
	public $objs = array();
	public $number = NULL;

	/*****
	** Return the list of different articles
	*****/
	public function listArticles($first, $count, $archive=0) {
		global $config;

		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT content_versions.id AS version_id, * FROM contents INNER JOIN content_locales ON contents.id = content_locales.content_id INNER JOIN content_versions ON content_locales.id = content_versions.locale_id WHERE is_archive=FALSE ";
		if ($archive != 1)
			$query .= "AND is_public=TRUE ";
		$query .= "AND type='blog' ORDER BY update_date DESC";
		$query .= " LIMIT $1 OFFSET $2";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($count, $first))
				or die ("Cannot execute statement\n");
		
		pg_close($con);

		for($i = 0; $i < pg_num_rows($result); $i++) {
			$row = pg_fetch_assoc($result, $i);
			$this->objs[$i] = new BlogArticle;
			$this->objs[$i]->populate($row);
		}
	}

	/*****
	** Return the number of articles
	*****/
	public function number($archive=0) {
		global $config;

		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT content_versions.id AS version_id, * FROM contents INNER JOIN content_locales ON contents.id = content_locales.content_id INNER JOIN content_versions ON content_locales.id = content_versions.locale_id WHERE is_archive=FALSE ";
		if ($archive == 1)
			$query .= "AND is_public=TRUE ";
		$query .= "AND type='blog' ORDER BY update_date DESC";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array())
				or die ("Cannot execute statement\n");
		
		pg_close($con);

		$this->number = pg_num_rows($result);
	}

	/*****
	** Return the list of archived version of a blog article
	*****/
	public function getHistory($url) {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT content_versions.id AS version_id, * FROM contents INNER JOIN content_locales ON contents.id = content_locales.content_id INNER JOIN content_versions ON content_locales.id = content_versions.locale_id WHERE permalink=$1 AND type='blog' ORDER BY update_date DESC";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($url))
			or die ("Cannot execute statement\n");

		pg_close($con);

		$this->number = pg_num_rows($result);

		for($i = 0; $i < $this->number; $i++) {
			$row = pg_fetch_assoc($result, $i);
			$this->objs[$i] = new BlogArticle;
			$this->objs[$i]->populate($row);
		}
	}
}
