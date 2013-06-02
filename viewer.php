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
	private $img=NULL;
	private $ts_imgs=array();
	private static $urls=array(
		'tmw'=>'https://raw.github.com/themanaworld/tmwa-client-data/master/',
		'evol'=>'https://raw.github.com/EvolOnline/clientdata-beta/master/',
		'tales'=>'https://raw.github.com/tales/sourceoftales/master/',
		'stendhal'=>'http://arianne.cvs.sourceforge.net/viewvc/arianne/stendhal/tiled/',
		);
	//constructors

	//methods
	public function setMap(Map $map) {
		$this->map=$map;
	}
	public function getMap() {
		return $this->map;
	}

	public function load_ts() {
		$this->ts_imgs=array();
		foreach($this->map->tilesets as $i=>$ts) {
			if( array_key_exists('ref', $_REQUEST) ) {
				//if( $_REQUEST['ref']=='tmw' ) {
				if(array_key_exists($_REQUEST['ref'], Viewer::$urls)) {
					$url=Viewer::$urls[$_REQUEST['ref']].dirname($this->map->filename).'/';
					if(strlen($ts->sourceTSX)>0) $url.=dirname($ts->sourceTSX).'/';
					$url.=$ts->source;
					$this->ts_imgs[$i]=create_image_from($url);
				}
				else {
					$this->ts_imgs[$i]=create_image_from(dirname($this->map->filename).'/'.dirname($ts->sourceTSX).'/'.$ts->source);
				}
			}
			else {
				$this->ts_imgs[$i]=create_image_from(dirname($this->map->filename).'/'.dirname($ts->sourceTSX).'/'.$ts->source);
			}
			if(function_exists('imageantialias')) {
				imageantialias($this->ts_imgs[$i], false);
			}
			//imagealphablending($this->ts_imgs[$i], true);
			imagealphablending($this->ts_imgs[$i], false);
			$transc=$ts->trans;
			$trans = imagecolorallocatealpha($this->ts_imgs[$i], 255, 255, 255, 127);//transparent
			//$trans = imagecolorallocatealpha($this->ts_imgs[$i], 255, 255, 255, 0);//opaque
			if((bool)$transc && $transc!='') {
				$r=hexdec(substr($transc,0,2));
				$g=hexdec(substr($transc,2,2));
				$b=hexdec(substr($transc,4,2));
				$color = imagecolorallocatealpha($this->ts_imgs[$i], $r, $g, $b, 0);//opaque
				//var_dump(imagesx($this->ts_imgs[$i]), imagesy($this->ts_imgs[$i]));die();
				my_transparent($this->ts_imgs[$i], $r, $g, $b, $trans);
				imagecolortransparent($this->ts_imgs[$i], $color);
			}
		}
		unset($i,$ts,$transc,$trans,$r,$g,$b,$color);
	}
	
	public function draw() {
		//ob_start();
		$zoom=1;

		assert(count($this->ts_imgs)==count($this->map->tilesets)) or die('tilesets not loaded.');
		
		$this->img=imagecreatetruecolor($this->map->width*$this->map->tilewidth*$zoom, $this->map->height*$this->map->tileheight*$zoom);
		//$img=imagecreatetruecolor($width*$tilewidth/2, $height*$tileheight/2);

		if(function_exists('imageantialias')) {
			imageantialias($this->img, false);
		}
		imagealphablending($this->img, true);
		//imagealphablending($this->img, false);
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
		//$color=imagecolorallocatealpha($this->img, 255, 255, 255, 0);//blanc
		//$color=imagecolorallocatealpha($this->img, 255, 255, 255, 127);//blanc
		$trans=imagecolorallocatealpha($this->img, 255, 255, 255, 127);//transparent
		$color=imagecolorallocatealpha($this->img, 0, 0, 0, 0);//noir
		$ligr=imagecolorallocatealpha($this->img, 0xcc, 0xcc, 0xcc, 0);//light gray
		$red=imagecolorallocatealpha($this->img, 255, 0, 0, 0);//rouge
		$green=imagecolorallocatealpha($this->img, 0, 255, 0, 0);//vert
		$blue=imagecolorallocatealpha($this->img, 0, 0, 255, 0);//bleu

		imagefill($this->img, 0, 0, $trans);

		foreach($this->map->layers as $index=>$ly) {
			//break;
			if(strlen($ly->name)>0&&in_array($ly->name, $_SESSION['layers_nodraw'])) continue;
			for($j=0;$j<$ly->height;++$j) {
				for($i=0;$i<$ly->width;++$i) {
					$cgid=$ly->get_tile($j*$ly->width+$i);
					if($cgid==0) continue;
					$ti=$this->map->get_tileset_index($cgid);
					if($ti==-1) {
						var_dump($cgid);
						die();
					}
					if(strlen($this->map->tilesets[$ti]->name)>0&&in_array($this->map->tilesets[$ti]->name, $_SESSION['tilesets_nodraw'])) continue;
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
						if($sx+$sw>imagesx($this->ts_imgs[$ti])) {
							trigger_error('width exceeded.');
						}
						if($sy+$sh>imagesy($this->ts_imgs[$ti])) {
							trigger_error('height exceeded.');
						}
						if($dy2 < 0) {
							$sy-=$dy2;
							$sh+=$dy2;
						}
						if($zoom==1) {
							image_copy_and_resize($this->img, $this->ts_imgs[$ti], $dx, $dy, $sx, $sy, $sw, $sh);
						}
						else {
							image_copy_and_resize($this->img, $this->ts_imgs[$ti], $dx*$zoom, $dy*$zoom, $sx, $sy, $sw*$zoom, $sh*$zoom, $sw, $sh);
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
						if($sx+$sw>imagesx($this->ts_imgs[$ti])) {
							trigger_error('width exceeded.');
						}
						if($sy+$sh>imagesy($this->ts_imgs[$ti])) {
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
							image_copy_and_resize($this->img, $this->ts_imgs[$ti], $dx, $dy, $sx, $sy, $sw, $sh);
						}
						else {
							image_copy_and_resize($this->img, $this->ts_imgs[$ti], $dx*$zoom, $dy*$zoom, $sx, $sy, $sw*$zoom, $sh*$zoom, $sw, $sh);
						}
						//if($lid==1) break(3);
					}
					/*if( $dy2 < 0) {// || $sx+$sw>imagesx($this->ts_imgs[$ti]) || $sy+$sh>imagesy($this->ts_imgs[$ti]) ) {
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
						imagerectangle($this->img, $o->x-$o->getWidthL(), $o->y-$o->getHeightT(),
							$o->x + $o->getWidthR(), $o->y + $o->getHeightB(),
							$ligr);
						if($o->polyline) {
							assert(count($o->points)/2>1);
							$x=$o->x+$o->points[0];
							$y=$o->y+$o->points[1];
							imagesetthickness($this->img, 2);
							for($i=2;$i<count($o->points);$i+=2) {
								imageline($this->img, $x, $y, $o->x+$o->points[$i], $o->y+$o->points[$i+1], $green);
								$x=$o->x+$o->points[$i];
								$y=$o->y+$o->points[$i+1];
							}
							imagesetthickness($this->img, 1);
						}
						if($o->polygon) {
							$ar=$o->points;
							for($i=0;$i<count($ar);$i+=2) {
								$ar[$i]+=$o->x;
								$ar[$i+1]+=$o->y;
							}
							imagesetthickness($this->img, 2);
							imagepolygon($this->img, $ar, count($ar)/2, $green);
							imagesetthickness($this->img, 1);
						}
						if($o->name!='') {
							imagettftext($this->img, 10, 0, $o->x-$o->getWidthL(), $o->y-$o->getHeightT()-4, $blue, './courbd.ttf', $o->name);
							//imagefttext($this->img, 10, 0, $o->x-$o->getWidthL(), $o->y-$o->getHeightT()-4, $blue, './courbd.ttf', $o->name);
							//imagefttext($this->img, 10, 0, $o->x-$o->getWidthL(), $o->y-$o->getHeightT()-4, $red, './cour.ttf', $o->name);
							//imagestring($this->img, 3, $o->x-$o->getWidthL(), $o->y-$o->getHeightT()-16, $o->name, $blue);
						}
					}
					else {
						imagesetthickness($this->img, 2);
						imagerectangle($this->img, $o->x, $o->y, $o->x + $o->width, $o->y + $o->height, $green);
						imagesetthickness($this->img, 1);
						if($o->name!='') {
							imagettftext($this->img, 10, 0, $o->x, $o->y-4, $blue, './courbd.ttf', $o->name);
							//imagefttext($this->img, 10, 0, $o->x, $o->y-4, $blue, './courbd.ttf', $o->name);
							//imagefttext($this->img, 10, 0, $o->x, $o->y-4, $red, './cour.ttf', $o->name);
							//imagestring($this->img, 3, $o->x, $o->y-16, $o->name, $blue);
						}
					}
				}
			}
		}
	}
	
	public function render($file=NULL) {
		imagesavealpha($this->img, true);

		//ini_set('output_buffering','off');

		//imagejpeg($this->img, $file, 80);
		imagepng($this->img, $file, 7);
	}
};

?>