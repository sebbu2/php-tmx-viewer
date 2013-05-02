<?php

require_once('error.php');

function get_ext($filename) {
	@assert(strpos($filename, '.')!==FALSE) or die('no extension.');
	return substr($filename, strrpos($filename, '.')+1);
}

function image_copy_and_resize( $dst_image , $src_image , $dst_x , $dst_y , $src_x , $src_y , $dst_w , $dst_h , $src_w=NULL , $src_h=NULL ) {
	global $quality;
	if($src_w==NULL&&$src_h==NULL) {
		$src_w=$dst_w;
		$src_h=$dst_h;
		return imagecopy($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h);
	}
	else if($quality==0) {
		return imagecopyresized($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
	}
	elseif($quality==1) {
		return imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
	}
	else {
		trigger_error('no/bad quality setting.', E_USER_NOTICE);
		return false;
	}
}

function create_image_from($file) {
	switch(get_ext($file)) {
	case 'png':
		$img=imagecreatefrompng($file);
		break;
	/*case 'bmp':
		$img=imagecreatefrombmp($file);
		break;*/
	case 'jpg':
	case 'jpe':
	case 'jpeg':
		$img=imagecreatefromjpeg($file);
		break;
	case 'gif':
		$img=imagecreatefromgif($file);
		break;
	default:
		trigger_error('empty image.', E_USER_NOTICE);
		$img=imagecreatetruecolor(0, 0);
		break;
	}
	return $img;
}

function my_transparent($img, $r, $g, $b, $trans) {
	$x=imagesx($img);
	$y=imagesy($img);
	for($i=0;$i<$y;++$i) {
		for($j=0;$j<$x;++$j) {
			$rgb=imagecolorat($img, $j, $i);
			$_r = ($rgb >> 16) & 0xFF;
			$_g = ($rgb >> 8) & 0xFF;
			$_b = $rgb & 0xFF;
			if($r==$_r && $g==$_g && $b==$_b) {
				imagesetpixel($img, $j, $i, $trans);
			}
		}
	}
}

?>