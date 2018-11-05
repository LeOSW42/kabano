<?

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
	public $type = "blog";
	public $name = NULL;
	public $content = NULL;

	/*****
	** Checks if a page at this URL exists and return the ID
	*****/
	public function checkPermalink($permalink, $withArchive=0, $elementNb=0) {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT * FROM contents WHERE permalink=$1 AND type='blog'";
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

		$query = "UPDATE contents SET is_archive = TRUE WHERE permalink = $1 AND type='blog'";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($this->permalink))
			or die ("Cannot execute statement\n");

		$query = "INSERT INTO contents (permalink, version, locale, creation_date, update_date, author, is_public, is_archive, is_commentable, type, name, content) VALUES
			($1, $2, $3, $4, $5, $6, TRUE, FALSE, $7, 'blog', $8, $9) RETURNING id";

		pg_prepare($con, "prepare2", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare2", array($this->permalink, $this->version, $this->locale, $this->creation_date, date('r'), $this->author, $this->is_commentable, $this->name, $this->content))
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
			date('r')." \t".$user->name." (".$user->id.") \tUPDATE \tEdit blog article '".$this->url."'\r\n",
			3,
			$config['logs_folder'].'blog.articles.log');
	}

	/*****
	** Delete an article by archiving it
	*****/
/*	public function delete() {
		global $config;
		global $user;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "UPDATE blog_articles SET archive = TRUE WHERE url = $1";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($this->url))
			or die ("Cannot execute statement\n");

		pg_close($con);

		error_log(
			date('r')." \t".$user->name." (".$user->id.") \tDELETE \tArchive blog article '".$this->url."'\r\n",
			3,
			$config['logs_folder'].'blog.articles.log');
	}*/

	/*****
	** Create an article
	*****/
	public function insert() {
		global $config;
		global $user;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "INSERT INTO contents (permalink, version, locale, creation_date, update_date, author, is_public, is_archive, is_commentable, type, name, content) VALUES
			($1, '0', $2, $3, $4, $5, TRUE, FALSE, $6, 'blog', $7, $8) RETURNING id";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($this->permalink, $this->locale, date('r'), date('r'), $user->id, $this->is_commentable, $this->name, $this->content))
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
			date('r')." \t".$user->name." (".$user->id.") \tINSERT \tCreate new blog article '".$this->url."'\r\n",
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

		if ($archive == 1) {
			// You just want one per url and the criteria is ORDER BY archives = true, time DES=C
			$query = "SELECT * FROM (SELECT a.id, a.update_date , ROW_NUMBER() OVER (PARTITION BY a.permalink ORDER BY CASE WHEN a.is_archive IS TRUE THEN 1 ELSE 0 END, a.update_date DESC) AS r FROM contents AS a) AS b WHERE r = 1 ORDER BY update_date DESC";
		}
		else {
			$query = "SELECT * FROM contents WHERE is_archive IS NOT TRUE AND is_public IS TRUE AND type='blog' ORDER BY update_date DESC";
		}
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

		if ($archive == 1) {
			// You just want one per url and the criteria is ORDER BY archives = true, time DES=C
			$query = "SELECT * FROM (SELECT a.id, a.update_date , ROW_NUMBER() OVER (PARTITION BY a.permalink ORDER BY CASE WHEN a.is_archive IS TRUE THEN 1 ELSE 0 END, a.update_date DESC) AS r FROM contents AS a) AS b WHERE r = 1 ORDER BY update_date DESC";
		}
		else {
			$query = "SELECT * FROM contents WHERE is_archive IS NOT TRUE AND is_public IS TRUE AND type='blog' ORDER BY update_date DESC";
		}

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

		$query = "SELECT * FROM contents WHERE permalink=$1 AND type='blog' ORDER BY update_date DESC";

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

/**********************************************************
***********************************************************
**  
**  This class is to manage a blog comment object
**  
***********************************************************
**********************************************************/

class BlogComment
{
	public $id = 0;
	public $locale = NULL;
	public $lastedit = NULL;
	public $archive = NULL;
	public $content = NULL;
	public $author = NULL;
	public $article = NULL;

	/*****
	** Populate the object using its ID
	*****/
	public function populate() {
		global $config;
		
		if($this->id != 0) {
			$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
				or die ("Could not connect to server\n");

			$query = "SELECT * FROM blog_comments WHERE id=$1";

			pg_prepare($con, "prepare1", $query) 
				or die ("Cannot prepare statement\n");
			$result = pg_execute($con, "prepare1", array($this->id))
				or die ("Cannot execute statement\n");

			pg_close($con);

			$blog_comment = pg_fetch_assoc($result);

			$this->locale = $blog_comment['locale'];
			$this->lastedit = $blog_comment['lastedit'];
			$this->archive = $blog_comment['archive'];
			$this->content = $blog_comment['content'];
			$this->author = $blog_comment['author'];
			$this->article = $blog_comment['article'];
		}
		else {
			die("Cannot populate a blog article without ID");
		}
	}

	/*****
	** Create a new comment
	*****/
	public function insert() {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "INSERT INTO blog_comments (content, lastedit, archive, locale, author, article) VALUES
			($1, $2, FALSE, $3, $4, $5)";

		pg_prepare($con, "prepare2", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare2", array($this->content, date('r'), $this->locale, $this->author, $this->article))
			or die ("Cannot execute statement\n");

		pg_close($con);
	}

	/*****
	** Archive a comment
	*****/
	public function delete() {
		global $config;
		global $user;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "UPDATE blog_comments SET archive = TRUE WHERE id = $1";

		pg_prepare($con, "prepare2", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare2", array($this->id))
			or die ("Cannot execute statement\n");

		pg_close($con);

		error_log(
			date('r')." \t".$user->name." (".$user->id.") \tDELETE  \tArchive comment ".$this->id."\r\n",
			3,
			$config['logs_folder'].'blog.comments.log');
	}

	/*****
	** DeArchive a comment
	*****/
	public function undelete() {
		global $config;
		global $user;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "UPDATE blog_comments SET archive = FALSE WHERE id = $1";

		pg_prepare($con, "prepare2", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare2", array($this->id))
			or die ("Cannot execute statement\n");

		pg_close($con);

		error_log(
			date('r')." \t".$user->name." (".$user->id.") \tPUBLISH \tUn archive comment ".$this->id."\r\n",
			3,
			$config['logs_folder'].'blog.comments.log');
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
**  This class is to manage a list of blog comments
**  
***********************************************************
**********************************************************/

class BlogComments
{
	public $ids = array();
	public $number = NULL;

	/*****
	** Return the list of different articles
	*****/
	public function listComments($id, $archive=0) {
		global $config;

		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT id FROM blog_comments WHERE article = $1 ";
		if ($archive == 0)
			$query .= "AND archive IS FALSE ";
		$query .= "ORDER BY lastedit DESC";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($id))
				or die ("Cannot execute statement\n");
		
		pg_close($con);

		$this->number = pg_num_rows($result);

		for($i = 0; $i < pg_num_rows($result); $i++) {
			$row = pg_fetch_assoc($result, $i);
			$this->ids[$i] = $row['id'];
		}
	}
}

?>