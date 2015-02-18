<?
// обновлено 02.09.2013
/*
$this->FE->load(array('path'=>$this->FE->config['sys_path'],'class'=>'Storagedata','var'=>'SD'));
$this->FE->SD->assign('test1',$_SESSION['arr']);
$parent=$this->FE->_post('parent');
$el=$this->FE->_post('el');
$value=$this->FE->_post('value');
$this->FE->SD->set('test1',$el,$value);

$this->FE->load(array('path'=>$this->FE->config['sys_path'],'class'=>'Storagedata','var'=>'SD'));
$this->FE->SD->assign('test1',$_SESSION['arr']);
$this->FE->SD->clear_data('test1');
*/
class Structdata
{
public $data=array();
private $delimit='.';
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
	
	public function set($index,$el,$val) {
		$par=&$this->_el($index,$el);
		//$this->data[$index][$parent][$id]=$arr;
		$par=$val;
		return 1;
		}
	
	public function remove($index,$el,$id) {
		$par=&$this->_el($index,$el);
		//unset($this->data[$index][$parent][$id]);
		unset($par);
		return 1;
		}
	
	public function get($index,$el) {
		$par=&$this->_el($index,$el);
		//return $this->data[$index][$parent][$id];
		return $par;
		}
	
	public function &_el($index,$str) {
		$ids=explode($this->delimit,$str);
		$ch_el=&$this->data[$index];
		foreach($ids as $i=>$id) {
			if($ch_el[$id]) {
				
				} else {
					$ch_el[$id]=array();
					}
			$ch_el=&$ch_el[$id];
			}
		return $ch_el;
		}
	
	public function change_parent($index,$el,$options) {
		$par=&$this->_el($index,$el);
		foreach($options as $name=>$value) {
			$par[$name]=$value;
			}
		return 1;
		}
	
	/*
	public function set($index,$parent,$id,$arr) {
		$par=&$this->get($index,$parent);
		//$this->data[$index][$parent][$id]=$arr;
		$par[$id]=$arr;
		return 1;
		}
	
	public function remove($index,$parent,$id) {
		$par=&$this->get($index,$parent);
		//unset($this->data[$index][$parent][$id]);
		unset($par[$id]);
		return 1;
		}
	
	public function view($index,$parent,$id) {
		$par=&$this->get($index,$parent);
		//return $this->data[$index][$parent][$id];
		return $par[$id];
		}
	*/
	
}

?>