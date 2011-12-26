<?php
/**
* Archivo de ejemplo
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
* @copyright  Copyright (c) 2010 WALTER CERRUDO (http://www.lawebdewalterio.com.ar)
* @license    http://www.gnu.org/licenses/   GPL License
* @version    0.9.1
* @link       http://www.lawebdewalterio.com.ar
* @since      File available since Release 1.0
*/

	session_start();											//INICIAR SESION
	$_SESSION['HOTLINK']='NO';									//INICIALIZAR VARIABLE PARA EVITAR HOTLINK
	require('./class/mvcaptcha/mvcaptcha.class.php');			//INCLUIR ARCHIVO CON LA DEFINICIÓN DE LA CLASE MVCAPTCHA
	$captcha = new mvcaptcha('nada');							//INSTANCIAR MVCAPTCHA
	if ($captcha->verformulario())								//EVALUAR SI MUESTRO FORMULARIO 
	{
		echo $captcha->vermvcaptcha('./ok.php','./error.php');	//MUESTRO EL MVCAPTCHA
	}
	else
	{
		$captcha->proceder();									//EVALUAR EL MVCAPTCHA Y CONTINUAR SEGUN CORRESPONDA
	}
 
?>
