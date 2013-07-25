<?php

function rotate90cw_lid($lid) {
	assert($lid>=0 && $lid<6*9);
	//if($lid==-1) return -1;
	if($lid==0) return 0;
	if($lid>0 && $lid<9) return -1;
	$x=(int)($lid%9);
	$y=(int)($lid/9);
	//var_dump($x,$y);die();
	//angles
	if($y==0 && $x==0) { /*do nothing*/ }
	else if($y==1 && ($x%3)==0) { $x+=2; }
	else if($y==1 && ($x%3)==2) { $y+=2; }
	else if($y==3 && ($x%3)==2) { $x-=2; }
	else if($y==3 && ($x%3)==0) { $y-=2; }
	//bords
	else if($y==1 && ($x%3)==1) { $x+=1; $y+=1; }
	else if($y==2 && ($x%3)==2) { $x-=1; $y+=1; }
	else if($y==3 && ($x%3)==1) { $x-=1; $y-=1; }
	else if($y==2 && ($x%3)==0) { $x+=1; $y-=1; }
	//middles
	else if($y==2 && ($x%3)==1) { /*do nothing*/ }
	//hole angles
	else if($y==4 && ($x%2)==0) { $x+=1; }
	else if($y==4 && ($x%2)==1) { $y+=1; }
	else if($y==5 && ($x%2)==1) { $x-=1; }
	else if($y==5 && ($x%2)==0) { $y-=1; }
	return $x+$y*9;
}

function rotate90ccw_lid($lid) {
	assert($lid>=0 && $lid<6*9);
	//if($lid==-1) return -1;
	if($lid==0) return 0;
	if($lid>0 && $lid<9) return -1;
	$x=(int)($lid%9);
	$y=(int)($lid/9);
	//var_dump($x,$y);die();
	//angles
	if($y==0 && $x==0) { /*do nothing*/ }
	else if($y==1 && ($x%3)==0) { $y+=2; }
	else if($y==1 && ($x%3)==2) { $x-=2; }
	else if($y==3 && ($x%3)==2) { $y-=2; }
	else if($y==3 && ($x%3)==0) { $x+=2; }
	//bords
	else if($y==1 && ($x%3)==1) { $x-=1;$y+=1; }
	else if($y==2 && ($x%3)==2) { $x-=1;$y-=1; }
	else if($y==3 && ($x%3)==1) { $x+=1;$y+=1; }
	else if($y==2 && ($x%3)==0) { $x+=1;$y-=1; }
	//middles
	else if($y==2 && ($x%3)==1) { /*do nothing*/ }
	//hole angles
	else if($y==4 && ($x%2)==0) { $y+=1; }
	else if($y==4 && ($x%2)==1) { $x-=1; }
	else if($y==5 && ($x%2)==1) { $y-=1; }
	else if($y==5 && ($x%2)==0) { $x+=1; }
	return $x+$y*9;
}

function rotate180_lid($lid) {
	assert($lid>=0 && $lid<6*9);
	//if($lid==-1) return -1;
	if($lid==0) return 0;
	if($lid>0 && $lid<9) return -1;
	$x=(int)($lid%9);
	$y=(int)($lid/9);
	if($y==0 && $x==0) { /*do nothing*/ }
	else if($y==1 && ($x%3)==0) { $x+=2;$y+=2; }
	else if($y==1 && ($x%3)==2) { $x-=2;$y+=2; }
	else if($y==3 && ($x%3)==2) { $x-=2;$y-=2; }
	else if($y==3 && ($x%3)==0) { $x+=2;$y-=2; }
	//bords
	else if($y==1 && ($x%3)==1) { $y+=2; }
	else if($y==2 && ($x%3)==2) { $x-=2; }
	else if($y==3 && ($x%3)==1) { $y-=2; }
	else if($y==2 && ($x%3)==0) { $x+=2; }
	//middles
	else if($y==2 && ($x%3)==1) { /*do nothing*/ }
	//hole angles
	else if($y==4 && ($x%2)==0) { $x+=1;$y+=1; }
	else if($y==4 && ($x%2)==1) { $x-=1;$y+=1; }
	else if($y==5 && ($x%2)==1) { $x-=1;$y-=1; }
	else if($y==5 && ($x%2)==0) { $x+=1;$y-=1; }
	return $x+$y*9;
}
?>