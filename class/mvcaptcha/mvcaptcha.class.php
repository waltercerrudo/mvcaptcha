<?php

/**
* Clase Principal de la Aplicaci�n.
*
* LICENSE:  This file is part of mvCaptcha.
* mvCaptcha is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* mvCaptcha is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SGU.  If not, see <http://www.gnu.org/licenses/>.
*
* @copyright  Copyright (c) 2010 WALTER CERRUDO (http://walterio.netau.net)
* @license    http://www.gnu.org/licenses/   GPL License
* @version    0.9
* @link       http://walterio.netau.net
* @since      File available since Release 1.0
*/

require('./class/captcha-2.0.1/captcha.class.php');
require './class/imagemask/class.imagemask.php';

class mvcaptcha
{
	var $URLOK;
	var $URLFallo;
	var $labelname;
	var $CaptchaSERVER;
	var $CaptchaUSER;
	var $Debug=false;
	
	
	/**
	* @return void
	* @param string $parLBL nombre del input
	* @desc Constructor MVCAPTCHA
	*/	
	function mvcaptcha($parLBL) {
		$this->labelname=$parLBL;
	}
	/**
    * @return void
    * @param string $parURLOK URL destino si el captcha ingresado es correcto
	* @param string $parURLFALLA URL destino si el captcha ingresado es incorrecto
    * @desc Crea y muestra el MVCAPTCHA
    */
	function vermvcaptcha($parURLOK,$parURLFALLA) {
		$this->URLOK=$parURLOK;
		$this->URLFallo=$parURLFALLA;
		$captcha = new captcha();
		$_SESSION['FILENAME']=$captcha->FileName;
		$_SESSION['CAPTCHAString']=$captcha->CaptchaString;
		$_SESSION['URLOK']=$this->URLOK;
		$_SESSION['URLFALLO']=$this->URLFallo;
		$this->generarMask();
		return $this->getHTML();
		}
	
	/**
    * @return boolean
    * @desc Comprueba si se ingresaron datos
    */	
	function verformulario() {
		return !isset($_POST[$this->labelname]);
		;
	}	
	
	/**
    * @return void
    * @param integer $mask Color sobre el cual aplicar la mascara
    * @desc Aplica la mascara
    */	
	function generarMask() {
	}

		
	/**
    * @return void
    * @desc Compara los captcha y redirecciona seg�n corresponda
    */		
	function proceder() {
		if ($this->getCaptchaSERVER()==$this->getCaptchaUSER())
		{
			header ("Location: ".$_SESSION['URLOK']);
		}
		else 
		{
			header ("Location: ".$_SESSION['URLFALLO']);
		}
		;
	}
	
	/**
    * @return string
    * @desc Devuelve el captcha ingresado por el usuario
    */	
	function getCaptchaUSER() {
		$this->CaptchaUSER=$_POST['captchastring'];
		return $this->CaptchaUSER;
		;
	}

	/**
    * @return string
    * @desc Devuelve el captcha generado por el servidor
    */	
	function getCaptchaSERVER() {
		$this->CaptchaSERVER=$_SESSION['CAPTCHAString'];
		return $this->CaptchaSERVER;
		;
	}	
	
	/**
    * @return string
    * @desc Generar salida HTML
    */	
	function getHTML() {
		$yori1=mt_rand(0,9)/10;
		$yori2=mt_rand(0,9)/10;
		$rndImg=mt_rand(1,4);
		$rndImg=1;
		$html="
			<link href=\"css/styles.css\" rel=\"stylesheet\" type=\"text/css\" >
			<style type=\"text/css\" media=\"screen, projection\">
			#parallax {
				position: relative;
				overflow: hidden;
				width: 100%;
				height: 75px;
			}
			
			#content {
				
			 	}
			.freeze     {  }
			.clasecss{
			   background-color: #ff8800;
			   font-weight: bold;
			}
			 	
			</style>
			
			<script
				type=\"text/javascript\" src=\"./js/jquery-1.3.2.min.js\"></script>
			<script
				type=\"text/javascript\" src=\"./js/jquery.jparallax.091.js\"></script>
			<script type=\"text/javascript\">
			<!--
			var inPullNav = false; 
			var varName = false;
			 $('a').click(function(){
   				 stopParallax = true;
			});
			jQuery(document).ready(function(){
					var corners='';  
					$('body').click(function(){
						varName = true;
					});					
				 	jQuery('#parallax').jparallax({
					 	mouseport: jQuery('body')},
					 		{yparallax: false},
					 		{xtravel:20, yorigin:".$yori1."},
					 		{xtravel: 20, yorigin:".$yori2."}).append(corners);
			});
			//-->
			</script>
			<!--[if lt IE 7]>
			<script type=\"text/javascript\" src=\"js/jquery.ifixpng.js\"></script>
			<script type=\"text/javascript\">jQuery(document).ready(function(){ jQuery('img[@src$=.png]').ifixpng(); });</script>
			<![endif]-->
			<!--[if lt IE 8]>
			<style type=\"text/css\">
			@import \"css/style_ie.css\";
			</style>
			</head>
			</body>
			<![endif]-->
			<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
				<tr>
					<td align=\"center\" class=\"descdet\">
					<div class=\"bordeder\">
						<div id=\"content\"><strong class=\"subder\">MVCaptcha</strong><br>
									Ingresar el texto mostrado en la imagen <br>
									Mueve el Mouse para formar el texto.<br>
									Haciendo click las im&aacute;genes dejar&aacute;n de moverse.
									<br />
									<a href=\"./\"><img src=\"./img/recargar.png\" alt=\"colabora\" width=\"32\" height=\"32\" border=\"0\"></a>
									<div id=\"formulariomayores\" style=\"display: none; \"></div>
									<div id=\"parallax\" class=\"clear\" style=\"background: url(./img/back/back".mt_rand(1, 4).".png) center;\">
										<div style=\"width: 256px; height: 75px\">
											<img src=\"img.php?s=".$rndImg."&l=a\" alt=\"\" style=\"background-repeat:repeat; position:absolute;left:".mt_rand(0, 10)."px;top:0px;\">
										</div>
										<div style=\"width: 256px; height: 75px;\">
											<img src=\"img.php?s=".$rndImg."&l=b\" alt=\"\" style=\"background-repeat:repeat; position:absolute;left:".mt_rand(10, 20)."px;top:0px;\">
										</div>
									</div>
					
								</div>
								
						<form action=\"./\" method=\"POST\">
					<p><input type=\"text\" name=\"captchastring\" size=\"30\" style=\"font-size: 18px;\"> <br> &#40;sEnSiblE a MayUsCulAs!&#41;</p>
					
					<p><input type=\"submit\" name=\"".$this->labelname."\" value=\"Comparar\" ></p></form>
					<p>
					<a href=\"http://jigsaw.w3.org/css-validator/check/referer\">
				    	<img style=\"border:0;\" src=\"./img/cssval.gif\"  alt=\"�CSS V�lido!\" >
					</a>
				
				    <a href=\"http://validator.w3.org/check?uri=referer\">
				    	<img src=\"./img/htmlval.gif\" style=\"border:0;\" alt=\"HTML 4.01 Strict V�lido\" >
				    </a>
				  	</p>					
					</div>
					</td>
				</tr>
			</table>";
		
		return $html;
		;
	}
}
?>
