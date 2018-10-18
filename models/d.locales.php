<?

/**********************************************************
***********************************************************
**  
**  This class is to manage Locale object
**  
***********************************************************
**********************************************************/

class Locale
{
    private $name = 0;
    public $display_name = NULL;
    public $flag_name = NULL;

    /*****
	** populate object using name
	*****/
	public function checkName($name) {
		global $config;
		
		$con = pg_connect("host=".$config['SQL_host']." dbname=".$config['SQL_db']." user=".$config['SQL_user']." password=".$config['SQL_pass'])
			or die ("Could not connect to server\n");

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
	private function populate($row) {
	    $this->name = $row['name'];
	    $this->display_name = $row['display_name'];
	    $this->flag_name = $row['flag_name'];
	}

	/*****
	** Simple return only functions
	*****/
	public function get_id() {
	    return $this->id;
	}
}

?>