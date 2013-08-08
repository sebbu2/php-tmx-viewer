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
		trigger_error('no/bad quality setting.', E_USER_ERROR);
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

function swap(&$a, &$b) {
	$tmp=$a;
	$a=$b;
	$b=$tmp;
}

function swap_ar(&$var, $pos1, $pos2) {
	$tmp=$var[$pos1];
	$var[$pos1]=$var[$pos2];
	$var[$pos2]=$tmp;
}

function parse_data($data, $encoding='', $compression='') {
	if($encoding=='base64') {
		$data=base64_decode($data);
	}
	else if($encoding=='csv') {
		$data2=explode(chr(10),$data);
		//var_dump(count($data2));
		$data3=array();
		$i=0;
		$data='';
		foreach($data2 as $line) {
			$line=trim($line, " \t\n\r\0\x0B,");
			$data3[$i]=explode(',',$line);
			//var_dump(count($data3[$i]));
			++$i;
		}
		unset($line,$data2);
		$irow=0;
		$icol=0;
		$icol2=0;
		foreach($data3 as $row) {
			$icol=0;
			foreach($row as $gid) {
				$data.=pack('V', $gid);
				++$icol;
			}
			if($icol>$icol2) $icol2=$icol;
			++$irow;
		}
		//var_dump($irow,$icol2);
		unset($gid,$row,$data3);
	}
	else {
		//$data=$data;
	}
	switch(strtolower($compression)) {
		case 'zlib':
			$data=gzuncompress($data);
			break;
		case 'gzip':
			//$data=gzuncompress($data);
			//$data=gzinflate($data);
			//$data=softcoded_gzdecode($data);
			$data=gzdecode($data);
			break;
		case 'bzip2':
		case 'bz2':
			$data=bzdecompress($data);
			break;
		case 'none':
		default:
			break;
	}
	return $data;
}

?>