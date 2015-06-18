<?
class mmForEach
{
// Переменные класса
public $config;
public $data;
//public $classes;
public $debug;
public $memory;
public $lazy=array();
public $version=array(
	'number'=>'3.3 beta2',
	'date'=>'201505221629',
	'secret'=>'NemoMeImpuneLacessit',
	);

	public function __construct($config=array()) // Конструктор класса
	{
		$this->config=$config;
		$this->data=array();
		//$this->classes=array('FE'=>&$this);
		
		$this->mem_mark('Init FE: '.$this->_server('REQUEST_URI'));//urldecode(isset($_SERVER["QUERY_STRING"])?$_SERVER["QUERY_STRING"]:'')
		$this->debug=$this->config['debug'];
		$this->date=$this->as_int(date("U"));
		}

	public function __destruct()
	{

	}

	public function destroy() // удаление объекта
	{
		$this->mem_mark('destroy');
		//if ($this->debug) {$this->WriteMemoryInfo();}
		unset($this);
		//return $this;
	}

	public function mem_mark($info,$status=0)
	{
		$this->memory[]=array('created_at'=>$this->getMicroTime(),'title'=>$info, 'status'=>$status, 'memory'=>memory_get_usage(),);
		return $this;
	}

	/*
	Работа с классами
	*/

	public function load($data,$from_file=true)
	{
		if (!is_array($data))
			{
				return false;
				} else {
					$path=$data['path'];
					$class=$data['class'];
					$var=$data['var'];
					//$this->mem_mark('load '.$class);
					if($from_file) {
						if(file_exists($path.'/'.strtolower($class).'.class.php')) {
							require_once($path.'/'.strtolower($class).'.class.php');
							} else {
								require_once($path.'/error.class.php');
								$class='Error';
								}
						}
					if(!empty($data['param'])) {
						$this->$var = new $class($data['param']);
						}  else {
							$this->$var = new $class();
							}
					$this->$var->FE=&$this;
					$this->$var->fe_config=&$this->config;
					//$this->classes[$var]=&$this->$var;
					$this->mem_mark('load '.$class);
					return $this;
					}
		}

	public function unload($class)
	{
		unset($this->$class);
		$this->mem_mark('unload '.$class);
		return $this;
		}

	public function run_app($url_arr)
	{
		if(!($url_arr['class'])) {
			$url_arr['class']=$this->config['main_app'];
			}
		if($this->load(array('path'=>$this->config['app_path'],'class'=>$url_arr['class'],'var'=>$url_arr['class']))) {
			if($url_arr['function'] && method_exists($this->$url_arr['class'],$url_arr['function'])) {
				$func=$url_arr['function'];
				} else {
					$func=$this->config['main_app_function'];
					}
			@$this->$url_arr['class']->$func($url_arr['param']);
			$this->mem_mark('run_app '.$url_arr['class'].'->'.$func);
			}
		return $this;
		}
	
	public function mdl($tpl,&$param)
	{
		if($param['fe_mdl'][$tpl]) {
			require_once('sites/'.$this->config['site'].'/fe_mdl/'.$param['fe_mdl'][$tpl].'.mdl.php');
			}
		$this->mem_mark('mdl '.$tpl);
		}
	
	public function runLazy($id)
	{
		if(isset($this->lazy[$id])) {
			$o=&$this->lazy[$id];
			if(method_exists($o,'run')) {
				$res=$o->run();
				}
			} else {
				$res=null;
				}
		$this->mem_mark('runLazy '.$id.' result='.$res);
		return $res;
		}
	
	public function setLazy($id,$tpl,&$param)
	{
		$file='sites/'.$this->config['site'].'/lazy/'.$tpl.'.lazy.php';
		if(file_exists($file)) {
			
			require_once($file);
			
			$name=basename($tpl);
			$o=new $name($param);
			
			if(method_exists($o,'importFE')) {
				$o->importFE($this);
				$this->lazy[$id]=&$o;
				$res=true;
				} else {
					$res=false;
					}
			
			} else {
				$res=false;
				}
		
		return $res;
		}
	
	/*
	Строковые функции
	*/

	public function hash($str,$salt1='',$salt2='') {
		return md5($salt1."\n".$salt2.$str);
		}

	public function _get($param) {
		if ($_GET[$param]) {
			return $this->c_s($_GET[$param]);
			} else {
				return null;
				}
		}

	public function _post($param) {
		if ($_POST[$param]) {
			return $this->c_s($_POST[$param]);
			} else {
				return null;
				}
		}

	public function _cookie($param) {
		if ($_COOKIE[$param]) {
			return $this->c_s($_COOKIE[$param]);
			} else {
				return null;
				}
		}

	public function _server($param) {
		if ($_SERVER[$param]) {
			return $this->c_s($_SERVER[$param]);
			} else {
				return null;
				}
		}
	
	public function as_int($value) {
		return (isset($value)?intval($value):0);
		}
	
	public function is_num($value) {
		if (preg_match("#^[0-9]+$#",$value)) {
			return true;
		} else {
			return false;
		}
	}

	public function ch($string,$changes=array("'"=>'&#039;')) {
		return strtr(stripcslashes(mysql_real_escape_string($string)),$changes);
		}

	public function c_s($string) {
		return htmlspecialchars(trim($string), ENT_QUOTES, $this->config['charset']);
		}

	public function c_a($arr) {
		if(count($arr)>0) {
			foreach($arr as $index=>$value) {
				$value=$this->c_s($value);
				}
			}
		return $arr;
		}

	public function c_email($email) {
		return htmlspecialchars((substr(trim(strtolower($email)), 0, 48)), ENT_QUOTES, $this->config['charset']);
		}

	/*
	Генерация php-заголовков
	*/

	public function genHeaders($contenttype,$compress=false) {
		$this->mem_mark('genHeaders');
		if($compress) {
			@ob_start();
			@ob_start(array('ob_gzhandler', $compress));
		}
		Header("Content-type: $contenttype; charset={$this->config['charset']}");
		Header("Expires: Fri, 32 Jul 1985 01:01:01 GMT");
		Header("Cache-Control: no-cache, must-revalidate");
		Header("Pragma: no-cache");
		Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
		return $this;
		}

	/*
	Перенаправление на другой адрес
	*/

	public function go2($url) {
		Header("Location: $url");
		return $this;
		}

	/*
	Отправка почты
	*/

	public function mail2($to, $from, $subject, $body, $headers=array()) {
		$headers_str="From: $from\r\n"."Reply-To: $from\r\n";
		if(count($headers)) {
			foreach($headers as $param=>$value) {
				$headers_str=$headers_str."$param: $value\r\n";
				}
			}
		@mail($to, $subject, $body, $headers_str);
		return $this;
		}
	
	public function is_email($email) {
		return preg_match("/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)*\.([a-zA-Z]{2,6})$/", $email);
	}

	/*
	Получение времени UTC на конкретный момент
	*/

	public function get_utc($year,$month,$day,$hour,$min,$sec) {
		return date("U", mktime($hour,$min,$sec,$month,$day,$year));
		}

	public function get_formdate($year,$month,$day,$hour,$min,$sec,$tpl) {
		return date($tpl, mktime($hour,$min,$sec,$month,$day,$year));
		}

	public function get_date($utc) {
		/*
Array(
[seconds] => 40
[minutes] => 58
[hours]   => 21
[mday]    => 17
[wday]    => 2
[mon]     => 6
[year]    => 2003
[yday]    => 167
[weekday] => Tuesday
[month]   => June
[0]       => 1055901520
)
		*/
		return getdate($utc);
		}

	/*
	Запись в файл
	*/

	public function w2f($file,$str) {
		$fp=fopen($file, "w");
		fwrite($fp, $str);
		fclose($fp);
		return $this;
		}

	/*
	Получение времени до микросекунды
	*/

	public function getMicroTime() {
		return microtime(1);
		}

	public function get_rand_arr($array) {
		return $array[rand(0,count($array)-1)];
		}

	public function randstr($len,$sym=false) {
		$tpl='qwertyuiopasdfghjklzxcvbnm0192837465';
		if($sym) {
			$tpl.='-_+=()%$#@!*^&\|/:';
			}
		$str='';
		for($i=0;$i<$len;$i++) {
			$str.=$tpl[rand(0,strlen($tpl)-1)];
			}
		return $str;
		}
	
	public function ru2en($str)
	{
		$str=mb_strtolower($str, $this->config['charset']);
		$str=strtr($str,array(
			'а'=>'a',	'б'=>'b',	'в'=>'v',	'г'=>'g',	'д'=>'d',	'е'=>'e',	'ё'=>'yo',	'ж'=>'zh',	'з'=>'z',	'и'=>'i',
			'й'=>'yi',	'к'=>'k',	'л'=>'l',	'м'=>'m',	'н'=>'n',	'о'=>'o',	'п'=>'p',	'р'=>'r',	'с'=>'s',	'т'=>'t',
			'у'=>'u',	'ф'=>'f',	'х'=>'h',	'ц'=>'ts',	'ч'=>'ch',	'ш'=>'sh',	'щ'=>'shch',	'ъ'=>'',	'ы'=>'y',	'ь'=>'',
			'э'=>'e',	'ю'=>'yu',	'я'=>'ya',
			//'А'=>'A',	'Б'=>'B',	'В'=>'V',	'Г'=>'G',	'Д'=>'D',	'Е'=>'E',	'Ё'=>'Yo',	'Ж'=>'Zh',	'З'=>'Z',	'И'=>'I',
			//'Й'=>'Yi',	'К'=>'K',	'Л'=>'L',	'М'=>'M',	'Н'=>'N',	'О'=>'O',	'П'=>'P',	'Р'=>'R',	'С'=>'S',	'Т'=>'T',
			//'У'=>'U',	'Ф'=>'F',	'Х'=>'H',	'Ц'=>'Ts',	'Ч'=>'Ch',	'Ш'=>'Sh',	'Щ'=>'Shch',	'Ъ'=>'',	'Ы'=>'Y',	'Ь'=>'',
			//'Э'=>'E',	'Ю'=>'Yu',	'Я'=>'Ya',
			));
		$str=preg_replace('~[^-a-z0-9_]+~u', '-', $str);
		$str=trim($str, "-");
		return $str;
		}
	
	public function arr2json(&$a) {
		function prepareUTF($matches){
			return json_decode('"'.$matches[1].'"');
			}
		
		return stripslashes(preg_replace_callback('/((\\\u[01-9a-fA-F]{4})+)/', 'prepareUTF',
			json_encode($a)
			));
		}
	
}

class feRunnable
{
	public $param;
	public $FE;
	
	function __construct(&$param) {
		$this->param=$param;
		}
	
	function __destruct() {
		
		}
	
	function importFE(&$FE) {
		$this->FE=$FE;
		}
	
	function run(){
		return $this;
		}
	
}

/*
class feR extends feRunnable {
	function run(){
		var_dump($this->param);
		return 1;
		}
	}
*/

?>