<?

class Cache
{

public $cache_write_count;
public $cache_read_count;
public $cache_size;
public $debug;

	function __construct()
	{
		
		}
	
	/*
	Сжатие html
	*/
	public function htmlCompress($html)
	{
		preg_match_all('!(<(?:code|pre|script).*>[^<]+</(?:code|pre|script)>)!',$html,$pre);
		$html = preg_replace('!<(?:code|pre).*>[^<]+</(?:code|pre)>!', '#pre#', $html);
		$html = preg_replace('#<!–[^\[].+–>#', '', $html);
		$html = preg_replace('/[\r\n\t]+/', ' ', $html);
		$html = preg_replace('/>[\s]+</', '><', $html);
		$html = preg_replace('/[\s]+/', ' ', $html);
		if (!empty($pre[0])) {
			foreach ($pre[0] as $tag) {
				$html = preg_replace('!#pre#!', $tag, $html,1);
				}
			}
		return $html;
		}

	/*
	Кеширование
	*/
	
	public function start_caching() // начало кеширования
	{
		ob_start();
		ob_implicit_flush(0);
	} 

	public function get_caching_content() // возврат вывода
	{
		return ob_get_contents();
	}
	
	public function finish_caching() // конец кеширования
	{
		ob_end_clean();
	} 

	public function write_caching_content($content, $file) // запись в файл
	{
		$fp = fopen($file, 'w');
		fwrite($fp, $content);
		fclose($fp);
		$this->cache_write_count++;
		$this->cache_size=$this->cache_size+ob_get_length();
	} 

	public function read_cache($file) // чтение из файла в буфер вывода
	{
		$file=$this->fe_config['cache_path'].'/'.$file;
		if (file_exists($file)) {
			readfile($file);
			$this->cache_read_count++;
			return true;
		} else {
			return false;
		}
	}

	public function stop_n_write($file) // запись вывода в файл + завершение
	{
		$this->write_caching_content(ob_get_contents(), $this->fe_config['cache_path'].'/'.$file);
		$this->finish_caching();
	}
	
}

?>