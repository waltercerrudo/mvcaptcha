<?php
/******************************************************************
Projectname:   CAPTCHA class
Version:       2.0
Author:        Pascal Rehfeldt <Pascal@Pascal-Rehfeldt.com>
Last modified: 15. January 2006

* GNU General Public License (Version 2, June 1991)
*
* This program is free software; you can redistribute
* it and/or modify it under the terms of the GNU
* General Public License as published by the Free
* Software Foundation; either version 2 of the License,
* or (at your option) any later version.
*
* This program is distributed in the hope that it will
* be useful, but WITHOUT ANY WARRANTY; without even the
* implied warranty of MERCHANTABILITY or FITNESS FOR A
* PARTICULAR PURPOSE. See the GNU General Public License
* for more details.

Description:
This class can generate CAPTCHAs, see README for more details!
******************************************************************/
error_reporting(E_ALL);

require('./class/captcha-2.0.1/filter.class.php');		//CHANGED BY WALTER CERRUDO 9/11/2010
require('./class/captcha-2.0.1/error.class.php');		//CHANGED BY WALTER CERRUDO 9/11/2010

class captcha
{
	var $Length;
	var $CaptchaString;
	var $FileName;
	var $fontpath;
	var $fonts;
	function captcha ($length = 4)			//CHANGED BY WALTER CERRUDO 9/11/2010
	{
		//header('Content-type: image/png'); CHANGED BY WALTER CERRUDO 9/11/2010
		$this->Length   = $length;
		//$this->fontpath = dirname($_SERVER['SCRIPT_FILENAME']) . './fonts/';
		$this->fontpath = './fonts/';
		$this->fonts    = $this->getFonts();
		$errormgr       = new error;
		if ($this->fonts == FALSE)
		{
			//$errormgr = new error;
			$errormgr->addError('No fonts available!');
			$errormgr->displayError();
			die();
		}
		if (function_exists('imagettftext') == FALSE)
		{
			$errormgr->addError('');
			$errormgr->displayError();
			die();
		}
		$this->stringGen();
		$this->makeCaptcha();
	} //captcha

	function getFonts ()
	{
		$fonts = array();
		if ($handle = @opendir($this->fontpath))
		{
			while (($file = readdir($handle)) !== FALSE)
			{
				$extension = strtolower(substr($file, strlen($file) - 3, 3));
				if ($extension == 'ttf')
				{
					$fonts[] = $file;
				}
			}
			closedir($handle);
		}
		else
		{
			return FALSE;
		}
		if (count($fonts) == 0)
		{
			return FALSE;
		}
		else
		{
			return $fonts;
		}
	} //getFonts

	function getRandFont ()
	{
		return $this->fontpath . $this->fonts[rand(0,count($this->fonts) - 1)];
	} //getRandFont

	function stringGen ()
	{
		$uppercase  = range('A', 'Z');
		$lowercase  = range('a', 'z');			//UNCOMMENT BY WALTER CERRUDO 9/11/2010
		//$numeric    = range(0, 9);			//COMMENT BY WALTER CERRUDO 9/11/2010
		$CharPool   = array_merge($uppercase, $lowercase);
		$PoolLength = count($CharPool) - 1;
		for ($i = 0; $i < $this->Length; $i++)
		{
			$this->CaptchaString .= $CharPool[mt_rand(0, $PoolLength)];
		}
	} //StringGen

	function makeFileName($n=16)
	{
		$numbers  = range('0', '9');
		$uppercase  = range('A', 'F');			//UNCOMMENT BY WALTER CERRUDO 9/11/2010
		$CharPool   = array_merge($uppercase, $numbers);
		$PoolLength = count($CharPool) - 1;
		for ($i = 0; $i < $n; $i++)
		{
			$this->FileName .= $CharPool[mt_rand(0, $PoolLength)];
		}
	} //StringGen
	
	function makeCaptcha ()
	{
		
		$imagelength = $this->Length * 45 + 200;			//CHANGED BY WALTER CERRUDO 9/11/2010
		$imageheight = 75;
		$image       = imagecreate($imagelength, $imageheight);
		$bgcolor     = imagecolorallocate($image, 255, 255, 255);
		$stringcolor = imagecolorallocate($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));		//CHANGED BY WALTER CERRUDO 9/11/2010
		$filter      = new filters;
		$filter->signs($image, $this->getRandFont());
		for ($i = 0; $i < strlen($this->CaptchaString); $i++)
		{
			$stringcolor = imagecolorallocate($image, mt_rand(75, 150), mt_rand(75, 150), mt_rand(75, 150));	//ADDED BY WALTER CERRUDO 9/11/2010
			imagettftext($image, 35, mt_rand(-15, 15), $i * 45 + 100,mt_rand(40, 70),$stringcolor,$this->getRandFont(),$this->CaptchaString{$i}); 	//CHANGED BY WALTER CERRUDO 9/11/2010
		}
		imagecolortransparent($image,hexdec('FFFFFF'));					//ADDED BY WALTER CERRUDO 9/11/2010
		$this->makeFileName();
		imagepng($image,'./tmp/'.$this->FileName.'.png');							//COMMENT BY WALTER CERRUDO 9/11/2010
		imagedestroy($image);
	} //MakeCaptcha

	function getCaptchaString ()
	{
		return $this->CaptchaString;
	} //GetCaptchaString
} //class: captcha

?>