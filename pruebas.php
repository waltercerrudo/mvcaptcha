
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"> 

<html> 

  <head> 

  <meta http-equiv="Content-Type" content="text/html;charset=utf-8" >

    <title> Captcha</title>

    

			<link href="css/styles.css" rel="stylesheet" type="text/css" >

			<style type="text/css" media="screen, projection">

			#parallax {

				position: relative;

				overflow: hidden;

				width: 100%;

				height: 75px;

			}

			

			#content {

				text-align: center;

			 	}

			.freeze     {  }

			.clasecss{

			   background-color: #ff8800;

			   font-weight: bold;

			}

			 	

			</style>

			

			<script

				type="text/javascript" src="./js/jquery-1.3.2.min.js"></script>

			<script

				type="text/javascript" src="./js/jquery.jparallax.091.js"></script>

			<script type="text/javascript">

			<!--

			var inPullNav = false;
			function setupHotkeys() {
				$(document).keypress(function(e) {
					switch(e.which)
					{
						case 32:
							delegates.jparallax.toggleSuspend(); 
							return false;
						case 99:
						case 67:
							cycle();
							return false;
					}
					return true;
				});
			}			  

			jQuery(document).ready(function(){

					var corners='';  

				 	jQuery('#parallax').jparallax({

					 	mouseport: jQuery('#parallax')},{yparallax: false},{xtravel:-0.8, ytravel:-0.5}, {xtravel:0.8, ytravel:-0.8}).append(corners);

			});

			//-->

			</script>

			<!--[if lt IE 7]>

			<script type="text/javascript" src="js/jquery.ifixpng.js"></script>

			<script type="text/javascript">jQuery(document).ready(function(){ jQuery('img[@src$=.png]').ifixpng(); });</script>

			<![endif]-->

			<!--[if lt IE 8]>

			<style type="text/css">

			@import "css/style_ie.css";

			</style>

			</head>

			</body>

			<![endif]-->

			<table width="100%" border="0" cellspacing="0" cellpadding="0">

				<tr>

					<td align="center" class="descdet">

					<div class="bordeder">

						<div id="content"><strong class="subder">CAPTCHA con PHP </strong><a href="./"><img src="./img/recargar.png" alt="colabora" width="32" height="32" ></a><br>

									Ingresar el texto mostrado en la imagen <br>

									Mueve el Mouse para formar el texto

									<div id="formulariomayores" style="display: none; "></div>

									<div id="parallax" class="clear" style="background: url(./img/back/back2.png) center;">

										<div style="width: 216px; height: 75px">

											<img src="tmp/img1.png" alt="" style="position:absolute;left:33px?>px;top:0px;">

										</div>

										<div style="width: 216px; height: 75px;">

											<img src="tmp/img2.png" alt="" style="position:absolute;left:39px;top:0px;">

										</div>

									</div>

					

								</div>

								

						<form action="./" method="POST">

					<p><input type="text" name="captchastring" size="30" style="font-size: 18px;"> <br> &#40;sEnSiblE a MayUsCulAs!&#41;</p>

					

					<p><input type="submit" name="aceptar" value="Comparar" ></p></form>

					<p>

					<a href="http://jigsaw.w3.org/css-validator/check/referer">

				    	<img style="border:0;" src="./img/cssval.gif"  alt="�CSS V�lido!" >

					</a>

				

				    <a href="http://validator.w3.org/check?uri=referer">

				    	<img src="./img/htmlval.gif" style="border:0;" alt="HTML 4.01 Strict V�lido" >

				    </a>

				  	</p>					

					</div>

					</td>

				</tr>

			</table></body>

</html>