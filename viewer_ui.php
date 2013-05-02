<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-15"/>
<title>(PHP) TMX Viewer [by sebbu]</title>
</head>
<style>
TABLE, TD {
	border: 2px solid #000000;
	border-collapse: collapse;
	empty-cells: show;
	padding: 2px;
}
#f1_list TR,
#f1_list TD {
	display: none;
	border-collapse: collapse;
	border: 0px none #FFFFFF;
}
#f1_url TR,
#f1_url TD {
	display: none;
	border-collapse: collapse;
	border: 0px none #FFFFFF;
}
#f1_file TR,
#f1_file TD {
	display: none;
	border-collapse: collapse;
	border: 0px none #FFFFFF;
}
</style>
<body>
<script type="text/javascript">
function show_f1_choice_list() {
	/*show list */
	document.getElementById('f1_list' ).cells.item(0)/*td*/.style.display="table-cell";
	document.getElementById('f1_list' ).cells.item(0)/*td*/.style.visibility="visible";
	/*hide url */
	document.getElementById('f1_url' ).cells.item(0)/*td*/.style.display="none";
	document.getElementById('f1_url' ).cells.item(0)/*td*/.style.visibility="hidden";
	/*hide file*/
	document.getElementById('f1_file').cells.item(0)/*td*/.style.display="none";
	document.getElementById('f1_file').cells.item(0)/*td*/.style.visibility="hidden";
}
function show_f1_choice_url() {
	/*show url */
	document.getElementById('f1_url' ).cells.item(0)/*td*/.style.display="table-cell";
	document.getElementById('f1_url' ).cells.item(0)/*td*/.style.visibility="visible";
	/*hide list */
	document.getElementById('f1_list' ).cells.item(0)/*td*/.style.display="none";
	document.getElementById('f1_list' ).cells.item(0)/*td*/.style.visibility="hidden";
	/*hide file*/
	document.getElementById('f1_file').cells.item(0)/*td*/.style.display="none";
	document.getElementById('f1_file').cells.item(0)/*td*/.style.visibility="hidden";
}
function show_f1_choice_file() {
	/*show file*/
	document.getElementById('f1_file').cells.item(0)/*td*/.style.display="table-cell";
	document.getElementById('f1_file').cells.item(0)/*td*/.style.visibility="visible";
	/*hide list */
	document.getElementById('f1_list' ).cells.item(0)/*td*/.style.display="none";
	document.getElementById('f1_list' ).cells.item(0)/*td*/.style.visibility="hidden";
	/*hide url */
	document.getElementById('f1_url' ).cells.item(0)/*td*/.style.display="none";
	document.getElementById('f1_url' ).cells.item(0)/*td*/.style.visibility="hidden";
}
function show_f1_choice_NONE() {
	/*hide list */
	document.getElementById('f1_list' ).cells.item(0)/*td*/.style.display="none";
	document.getElementById('f1_list' ).cells.item(0)/*td*/.style.visibility="hidden";
	/*hide url */
	document.getElementById('f1_url' ).cells.item(0)/*td*/.style.display="none";
	document.getElementById('f1_url' ).cells.item(0)/*td*/.style.visibility="hidden";
	/*hide file*/
	document.getElementById('f1_file').cells.item(0)/*td*/.style.display="none";
	document.getElementById('f1_file').cells.item(0)/*td*/.style.visibility="hidden";
}
window.onload=function() {
	/*force none selected*/
	document.getElementById('f1_choice_list' ).checked=false;
	document.getElementById('f1_choice_url' ).checked=false;
	document.getElementById('f1_choice_file').checked=false;
	/*event handler*/
	document.getElementById('f1_choice_list' ).onclick=show_f1_choice_list;
	document.getElementById('f1_choice_url' ).onclick=show_f1_choice_url;
	document.getElementById('f1_choice_file').onclick=show_f1_choice_file;
	document.getElementById('reset1').onclick=show_f1_choice_NONE;
	/* force hide form parts*/
	show_f1_choice_NONE();
};
</script>

<h1>MAP</h1>

<form id="form1" action="viewer_view.php" method="POST" enctype="multipart/form-data">
<input type="hidden" name="called_from" value="script"/>
<table id="table1">
  <tr>
	<td colspan="2"><label for="f1_choice_list"><input type="radio" name="choice" value="LIST" id="f1_choice_list"/>LIST</label></td>
	<td colspan="2"><label for="f1_choice_url"><input type="radio" name="choice" value="URL" id="f1_choice_url"/>URL</label></td>
	<td colspan="2"><label for="f1_choice_file"><input type="radio" name="choice" value="FILE" id="f1_choice_file" disabled="disabled"/>FILE</label></td>
  </tr>
  <tr id="f1_list">
	<td colspan="6"><select name="list" size="1">
<option value="">&nbsp;</option>
<?php
$files=array();
$files=array_merge($files, glob('../../tmw/*.tmx'));
$files=array_merge($files, glob('../../tmx/*.tmx'));
$files=array_merge($files, glob('../../maps/*.tmx'));
$files=array_merge($files, glob('../tmw/*.tmx'));
$files=array_merge($files, glob('../tmx/*.tmx'));
$files=array_merge($files, glob('../maps/*.tmx'));
$files=array_merge($files, glob('../*.tmx'));
$files=array_merge($files, glob('tmw/*.tmx'));
$files=array_merge($files, glob('tmx/*.tmx'));
$files=array_merge($files, glob('maps/*.tmx'));
//$files=array_merge($files, glob('*/*.tmx'));
$files=array_merge($files, glob('*.tmx'));
$files=array_values(array_unique($files));
foreach($files as $file) {
	echo '<option value="'.$file.'">'.$file.'</option>'."\r\n";
}
?></select></td>
  </tr>
  <tr id="f1_url">
	<td colspan="6"><input type="text" name="url" size="50"/></td>
  </tr>
  <tr id="f1_file">
	<td colspan="6"><input type="file" name="file" size="10"/></td>
  </tr>
  <tr>
	<td colspan="3"><input type="submit" id="submit1" value="Envoyer"/></td>
	<td colspan="3"><input type="reset" id="reset1" value="Remettre &agrave; z&eacute;ro"/></td>
  </tr>
</table>
</form>

</body>
</html>