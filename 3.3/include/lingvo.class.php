<?

class Lingvo {

/*
data[]=array(
	'info'=>array(
		'stat'=>array()
		),
	'editable'=>array(),
	'phrase'=>array(),
	'word'=>array(),
	'synonym'=>array(),
	'forms'=>array(),
	)
*/
public $text=array();
public $source=array();
public $sym=array(
	'space'=>' ',
	'unprint'=>array(
		"\n"	=>	' ',
		"\r"	=>	' ',
		"\t"	=>	' ',
		"\\"	=>	' ',
		'  '	=>	' ',
		'   '	=>	' ',
		'<br />'=>	' ',
		'	'	=>	' ',
		),
	'some_words'=>"/\s{1,}(|если|него|перед|почти|пока|около|только|будет|быть|был|была|было|есть|хоть|хотя|после|кстати|может|можно|всем|нами|мною|меня|даже|вообще|себя|когда|обычно|очень|больше|более|что|чего|кого|кому|чему|среди|нибудь|надо|тоже)\s{1,}/isu",
	'symbols'=>"/[(\<)|(\>)|(\=)|(\+)|(\$)|(\#)|(\@)|(\.)|(\,)|(\-)|(\+)|(\=)|(\[)|(\])|(\{)|(\})|(\/)|(\!)|(\?)|(\^)|(\&)|(\*)|(\))|(\()|(\:)|(\;)|(\")|(\~)|(\`)|(\»)|(\«)|(\—)|(\%)]{1,}/isu"
	);

/*
Именительный	Номинатив (Nominative)		Есть		Кто? Что?
Родительный		Генитив (Genitive)			Нет			Кого? Чего?
Дательный		Датив (Dative)				Давать		Кому? Чему?
Винительный		Аккузатив (Accusative)		Винить		Кого? Что?
Творительный	Аблатив (Instrumentative)	Доволен		Кем? Чем?
Предложный		Препозитив (Preposition)	Думаю		О ком? О чём?; В ком? В чём?
*/
	
	function __construct()
	{
		//echo 123;
		}
	
	public function loadText($i=0,$text='') {
		$this->source[$i]=$text;
		$this->text[$i]['editable']=$this->source[$i];
		}
	
	public function getResText($i) {
		return $this->text[$i]['editable'];
		}
	
	public function removeUnprint($i) {
		$this->text[$i]['editable']=strtr(strtr($this->text[$i]['editable'], $this->sym['unprint']), $this->sym['unprint']);
		}
	
	public function removeTags($i,$enable_tags='<a>') {
		$this->text[$i]['editable']=strip_tags($this->text[$i]['editable'],$enable_tags);
		}
	
	public function changeInText($i,$change=array()) {
		$this->text[$i]['editable']=strtr($this->text[$i]['editable'],$change);
		}
	
	public function regReplace($i,$from,$to) {
		$this->text[$i]['editable']=preg_replace($from,$to,$this->text[$i]['editable']);
		}
	
	public function explodeTextForPhrases($i,$separator) {
		$phrase=explode($separator,$this->text[$i]['editable']);
		if(count($phrase)) {
			//$this->text[$i]['phrase']
			foreach($phrase as $ph) {
				$this->text[$i]['phrase'][]=trim($ph);
				}
			}
		}
	
	public function explodePhrasesForWords($i,$separator) {
		if(count($this->text[$i]['phrase'])) {
			foreach($this->text[$i]['phrase'] as $i=>$ph) {
				$words=explode($separator,$ph);
				foreach($words as $word) {
					$this->text[$i]['word'][strip_tags(mb_strtolower(trim($word),'UTF-8'))]++;
					}
				}
			unset($this->text[$i]['word']['']);
			}
		}
	
	
	/* --------- новый код закончен --------- */
	
	public function change_str($str) {
		$str=mb_strtolower($str,'UTF-8');
		$str=strtr($str, $this->unprint);
		$str=' '.$str.' ';
		$str=preg_replace("#http://[^<\s\n]+#",$this->none_symbol,$str);
		$str=preg_replace($this->symbol_str,$this->none_symbol,$str);
		$str=preg_replace($this->word_str,$this->none_symbol,$str);
		$str=preg_replace($this->black_str,$this->none_symbol,$str);
		$str=preg_replace($this->personal_from,$this->personal_to,$str);
		$str=preg_replace("/\s{1,}[а-яё0-9]{1,3}\s{1,}/isu",$this->none_symbol,$str);
		//$status->text=strtr($status->text, $this->symbols);
		$str=strtr($str, $this->unprint);
		return $str;
		}

}

?>