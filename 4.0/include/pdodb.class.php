<?
// обновлено 02.07.2015 22:33
class pdoDB
{
public $dbc;

public $dbtables;
public $dbquerycount;
public $dberrors;
public $result;
public $debug;

	function __construct()
	{
		
	}
	

	public function _init(&$config=array())
	{
		$this->config=&$config;
		$this->dbtables=&$config['mysql_tables'];
        $this->debug=&$config['debug'];
		$this->dbquerycount=0;
		
		if (isset($this->config['mysql_host']) & isset($this->config['mysql_base']) & isset($this->config['mysql_user']) & isset($this->config['mysql_pass']) & !isset($this->dbc)) {
			@$this->dbpConnect();
			$this->dbQuery("SET NAMES 'utf8'");
			//$this->dbQuery("SET lc_time_names = 'ru_RU'");
			$this->dbQuery("SET character_set_connection=utf8"); 
		}
	}
	

	public function dbDestroy()
	{
		$this->dbClose();
		unset($this);
	}
	

	public function dbGetConnection()
	{
		return $this->dbc;
	}
	

	public function dbGetDBQueryCount()
	{
		return $this->dbquerycount;
	}
	

	public function dbConnect()
	{
		//$DBH = new PDO("sqlite:my/database/path/database.db");
		@$this->dbc = new PDO('mysql:host='.$this->config['mysql_host'].';dbname='.$this->config['mysql_base'], $this->config['mysql_user'], $this->config['mysql_pass']);
	}
	

	public function dbpConnect()
	{
		@$this->dbc = new PDO('mysql:host='.$this->config['mysql_host'].';dbname='.$this->config['mysql_base'], $this->config['mysql_user'], $this->config['mysql_pass'], array(
			PDO::ATTR_PERSISTENT => true,
		));
	}
	

	public function dbClose()
	{
		@$this->dbc = null;
	}
	

	public function dbGetCountEnt($table,$param,$value)
	{
		return $this->dbSelectElement($this->dbSelectFirstRow("SELECT COUNT(*) FROM $table WHERE $param='$value'"),0);
	}
	

	public function dbSelect($query)
	{
		return $this->dbQuery($query);
	}
	

	public function dbSelectArr($query,$field='id')
	{
		$result=array();
		@$result_sql=$this->dbSelect($query);
		while($row = $result_sql->fetch()){
			$result[$row[$field]]=$row;
		}
		return $result;
	}
	

	public function dbSelectFirstRow($query)
	{
		@$res = $this->dbQuery($query);
		if(is_object($res)) {
			//var_dump($res);
			return $res->fetch();
		} else {
			return null;
		}
	}
	

	public function dbSelectElement($row,$element)
	{
		return $row[$element];
	}
	

	public function dbInsert($table,$_array,$ignore=false)
	{
		if(count($_array)) {
			$_arr_=array();
			foreach($_array as $_index=>$_value){
				$_arr_[]="`$_index`='$_value'";
			}
			$insert_string=implode(',',$_arr_);
			if($ignore) {
				$ignore_str='IGNORE';
			} else {
				$ignore_str='';
			}
			$this->dbQuery('INSERT '.$ignore_str.' INTO `'.$table.'` SET '.$insert_string);
			return $this->dbc->lastInsertId();
		} else {
			return 0;
		}
	}
	

	public function dbMassInsert($table,$tpl,$_array)
	{
		$insert_string='';
		if(count($_array)) {
			$insert_string=implode(',', $_array);
			$this->dbQuery('INSERT INTO `'.$table.'` '.$tpl.' VALUES '.$insert_string);
			return $this->dbc->lastInsertId();
		} else {
			return 0;
		}
	}
	

	public function dbInsertIgnore($table,$_array)
	{
		return $this->dbInsert($table,$_array,true);
	}
	

	public function dbDelete($table,$param)
	{
		return $this->dbQuery('DELETE FROM '.$table.' '.$param);
	}
	

	public function dbUpdate($table,$newvalue,$param)
	{
		return $this->dbQuery('UPDATE `'.$table.'` SET '.$newvalue.' '.$param);
	}
	

	public function dbUpdateArr($table,$_array,$param)
	{
		if(count($_array)) {
			$_arr_=array();
			foreach($_array as $_index=>$_value){
				$_arr_[]="`$_index`='$_value'";
				}
			$insert_string=implode(',',$_arr_);
			return $this->dbQuery('UPDATE `'.$table.'` SET '.$insert_string.' '.$param);
		} else {
			return 0;
		}
	}
	

	public function dbQuery($query)
	{
		@$result=$this->dbc->query($query);
		$this->dbquerycount++;
		return $result;
		//while($row = $result->fetch()){}
	}
	

	public function dbDump($table,$file) {
		$fp=fopen($file, "w");
		
		$res=$this->dbQuery("SHOW CREATE TABLE `$table`");
		$row=$res->fetch();
		fwrite($fp, $row[1].";\n\n");
		
		$res=$this->dbQuery("SELECT * FROM `$table`");
		while ($row = $res->fetch()) {
			$keys = implode("`, `", array_keys($row));
			$values = array_values($row);
			foreach($values as $k=>$v) {
				$values[$k]=addslashes($v);
			}//addslashes(iconv($CONFIG["charset"], "UTF-8", $v))
			$values = implode("', '", $values);
			$sql="INSERT INTO `$table`(`$keys`) VALUES ('$values');\n";
			fwrite($fp, $sql);
		}
		fclose($fp);
	}
	

	public function dbCopyTable($table_from,$table_to)
	{
		$this->dbQuery("CREATE TABLE $table_to LIKE $table_from");
		$this->dbQuery("INSERT $table_to SELECT * FROM $table_from");
	}
	
	/*
	"SHOW DATABASES";
	"SHOW TABLES";
	"SHOW COLUMNS FROM `$table`";
	"DROP TABLE `$table`";
	"ALTER TABLE `$table` DROP COLUMN `$col`";
	"ALTER TABLE `$table` ADD COLUMN `$col` VARCHAR (20)";
	"ALTER TABLE `$table` CHANGE `$col` `$col_new` VARCHAR (50)";
	"ALTER TABLE `$table` MODIFY `$col` VARCHAR(3)";
	"LOAD DATA INFILE '$file' replace INTO TABLE `$table` FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n' (field1,field2,field3)";
	*/
	
}

?>