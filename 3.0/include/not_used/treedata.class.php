<?
// обновлено 30.08.2013
class Treedata
{
public $data=array();
private $tree='tree';
private $elements='elements';
private $debug;
//private $debug;

	function __construct()
	{
		//echo 1;
		}
	
	public function assign($index,&$data) {
		$this->data[$index]=&$data;
		}
	
	public function data2str($index) {
		return serialize($this->data[$index]);
		}
	
	public function str2data($index,$str) {
		$this->data[$index]=unserialize($str);
		return 1;
		}
	
	public function clear_data($index) {
		$this->data[$index]=array();
		return 1;
		}
	
	public function add($index,$parent,$id,$arr) {
		$this->data[$index][$this->elements][$id]=$arr;
		$this->data[$index][$this->tree][$parent][$id]=$id;
		return 1;
		}
	
	public function remove($index,$parent,$id) {
		unset($this->data[$index][$this->elements][$id]);
		unset($this->data[$index][$this->tree][$parent][$id]);
		return 1;
		}
	
	public function get($index,$parent,$id) {
		return $this->data[$index][$this->elements][$id];
		}
	
	public function childs($index,$parent) {
		if(count($this->data[$index][$this->tree][$parent])) {
			$tmp_arr=array();
			foreach($this->data[$index][$this->tree][$parent] as $el) {
				$tmp_arr[$el]=$this->data[$index][$this->elements][$el];
				}
			return $tmp_arr;
			} else {
				return array();
				}
		}
	
	/*
	public function add($index,$parent,$id,$arr) {
		$this->data[$index][$this->elements][$parent.':'.$id]=$arr;
		$this->data[$index][$this->tree][$parent][$id]=$id;
		return 1;
		}
	
	public function remove($index,$parent,$id) {
		unset($this->data[$index][$this->elements][$parent.':'.$id]);
		unset($this->data[$index][$this->tree][$parent][$id]);
		return 1;
		}
	
	public function get($index,$parent,$id) {
		return $this->data[$index][$this->elements][$parent.':'.$id];
		}
	
	public function childs($index,$parent) {
		if(count($this->data[$index][$this->tree][$parent])) {
			$tmp_arr=array();
			foreach($this->data[$index][$this->tree][$parent] as $el) {
				$tmp_arr[$el]=$this->data[$index][$this->elements][$parent.':'.$el];
				}
			return $tmp_arr;
			} else {
				return array();
				}
		}
	*/
	
}

?>