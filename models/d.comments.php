<?php

namespace Kabano;

/**********************************************************
***********************************************************
**  
**  This class is to manage a comment object
**  
***********************************************************
**********************************************************/

require_once($config['third_folder']."Md/MarkdownExtra.inc.php");

class Comment
{
	public $id = NULL;
	public $version = 0;
	public $creation_date = NULL;
	public $update_date = NULL;
	public $author = NULL;
	public $is_public = NULL;
	public $is_archive = NULL;
	public $content = NULL;
	public $comment = NULL;
	public $locale = NULL;
	public $comment_html = NULL;
	public $author_obj = NULL;


	/*****
	** Connect to correct account using ID and stores its ID
	*****/
	public function checkID($id) {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT * FROM content_comments WHERE id=$1";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($id))
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
		$this->version = $row['version'];
		$this->creation_date = $row['creation_date'];
		$this->update_date = $row['update_date'];
		$this->author = $row['author'];
		$this->is_public = $row['is_public'];
		$this->is_archive = $row['is_archive'];
		$this->content = $row['content'];
		$this->comment = $row['comment'];
		$this->locale = $row['locale'];
	}

	/*****
	** Create a new comment
	*****/
	public function insert() {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "INSERT INTO content_comments (version, creation_date, update_date, author, is_public, is_archive, content, comment, locale) VALUES
			('0', $1, $2, $3, TRUE, FALSE, $4, $5, $6) RETURNING id";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array(date('r'), date('r'), $this->author, $this->content, $this->comment, $this->locale))
			or die ("Cannot execute statement\n");

		$this->id = pg_fetch_assoc($result)['id'];

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

		$query = "UPDATE content_comments SET is_public = FALSE WHERE id = $1";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($this->id))
			or die ("Cannot execute statement\n");

		pg_close($con);

		error_log(
			date('r')." \t".$user->name." (".$user->id.") \tDELETE  \tArchive comment ".$this->id."\r\n",
			3,
			$config['logs_folder'].'blog.comments.log');
	}

	/*****
	** Restore a comment
	*****/
	public function restore() {
		global $config;
		global $user;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "UPDATE content_comments SET is_public = TRUE WHERE id = $1";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($this->id))
			or die ("Cannot execute statement\n");

		pg_close($con);

		error_log(
			date('r')." \t".$user->name." (".$user->id.") \tPUBLISH \tUn archive comment ".$this->id."\r\n",
			3,
			$config['logs_folder'].'blog.comments.log');
	}

	/*****
	** Converts the Markdown comment to HTML
	*****/
	public function md2html() {
		$this->comment_html = \Michelf\MarkdownExtra::defaultTransform($this->comment);
	}

	/*****
	** Converts the Markdown comment to text
	*****/
	public function md2txt() {
		$this->md2html();
		$this->comment_txt = strip_tags($this->comment_html);
	}
}


/**********************************************************
***********************************************************
**  
**  This class is to manage a list of blog comments
**  
***********************************************************
**********************************************************/

class Comments
{
	public $objs = array();
	public $number = NULL;

	/*****
	** Return the list of different articles
	*****/
	public function listComments($id, $archive=0) {
		global $config;

		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

		$query = "SELECT * FROM content_comments WHERE content = $1 ";
		if ($archive == 0)
			$query .= "AND is_archive IS FALSE AND is_public IS TRUE ";
		$query .= "ORDER BY update_date DESC";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($id))
				or die ("Cannot execute statement\n");
		
		pg_close($con);

		$this->number = pg_num_rows($result);

		for($i = 0; $i < pg_num_rows($result); $i++) {
			$row = pg_fetch_assoc($result, $i);
			$this->objs[$i] = new Comment;
			$this->objs[$i]->populate($row);
		}
	}
}
