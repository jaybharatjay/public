<?php
ob_start();
if($_POST['controller']=="upload")
{
	$flag=1;
	$ext = end(explode('.', strtoupper($_FILES['image']['name'])));
	echo $ext;
	if(($ext!='JPG')&&($ext!='jpg')&&($ext!='JPEG')&&($ext!='jpeg')&&($ext!='GIF')&&($ext!='gif')&&($ext!='PNG')&&($ext!='png'))
	{
		$msg="<font color=\"FF0000\">Image Not uploaded, Try to upload only JPG/JPEG/GIF/PNG 1";
		$flag=0;
		header("Location:$_SERVER[PHP_SELF]?flag=$flag&msg=$msg");
	}
	/*if(($_FILES['image']['type']!="image/png")&&($_FILES['image']['type']!="image/gif")&&($_FILES['image']['type']!="image/jpeg")&&($_FILES['image']['type']!="image/jpg"))
	{
		$msg="<font color=\"FF0000\">Image Not uploaded, Try to upload only JPG/JPEG/GIF/PNG 0";
		$flag=0;
		header("Location:$_SERVER[PHP_SELF]?flag=$flag&msg=$msg");
	}*/
	if(($_FILES['image']['size'])>=5000000) //500 kb *1000
	{
		$msg="<font color=\"FF0000\">Image Not uploaded, Try to upload only below 500kb";
		$flag=0;
		header("Location:$_SERVER[PHP_SELF]?flag=$flag&msg=$msg");
	}
	if(($_FILES['image']['error'])!=0) 
	{
		$msg="<font color=\"FF0000\">Image Not uploaded, Some Un-Expected error";
		$flag=0;
		header("Location:$_SERVER[PHP_SELF]?flag=$flag&msg=$msg");
	}
	if($flag==1)
	{		
		$source =$_FILES[image][tmp_name];
		$ext = end(explode('.', $_FILES[image][name]));
		$msg="";
		//Original Start
		$original=0;
		$dest = "IMAGES_ORIGINAL/"."1.".$ext;
		$source_uploaded_image = $dest;
		if($image_original=UPLOAD($source,$dest))
		{
			$original=1;
			$msg="<br><font color=\"FF0000\"><b>IMAGE Uploaded successfully</b></font>";
		}
		else
		{
			$original=0;
			$msg="<br><font color=\"FF0000\"><b>Failure IMAGE Uploaded Failure</b></font>";
		}
		//Original End
		//Crop start
		if($msg!="")
		{
			$dest = "IMAGES_CROP/"."1.".$ext;
			$WidthCrop=163;
			$HeightCrop=126;
			$image_crop =CROP($WidthCrop, $HeightCrop, $source_uploaded_image,$dest);
			$crop=0;
			//echo "<br>image_crop1=".$image_crop;
			if($image_crop)
			{
				$crop=1;
			}
			else
			{
				$crop=0;
			}
		}
		//Crop end
		//thumb start
		if($msg!="")
		{
			$dest = "IMAGES_THUMB/"."1.".$ext;
			$new_width=60;
			$image_thumb=THUMB($source_uploaded_image,$dest,$new_width);
			$thumb=0;
			//echo "<br>image_thumb2=".$image_thumb;
			if($image_thumb)
			{
				$thumb=1;
			}
			else
			{
				$thumb=0;
			}
		}
		//thumb end
		header("Location:$_SERVER[PHP_SELF]?original=$original&crop=$crop&thumb=$thumb&msg=$msg&image_original=$image_original&image_crop=$image_crop&image_thumb=$image_thumb&okDisplay=1");
	}
}
function UPLOAD($source,$dest)
{
	if(move_uploaded_file($source, $dest))
	{
		return $image_original=$dest;
	}
}
function CROP($WidthCrop, $HeightCrop, $source,$dest)
{
	$size = getimagesize($source);
	$wm = 0;
	$hm = 0;
	$w = $size[0];
	$h = $size[1];
	list($width, $height,$type) = getimagesize($source) ;
	if($type==1)
	{
		$simg = imagecreatefromgif($source);
	}
	elseif($type==2)
	{
		//header("Content-Type: image/jpg");
		$simg = imagecreatefromjpeg($source);
	}
	elseif($type==3)
	{
		$simg = imagecreatefrompng($source);
	}
	$dimg = imagecreatetruecolor($WidthCrop, $HeightCrop);		
	$r = $size[0] /  $size[1];
	$rx = $WidthCrop / $HeightCrop;
	if($wm == 0 || $hm == 0)
	{
		$rm = $rx;
		//echo "<br>Line56";
	}
	else
	{
		$rm = $wm / $hm;
		//echo "<br>Line61";
	}
	$dx=0; $dy=0; $sx=0; $sy=0; $dw=0; $dh=0; $sw=0; $sh=0; $w=0; $h=0;
	if($r > $rx && $r > $rm) 
	{ //1 if
		$w = $WidthCrop;
		$h = $HeightCrop;

		$sw = $size[1] * $rx;
		$sh = $size[1];
		$sx = ($size[0] - $sw) / 2;

		$dw = $WidthCrop;
		$dh = $HeightCrop;
		//echo "<br>If 1";
	} 
	elseif($r < $rm && $r < $rx) 
	{  //2 if
		$w = $WidthCrop;
		$h = $HeightCrop;
		$sh = $size[0] / $rx;
		//$sy = ($size[1] - $sh) / 2;
		$sy=5;
		$sw = $size[0];
		$dw = $WidthCrop;
		$dh = $HeightCrop;
		//echo "<br>If 2";
	} 
	elseif($r >= $rx && $r <= $rm) 
	{ //3 if
		$w = $WidthCrop;
		$h = $WidthCrop / $r;
		$dw = $WidthCrop;
		$dh = $WidthCrop / $r;
		$sw = $size[0];
		$sh = $size[1];
		//echo "<br>If 3";
	} 
	elseif($r <= $rx && $r >= $rm) 
	{ //4 if
		$w = $HeightCrop * $r;
		$h = $HeightCrop;
		$dw = $HeightCrop * $r;
		$dh = $HeightCrop;
		$sw = $size[0];
		$sh = $size[1];
		//echo "<br>If 4";
	} 
	imagecopyresampled($dimg,$simg,$dx, $dy, $sx, $sy, $dw, $dh, $sw, $sh);
	if($type==1)
	{
		imagegif($dimg,$dest,100);
	}
	else if($type==2)
	{
		imagejpeg($dimg,$dest,100);
	}
	else if($type==3)
	{
		imagepng($dimg,$dest,0);
	}
	//return $dimg;
	//echo $image_name=$dest;
	return $image_crop=$dest;
}
function THUMB($source,$dest,$new_width)
{
	//Thumb measure start
	list($width, $height, $type, $attr) = getimagesize($source);
	if($width>$new_width)
	{
		// calculate thumbnail size
		$thumb_width=$new_width;
		$thumb_height = floor( $height * ( $thumb_width / $width ) );
	}
	else
	{
		$thumb_width=$width;
		$thumb_height=$height;
	}
	//Thumb measure end
	//header('Content-type: image/jpeg') ;  //Comented by jaybharat
	list($width, $height,$type) = getimagesize($source) ;
	$tn = imagecreatetruecolor($thumb_width, $thumb_height) ;
	if($type==2)
		$image = imagecreatefromjpeg($source) ;
	else if($type==1)
		$image = imagecreatefromgif($source) ;
	else if($type==3)
		$image = imagecreatefrompng($source) ;
	imagecopyresampled($tn, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height) ;
	
	if($type==2)
		imagejpeg($tn, $dest, 100) ;
	else if($type==1)
		imagegif($tn, $dest, 100) ;
	else if($type==3)
		imagepng($tn, $dest) ;
	return $image_thumb=$dest;
}
?>
<script language="javascript" type="text/javascript">
function jayjava1()
{
	if(document.form1.image.value=="")
	{
		alert("Please UPLOAD \n\" IMAGE\"\n ");
		document.form1.image.focus();
		return false;
	}
	
	if(document.form1.image.value!= "")
	{	
		var ext = document.form1.image.value;
		ext = ext.substring(ext.length-3,ext.length);
		ext = ext.toLowerCase();
		if((ext != 'jpg')&&(ext != 'JPG')&(ext != 'JPEG')&&(ext != 'jpeg')&&(ext != 'gif')&&(ext != 'GIF')&(ext != 'png')&&(ext != 'PNG'))
		{
			alert('You selected a .'+ ext + ' file; \n please select a .jpg/.jpeg/.gif/.png  image file while upload IMAGE');
			document.form1.image.focus();
			document.form1.image.value="";
			return false;
		}
	}
}
</script>
<div style="color:#666666">This code Demonstrate to: Upload a image with all possible server and client side Validation + Croping the image + Thumb the Image with easy understanding example</div>
<div style="line-height:50px;color:#CCCCCC">First Test and satisfied, if you satisfied then you can.<a href="crop_resize.zip">download</a> </div>
<form name="form1" method="post" action="<?php $_SERVER['PHP_SELF']; ?>" onSubmit="return jayjava1();" enctype="multipart/form-data">
<input type="hidden" name="controller" value="upload" />
<table width="100%" border="0">
  <tr>
    <td width="4%">Upload Image:</td>
	<td width="21%"> <input type="hidden" name="MAX_FILE_SIZE" value="5000000" /> <!--500kb(=500 * 1000)--><input type="file" name="image" /></td> <td width="75%"> Maximum file size 500 kb jpg/gif/png </td>
  </tr>
</table>
<table width="100%" border="0">
  <tr>
  	<td width="40%">&nbsp;</td>
    <td width="60%"><input type="submit" name="submit" value="submit"/></td>
  </tr>
</table>
</form>
<?php
if($_GET['okDisplay']==1)
{
?>
	<?php
	if($_GET['original']==1)
	{
	?>
		<table width="100%" border="0">
		  <tr>
			<td width="11%" valign="top">Upload Successfully</td>
			<td width="89%" align="left">If you do'not able to see your new uploaded image<strong><font color="#FF0000"> please Press F5 F5 F5 F5 F5</font>(most probably in IE browser)</strong> </td>
		  </tr>
		</table>
		<?php
		if($_GET['thumb']==1)
		{
		?>
			<?php
			if($_GET[image_thumb])
			{
			?>
			<table width="100%" border="0">
			  <tr>
				 <td width="24%" valign="top">Thumb Image (width60*crosspondingHeight)=</td>
				<td width="76%" align="left"><img src=<?php echo $_GET[image_thumb] ?> /></td>
			  </tr>
			</table>
			<?php
			}
			?>
		<?php
		}
		else
		{
		?>
			<table width="100%" border="0">
			  <tr>
				 <td width="24%" valign="top">Thumb Image=</td>
				<td width="76%" align="left">Sorry, Thumb image creating Error</td>
			  </tr>
			</table>
		
		<?php
		}
		?>
		<?php
		if($_GET['crop']==1)
		{
		?>
			<?php
			if($_GET[image_crop])
			{
			?>
			<table width="100%" border="0">
			  <tr>
				 <td width="24%" valign="top">Crop Image(163*126 size)=</td>
				<td width="76%" align="left"><img src=<?php echo $_GET[image_crop] ?> /></td>
			  </tr>
			</table>
			<?php
			}
			?>
		<?php
		}
		else
		{
		?>
			<table width="100%" border="0">
			  <tr>
				 <td width="24%" valign="top">Crop Image=</td>
				<td width="76%" align="left">Sorry, Crop image creating Error</td>
			  </tr>
			</table>
		<?php
		}
		?>
		<?php
		if($_GET['original']==1)
		{
		?>
			<?php
			if($_GET[image_original])
			{
			?>
			<table width="100%" border="0">
			  <tr>
				 <td width="24%" valign="top">Original Image=</td>
				<td width="76%"><img src="<?php echo $_GET[image_original] ?>"/></td>
			  </tr>
			</table>
			<?php
			}
			?>
		<?php
		}
		else
		{
		?>
			<table width="100%" border="0">
			  <tr>
				 <td width="24%" valign="top">Original Image=</td>
				<td width="76%">Sorry, Original image saving Error</td>
			  </tr>
			</table>
		
		<?php
		}
		?>
	<?php
	}
	else
	{
	?>
		<table width="100%" border="0">
			<tr>
			 	<td width="100%" align="center">Sorry, Original image uploading Error</td>
			</tr>
		</table>
	<?php
	}
	?>
<?php
}
?>

	<?php
	if($_GET['flag']=='0')
	{
	?>
	<table width="100%" border="0">
	  <tr>
		<td><?php echo $_GET['msg'] ?></td>
	  </tr>
	</table>
	<?php
	}
	?>

<?php
ob_flush();
?>