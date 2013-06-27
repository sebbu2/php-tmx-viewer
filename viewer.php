<?php

require_once('map.php');
require_once('layer.php');
require_once('tileset.php');
require_once('tilelayer.php');
require_once('functions.inc.php');

class Viewer {
	//attributes
	public $name='';
	private $map=NULL;
	private $data='';
	public $draw_objects=true;
	public $draw_imagelayers=true;
	private $img=NULL;
	private $ts_imgs=array();
	private $ts_largeur=array();
	private $colors=array();
	public $zoom=1;
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
			if( strlen($ts->source)==0 ) {
				$this->ts_imgs[$i]=array();
				foreach($ts->tiles as $id => $ar) {
					if(is_int($id)) {
						if(array_key_exists('imagecontent', $ar)) {
							$this->ts_imgs[$i][$id]=imagecreatefromstring($ar['imagecontent']);
						}
						else if(array_key_exists('imagesource', $ar)) {
							$this->ts_imgs[$i][$id]=create_image_from(dirname($this->map->filename).'/'.$ar['imagesource']);
						}
						else {
							throw new Exception('Other format of multiple image tileset not implemented yet.');
						}
					}
				}
			}
			else if( array_key_exists('ref', $_REQUEST) ) {
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
			//$this->ts_largeur[$i]=$ly->width;
			$this->ts_largeur[$i]=0;
			for( $a=0; $a<$this->map->tilesets[$i]->width-$this->map->tilesets[$i]->margin; $a+=$this->map->tilesets[$i]->tilewidth ) {
				++$this->ts_largeur[$i];
				if($this->map->tilesets[$i]->spacing>0) $a+=$this->map->tilesets[$i]->spacing;
			}
			//var_dump($this->ts_largeur[$i]);
			assert($this->ts_largeur[$i]>0) or die('ts_largeur == 0');
			//var_dump($this->ts_largeur);die();
		}
		unset($i,$ts,$transc,$trans,$r,$g,$b,$color);
	}
	
	private function load_colors() {
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
		//$this->colors['white']=imagecolorallocatealpha($this->img, 255, 255, 255, 0);//blanc
		$this->colors['trans']=imagecolorallocatealpha($this->img, 255, 255, 255, 127);//transparent
		$this->colors['default']=imagecolorallocatealpha($this->img, 0, 0, 0, 0);//noir
		$this->colors['ligra']=imagecolorallocatealpha($this->img, 0xcc, 0xcc, 0xcc, 0);//light gray
		$this->colors['red']=imagecolorallocatealpha($this->img, 255, 0, 0, 0);//rouge
		$this->colors['green']=imagecolorallocatealpha($this->img, 0, 255, 0, 0);//vert
		$this->colors['blue']=imagecolorallocatealpha($this->img, 0, 0, 255, 0);//bleu
		$this->colors['yellow']=imagecolorallocatealpha($this->img, 255, 255, 0, 0);//yellow
		$this->colors['rose']=imagecolorallocatealpha($this->img, 255, 0, 255, 0);//rose
		$this->colors['cyan']=imagecolorallocatealpha($this->img, 0, 255, 255, 0);//cyan
		$this->colors['orange']=imagecolorallocatealpha($this->img, 255, 128, 0, 0);//orange
		$this->colors['magenta']=imagecolorallocatealpha($this->img, 255, 0, 128, 0);//magenta
		$this->colors['ligre']=imagecolorallocatealpha($this->img, 128, 255, 0, 0);//light green
		$this->colors['purple']=imagecolorallocatealpha($this->img, 128, 0, 255, 0);//purple
		$this->colors['licya']=imagecolorallocatealpha($this->img, 0, 255, 128, 0);//light cyan
		$this->colors['violet']=imagecolorallocatealpha($this->img, 0, 128, 255, 0);//violet
	}
	
	public function init_draw() {
		$this->img=imagecreatetruecolor($this->map->width*$this->map->tilewidth*$this->zoom, $this->map->height*$this->map->tileheight*$this->zoom);
		//$img=imagecreatetruecolor($width*$tilewidth/2, $height*$tileheight/2);
		
		if(function_exists('imageantialias')) {
			imageantialias($this->img, false);
		}
		imagealphablending($this->img, true);
		//imagealphablending($this->img, false);

		$this->load_colors();
		
		if(strlen($this->map->backgroundcolor)>0) {
			$r=hexdec(substr($this->map->backgroundcolor,0,2));
			$g=hexdec(substr($this->map->backgroundcolor,2,2));
			$b=hexdec(substr($this->map->backgroundcolor,4,2));
			$color = imagecolorallocatealpha($this->img, $r, $g, $b, 0);//opaque
			imagefill($this->img, 0, 0, $color);
			unset($r,$g,$b,$color);
		}
		else {
			imagefill($this->img, 0, 0, $this->colors['trans']);
		}
	}
	
	public function draw() {
		return $this->draw_layers();
	}
	
	private function draw_tile(&$ly, $cgid, $i=NULL, $j=NULL, &$o=NULL) {
		if($cgid==0) return;
		$ti=$this->map->get_tileset_index($cgid);
		if($ti==-1) {
			var_dump($cgid);
			die();
		}
		if(!is_object($o)) {
			if(strlen($this->map->tilesets[$ti]->name)>0&&in_array($this->map->tilesets[$ti]->name, $_SESSION['tilesets_nodraw'])) return;
		}
		$lid=$cgid-$this->map->tilesets[$ti]->firstgid;
		//var_dump($lid);print('<br/>'."\n");return;
		$tx=$lid%($this->ts_largeur[$ti]);
		$tx2=0;
		if($this->map->tilesets[$ti]->spacing>0) $tx2+=$this->map->tilesets[$ti]->spacing*$tx;
		if($this->map->tilesets[$ti]->margin>0) $tx2+=$this->map->tilesets[$ti]->margin;
		$ty=(int)($lid/$this->ts_largeur[$ti]);
		$ty2=0;
		if($this->map->tilesets[$ti]->spacing>0) $ty2+=$this->map->tilesets[$ti]->spacing*$ty;
		if($this->map->tilesets[$ti]->margin>0) $ty2+=$this->map->tilesets[$ti]->margin;
		//var_dump($tx,$tx2,$ty,$ty2);die();
		if($this->map->orientation=='orthogonal') {
			if(is_object($o)) {
				$dx=$o->x;
				$dy2=$o->y-$this->map->tilesets[$ti]->tileheight;
			}
			else {
				$dx=$i*$this->map->tilewidth;
				$dy2=($j+1)*$this->map->tileheight-$this->map->tilesets[$ti]->tileheight;
			}
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
			if($this->zoom==1) {
				image_copy_and_resize($this->img, $this->ts_imgs[$ti], $dx, $dy, $sx, $sy, $sw, $sh);
			}
			else {
				image_copy_and_resize($this->img, $this->ts_imgs[$ti], $dx*$this->zoom, $dy*$this->zoom, $sx, $sy, $sw*$this->zoom, $sh*$this->zoom, $sw, $sh);
			}
			if(!is_object($o)) $this->draw_inside_tilelayer($ly, $j, $i);
		}
		elseif($this->map->orientation=='isometric') {
			if(is_object($o)) {
				trigger_error('not yet implemented');
			}
			else {
				$dx=(($this->map->width-1+$i-$j)*$this->map->tilewidth/2);
				$dy2=($i+$j+1)*$this->map->tileheight/2-$this->map->tilesets[$ti]->tileheight/2;
			}
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
			if($this->zoom==1) {
				image_copy_and_resize($this->img, $this->ts_imgs[$ti], $dx, $dy, $sx, $sy, $sw, $sh);
			}
			else {
				image_copy_and_resize($this->img, $this->ts_imgs[$ti], $dx*$this->zoom, $dy*$this->zoom, $sx, $sy, $sw*$this->zoom, $sh*$this->zoom, $sw, $sh);
			}
			if(!is_object($o)) $this->draw_inside_tilelayer($ly, $j, $i);
			//if($lid==1) break(3);
		}
		//trigger_error('not yet implemented');
	}
	
	public function draw_inside_tilelayer(Layer $ly, $j, $i) {
	/*
	ly	layer
	j	row
	i	col
	*/
		if($this->map->orientation=='orthogonal') {
			//
		}
		elseif($this->map->orientation=='isometric') {
			//
		}
		return false;
	}
	
	public function draw_layers() {
		//ob_start();

		assert(count($this->ts_imgs)==count($this->map->tilesets)) or die('tilesets not loaded.');

		foreach($this->map->layers as $index=>$ly) {
			//var_dump($index, $ly);die();
			if($ly instanceof TileLayer) {
				$tl=$ly;
				//break;
				if(strlen($tl->name)>0&&in_array($tl->name, $_SESSION['layers_nodraw'])) continue;
				for($j=0;$j<$tl->height;++$j) {
					for($i=0;$i<$tl->width;++$i) {
						$cgid=$tl->get_tile($j*$tl->width+$i);
						$this->draw_tile($tl, $cgid, $i, $j);
					}
				}
			}
			elseif($ly instanceof ObjectLayer) {
				//die();
				$ol=$ly;
				if($this->draw_objects) {
					foreach($ol->getAllObjects() as $o) {
						if($o->polygon || $o->polyline) {
							imagerectangle($this->img, ($o->x-$o->getWidthL())*$this->zoom, ($o->y-$o->getHeightT())*$this->zoom,
								($o->x + $o->getWidthR())*$this->zoom, ($o->y + $o->getHeightB())*$this->zoom,
								$this->colors['ligra']);
							if($o->polyline) {
								assert(count($o->points)/2>1);
								$x=($o->x+$o->points[0])*$this->zoom;
								$y=($o->y+$o->points[1])*$this->zoom;
								imagesetthickness($this->img, 2);
								for($i=2;$i<count($o->points);$i+=2) {
									imageline($this->img, $x, $y, ($o->x+$o->points[$i])*$this->zoom, ($o->y+$o->points[$i+1])*$this->zoom, $this->colors['green']);
									$x=($o->x+$o->points[$i])*$this->zoom;
									$y=($o->y+$o->points[$i+1])*$this->zoom;
								}
								imagesetthickness($this->img, 1);
							}
							else if($o->polygon) {
								$ar=$o->points;
								for($i=0;$i<count($ar);$i+=2) {
									$ar[$i]*=$this->zoom;
									$ar[$i]+=$o->x*$this->zoom;
									$ar[$i+1]*=$this->zoom;
									$ar[$i+1]+=$o->y*$this->zoom;
								}
								imagesetthickness($this->img, 2);
								imagepolygon($this->img, $ar, count($ar)/2, $this->colors['green']);
								imagesetthickness($this->img, 1);
							}
							else if($o->name!='') {
								imagettftext($this->img, 10*$this->zoom, 0, ($o->x-$o->getWidthL())*$this->zoom, ($o->y-$o->getHeightT()-4)*$this->zoom, $this->colors['blue'], './courbd.ttf', $o->name);
								//imagestring($this->img, 3, ($o->x-$o->getWidthL())*$this->zoom, ($o->y-$o->getHeightT()-16)*$this->zoom, $o->name, $this->colors['blue']);
							}
						}
						elseif($o->ellipse) {
							imagesetthickness($this->img, 2);
							//imageellipse($this->img, $o->x+$o->width/2, $o->y+$o->height/2, $o->width, $o->height, $this->colors['purple']);//NOTE: doesn't work with setthickness, known bug (
							imagearc($this->img, $o->x+$o->width/2, $o->y+$o->height/2, $o->width, $o->height, 0, 180, $this->colors['purple']);
							imagearc($this->img, $o->x+$o->width/2, $o->y+$o->height/2, $o->width, $o->height, 180, 360, $this->colors['purple']);
							imagesetthickness($this->img, 1);
						}
						elseif(is_int($o->gid)) {
							$cgid=$o->gid;
							//var_dump($o);die();
							$this->draw_tile($ol, $cgid, NULL, NULL, $o);
						}
						else {
							imagesetthickness($this->img, 2);
							imagerectangle($this->img, $o->x*$this->zoom, $o->y*$this->zoom, ($o->x + $o->width)*$this->zoom, ($o->y + $o->height)*$this->zoom, $this->colors['green']);
							imagesetthickness($this->img, 1);
							if($o->name!='') {
								imagettftext($this->img, 10*$this->zoom, 0, $o->x*$this->zoom, ($o->y-4)*$this->zoom, $this->colors['blue'], './courbd.ttf', $o->name);
								//imagestring($this->img, 3, $o->x*$this->zoom, ($o->y-16)*$this->zoom, $o->name, $this->colors['blue']);
							}
						}
					}
				}
			}
			elseif($ly instanceof ImageLayer) {
				if($this->draw_imagelayers) {
					$il=$ly;
					$img_=create_image_from(dirname($this->map->filename).'/'.$il->source);
					if(function_exists('imageantialias')) {
						imageantialias($img_, false);
					}
					//imagealphablending($img_, true);
					imagealphablending($img_, false);
						$transc=$il->trans;
					$trans = imagecolorallocatealpha($img_, 255, 255, 255, 127);//transparent
					//$trans = imagecolorallocatealpha($img_, 255, 255, 255, 0);//opaque
					if((bool)$transc && $transc!='') {
						$r=hexdec(substr($transc,0,2));
						$g=hexdec(substr($transc,2,2));
						$b=hexdec(substr($transc,4,2));
						//var_dump($transc, $r, $g, $b);
						$color = imagecolorallocatealpha($img_, $r, $g, $b, 0);//opaque
						//var_dump(imagesx($img_), imagesy($this->ts_imgs[$i]));die();
						my_transparent($img_, $r, $g, $b, $trans);
						imagecolortransparent($img_, $color);
					}
					if($this->map->orientation=='orthogonal') {
						$sw=min($il->width *$this->map->tilewidth , $this->map->width *$this->map->tilewidth , imagesx($img_));
						$sh=min($il->height*$this->map->tileheight, $this->map->height*$this->map->tileheight, imagesy($img_));
						if($this->zoom==1) {
							image_copy_and_resize($this->img, $img_, $il->x, $il->y, 0, 0, $sw, $sh);
						}
						else {
							image_copy_and_resize($this->img, $img_, $il->x*$this->zoom, $il->y*$this->zoom, 0, 0, $sw*$this->zoom, $sh*$this->zoom, $sw, $sh);
						}
					}
					elseif($this->map->orientation=='isometric') {
						throw new Exception('image layer on isometric map not yet implemented.');
					}
					imagedestroy($img_);
					unset($img_);
					$img_=NULL;
				}
			}
			unset($tl, $ol, $il);
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