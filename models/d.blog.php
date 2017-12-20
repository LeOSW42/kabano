<?

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
	public $id = 0;
	public $title = NULL;
	public $url = NULL;
	public $locale = NULL;
	public $lastedit = NULL;
	public $archive = NULL;
	public $content = NULL;
	public $author = NULL;
	public $comments = NULL;

	/*****
	** Checks if a page at this URL exists and return the ID
	*****/
	public function checkUrl($url, $withArchive=0, $elementNb=0) {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT id FROM blog_articles WHERE url=$1";
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
			$article = pg_fetch_assoc($result);
			$this->id = $article['id'];
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

			$query = "SELECT * FROM blog_articles WHERE id=$1";

			pg_prepare($con, "prepare1", $query) 
				or die ("Cannot prepare statement\n");
			$result = pg_execute($con, "prepare1", array($this->id))
				or die ("Cannot execute statement\n");

			pg_close($con);

			$blog_article = pg_fetch_assoc($result);

			$this->title = $blog_article['title'];
			$this->url = $blog_article['url'];
			$this->locale = $blog_article['locale'];
			$this->lastedit = $blog_article['lastedit'];
			$this->archive = $blog_article['archive'];
			$this->content = $blog_article['content'];
			$this->author = $blog_article['author'];
			$this->comments = $blog_article['comments'];
		}
		else {
			die("Cannot populate a blog article without ID");
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

		// Archive previous article
		$query = "UPDATE blog_articles SET archive = TRUE WHERE url = $1";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($this->url))
			or die ("Cannot execute statement\n");

		// Publish the new one
		$query = "INSERT INTO blog_articles (url, title, content, lastedit, archive, locale, author, comments) VALUES
			($1, $2, $3, $4, FALSE, $5, $6, $7) RETURNING id";

		pg_prepare($con, "prepare2", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare2", array($this->url, $this->title, $this->content, date('r'), $this->locale, $this->author, $this->comments))
			or die ("Cannot execute statement\n");

		$this->id = pg_fetch_assoc($result)['id'];

		// Move all comments to the new one

		$query = "UPDATE blog_comments bc SET article = $1 FROM blog_articles ba WHERE bc.article = ba.id AND ba.url = $2";

		pg_prepare($con, "prepare3", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare3", array($this->id, $this->url))
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
	public function delete() {
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
	}

	/*****
	** Create an article
	*****/
	public function insert() {
		global $config;
		global $user;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "INSERT INTO blog_articles (url, title, content, lastedit, archive, locale, author, comments) VALUES
			($1, $2, $3, $4, FALSE, $5, $6, $7)";

		pg_prepare($con, "prepare2", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare2", array($this->url, $this->title, $this->content, date('r'), $this->locale, $this->author, $this->comments))
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
	public $ids = array();
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
			$query = "SELECT id FROM (SELECT a.id, a.lastedit , ROW_NUMBER() OVER (PARTITION BY a.url ORDER BY CASE WHEN a.archive IS TRUE THEN 1 ELSE 0 END, a.lastedit DESC) AS r FROM blog_articles AS a) AS b WHERE r = 1 ORDER BY lastedit DESC";
		}
		else {
			$query = "SELECT id FROM blog_articles WHERE archive IS NOT TRUE ORDER BY lastedit DESC";
		}
		$query .= " LIMIT $1 OFFSET $2";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($count, $first))
				or die ("Cannot execute statement\n");
		
		pg_close($con);

		for($i = 0; $i < pg_num_rows($result); $i++) {
			$row = pg_fetch_assoc($result, $i);
			$this->ids[$i] = $row['id'];
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
			$query = "SELECT id FROM (SELECT a.id, a.lastedit , ROW_NUMBER() OVER (PARTITION BY a.url ORDER BY CASE WHEN a.archive IS TRUE THEN 1 ELSE 0 END, a.lastedit DESC) AS r FROM blog_articles AS a) AS b WHERE r = 1 ORDER BY lastedit DESC";
		}
		else {
			$query = "SELECT id FROM blog_articles WHERE archive IS NOT TRUE ORDER BY lastedit DESC";
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

		$query = "SELECT id FROM blog_articles WHERE url=$1 ORDER BY lastedit DESC";

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