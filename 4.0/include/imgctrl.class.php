<?

class Imgctrl {

public $imgs=array();

	function __construct()
	{
		
		}
	
	/*
	public function img($id)
	{
		return $this->imgs[$id];
		}
	*/
		
	public function load($id,$file,$params=array())
	{
		@imagedestroy($this->imgs[$id]);
		
		$this->imgs[$id]=new stdClass();
		if(count($params)) {
			foreach($params as $key=>$value) {
				$this->imgs[$id]->$key=$value;
				}
			}
		$this->imgs[$id]->file=$file;
		$this->imgs[$id]->info=getimagesize($this->imgs[$id]->file);
		$this->imgs[$id]->w=$this->imgs[$id]->info[0];
		$this->imgs[$id]->h=$this->imgs[$id]->info[1];
		
		switch($this->imgs[$id]->info['mime']) {
			case 'image/jpeg': {
				@$this->imgs[$id]->img=imagecreatefromjpeg($this->imgs[$id]->file);
				$this->imgs[$id]->mime_type=image_type_to_mime_type(IMAGETYPE_JPEG);
				//@$this->exif=read_exif_data($url);
				}
				break;
			case 'image/gif': {
				@$this->imgs[$id]->img=imagecreatefromgif($this->imgs[$id]->file);
				$this->imgs[$id]->mime_type=image_type_to_mime_type(IMAGETYPE_GIF);
				}
				break;
			case 'image/png': {
				@$this->imgs[$id]->img=imagecreatefrompng($this->imgs[$id]->file);
				$this->imgs[$id]->mime_type=image_type_to_mime_type(IMAGETYPE_PNG);
				}
				break;
			default: {
				@$this->imgs[$id]->img=imagecreatefrompng($this->imgs[$id]->file);
				$this->imgs[$id]->mime_type=image_type_to_mime_type(IMAGETYPE_PNG);
				}
				break;
			}
		
		return true;
		}
	
	public function create_img($id,$w,$h,$params=array(
		'antialias'=>false,
		))
	{
		@imagedestroy($this->imgs[$id]->img);
		unset($this->imgs[$id]);
		
		$this->imgs[$id]=new stdClass();
		$this->imgs[$id]->w=$w;
		$this->imgs[$id]->h=$h;
		
		$this->imgs[$id]->img=imagecreatetruecolor($this->imgs[$id]->w, $this->imgs[$id]->h);
		
		if($params['antialias']) {
			imageantialias($this->imgs[$id]->img, true);
			} else {
				imageAlphaBlending($this->imgs[$id]->img, false);
				imageSaveAlpha($this->imgs[$id]->img, true);
				}
		//$transparent = imagecolorallocatealpha($this->imgs[$id]->img, 255, 255, 255, 127);
		//imagefilledrectangle($this->imgs[$id]->img, 0, 0, $this->imgs[$id]->w, $this->imgs[$id]->h, $transparent);
		//imagefill($this->imgs[$id]->img, 0, 0, imagecolorallocate($this->imgs[$id]->img, 255, 255, 255));
		
		if(count($params)) {
			foreach($params as $key=>$value) {
				$this->imgs[$id]->$key=$value;
				}
			}
		
		return true;
		}
	
	/* --------------- */
	
	public function px($id,$x,$y,$color)
	{
		return imagesetpixel($this->imgs[$id]->img,$x,$y,imagecolorallocate($this->imgs[$id]->img, $color['r'], $color['g'], $color['b']));
		}
	
	public function pxa($id,$x,$y)
	{
		return imagesetpixel($this->imgs[$id]->img,$x,$y,imageColorAllocateAlpha($this->imgs[$id]->img, 0, 0, 0, 127));
		}
	
	public function get_px($id,$x,$y)
	{
		$c_=imagecolorsforindex($this->imgs[$id]->img,imagecolorat($this->imgs[$id]->img,$x,$y));
		return array('r'=>$c_['red'],'g'=>$c_['green'],'b'=>$c_['blue'],'a'=>$c_['alpha']);
		}
	
	public function copy_block($new_id,$new_x,$new_y,$new_w,$new_h,	$id,$x,$y,$w,$h)
	{
		return imagecopyresampled(
			$this->imgs[$new_id]->img,	// Идентификатор нового изображения
			$this->imgs[$id]->img,	// Идентификатор исходного изображения
			$new_x,$new_y,		// Координаты (x,y) верхнего левого угла в новом изображении
			$x,$y,		// Координаты (x,y) верхнего левого угла копируемого блока существующего изображения
			$new_w,$new_h,	// Новые ширина и высота копируемого блока
			$w,$h	// ширина и высота копируемого блока
			);
		}
	
	public function calcWbyH($id,$h)
	{
		return round($this->imgs[$id]->w*($h/$this->imgs[$id]->h));
		}
	
	public function calcHbyW($id,$w)
	{
		return round($this->imgs[$id]->h*($w/$this->imgs[$id]->w));
		}
	
	/* --------------- */
	
	public function watermark($id,$wm_file)
	{
		$wm=md5(date("U"));
		$wm_=$wm.'_';
		$this->load($wm_,$wm_file,array());
		$this->create_img($wm, $this->imgs[$wm_]->w, $this->imgs[$wm_]->h, array());
		$this->copy_block(	$wm, 0, 0, $this->imgs[$wm]->w, $this->imgs[$wm]->h,
							$wm_, 0, 0, $this->imgs[$wm_]->w,$this->imgs[$wm_]->h);
		$this->destroy_img($wm_);
		
		imageAlphaBlending($this->imgs[$id]->img, true);
		imageSaveAlpha($this->imgs[$id]->img, false);
		
		$_w=round($this->imgs[$id]->w/3.5);
		$_h=$this->calcHbyW($wm,$_w);
		
		$this->copy_block(	$id, ($this->imgs[$id]->w-$_w-5), ($this->imgs[$id]->h-$_h-5), ($_w), ($_h),
							$wm, 0, 0, $this->imgs[$wm]->w,$this->imgs[$wm]->h);
		//$this->save2PNG($wm,'img/yumboo.com/123.png');
		$this->destroy_img($wm);
		return true;
		}
	
	public function filter($id,$filter_arr=array())
	{ //
		if(count($filter_arr)) {
			foreach($filter_arr as $fid=>$fparam) {
				switch(strtolower($fid)) {
					
					case 'negate': { // негатив
						imagefilter($this->imgs[$id]->img,IMG_FILTER_NEGATE);
						break;
						}
					
					case 'grayscale': { // серые тона
						imagefilter($this->imgs[$id]->img,IMG_FILTER_GRAYSCALE);
						break;
						}
					
					case 'brightness': { // яркость
						imagefilter($this->imgs[$id]->img,IMG_FILTER_BRIGHTNESS,$fparam['level']); // Диапазон яркости: -255 (темнее)...255(светлее)
						break;
						}
					
					case 'contrast': { // контраст
						imagefilter($this->imgs[$id]->img,IMG_FILTER_CONTRAST,$fparam['level']); // Диапазон контраста: -100...100
						break;
						}
					
					case 'colorize': { // цветовая схема (доступен с версии php 5.2.5)
						if(isset($fparam['a'])) {
							// Диапазон для каждого цвета: 0...255 Диапазон прозрачности: 0...127
							imagefilter($this->imgs[$id]->img,IMG_FILTER_COLORIZE,$fparam['r'],$fparam['g'],$fparam['b'],$fparam['a']);
							} else {
								imagefilter($this->imgs[$id]->img,IMG_FILTER_COLORIZE,$fparam['r'],$fparam['g'],$fparam['b']);
								}
						break;
						}
					
					case 'edgedetect': {
						imagefilter($this->imgs[$id]->img,IMG_FILTER_EDGEDETECT);
						break;
						}
					
					case 'emboss': {
						imagefilter($this->imgs[$id]->img,IMG_FILTER_EMBOSS);
						break;
						}
					
					case 'gaussian_blur': {
						imagefilter($this->imgs[$id]->img,IMG_FILTER_GAUSSIAN_BLUR);
						break;
						}
					
					case 'selective_blur': {
						imagefilter($this->imgs[$id]->img,IMG_FILTER_SELECTIVE_BLUR);
						break;
						}
					
					case 'mean_removal': {
						imagefilter($this->imgs[$id]->img,IMG_FILTER_MEAN_REMOVAL);
						break;
						}
					
					case 'smooth': {
						imagefilter($this->imgs[$id]->img,IMG_FILTER_SMOOTH,$fparam['level']);// int -8..8
						break;
						}
					
					case 'pixelate': {
						imagefilter($this->imgs[$id]->img,IMG_FILTER_PIXELATE,$fparam['size'],$fparam['effect']); // int, true/false
						break;
						}
						
					default: {
						
						break;
						}
					
					}
				}
			}
		return true;
		}
	
	/* --------------- */

	public function save2JPG($id,$file,$quality=100)
	{
		return imagejpeg($this->imgs[$id]->img,$file,$quality);
		}

	public function save2GIF($id,$file)
	{
		return imagegif($this->imgs[$id]->img,$file);
		}
	
	public function save2PNG($id,$file)
	{
		return imagepng($this->imgs[$id]->img,$file);
		}
	
	public function getJPG($id)
	{
		Header('Content-Type: image/jpeg');
		imagejpeg($this->imgs[$id]->img);
		}

	public function getGIF($id)
	{
		Header('Content-Type: image/gif');
		imagegif($this->imgs[$id]->img);
		}
	
	public function getPNG($id)
	{
		Header('Content-Type: image/png');
		imagepng($this->imgs[$id]->img);
		}

	public function base64_img($id)
	{
		//return 'data:'.$this->imgs[$id]->info['mime'].';base64,'.chunk_split(base64_encode($this->imgs[$id]->img));
		return chunk_split(base64_encode($this->imgs[$id]->img));
		}
	
	/* --------------- */
	
	public function destroy_img($id)
	{
		return imagedestroy($this->imgs[$id]);
		}
	
	public function destroy()
	{
		if(count($this->imgs)) {
			foreach($this->imgs as $id=>$img) {
				@imagedestroy($this->imgs[$id]);
				}
			}
		unset($this);
		}

}

?>