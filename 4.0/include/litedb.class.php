<?
// обновлено 17.10.2013
class LiteDB
{
public $db=array();
public $db_file=array();
public $q_count=array();
public $errors=array();

public $debug;
/*
const IS_INT = 1;
const IS_STR = 2;
const ASSOC = 1;
const NUM = 2;
const BOTH = 3;
*/
	function __construct()
	{
		
		}
	
	public function _init($config=array())
	{
		
		}
	
	public function O($db,$file)
	{
		$this->db_file[$db]=$file;
		
		if($this->db[$db]=sqlite_popen($this->db_file[$db])) {
			$this->Q($db,"PRAGMA journal_mode=WAL;");
			$this->Q($db,"PRAGMA encoding='UTF-8';");
			return true;
			} else {
				$this->error($db);
				return false;
				}
		}
	
	public function C($db)
	{
		sqlite_close($this->db[$db]);
		return true;
		}
	
	/* --------------------------- */
	
	public function Q($db,$str)
	{
		//$str = preg_replace("/escape_string\((.*?)\)/", $this->escape_string("$1"), $str);
		$this->q_count[$db]++;
		return sqlite_query($this->db[$db], $str.';');
		}
	
	public function S($db,$table,$param)
	{
		return $this->Q($db,"SELECT * FROM $table $param");
		}
	
	public function I($db,$table,$field,$value)
	{
		return $this->Q($db,"INSERT INTO $table($field) VALUES ($value)");
		}
	
	public function D($db,$table,$param='')
	{
		return $this->Q($db,"DELETE FROM $table $param");
		}
	
	public function U($db,$table,$newvalue,$param='')
	{
		return $this->Q($db,"UPDATE $table SET $newvalue $param");
		}
	
	public function getArr($db,$table,$param)
	{
		return sqlite_fetch_array($this->S($db,$table,$param), SQLITE_ASSOC);
		}
	
	public function getAll($db,$table,$param)
	{
		return sqlite_fetch_all($this->S($db,$table,$param), SQLITE_ASSOC);
		}
	
	public function last_insert_id($db)
	{
		return sqlite_last_insert_rowid($this->db[$db]);
		}
	
	public function rows_count($query)
	{
		return sqlite_num_rows($query);
		}
	
	private function error($db)
	{
		$this->errors[$db][]=sqlite_last_error($this->db[$db]);
		return true;
		}
	
	/* --------------------------- */
	
	public function OpenAll($dbs=array())
	{
		if(count($dbs)) {
			foreach($dbs as $dbname=>$dbfile) {
				$this->O($dbname,$dbfile);
				}
			}
		}
	
	public function CreateTable($db,$table,$fields=array())
	{
		if(count($fields)) {
			$tmp_str=array();
			foreach($fields as $name=>$type) {
				$tmp_str[]=$name.' '.$type;
				}
			$str=implode(',', $tmp_str);
			$this->DropTable($db,$table);
			return $this->Q($db,"CREATE TABLE $table ($str)");
			} else {
				return false;
				}
		}
	
	public function DropTable($db,$table)
	{
		return $this->Q($db,"DROP TABLE $table");
		}
	
	/*
	
	public function getColumns($db,$table)
	{
		return $this->getArr($db,"PRAGMA table_info($table)");
		}
	
	public function getTables($db)
	{
		return $this->getArr($db,"SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
		}
	
	private function escape_string($string, $quotestyle='both')
	{
		if( function_exists('sqlite_escape_string') ){
			$string = sqlite_escape_string($string);
			$string = str_replace("''","'",$string); #- no quote escaped so will work like with no sqlite_escape_string available
			} else {
				$escapes = array("\x00", "\x0a", "\x0d", "\x1a", "\x09","\\");
				$replace = array('\0',   '\n',    '\r',   '\Z' , '\t',  "\\\\");
				}
		switch(strtolower($quotestyle)){
			case 'double':
			case 'd':
			case '"':
				$escapes[] = '"';
				$replace[] = '\"';
				break;
			case 'single':
			case 's':
			case "'":
				$escapes[] = "'";
				$replace[] = "''";
				break;
			case 'both':
			case 'b':
			case '"\'':
			case '\'"':
				$escapes[] = '"';
				$replace[] = '\"';
				$escapes[] = "'";
				$replace[] = "''";
				break;
			}
		return str_replace($escapes,$replace,$string);
		}
	
	*/
	
}

?>