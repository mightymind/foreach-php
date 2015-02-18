<?
// обновлено 30.07.2013
class InterfaceDB
{
public $dbconnection;
private $dbhost;
private $dbbase;
private $dblogin;
private $dbpassword;
public $dbtables;
public $dbquerycount;
public $dberrors;
public $result;
public $debug;

	function __construct()
	{
		
		}

	public function _init($config=array()) // Инициализация класса
	{
		if(count($config)) {
			$this->dbhost=$config['mysql_host'];
			$this->dbbase=$config['mysql_base'];
			$this->dblogin=$config['mysql_user'];
			$this->dbpassword=$config['mysql_pass'];
			$this->dbtables=$config['mysql_tables'];
			} else {
				$this->dbhost=$this->FE->config['mysql_host'];
				$this->dbbase=$this->FE->config['mysql_base'];
				$this->dblogin=$this->FE->config['mysql_user'];
				$this->dbpassword=$this->FE->config['mysql_pass'];
				$this->dbtables=$this->FE->config['mysql_tables'];
				}
        $this->debug=$this->FE->config['debug'];
		$this->dbquerycount=0;
		if (isset($this->dbhost) & isset($this->dbbase) & isset($this->dblogin) & isset($this->dbpassword) & !isset($this->dbconnection)) {
			@$this->dbpConnect();
			$this->dbQuery("SET NAMES 'utf8'");
			$this->dbQuery("set character_set_connection=utf8"); 
			}
		}

	public function dbDestroy() // Удаление класса
	{
        if ($this->debug) {$this->dbWriteDBErrorLog();}
        $this->dbClose();
        unset($this);
		}

    public function dbWriteDBErrorLog(){
        if (count($this->dberrors)>0) {
			$fp = fopen($this->FE->config['cache_path'].'/db_errors.txt', 'a+');
			foreach($this->dberrors as $index=>$text) {
				fwrite($fp, $_SERVER['REQUEST_URI'].' - '.date("d-m-Y, H:i:s").' - '.$index.' - '.$text."\n");
				}
			fclose($fp);
			return true;
            } else {
                return false;
            }
    }

    public function dbShowErrors() {
        if (count($this->dberrors)>0) {
            foreach($this->dberrors as $index=>$text) {
                echo $index."\t".$text."<br />\n";
            }
        }
    }

	public function dbGetConnection() //Возвращяет созданное db-соединение
	{
		return $this->dbconnection;
		}

	public function dbGetDBQueryCount() //Возвращяет количество запросов к БД
	{
		return $this->dbquerycount;
		}
	
	public function dbConnect() // Соединение с БД, используется в случае нерабочего mysql_pconnect()
	{
		@$this->dbconnection=mysql_connect($this->dbhost,$this->dblogin,$this->dbpassword) or ($this->dberrors[].=mysql_error());
		@mysql_select_db($this->dbbase,$this->dbconnection) or ($this->dberrors[].=mysql_error());
		}

	public function dbpConnect() // Соединение с БД
	{
		@$this->dbconnection=mysql_pconnect($this->dbhost,$this->dblogin,$this->dbpassword) or ($this->dberrors[].=mysql_error());
		@mysql_select_db($this->dbbase,$this->dbconnection) or ($this->dberrors[].=mysql_error());
		}
		
	public function dbClose() // Закрытие соединения с БД
	{
		@mysql_close($this->dbconnection) or ($this->dberrors[].=mysql_error());
		}
		
	public function dbGetCountEnt($table,$param,$value) // Возвращает количество записей в таблице
	{
		return $this->dbSelectElement($this->dbSelectFirstRow("SELECT COUNT(*) FROM $table WHERE $param='$value'"),0);
		}
		
	public function dbSelect($query) // Выбирает запросом записи из таблицы
	{
		@$result=mysql_query($query,$this->dbconnection) or ($this->dberrors[].=mysql_error());
		$this->dbquerycount++;
		return $result;
		//@mysql_free_result($result);
		}
	
	public function dbSelectArr($query,$field) // Выбирает запросом записи из таблицы
	{
		$result=array();
		@$result_sql=$this->dbSelect($query);
		while($row=mysql_fetch_array($result_sql)) {
			$result[$row[$field]]=$row;
			}
		return $result;
		//@mysql_free_result($result);
		}

	public function dbSelectFirstRow($query) // Выбирает запросом запись из таблицы
	{
		@$result=$this->dbSelect($query) or ($this->dberrors[].=mysql_error());
        @$row=mysql_fetch_array($result);
		//@mysql_free_result($result);
		return $row;
		}

	public function dbSelectElement($row,$element) // Выбирает элемент записи
	{
		return $row[$element];
		}
	
	public function dbInsert($table,$_array) // Вставка записи в БД
	{
		$insert_string='';
		if(count($_array)) {
			foreach($_array as $_index=>$_value){
				if($insert_string<>'') {$insert_string=$insert_string.',';};
				$insert_string=$insert_string."`$_index`='$_value'";
				}
			@mysql_query('INSERT INTO `'.$table.'` SET '.$insert_string,$this->dbconnection) or ($this->dberrors[].=mysql_error());
			$this->dbquerycount++;
			return mysql_insert_id();
		} else {
			return 0;
			}
		}
	
	public function dbArrAsStr($_array) // Вставка записей в БД: (`barcode`, `param`) ('',''),('','')
	{
		$insert_string='';
		if(count($_array)) {
			foreach($_array as $key=>$value) {
				$_array[$key]="'$value'";
				}
			$insert_string="(".implode(",", $_array).")";
			}
		return $insert_string;
		}
	
	public function dbMassInsert($table,$tpl,$_array) // Вставка записей в БД: (`barcode`, `param`) ('',''),('','')
	{
		$insert_string='';
		if(count($_array)) {
			$insert_string=implode(",", $_array);
			@mysql_query('INSERT INTO `'.$table.'` '.$tpl.' VALUES '.$insert_string,$this->dbconnection) or ($this->dberrors[].=mysql_error());
			$this->dbquerycount++;
			return mysql_insert_id();
			} else {
				return 0;
				}
		}
	
	public function dbInsertIgnore($table,$_array) // Вставка записи в БД
	{
		$insert_string='';
		if(count($_array)) {
			foreach($_array as $_index=>$_value){
				if($insert_string<>'') {$insert_string=$insert_string.',';};
				$insert_string=$insert_string."`$_index`='$_value'";
				}
			@mysql_query('INSERT IGNORE INTO `'.$table.'` SET '.$insert_string,$this->dbconnection) or ($this->dberrors[].=mysql_error());
			$this->dbquerycount++;
			return mysql_insert_id();
		} else {
			return 0;
			}
		}

	public function dbDelete($table,$param) // Удаление записи из БД
	{
		@$result=mysql_query("DELETE FROM $table $param",$this->dbconnection) or ($this->dberrors[].=mysql_error());
		$this->dbquerycount++;
		return $result;
		}

	public function dbUpdate($table,$newvalue,$param) // Обновление записи в БД
	{
		@$result=mysql_query("UPDATE $table SET $newvalue $param",$this->dbconnection) or ($this->dberrors[].=mysql_error());
		$this->dbquerycount++;
		return $result;
		}
	
	public function dbUpdateArr($table,$_array,$param) // Обновление записи в БД
	{
		$insert_string='';
		if(count($_array)) {
			foreach($_array as $_index=>$_value){
				if($insert_string<>'') {$insert_string=$insert_string.',';};
				$insert_string=$insert_string."`$_index`='$_value'";
				}
		@$result=mysql_query("UPDATE $table SET $insert_string $param",$this->dbconnection) or ($this->dberrors[].=mysql_error());
		$this->dbquerycount++;
		return $result;
			} else {
				return 0;
				}
		}
	
	public function dbQuery($query) // Простой запрос к БД
	{
		@$result=mysql_query($query,$this->dbconnection) or ($this->dberrors[].=mysql_error());
		$this->dbquerycount++;
		return $result;
		}
	
	public function dbDump($table,$file) {
		$fp=fopen($file, "w");
		// получаем текст запроса создания структуры таблицы
		$res=$this->dbQuery("SHOW CREATE TABLE `$table`");
		$row=mysql_fetch_row($res);
		fwrite($fp, $row[1].";\n\n");
		
		// получаем данные таблицы
		$res=$this->dbQuery("SELECT * FROM `$table`");
		if (mysql_num_rows($res)>0) {
			while ($row = mysql_fetch_assoc($res)) {
				$keys = implode("`, `", array_keys($row));
				$values = array_values($row);
				foreach($values as $k=>$v) {
						$values[$k]=addslashes($v);
					}//addslashes(iconv($CONFIG["charset"], "UTF-8", $v))
				$values = implode("', '", $values);
				$sql="INSERT INTO `$table`(`$keys`) VALUES ('$values');\n";
				fwrite($fp, $sql);
				}
			}
		fclose($fp);
		}
	
	public function dbCopyTable($table_from,$table_to)
	{
		$this->FE->DB->dbQuery("CREATE TABLE $table_to LIKE $table_from");
		$this->FE->DB->dbQuery("INSERT $table_to SELECT * FROM $table_from");
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