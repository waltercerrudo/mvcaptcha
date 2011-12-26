<?php

/**
 * this class can be used to resize, crop and add watermarks to images.
 * it can read GIF, JPEG or PNG images and resize them, crop or add a watermark in the initial image.
 * in crop function is used javascript (jquery + jcrop) to select the crop area.
 * the resulting image can be saved to a new JPEG, GIF or PNG image file.
 *
 * @version 1.1 2011/01/21
 * @author rodrigo brandao
 * @link www.rodrigobrandao.com.br
 * @license GNU public license
 *
 * @example resize.php : how to define and resize images
 * @example crop.php : how to crop images
 */
class image {
	
	/**
	 * source image
	 *
	 * @var string|array
	 */
	private $source;
	
	/**
	 * temporay image
	 *
	 * @var file
	 */
	private $image;
	
	/**
	 * erros
	 *
	 * @var array
	 */
	private $error;
	
	/**
	 * construct
	 *
	 * @param string|array $source
	 */
	public function __construct($source = NULL) {
		if($source != NULL) {
			$this->source($source);
		}
	}
	
	/**
	 * set the source image
	 *
	 * @param string|array $source
	 */
	public function source($source) { 
		if(!is_array($source)) {
			$this->source["name"] = $source;
			$this->source["tmp_name"] = $source;
			$type = NULL;
			$ext = strtolower(end(explode(".",$source)));
			switch($ext) {
				case "jpg"  : 
				case "jpeg" : $type = "image/jpeg"; break;
				case "gif"  : $type = "image/gif"; break;
				case "png"  : $type = "image/png"; break;
			}
			$this->source["type"] = $type;
		} else {
			$this->source = $source;
		}
		$this->destination = $this->source["name"];
	}
	
	/**
	 * resize the image
	 *
	 * @param int $width
	 * @param int $height
	 */
	public function resize($width = NULL,$height = NULL) {
		if(isset($this->source["tmp_name"]) && file_exists($this->source["tmp_name"])) {
			list($source_width,$source_height) = getimagesize($this->source["tmp_name"]);
			if(($width == NULL) && ($height != NULL)) {
				$width = ($source_width * $height) / $source_height;
			}
			if(($width != NULL) && ($height == NULL)) {
				$height = ($source_height * $width) / $source_width;
			}
			if(($width == NULL) && ($height == NULL)) {
				$width = $source_width;
				$height = $source_height;
			}
			switch($this->source["type"]) {
				case "image/jpeg" : $created = imagecreatefromjpeg($this->source["tmp_name"]); break;
				case "image/gif"  : $created = imagecreatefromgif($this->source["tmp_name"]);  break;
				case "image/png"  : $created = imagecreatefrompng($this->source["tmp_name"]);  break;
			}
			$this->image = imagecreatetruecolor($width,$height);
			imagecopyresampled($this->image,$created,0,0,0,0,$width,$height,$source_width,$source_height);
		}
	}
	
	/**
	 * add watermark on image
	 *
	 * @param string $mark
	 * @param int $opac
	 * @param int $x_pos
	 * @param int $y_pos
	 */
	public function watermark($mark,$opac,$x_pos,$y_pos) {
		if(file_exists($mark) && ($this->image != "")) {
			$ext = strtolower(end(explode(".",$mark)));
			switch($ext) {
				case "jpg"  : 
				case "jpeg" : $watermark = imagecreatefromjpeg($mark); break;
				case "gif"  : $watermark = imagecreatefromgif($mark);  break;
				case "png"  : $watermark = imagecreatefrompng($mark);  break;
			}
			list($watermark_width,$watermark_height) = getimagesize($mark);
			$source_width = imagesx($this->image);
			$source_height = imagesy($this->image);
			if($x_pos == "top") $pos  = "t"; else $pos  = "b";
			if($y_pos == "left") $pos .= "l"; else $pos .= "r";
			$dest_x = 0; 
			$dest_y = 0; 
			switch($pos) {
				case "tr" : $dest_x = $source_width - $watermark_width; break;
				case "bl" : $dest_y = $source_height - $watermark_height; break;
				case "br" : $dest_x = $source_width - $watermark_width; $dest_y = $source_height - $watermark_height; break;
			}
			imagecopymerge($this->image,$watermark,$dest_x,$dest_y,0,0,$watermark_width,$watermark_height,$opac);
		}
	}
	
	/**
	 * crop the image
	 *
	 * @param int $x
	 * @param int $y
	 * @param int $width
	 * @param int $height
	 */
	public function crop($x,$y,$width,$height) {
		if(isset($this->source["tmp_name"]) && file_exists($this->source["tmp_name"]) && ($width > 10) && ($height > 10)) {
			switch($this->source["type"]) {
				case "image/jpeg" : $created = imagecreatefromjpeg($this->source["tmp_name"]); break;
				case "image/gif"  : $created = imagecreatefromgif($this->source["tmp_name"]);  break;
				case "image/png"  : $created = imagecreatefrompng($this->source["tmp_name"]);  break;
			}			
			$this->image = imagecreatetruecolor($width,$height);
			$bg_color = hexdec('ffffff');
			imagefill($this->image, 0, 0, $bg_color );
			imagecopy($this->image,$created,0,0,$x,$y,$width,$height);
		}
	}
	
	/**
	 * create final image file 
	 *
	 * @param string $destination
	 * @param int $quality
	 */
	public function create($destination,$quality = 100) {
		if($this->image != "") {
			$extension = substr($destination,-3,3);
			switch($extension) {
				case "gif" :  
					imagegif($this->image,$destination,$quality); 
					break;
				case "png" :
					$quality = ceil($quality/10) - 1;
					imagepng($this->image,$destination,$quality); 
					break;
				default    : 
					imagejpeg($this->image,$destination,$quality); 
					break;
			}
		}
	}
	
	/**
	 * check if extension is valid
	 *
	 */
	public function validate_extension() {
		if(isset($this->source["tmp_name"]) && file_exists($this->source["tmp_name"])) {
			$exts = array("image/jpeg","image/gif","image/png");
			$ext = $this->source["type"];
			$valid = 0;
			foreach($exts as $current) {
				if($current == $ext) {
					$valid = 1;
				}
			}
			if($valid != 1) {
				$this->error .= "extension";
			}
		} else {
			$this->error .= "source";
		}
	}
	
	/**
	 * check if the size is correct
	 *
	 * @param int $max
	 */
	public function validate_size($max) {
		if(isset($this->source["tmp_name"]) && file_exists($this->source["tmp_name"])) {
			$max = $max * 1024;
			if($this->source["size"] >= $max) {
				$this->error .= "size";
			}
		} else {
			$this->error .= "source";
		}
	}
	
	/**
	 * check if the dimension is correct
	 *
	 * @param int $limit_width
	 * @param int $limit_height
	 */
	public function validate_dimension($limit_width,$limit_height) {
		if(isset($this->source["tmp_name"]) && file_exists($this->source["tmp_name"])) {
			list($source_width,$source_height) = getimagesize($this->source["tmp_name"]);
			if(($source_width > $limit_width) || ($source_height > $limit_height)) {
				$this->error .= "dimension";
			}
		} else {
			$this->error .= "source";
		}
	}
	
	/**
	 * get the found errors
	 *
	 */
	public function error() {
		$error = NULL;
		if(stristr($this->error,"source")) $error[] = "no selected file";
		if(stristr($this->error,"dimension")) $error[] = "dimensions too large";
		if(stristr($this->error,"extension")) $error[] = "invalid extension";
		if(stristr($this->error,"size")) $error[] = "size too large";
		return $error;
	}
	
}