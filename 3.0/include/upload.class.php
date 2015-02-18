<?
// обновлено 06.12.2012
class Upload
{
public $path='./';
public $debug;
public $uploaded;
public $def_resp='tmp';
public $types=array(
				'image/gif'=>'gif',
				'image/png'=>'png',
				'image/jpeg'=>'jpg',
				'text/html'=>'html',
				'text/plain'=>'txt'
					);
//private $debug;

	function __construct()
	{
		//echo 1;
		}
	
	public function _init($config=array()) {
		if(count($config)) {
			$this->path=$config['path'];
			} else {
				$this->path='./upload/';
				}
        $this->debug=$this->FE->config['debug'];
		$this->uploaded=0;
		}

	public function save_arr($param) {
		/*
		$param=array(
			'field_name'=>$field_name,
			'new_file'=>$new_file,
			'suff'=>$suff
			);
		*/
		$arr=array();
		if(count($_FILES[$param['field_name']]['tmp_name'])) {
			foreach($_FILES[$param['field_name']]['tmp_name'] as $index=>$file) {
				$file_new=$this->path.$param['new_file'].'_'.$index.$param['suff'];
				if (move_uploaded_file($file, $file_new)) {
					/*
					name, size, type, tmp_name, error
					*/
					$this->uploaded++;
					$arr[$index]=$file_new;
					} else {
						unset($arr[$index]);
						}
				}
			}
		return $arr;
		}
	
	public function save($param) {
		/*
		$param=array(
			'field_name'=>$field_name,
			'new_file'=>$new_file,
			'suff'=>$suff
			);
		
Содержимое массива $_FILES для нашего примера приведено ниже. Обратите внимание, что здесь предполагается использование имени userfile для поля выбора файла, как и в приведенном выше примере. На самом деле имя поля может быть любым.
$_FILES['userfile']['name']
Оригинальное имя файла на компьютере клиента.
$_FILES['userfile']['type']
Mime-тип файла, в случае, если браузер предоставил такую информацию. Пример: "image/gif". Этот mime-тип не проверяется в PHP, так что не полагайтесь на его значение без проверки.
$_FILES['userfile']['size']
Размер в байтах принятого файла.
$_FILES['userfile']['tmp_name']
Временное имя, с которым принятый файл был сохранен на сервере.
$_FILES['userfile']['error']
Код ошибки, которая может возникнуть при загрузке файла. Этот элемент был добавлен в PHP 4.2.0
		
		*/
		$new_file=$this->path.$param['new_file'].$param['suff'];
		if (move_uploaded_file($_FILES[$param['field_name']]['tmp_name'], $new_file)) {
			/*
			name, size, type, tmp_name, error
			*/
			$this->uploaded++;
			return $new_file;
			} else {
				return 0;
				}
		}
	
	public function get_resp($str,$point='') {
		if($this->types[$str]) {
			return $point.$this->types[$str];
			} else {
				return $point.$this->def_resp;
				}
		}
	
}

?>