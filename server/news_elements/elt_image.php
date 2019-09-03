<?php
	if ($valeur=='') {
		$valeur="../data/files/brand/logo.png";
	}
	if (strpos($valeur,'data')===0) $valeur="../$valeur";
	//on detecte le type
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	$mime=finfo_file($finfo, $valeur);
	$w=0;
	$in=0;
	if (isset($tab[2])) $w=	$tab[2];
	if (isset($tab[3])) $h=	$tab[3];
	else $h=$w;
	if (isset($tab[4])) $in=$tab[4];
	if ($w!=0) {
		$path_parts = pathinfo($valeur);
		switch ($mime) {
			case "image/png":
				$dest=$path_parts['dirname']."/min/".$path_parts['filename']."_$w"."_$h"."_$in.png";
				break;
			case "image/jpeg":
				$dest=$path_parts['dirname']."/min/".$path_parts['filename']."_$w"."_$h"."_$in.jpg";
				break;
			case "image/gif":
				$dest=$path_parts['dirname']."/min/".$path_parts['filename']."_$w"."_$h"."_$in.gif";
				break;
		}
		if (!file_exists($dest)) {
			if (!file_exists($path_parts['dirname']."/min")) mkdir($path_parts['dirname']."/min",0777,true);
			// Calcul des nouvelles dimensions
				list($largeur, $hauteur) = getimagesize($valeur); //list est un moyen plus pratique pour ne récupérer que ce qu'on veut
			if ($in==0) {
				if ($w/$h>$largeur/$hauteur) {
					$width=$w;
					$height=$width*$h/$w;
					$r=$width/$largeur;
				}else{
					$height=$h;
					$width=$height*$w/$h;
					$r=$height/$hauteur;
				}
			}
			if ($in==1) {
				if ($w/$h<$largeur/$hauteur) {
					$width=$w;
					$height=$width*$hauteur/$largeur;
					$r=$width/$largeur;
				}else{
					$height=$h;
					$width=$height*$largeur/$hauteur;
					$r=$height/$hauteur;
				}
			}
			//création de la destination
			$destination = imagecreatetruecolor(min($width,$w), min($height,$h));
			$back = imagecolorallocate($destination, 255, 255, 255);

			//on ouvre la source
			switch ($mime) {
				case "image/png":
					$source = imagecreatefrompng($valeur);
					break;
				case "image/jpeg":
					$source = imagecreatefromjpeg($valeur);
					break;
				case "image/gif":
					$source = imagecreatefromgif($valeur);
					break;
			}
			imagealphablending($source, false);
			imagesavealpha($source, true);
			imagealphablending($destination, false);
			imagesavealpha($destination, true);
			// Redimensionnement
			imagecopyresampled($destination, $source, 0, 0, max(0,($largeur-$w/$r)/2), max(0,($hauteur-$h/$r)/2), min($width,$w), min($height,$h),min($width,$w)/$r, min($height,$h)/$r);

			switch ($mime) {
				case "image/png":
					imagepng($destination,$dest);
					break;
				case "image/jpeg":
					imagejpeg($destination,$dest);
					break;
				case "image/gif":
					imagejpeg($destination,$dest);
					break;
			}
			imagedestroy($destination);
			imagedestroy($source);
		}
		$valeur=$dest;
	}
?>
