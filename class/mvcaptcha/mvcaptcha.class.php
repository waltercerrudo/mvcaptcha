<?php

/**
* Clase Principal de la Aplicación.
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
	public $URLOK;
	public $URLFallo;
	public $labelname;
	public $CaptchaSERVER;
	public $CaptchaUSER;
	public $Debug = false;

	function __construct($parLBL, $parURLOK, $parURLFALLA) {
		$this->labelname = $parLBL;
		$this->URLOK = $parURLOK;
		$this->URLFallo = $parURLFALLA;
	}

	function vermvcaptcha() {
		$captcha = new captcha();
		$_SESSION['FILENAME'] = $captcha->FileName;
		$_SESSION['CAPTCHAString'] = $captcha->CaptchaString;
		$_SESSION['URLOK'] = $this->URLOK;
		$_SESSION['URLFALLO'] = $this->URLFallo;
		$this->generarMask();
		return $this->getHTML();
	}

	function verformulario() {
		return !isset($_POST[$this->labelname]);
	}

	function run() {
		if ($this->verformulario()) {
			echo $this->vermvcaptcha();
		} else {
			$this->proceder();
		}
	}

	function generarMask() {
	}

	function proceder() {
		$s1 = $this->getCaptchaSERVER();
		$s2 = $this->getCaptchaUSER();
		if ( $s1 === $s2 ) {
			header('Location: ' . $_SESSION['URLOK']);
		} else {
			header('Location: ' . $_SESSION['URLFALLO']);
		}
	}

	function getCaptchaUSER() {
		$this->CaptchaUSER = $_POST['captchastring'] ?? '';
		return $this->CaptchaUSER;
	}

	function getCaptchaSERVER() {
		$this->CaptchaSERVER = $_SESSION['CAPTCHAString'] ?? '';
		return $this->CaptchaSERVER;
	}

	function getHTML() {
		$rndImg = 1;
		$rndBack = mt_rand(1, 4);
		$leftA = mt_rand(0, 10);
		$leftB = mt_rand(10, 20);
		$labelSafe = htmlspecialchars($this->labelname, ENT_QUOTES);
		$html = "<!DOCTYPE html>
<html lang=\"es\">
<head>
	<meta charset=\"utf-8\">
	<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
	<link href=\"css/styles.css\" rel=\"stylesheet\">
	<style>
		#scene {
			position: relative;
			overflow: hidden;
			width: 100%;
			height: 75px;
		}
		.clasecss {
			background-color: #ff8800;
			font-weight: bold;
		}
	</style>
</head>
<body>
	<div style=\"text-align:center;\">
		<div class=\"bordeder\">
			<div id=\"content\">
				<strong class=\"subder\">MVCaptcha</strong>
				<br>Ingresar el texto mostrado en la imagen
				<br>Mueve el Mouse para formar el texto.
				<br>Haciendo click las im&aacute;genes dejar&aacute;n de moverse.
				<br>
				<a href=\"./\">
					<img src=\"./img/recargar.png\" alt=\"Recargar\" width=\"32\" height=\"32\">
				</a>

				<div id=\"container\" class=\"container\">
					<div id=\"scene1\" class=\"scene\"></div>
					<div id=\"scene\" class=\"clear\" style=\"background: url(./img/back/back{$rndBack}.png) center;\">
						<div data-depth-y=\"-0.80\" data-depth-x=\"0.70\">
							<img src=\"img.php?s={$rndImg}&amp;l=a\" alt=\"\" style=\"position:absolute;left:{$leftA}px;top:0px;\">
						</div>
						<div data-depth-y=\"0.70\" data-depth-x=\"-0.80\">
							<img src=\"img.php?s={$rndImg}&amp;l=b\" alt=\"\" style=\"position:absolute;left:{$leftB}px;top:0px;\">
						</div>
					</div>
				</div>

				<form action=\"./\" method=\"post\">
					<p>
						<input type=\"text\" name=\"captchastring\" maxlength=\"20\" style=\"font-size: 18px;\">
						<br>(sEnSiblE a MayUsCulAs!)
					</p>
					<p><input type=\"submit\" name=\"{$labelSafe}\" value=\"Comparar\"></p>
				</form>
			</div>
		</div>
	</div>

	<script src=\"./js/parallax.js\"></script>
	<script>
		var scene = document.getElementById('scene');
		var parallax = new Parallax(scene);
		document.body.addEventListener('click', function () {
			parallax.disable();
		});
	</script>
</body>
</html>";
		return $html;
	}
}
?>
