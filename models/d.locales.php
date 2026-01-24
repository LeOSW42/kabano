<?
 
namespace Kabano;

/**********************************************************
***********************************************************
**  
**  This class is to manage Locale object
**  
***********************************************************
**********************************************************/

require_once($config['includes_folder']."database.php");

class Locale
{
    public $name = 0;
    public $display_name = NULL;
    public $flag_name = NULL;

    /*****
	** populate object using name
	*****/
	public function checkName($name) {
		global $config;
		
		$con = sql_connect();

		$query = "SELECT * FROM locales WHERE name=$1";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array($name))
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
	    $this->name = $row['name'];
	    $this->display_name = $row['display_name'];
	    $this->flag_name = $row['flag_name'];
	}
}

/**********************************************************
***********************************************************
**  
**  This class is to manage Locales list object
**  
***********************************************************
**********************************************************/

class Locales
{
    public $number = 0;
    public $objs = array();

    /*****
	** Get all locales
	*****/
	public function getAll() {
		global $config;
		
		$con = sql_connect();

		$query = "SELECT * FROM locales";

		pg_prepare($con, "prepare1", $query) 
			or die ("Cannot prepare statement\n");
		$result = pg_execute($con, "prepare1", array())
			or die ("Cannot execute statement\n");

		pg_close($con);

		$this->number = pg_num_rows($result);

		for($i = 0; $i < $this->number; $i++) {
			$locale = pg_fetch_assoc($result, $i);
			$this->objs[$i] = new Locale;
			$this->objs[$i]->populate($locale);
		}
	}
}

?>
