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
require_once($config['includes_folder']."database.php");

// Objet représentant un commentaire sur un contenu.
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
	public $photo = NULL;

	public function checkID($id) {
	    global $config;

		// Chargement du commentaire par ID.
	    $con = sql_connect();

	    $query = "SELECT c.*, p.url AS photo_url FROM content_comments c
	        LEFT JOIN content_comment_photos p ON p.comment_id = c.id WHERE c.id = $1";

	    pg_prepare($con, "prepare_comment_join", $query)
	        or die("Cannot prepare statement\n");

	    $result = pg_execute($con, "prepare_comment_join", array($id))
	        or die("Cannot execute statement\n");

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
		if (!is_array($row)) {
			return;
		}

		// Mapping des champs SQL vers les propriétés.
		if (array_key_exists('id', $row)) {
			$this->id = $row['id'];
		}
		if (array_key_exists('version', $row)) {
			$this->version = $row['version'];
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
		if (array_key_exists('content', $row)) {
			$this->content = $row['content'];
		}
		if (array_key_exists('comment', $row)) {
			$this->comment = $row['comment'];
		}
		if (array_key_exists('locale', $row)) {
			$this->locale = $row['locale'];
		}
		if (array_key_exists('photo_url', $row)) {
			$this->photo = $row['photo_url'];
		}
	}

	/*****
	** Create a new comment
	*****/
	public function insert() {
		global $config;
		
		// Insertion d'un nouveau commentaire.
		$con = sql_connect();

		$query = "INSERT INTO content_comments (version, creation_date, update_date, author, is_public, is_archive, content, comment, locale) VALUES
			('0', $1, $2, $3, TRUE, FALSE, $4, $5, $6) RETURNING id";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array(date('r'), date('r'), $this->author, $this->content, $this->comment, $this->locale))
			or die ("Cannot execute statement\n");

		$this->id = pg_fetch_assoc($result)['id'];

		if(isset($this->photo) && $this->photo != NULL) {
			$query = "INSERT INTO content_comment_photos (comment_id, url) VALUES
			($1, $2)";

			pg_prepare($con, "prepare2", $query) 
				or die ("Cannot prepare statement\n");
			pg_execute($con, "prepare2", array($this->id, $this->photo));
		}

		pg_close($con);
	}

	/*****
	** Archive a comment
	*****/
	public function delete() {
		global $config;
		global $user;
		
		// Archivage logique du commentaire.
		$con = sql_connect();

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
		
		// Restauration d'un commentaire archivé.
		$con = sql_connect();

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
		// Conversion Markdown -> HTML.
		$this->comment_html = \Michelf\MarkdownExtra::defaultTransform($this->comment);
	}

	/*****
	** Converts the Markdown comment to text
	*****/
	public function md2txt() {
		// Conversion Markdown -> texte brut.
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

// Liste de commentaires liée à un contenu.
class Comments
{
	public $objs = array();
	public $number = NULL;

	// Retourne la liste des commentaires pour un contenu.
	/*****
	** Return the list of different articles
	*****/
	public function listComments($id, $archive = 0) {
	    global $config;

	    $con = sql_connect();

	    $query = "SELECT c.*, p.url AS photo_url FROM content_comments c
	              LEFT JOIN content_comment_photos p ON p.comment_id = c.id WHERE c.content = $1 ";

	    if ($archive == 0)
	        $query .= "AND c.is_archive IS FALSE AND c.is_public IS TRUE ";

	    $query .= "ORDER BY c.update_date DESC";

	    pg_prepare($con, "prepare_comment_list", $query)
	        or die("Cannot prepare statement\n");

	    $result = pg_execute($con, "prepare_comment_list", array($id))
	        or die("Cannot execute statement\n");

	    pg_close($con);

	    $this->number = pg_num_rows($result);

	    for ($i = 0; $i < $this->number; $i++) {
	        $row = pg_fetch_assoc($result, $i);
	        $this->objs[$i] = new Comment;
	        $this->objs[$i]->populate($row);
	    }
	}
}
