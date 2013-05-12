<?php

require_once('map.php');
require_once('tileset.php');
require_once('layer.php');
require_once('functions.inc.php');

class Viewer {
	//attributes
	public $name='';
	private $map=NULL;
	private $data='';
	public $draw_objects=false;
	private static $urls=array(
		'tmw'=>'https://raw.github.com/themanaworld/tmwa-client-data/master/',
		'evol'=>'https://raw.github.com/EvolOnline/clientdata-beta/master/',
		'tales'=>'https://raw.github.com/tales/sourceoftales/master/',
		);
	//constructors

	//methods
	public function setMap(Map $map) {
		$this->map=$map;
	}
	public function getMap() {
		return $this->map;
	}

	public function draw($file=NULL) {
		//ob_start();
		$zoom=1;
		$images=array();
		foreach($this->map->tilesets as $i=>$ts) {
			if( array_key_exists('ref', $_REQUEST) ) {
				//if( $_REQUEST['ref']=='tmw' ) {
				if(array_key_exists($_REQUEST['ref'], Viewer::$urls)) {
					$url=Viewer::$urls[$_REQUEST['ref']].dirname($this->map->filename).'/';
					if(strlen($ts->sourceTSX)>0) $url.=dirname($ts->sourceTSX).'/';
					$url.=$ts->source;
					$images[$i]=create_image_from($url);
				}
				else {
					$images[$i]=create_image_from(dirname($this->map->filename).'/'.dirname($ts->sourceTSX).'/'.$ts->source);
				}
			}
			else {
				$images[$i]=create_image_from(dirname($this->map->filename).'/'.dirname($ts->sourceTSX).'/'.$ts->source);
			}
			if(function_exists('imageantialias')) {
				imageantialias($images[$i], false);
			}
			//imagealphablending($images[$i], true);
			imagealphablending($images[$i], false);
			$transc=$ts->trans;
			$trans = imagecolorallocatealpha($images[$i], 255, 255, 255, 127);//transparent
			//$trans = imagecolorallocatealpha($images[$i], 255, 255, 255, 0);//opaque
			if((bool)$transc && $transc!='') {
				$r=hexdec(substr($transc,0,2));
				$g=hexdec(substr($transc,2,2));
				$b=hexdec(substr($transc,4,2));
				$color = imagecolorallocatealpha($images[$i], $r, $g, $b, 0);//opaque
				//var_dump(imagesx($images[$i]), imagesy($images[$i]));die();
				my_transparent($images[$i], $r, $g, $b, $trans);
				imagecolortransparent($images[$i], $color);
			}
		}
		unset($i,$ts,$transc,$trans,$r,$g,$b,$color);

		$img=imagecreatetruecolor($this->map->width*$this->map->tilewidth*$zoom, $this->map->height*$this->map->tileheight*$zoom);
		//$img=imagecreatetruecolor($width*$tilewidth/2, $height*$tileheight/2);

		if(function_exists('imageantialias')) {
			imageantialias($img, false);
		}
		imagealphablending($img, true);
		//imagealphablending($img, false);
		/*
		imagecolorallocatealpha
		0		opaque
		127		transparent

		imagecopymerge
		0		nothing
		100		copy

		imagecolortransparent
		0		blanc
		127		gris
		255		noir
		*/
		//$color=imagecolorallocatealpha($img, 255, 255, 255, 0);//blanc
		//$color=imagecolorallocatealpha($img, 255, 255, 255, 127);//blanc
		$trans=imagecolorallocatealpha($img, 255, 255, 255, 127);//transparent
		$color=imagecolorallocatealpha($img, 0, 0, 0, 0);//noir
		$ligr=imagecolorallocatealpha($img, 0xcc, 0xcc, 0xcc, 0);//light gray
		$red=imagecolorallocatealpha($img, 255, 0, 0, 0);//rouge
		$green=imagecolorallocatealpha($img, 0, 255, 0, 0);//vert
		$blue=imagecolorallocatealpha($img, 0, 0, 255, 0);//bleu

		imagefill($img, 0, 0, $trans);


		foreach($this->map->layers as $index=>$ly) {
			//break;
			for($j=0;$j<$ly->height;++$j) {
				for($i=0;$i<$ly->width;++$i) {
					$cgid=$ly->get_tile($j*$ly->width+$i);
					if($cgid==0) continue;
					$ti=$this->map->get_tileset_index($cgid);
					if($ti==-1) {
						var_dump($cgid);
						die();
					}
					$lid=$cgid-$this->map->tilesets[$ti]->firstgid;
					//var_dump($lid);print('<br/>'."\n");continue;
					//$largeur=$ly->width;
					$largeur=0;
					for( $a=0; $a<$this->map->tilesets[$ti]->width-$this->map->tilesets[$ti]->margin; $a+=$this->map->tilesets[$ti]->tilewidth ) {
						++$largeur;
						if($this->map->tilesets[$ti]->spacing>0) $a+=$this->map->tilesets[$ti]->spacing;
					}
					//var_dump($largeur);
					assert($largeur>0) or die('largeur == 0');
					//var_dump($largeur);die();
					$tx=$lid%($largeur*$zoom);
					$tx2=0;
					if($this->map->tilesets[$ti]->spacing>0) $tx2+=$this->map->tilesets[$ti]->spacing*$tx;
					if($this->map->tilesets[$ti]->margin>0) $tx2+=$this->map->tilesets[$ti]->margin;
					$ty=(int)($lid/($largeur*$zoom));
					$ty2=0;
					if($this->map->tilesets[$ti]->spacing>0) $ty2+=$this->map->tilesets[$ti]->spacing*$ty;
					if($this->map->tilesets[$ti]->margin>0) $ty2+=$this->map->tilesets[$ti]->margin;
					//var_dump($tx,$tx2,$ty,$ty2);die();
					if($this->map->orientation=='orthogonal') {
						$dx=$i*$this->map->tilewidth;
						$dy2=$j*$this->map->tileheight-($this->map->tilesets[$ti]->tileheight-$this->map->tileheight);
							$dy=max($dy2, 0);
						$sx=$tx2+$tx*$this->map->tilesets[$ti]->tilewidth;
						$sy=$ty2+$ty*$this->map->tilesets[$ti]->tileheight;
						$sw=$this->map->tilesets[$ti]->tilewidth;
						$sh=$this->map->tilesets[$ti]->tileheight;
						if($sx+$sw>imagesx($images[$ti])) {
							trigger_error('width exceeded.');
						}
						if($sy+$sh>imagesy($images[$ti])) {
							trigger_error('height exceeded.');
						}
						if($dy2 < 0) {
							$sy-=$dy2;
							$sh+=$dy2;
						}
						if($zoom==1) {
							image_copy_and_resize($img, $images[$ti], $dx, $dy, $sx, $sy, $sw, $sh);
						}
						else {
							image_copy_and_resize($img, $images[$ti], $dx*$zoom, $dy*$zoom, $sx, $sy, $sw*$zoom, $sh*$zoom, $sw, $sh);
						}
					}
					elseif($this->map->orientation=='isometric') {
						$dx=($this->map->width*$this->map->tilewidth/2)-$this->map->tilewidth/2-($j*$this->map->tilewidth/2)+($i*$this->map->tilewidth/2);
						$dy2=($i+$j)*$this->map->tileheight/2-($this->map->tilesets[$ti]->tileheight-$this->map->tileheight)/2;
							$dy=max($dy2, 0);
						$sx=$tx2+$tx*$this->map->tilesets[$ti]->tilewidth;
						$sy=$ty2+$ty*$this->map->tilesets[$ti]->tileheight;
						$sw=$this->map->tilesets[$ti]->tilewidth;
						$sh=$this->map->tilesets[$ti]->tileheight;
						if($sx+$sw>imagesx($images[$ti])) {
							trigger_error('width exceeded.');
						}
						if($sy+$sh>imagesy($images[$ti])) {
							trigger_error('height exceeded.');
						}
						if($dy2 < 0) {
							//var_dump($dx, $dy2, $sx, $sy, $sw, $sh);echo '<br/>'."\r\n";
							$sy+=abs($dy2);
							$sh-=abs($dy2);
							//var_dump($dx, $dy, $sx, $sy, $sw, $sh);echo '<br/>'."\r\n";
							//die();
						}
						if($zoom==1) {
							image_copy_and_resize($img, $images[$ti], $dx, $dy, $sx, $sy, $sw, $sh);
						}
						else {
							image_copy_and_resize($img, $images[$ti], $dx*$zoom, $dy*$zoom, $sx, $sy, $sw*$zoom, $sh*$zoom, $sw, $sh);
						}
						//if($lid==1) break(3);
					}
					/*if( $dy2 < 0) {// || $sx+$sw>imagesx($images[$ti]) || $sy+$sh>imagesy($images[$ti]) ) {
						/*var_dump($dx, $dy2, $sx, $sy, $sw, $sh);
						echo '<br/>'."\r\n";
						var_dump($dx, $dy, $sx, $sy-$dy2, $sw, $sh+$dy2);
						echo '<br/>'."\r\n";//
						//die();
					}//*/
				}
			}
		}
		//die();
		if($this->draw_objects) {
			foreach($this->map->objectlayers as $index=>$ol) {
				foreach($ol->getAllObjects() as $o) {
					if($o->polygon || $o->polyline) {
						imagerectangle($img, $o->x-$o->getWidthL(), $o->y-$o->getHeightT(),
							$o->x + $o->getWidthR(), $o->y + $o->getHeightB(),
							$ligr);
						if($o->polyline) {
							assert(count($o->points)/2>1);
							$x=$o->x+$o->points[0];
							$y=$o->y+$o->points[1];
							imagesetthickness($img, 2);
							for($i=2;$i<count($o->points);$i+=2) {
								imageline($img, $x, $y, $o->x+$o->points[$i], $o->y+$o->points[$i+1], $green);
								$x=$o->x+$o->points[$i];
								$y=$o->y+$o->points[$i+1];
							}
							imagesetthickness($img, 1);
						}
						if($o->polygon) {
							$ar=$o->points;
							for($i=0;$i<count($ar);$i+=2) {
								$ar[$i]+=$o->x;
								$ar[$i+1]+=$o->y;
							}
							imagesetthickness($img, 2);
							imagepolygon($img, $ar, count($ar)/2, $green);
							imagesetthickness($img, 1);
						}
						if($o->name!='') {
							imagettftext($img, 10, 0, $o->x-$o->getWidthL(), $o->y-$o->getHeightT()-4, $blue, './courbd.ttf', $o->name);
							//imagefttext($img, 10, 0, $o->x-$o->getWidthL(), $o->y-$o->getHeightT()-4, $blue, './courbd.ttf', $o->name);
							//imagefttext($img, 10, 0, $o->x-$o->getWidthL(), $o->y-$o->getHeightT()-4, $red, './cour.ttf', $o->name);
							//imagestring($img, 3, $o->x-$o->getWidthL(), $o->y-$o->getHeightT()-16, $o->name, $blue);
						}
					}
					else {
						imagesetthickness($img, 2);
						imagerectangle($img, $o->x, $o->y, $o->x + $o->width, $o->y + $o->height, $green);
						imagesetthickness($img, 1);
						if($o->name!='') {
							imagettftext($img, 10, 0, $o->x, $o->y-4, $blue, './courbd.ttf', $o->name);
							//imagefttext($img, 10, 0, $o->x, $o->y-4, $blue, './courbd.ttf', $o->name);
							//imagefttext($img, 10, 0, $o->x, $o->y-4, $red, './cour.ttf', $o->name);
							//imagestring($img, 3, $o->x, $o->y-16, $o->name, $blue);
						}
					}
				}
			}
		}
		/*$data=ob_get_clean();
		if(!empty($data)) {
			header('Content-Type: text/plain'."\r\n");
			echo $data;
			die();
		}

		if(!defined('DEBUG')||DEBUG!==true) {
			header('Content-Type: image/jpeg'."\r\n");
		}*/

		imagesavealpha($img, true);

		//ini_set('output_buffering','off');

		//imagejpeg($img, $file, 80);
		imagepng($img, $file, 7);
	}
};

?>
