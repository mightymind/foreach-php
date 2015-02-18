<?
// обновлено 16.10.2013
class Lite3DB
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
	
	public function _init($config=array()) {
		
		}
	
	public function O($db,$file)
	{
		$this->db_file[$db]=$file;
		
		if($this->db[$db]=new PDO('sqlite:'.$this->db_file[$db])) {
			//$this->Q($db,"PRAGMA encoding='UTF-8';");
			return 1;
			} else {
				$this->error($db);
				return 0;
				}
		}
	
	public function C($db)
	{
		$this->db[$db]->close();
		return true;
		}
	
	/* --------------------------- */
	
	public function Q($db,$str)
	{
		$this->q_count[$db]++;
		return $this->db[$db]->exec($str);
		}
	
	public function S($db,$table,$param)
	{
		return $this->Q($db,"SELECT * FROM $table $param");
		}
	
	public function I($db,$table,$value)
	{
		$this->Q($db,"INSERT INTO $table VALUES($value)");
		return $this->last_insert_id($db);
		}
	
	public function D($db,$table,$param='')
	{
		return $this->Q($db,"DELETE FROM $table $param");
		}
	
	public function U($db,$table,$newvalue,$param='')
	{
		return $this->Q($db,"UPDATE $table SET $newvalue $param");
		}
	
	public function getArr($db,$q)
	{
		$res=array();
		while($row=$this->Q($db,$q)->fetchArray(SQLITE3_ASSOC)) {
			$res[]=$row;
			}
		return $res;
		}
	
	public function last_insert_id($db)
	{
		return $this->db[$db]->lastInsertRowID();
		}
	
	private function error($db)
	{
		$this->errors[$db][]=$this->db[$db]->lastErrorMsg();
		return true;
		}
	
}

?>