<?php

//
// class.imagemask.php
// version 1.0.0, 19th January, 2004
//
// Description
//
// This is a class allows you to apply a mask to an image much like you could
// do in PhotoShop, Gimp, or any other such image manipulation programme.  The
// mask is converted to grayscale so it's best to use black/white patterns.
// If the mask is smaller than the image then the mask can be placed in various
// positions (top left, left, top right, left, centre, right, bottom left,
// bottom, bottom right) or the mask can be resized to the dimensions of the
// image.
//
// Requirements
//
// This class NEEDS GD 2.0.1+ (preferrably the version bundled with PHP)
//
// Notes
//
// This class has to copy an image one pixel at a time.  Please bare in mind
// that this process may take quite some time on large images, so it's probably
// best that it's used on thumbnails and smaller images.
//
// Author
//
// Andrew Collington, 2004
// php@amnuts.com, http://php.amnuts.com/
//
// Feedback
//
// There is message board at the following address:
//
//    http://php.amnuts.com/forums/index.php
//
// Please use that to post up any comments, questions, bug reports, etc.  You
// can also use the board to show off your use of the script.
//
// Support
//
// If you like this script, or any of my others, then please take a moment
// to consider giving a donation.  This will encourage me to make updates and
// create new scripts which I would make available to you.  If you would like
// to donate anything, then there is a link from my website to PayPal.
//
// Example of use
//
//    require 'class.imagemask.php';
//    $im = new imageMask('ffffff');
//    $im->maskOption(mdCENTRE);
//    if ($im->loadImage(dirname(__FILE__) . "/pictures/{$_POST['file']}"))
//    {
//        if ($im->applyMask(dirname(__FILE__) . "/masks/{$_POST['mask']}"))
//        {
//            $im->showImage('png');
//        }
//    }
//



define('mdTOPLEFT',     0);
define('mdTOP',         1);
define('mdTOPRIGHT',    2);
define('mdLEFT',        3);
define('mdCENTRE',      4);
define('mdCENTER',      4);
define('mdRIGHT',       5);
define('mdBOTTOMLEFT',  6);
define('mdBOTTOM',      7);
define('mdBOTTOMRIGHT', 8);
define('mdRESIZE',      9);


class imageMask
{
    var $_colours;
    var $_img;
    var $_mask;
    var $_bgc;
    var $_showDebug;
    var $_maskDynamic;
    
    
    /**
    * @return imageMask
    * @param string $bg
    * @desc Class constructor.  Pass the background colour as an HTML colour string.
    */
    function imageMask($bg = 'FFFFFF')
    {
        $this->setDebugging(false);
        $this->maskOption(mdCENTER);
        $this->_colours = array();
        $this->_img     = array();
        $this->_mask    = array();
        $this->_bgc     = $this->_htmlHexToBinArray($bg);
    }
    
	/**
    * @return boolean
    * @desc Comprueba si se ingresaron datos
    */	
    function crop(){

	}
    /**
    * @return void
    * @param bool $do
    * @desc Toggles debugging
    */
    function setDebugging($do = false)
    {
        $this->_showDebug = ($do === true) ? true : false;
    }
    
    
    /**
    * @return bool
    * @param string $filename
    * @desc Load an image from the file system - method based on file extension
    */
    function loadImage($filename)
    {
        return ($this->_realLoadImage($filename, $this->_img['orig']));
    }
    
    
    /**
    * @return bool
    * @param string $string
    * @desc Load an image from a string (eg. from a database table)
    */
    function loadImageFromString($string)
    {
        $this->_img['orig'] = @ImageCreateFromString($string);
        if ($this->_img['orig'])
        {
            return true;
        }
        else
        {
            $this->_debug('loadImageFromString', 'The original image could not be loaded.');
            return false;
        }
    }
    
    
    /**
    * @return bool
    * @param string $filename
    * @param int $quality
    * @desc Save the masked image
    */
    function saveImage($filename, $quality = 100)
    {
        if ($this->_img['final'] == null)
        {
            $this->_debug('saveImage', 'There is no processed image to save.');
            return false;
        }

        $ext = strtolower($this->_getExtension($filename));
        $func = "image$ext";
        
        if (!@function_exists($func))
        {
            $this->_debug('saveImage', "That file cannot be saved with the function '$func'.");
            return false;
        }

        $saved = ($ext == 'png') ? $func($this->_img['final'], $filename) : $func($this->_img['final'], $filename, $quality);
        if ($saved == false)
        {
            $this->_debug('saveImage', "Could not save the output file '$filename' as a $ext.");
            return false;
        }

        return true;
    }
    
    
    /**
    * @return bool
    * @param string $type
    * @param int $quality
    * @desc Shows the masked image without any saving
    */
    function showImage($type = 'png', $quality = 100)
    {
        $type = strtolower($type);
        if ($this->_img['final'] == null)
        {
            $this->_debug('showImage', 'There is no processed image to show.');
        }
        else if ($type == 'png')
        {
        	header('Content-type: image/png');
            echo @imagepng($this->_img['final']);
            return true;
        }
        else if ($type == 'jpg' || $type == 'jpeg')
        {
            header('Content-type: image/jpeg');
            echo @imagejpeg($this->_img['final'], '', $quality);
            return true;
        }
        else
        {
            $this->_debug('showImage', "Could not show the output file as a $type.");
        }

        return false;
    }
    
    
    /**
    * @return void
    * @param int $do
    * @desc Set the mask overlay option (position or resize to image size)
    */
    function maskOption($do = mdCENTER)
    {
        $this->_maskDynamic = $do;
    }
    
    
    /**
    * @return bool
    * @param string $filename
    * @desc Apply the mask to the image
    */
    function applyMask($filename)
    {
        if ($this->_img['orig'])
        {
            if ($this->_generateInitialOutput())
            {
                if ($this->_realLoadImage($filename, $this->_mask['orig']))
                {
                    if ($this->_getMaskImage())
                    {
                        $sx = imagesx($this->_img['final']);
                        $sy = imagesy($this->_img['final']);
                        
                        //set_time_limit(120);
                        for ($x = 0; $x < $sx; $x++)
                        {
                            for ($y = 0; $y < $sy; $y++)
                            {
                                $thres = $this->_pixelAlphaThreshold($this->_mask['gray'], $x, $y);
                                if (!in_array($thres, array_keys($this->_colours))) {
                                    $this->_colours[$thres] = imagecolorallocatealpha($this->_img['final'], $this->_bgc[0], $this->_bgc[1], $this->_bgc[2], $thres);
                                }
                                imagesetpixel($this->_img['final'], $x, $y, $this->_colours[$thres]);
                            }
                        }
                        return true;
                    }
                    else
                    {
                        $this->_debug('applyMask', 'The grayscale mask could not be created.');
                    }
                }
            }
        }
        else
        {
            $this->_debug('applyMask', 'The original image has not been loaded.');
        }
        return false;
    }
    
    
    /**
    * @return bool
    * @param string $filename
    * @param pointer $img
    * @desc Enter description here...
    */
    function _realLoadImage($filename, &$img)
    {
        if (!@file_exists($filename))
        {
            $this->_debug('_realLoadImage', "The supplied filename '$filename' does not point to a readable file.");
            return false;
        }
        
        $ext  = strtolower($this->_getExtension($filename));
        $func = "imagecreatefrom$ext";
        
        if (!@function_exists($func))
        {
            $this->_debug('_realLoadImage', "That file cannot be loaded with the function '$func'.");
            return false;
        }
        
        $img = @$func($filename);
        return ($img) ? true : false;
    }
    
    
    /**
    * @return bool
    * @desc Copies the original image into the final image ready for the mask overlay
    */
    function _generateInitialOutput()
    {
        if ($this->_img['orig'])
        {
            $isx = imagesx($this->_img['orig']);
            $isy = imagesy($this->_img['orig']);
            $this->_img['final'] = imagecreatetruecolor($isx, $isy);
            $bg_color = hexdec('ffffff');
			imagecolortransparent($this->_img['final'], $bg_color);
            imagefill($this->_img['final'], 0, 0, $color);
            if ($this->_img['final'])
            {
                imagealphablending($this->_img['final'], true);
                imagecopy($this->_img['final'], $this->_img['orig'], 0, 0, 0, 0, $isx, $isy);
                return true;
            }
            else
            {
                $this->_debug('_generateInitialOutput', 'The final image (without the mask) could not be created.');
            }
        }
        else
        {
            $this->_debug('_generateInitialOutput', 'The original image has not been loaded.');
        }
        return false;
    }
    
    
    /**
    * @return bool
    * @desc Creates the mask image and determines position and size of mask
    *       based on the _maskOption value and image size.  If the image is
    *       smaller than the mask (and the mask isn't set to resize) then the
    *       mask defaults to the top-left position and will be cut off.
    */
    function _getMaskImage()
    {
        $isx = imagesx($this->_img['final']);
        $isy = imagesy($this->_img['final']);
        $msx = imagesx($this->_mask['orig']);
        $msy = imagesy($this->_mask['orig']);
        
        $this->_mask['gray'] = imagecreatetruecolor($isx, $isy);
        imagefill($this->_mask['gray'], 0, 0, imagecolorallocate($this->_mask['gray'], 0, 0, 0));
        
        if ($this->_mask['gray'])
        {
          
            switch($this->_maskDynamic)
            {
                case mdTOPLEFT:
                    $sx = $sy = 0;
                    break;
                case mdTOP:
                    $sx = ceil(($isx - $msx) / 2);
                    $sy = 0;
                    break;
                case mdTOPRIGHT:
                    $sx = ($isx - $msx);
                    $sy = 0;
                    break;
                case mdLEFT:
                    $sx = 0;
                    $sy = ceil(($isy - $msy) / 2);
                    break;
                case mdCENTRE:
                    $sx = ceil(($isx - $msx) / 2);
                    $sy = ceil(($isy - $msy) / 2);
                    break;
                case mdRIGHT:
                    $sx = ($isx - $msx);
                    $sy = ceil(($isy - $msy) / 2);
                    break;
                case mdBOTTOMLEFT:
                    $sx = 0;
                    $sy = ($isy - $msy);
                    break;
                case mdBOTTOM:
                    $sx = ceil(($isx - $msx) / 2);
                    $sy = ($isy - $msy);
                    break;
                case mdBOTTOMRIGHT:
                    $sx = ($isx - $msx);
                    $sy = ($isy - $msy);
                    break;
            }
            if ($isx < $msx)
            {
                $sx = 0;
            }
            if ($isy < $msy)
            {
                $sy = 0;
            }
            if ($this->_maskDynamic == mdRESIZE)
            {
                $this->_mask['temp'] = imagecreatetruecolor($isx, $isy);
                imagecopyresampled($this->_mask['temp'], $this->_mask['orig'], 0, 0, 0, 0, $isx, $isy, $msx, $msy);
                imagecopymergegray($this->_mask['gray'], $this->_mask['temp'], 0, 0, 0, 0, $isx, $isy, 100);
                imagedestroy($this->_mask['temp']);
            }
            else
            {
            	$sx = 0;
                $sy = 0;
                imagecopymergegray($this->_mask['gray'], $this->_mask['orig'], $sx, $sy, 0, 0, $msx, $msy, 100);
            }
            return true;
        }
        return false;
    }
    
    
    /**
    * @return int
    * @param resource $img
    * @param int $x
    * @param int $y
    * @desc Determines the colour value of a pixel and returns the required value for the alpha overlay
    */
    function _pixelAlphaThreshold($img, $x, $y)
    {
        
        $rgb = imagecolorat($img, $x, $y);
        $r   = ($rgb >> 16) & 0xFF;
        $g   = ($rgb >> 8) & 0xFF;
        $b   = $rgb & 0xFF;
        $ret = round(($r + $g + $b) / 6);
        return ($ret > 1) ? ($ret - 1) : 0;
    }
    
    
    /**
    * @return array
    * @param string $hex
    * @desc Converts an HTML hex colour value to an array of integers
    */
    function _htmlHexToBinArray($hex)
    {
        $hex = @preg_replace('/^#/', '', $hex);
        for ($i=0; $i<3; $i++)
        {
            $foo = substr($hex, 2*$i, 2);
            $rgb[$i] = 16 * hexdec(substr($foo, 0, 1)) + hexdec(substr($foo, 1, 1));
        }
        return $rgb;
    }
    
    
    /**
    * @return string
    * @param string $filename
    * @desc Get the extension of a file name
    */
    function _getExtension($filename)
    {
        $ext  = @strtolower(@substr($filename, (@strrpos($filename, ".") ? @strrpos($filename, ".") + 1 : @strlen($filename)), @strlen($filename)));
        return ($ext == 'jpg') ? 'jpeg' : $ext;
    }
    
    
    /**
    * @return void
    * @param string $function
    * @param string $string
    * @desc Shows debugging information
    */
    function _debug($function, $string)
    {
        if ($this->_showDebug)
        {
            echo "<p><strong style=\"color:#FF0000\">Error in function $function:</strong> $string</p>\n";
        }
    }
    
    
}

?>