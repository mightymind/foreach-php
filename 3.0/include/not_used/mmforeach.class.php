<?
// Версия 2.8 от 2013.07.12 08:59
class mmForEach
{
// Переменные класса
public $config;
public $data;
public $classes;
//private $uid;
public $debug;
public $memory;

	public function __construct($config=array()) // Конструктор класса
	{
		$this->config=$config;
		$this->data=array();
		$this->classes=array('FE'=>&$this);
		//$this->uid=$this->hash($this->getMicroTime());

		$this->memory[0]=$_SERVER["SCRIPT_FILENAME"].'?'.urldecode($_SERVER["QUERY_STRING"]).' create '.memory_get_usage();
		$this->debug=$this->config['debug'];
		$this->date=date("U");
		}

	public function __destruct()
	{

	}

	public function destroy() // удаление объекта
	{
		$this->mem_mark('destroy');
		if ($this->debug) {$this->WriteMemoryInfo();}
		unset($this);
		//return $this;
	}

	public function WriteMemoryInfo() // удаление объекта
	{
		if (count($this->memory)>0) {
			$fp = fopen($this->config['cache_path'].'/memory.txt', 'a+');
			foreach($this->memory as $index=>$text) {
				fwrite($fp, date("d-m-Y, H:i:s").' - '.$index.' - '.$text."\n");
				}
			fclose($fp);
			return $this;
			} else {
				return false;
				}
	}

	public function mem_mark($info)
	{
		$this->memory[].=$info.' '.memory_get_usage();
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
					//$param=$data['param'];
					$var=$data['var'];
					$this->mem_mark('load '.$class);
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
					$this->classes[$var]=&$this->$var;
					$this->mem_mark('loaded '.$class);
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

	public function load_app($data)
	{
		if(!($data['class'])) {
			$data['class']='Main';
			}
			$this->mem_mark('load_app '.$data['class']);
		return $this->load(array('path'=>$this->config['app_path'],'class'=>$data['class'],'var'=>$data['var']));
		}
	
	public function mdl($tpl,&$param)
	{
		if($param['fe_mdl'][$tpl]) {
			require_once('sites/'.$this->config['site'].'/fe_mdl/'.$param['fe_mdl'][$tpl].'.mdl.php');
			}
		}
	
	/*
	// Garbage Collector
	public function gc()
	{
		@gc_enable();
		@gc_collect_cycles();
		}
	*/
	
	
	/*
	Строковые функции
	*/

	public function hash($str) {
		return md5("\n".$str);
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

	/*
	public function who() {
		return $this->uid;
		}
	*/
	
	public function ch($string) {
		//$this->mem_mark('ch');
		//return strtr($string,array("'"=>'&#039;'));
		return mysql_real_escape_string($string);
		}

	public function c_s($string) {
		//$this->mem_mark('c_s');
		return htmlspecialchars(trim($string), ENT_QUOTES, $this->config['charset']);
		}

	public function c_a($arr) {
		//$this->mem_mark('c_a');
		if(count($arr)>0) {
			foreach($arr as $index=>$value) {
				$value=$this->c_s($value);
				}
			}
		return $arr;
		}

	public function c_ga(&$arr) {
		$arr=$this->c_a($arr);
		return $this;
		}

	public function c_email($email) {
		//$this->mem_mark('c_email');
		return htmlspecialchars((substr(trim(strtolower($email)), 0, 30)), ENT_QUOTES, $this->config['charset']);
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

	public function mail2($to, $from, $subject, $body) {
		@mail($to, $subject, $body, "From: $from\r\n"."Reply-To: $from\r\n");
		return $this;
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
	Сохранение переменных в файл и загрузка из файла
	*/

	public function arr2f($arr,$file) {
		$this->w2f($file,serialize($arr));
		return $this;
		}

	public function data2f($file) {
		$this->w2f($file,serialize($this->data));
		return $this;
		}

	public function f2data($file) {
		if (file_exists($file)) {
			$this->data=unserialize(file_get_contents($file));
			}
		return $this;
		}

	public function f2arr($file) {
		if (file_exists($file)) {
			return unserialize(file_get_contents($file));
			}
		return $this;
		}

	public function v2data($arr) {
		if(count($arr)>0) {
			foreach($arr as $index=>$value) {
				$this->data[$index]=$value;
				}
			} else {
				$this->data=$arr;
				}
		return $this;
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
	Загрузка файла в браузер
	*/

	public function f2download($file,$name) {
		header("Content-Disposition: attachment; filename=$name");
		echo fread(fopen($file, "rb"), filesize($file));
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
}

/*
class MyException extends Exception {
	public function __construct($message, $errorLevel = 0, $errorFile = '', $errorLine = 0) {
		parent::__construct($message, $errorLevel);
		$this->file = $errorFile;
		$this->line = $errorLine;
		//if($_GET['error']) {
		$FE->mem_mark("Error ($errorLevel): $errorFile:$errorLine: $message");
		//echo "$message: $errorFile:$errorLine<br />";
		//	}
		}
}
*/
//if($CONFIG['debug']) {
//set_error_handler(create_function('$c, $m, $f, $l', 'if ($c === E_NOTICE) {echo "This is notice: ".$m." ".$f." ".$l."<br />";} else {throw new MyException($m, $c, $f, $l);}'), E_ALL);
////set_error_handler(create_function('$c, $m, $f, $l', 'if ($c === E_NOTICE) {echo "This is notice: ".$m." ".$f." ".$l."<br />";} else {throw new MyException($m, $c, $f, $l);}'), E_ALL);
////set_error_handler(create_function('$c, $m, $f, $l', 'if ($c === E_NOTICE) {echo "This is notice: ".$m} else {throw new MyException($m, $c, $f, $l);}'), E_ERROR & E_NOTICE);
//	}

/*
function feErrorHandler($type, $msg, $file, $line) {
	global $FE;
	static $titles = array(
		E_WARNING=>'Предупреждение',
		E_NOTICE=>'Уведомление',
		E_USER_ERROR=>'Ошибка, определенная пользователем',
		E_USER_WARNING=>'Предупреждение, определенное пользователем',
		E_USER_NOTICE=>'Уведомление, определенное пользователем',
		E_STRICT=>'Проблема совместимости в коде',
		E_RECOVERABLE_ERROR=>'Поправимая ошибка'
		);
	//print_r(array('type'=>$titles[$type],'msg'=>$msg,'file'=>$file,'line'=>$line));
	$FE->mem_mark($titles[$type].': '.$msg.' ('.$file.':'.$line.')');
	}
set_error_handler('feErrorHandler');
*/

?>