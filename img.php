<?php
	//include '/class/image/image.class.php';	
	session_start();
	if ($_SERVER['HTTP_REFERER']<>'')
	{
	require './class/imagemask/class.imagemask.php';	
	$im = new imageMask('FFFFFF');
    $im->setDebugging(false);
    $im->maskOption('centre');
    if ($im->loadImage("./tmp/".$_SESSION['FILENAME'].'.png'))
    {
        if ($im->applyMask("./img/mask/mask".$_GET['s'].$_GET['l'].".png"))
        {
        	$img=imagecreatetruecolor(300, 75);
        	if ($_GET['l']=='a')
        	{
        		$or=mt_rand(0, 30);
        	}
        	else
        	{
        		$or=mt_rand(40, 60);
        	}
        	imagecopy($img,$im->_img['final'], 0, 0, $or, 0, 380, 75);
        	$im->_img['final']=$img;
	       	$bg_color = hexdec('ffffff');
			imagecolortransparent($im->_img['final'], $bg_color);
			$im->showImage('png',3);
			//$im->saveImage('./tmp/'.$_SESSION['FILENAME'].$_GET['l'].'.png');
			if ($_GET['l']=='b')
			{
				unlink('./tmp/'.$_SESSION['FILENAME'].'.png');
			}
        }	        
    }
	}
	else
	{
		echo 'NOT HOTLINK';
	}
	
?>