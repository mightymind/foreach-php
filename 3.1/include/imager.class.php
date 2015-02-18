<?

class Imager {

//IMAGETYPE_GIF	image/gif
//IMAGETYPE_JPEG	image/jpeg
//IMAGETYPE_PNG	image/png
//IMAGETYPE_SWF	application/x-shockwave-flash
//IMAGETYPE_PSD	image/psd
//IMAGETYPE_BMP	image/bmp
//IMAGETYPE_TIFF_II (intel byte order)	image/tiff
//IMAGETYPE_TIFF_MM (motorola byte order) 	image/tiff
//IMAGETYPE_JPC	application/octet-stream
//IMAGETYPE_JP2	image/jp2
//IMAGETYPE_JPX	application/octet-stream
//IMAGETYPE_JB2	application/octet-stream
//IMAGETYPE_SWC	application/x-shockwave-flash
//IMAGETYPE_IFF	image/iff
//IMAGETYPE_WBMP	image/vnd.wap.wbmp
//IMAGETYPE_XBM	image/xbm
//IMAGETYPE_ICO	image/vnd.microsoft.icon

public $new_width;
public $new_height;
public $width;
public $height;
public $bits;
public $pixels;
public $url;
public $image;
public $new_image;
public $type;
public $mime_type;
//public $exif;

	public function load($url) {
		@imagedestroy($this->image);
		$this->url=$url;
		$info=getimagesize($url);
		$this->width=$info[0];
		$this->height=$info[1];
		$this->bits=array_key_exists('bits',$info)?$info['bits']:null;
		$this->pixels=$this->width*$this->height;
		//list($this->width,$this->height)=getimagesize($url);
		if ($this->pixels) {
			switch($info['mime']) {
				case 'image/jpeg': {
					@$this->image=imagecreatefromjpeg($url);
					$this->mime_type=image_type_to_mime_type(IMAGETYPE_JPEG);
					//@$this->exif=read_exif_data($url);
					}
					break;
				case 'image/gif': {
					@$this->image=imagecreatefromgif($url);
					$this->mime_type=image_type_to_mime_type(IMAGETYPE_GIF);
					}
					break;
				case 'image/png': {
					@$this->image=imagecreatefrompng($url);
					$this->mime_type=image_type_to_mime_type(IMAGETYPE_PNG);
					}
					break;
				default: {
					@$this->image=imagecreatefrompng($url);
					$this->mime_type=image_type_to_mime_type(IMAGETYPE_PNG);
					}
					break;
				}
			return true;
			} else {
				return false;
				}
		}
	
	public function _create($w,$h) {
		$this->new_width=$w;
		$this->new_height=$h;
		$this->new_image=imagecreatetruecolor($this->new_width, $this->new_height);
		imagefill($this->new_image, 0, 0, imagecolorallocate($this->new_image, 255, 255, 255));
	}
	
	public function _write($text,$fontcolor,$w,$h) {
		$fontcolor=$this->getcolorsfromstring($fontcolor);
		return imagettftext(
			$this->new_image,
			12,				// размер шрифта
			0,				// угол наклона шрифта
			$w,$h,		// координаты (x,y), соответствующие левому нижнему углу первого символа
			imagecolorallocate($this->new_image, $fontcolor[0], $fontcolor[1], $fontcolor[2]),		// цвет шрифта
			'/home/m/mightymind/azbn.ru/public_html/cron/times.ttf',	// имя ttf-файла
			$text);
	}
	
	public function _png() {
		imagepng($this->new_image);
	}
	
	public function _jpg() {
		imagejpeg($this->new_image);
	}
	
	public function _gif() {
		imagegif($this->new_image);
	}
	
	public function _copy($url,$x,$y,$w,$h) {
		$this->load($url);
		imagecopyresampled(
			$this->new_image,	// Идентификатор нового изображения
			$this->image,	// Идентификатор исходного изображения
			$x,$y,		// Координаты (x,y) верхнего левого угла в новом изображении
			0,0,		// Координаты (x,y) верхнего левого угла копируемого блока существующего изображения
			$w,$h,	// Новые ширина и высота копируемого блока
			$this->width,$this->height	// ширина и высота копируемого блока
			);
	}
	
	public function filter($id,$x=null,$y=null,$z=null) {
		/*
		
IMG_FILTER_NEGATE: Reverses all colors of the image. 
IMG_FILTER_GRAYSCALE: Converts the image into grayscale. 
IMG_FILTER_BRIGHTNESS: Changes the brightness of the image. Use arg1 to set the level of brightness. 
IMG_FILTER_CONTRAST: Changes the contrast of the image. Use arg1 to set the level of contrast. 
IMG_FILTER_COLORIZE: Like IMG_FILTER_GRAYSCALE, except you can specify the color. Use arg1, arg2 and arg3 in the form of red, green, blue and arg4 for the alpha channel. The range for each color is 0 to 255. 
IMG_FILTER_EDGEDETECT: Uses edge detection to highlight the edges in the image. 
IMG_FILTER_EMBOSS: Embosses the image. 
IMG_FILTER_GAUSSIAN_BLUR: Blurs the image using the Gaussian method. 
IMG_FILTER_SELECTIVE_BLUR: Blurs the image. 
IMG_FILTER_MEAN_REMOVAL: Uses mean removal to achieve a "sketchy" effect. 
IMG_FILTER_SMOOTH: Makes the image smoother. Use arg1 to set the level of smoothness. 
IMG_FILTER_PIXELATE: Applies pixelation effect to the image, use arg1 to set the block size and arg2 to set the pixelation effect mode.
		
		*/
		switch($id) {
			case 0: {
				imagefilter($this->new_image,IMG_FILTER_NEGATE);
				break;
				}
			case 1: {
				imagefilter($this->new_image,IMG_FILTER_GRAYSCALE);
				break;
				}
			case 2: {
				imagefilter($this->new_image,IMG_FILTER_BRIGHTNESS,$x);
				break;
				}
			case 3: {
				imagefilter($this->new_image,IMG_FILTER_CONTRAST,$x);
				break;
				}
			case 4: {
				imagefilter($this->new_image,IMG_FILTER_COLORIZE,$x,$y,$z);
				break;
				}
			case 5: {
				imagefilter($this->new_image,IMG_FILTER_EDGEDETECT);
				break;
				}
			case 6: {
				imagefilter($this->new_image,IMG_FILTER_EMBOSS);
				break;
				}
			case 7: {
				imagefilter($this->new_image,IMG_FILTER_MEAN_REMOVAL,$x,$y,$z);
				break;
				}
			case 8: {
				imagefilter($this->new_image,IMG_FILTER_SMOOTH,$x,$y,$z);
				break;
				}
			case 9: {
				imagefilter($this->new_image,IMG_FILTER_PIXELATE,$x,$y,$z);
				break;
				}
			default: {
				
				break;
				}
			}
	}
	
	public function destroy() {
		@imagedestroy($this->image);
		@imagedestroy($this->new_image);
		unset($this);
	}

	public function getcolorsfromstring($color) {
		$r=sscanf($color,"%2x%2x%2x");
		$red=(array_key_exists(0,$r) && is_numeric($r[0])?$r[0]:0);
		$green=(array_key_exists(1,$r) && is_numeric($r[1])?$r[1]:0);
		$blue=(array_key_exists(2,$r) && is_numeric($r[2])?$r[2]:0);
		return array($red, $green, $blue);
	}	

	public function CalcWByH($h) {
		$percent=$h/$this->height;
		$new_width=round($this->width*$percent);
		return $new_width;
	}

	public function CalcHByW($w) {
		$percent=$w/$this->width;
		$new_height=round($this->height*$percent);
		return $new_height;
	}

	public function CalcSizeForNewImage($w,$h) {
		$h_by_w=$this->CalcHByW($w);
		$w_by_h=$this->CalcWByH($h);
		if (($h_by_w!=$h) && ($w_by_h!=$w)) {
			$n_w=($w_by_h<$w)?$w_by_h:$w;
			$n_h=$this->CalcHByW($n_w);
			} else {
				$n_w=$w;
				$n_h=$h;
				}
		$this->new_width=$n_w;
		$this->new_height=$n_h;
	}

	public function SizeForNewImage($w,$h) {
		$this->new_width=$w;
		$this->new_height=$h;
	}

	public function CreateImageJPG($image,$file,$quality=100) {
		return imagejpeg($image,$file,$quality);
	}

	public function CreateImageGIF($image,$file) {
		return imagegif($image,$file);
	}

	public function CreateImagePNG($image,$file,$compression=0,$filter=PNG_NO_FILTER) {
		return imagepng($image,$file,$compression,$filter);
	}

	public function DestroyImage($image) {
		return imagedestroy($image);
	}

	public function CreateNewImage() {
		$this->new_image=imagecreatetruecolor($this->new_width, $this->new_height);
		return imagecopyresampled($this->new_image, $this->image, 0, 0, 0, 0, $this->new_width, $this->new_height, $this->width, $this->height);
	}

	public function genEncImageSrc($image,$mime_type) {
		$encoded=chunk_split(base64_encode($image));
		return "data:$mime_type;base64,$encoded";
	}

	public function WriteOnImage($image,$fontcolor,$size,$x,$y,$text) {
		// Цвет фона
		//$bg=imagecolorallocate($image, $bgcolor[0], $bgcolor[1], $bgcolor[2]);
		// Задаем черный цвет для шрифта
		if (!(count($fontcolor)>0)) {$fontcolor=array(0,0,0);}
		if (!$size) {$size=32;}
		$font=imagecolorallocate($image, $fontcolor[0], $fontcolor[1], $fontcolor[2]);
		// Задаем размер шрифта
		//$size=32;
		// Делаем белый цвет прозрачным
		//imagecolortransparent($image,$bg);
		// Наносим надписи на изображение
		return imagestring($image,$size,$x,$y,$text,$font);//imagestringup
	}

}

?>