<?

class Rg
{

private $data=array();
private $tmp_data_index=array();
public $debug;

	function __construct()
	{
		
		}

	public function get($key) // получение по id
	{
		return $this->data[$key];
	}
	
	public function set($key,$value,$to_tmp=false) // запись по id
	{
		$this->data[$key]=$value;
		if($to_tmp) {
			$this->tmp_data_index[]=$key;
			}
		return true;
	}
	
	public function setL($key,&$value,$to_tmp=false) // запись по id переменной по ссылке
	{
		$this->data[$key]=&$value;
		if($to_tmp) {
			$this->tmp_data_index[]=$key;
			}
		return true;
	}
	
	public function remove($key) // удаление по id
	{
		unset($this->data[$key]);
	}
	
	public function has($key) // проверка на существование
	{
		return isset($this->data[$key]);
	}
	
	public function clearTmp() // очистка временных данных
	{
		if(count($this->tmp_data_index)) {
			foreach($this->tmp_data_index as $ind) {
				$this->remove($ind);
				}
			}
		$this->tmp_data_index=array();
	}
	
}

?>