<?php

$ngversion = 'v0.2';

$ng_tz = 'Etc/UTC';

date_default_timezone_set($ng_tz);

function mk_cookie($name, $data = null)
{
    if ($data !== null) {
       setcookie($name, $data, time()+3600*24*365, '/', $_SERVER['SERVER_NAME']);
       $_COOKIE[$name] = $data;
    } else {
       setcookie($name, '', time()-3600, '/', $_SERVER['SERVER_NAME']);
       unset($_COOKIE[$name]);
    }
}

function page_head($title, $first=null)
{
    print '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"'.
      ' "http://www.w3.org/TR/html4/loose.dtd">';

    print '<html><head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<link rel="stylesheet" type="text/css" media="screen" href="newsgroup.css">
<script src="newsgroup.js" type="text/javascript"></script>
<title>'.($first ? ($first.' - ') : '').$title.'</title></head>';

    print '<body>';
    print '<a name="top"></a>';
    print '<h1>'.$title.'</h1>';
    if ($first) {
       print '<h2>'.$first.'</h2>';
    }
}

function page_foot($pagetype="",$max_index_entry=0)
{
    global $ngversion;
    print '
<script type="text/javascript">
<!--
var pagetype = "'.$pagetype.'";
var max_index_entry = '.$max_index_entry.';
document.write("<"+"div class=\'footer\'><"+"a href=\'http://github.com/paxed/ngbrowser\'>ngbrowser '.$ngversion.'</"+"a></"+"div>");
//-->
</script>
';
    print '</body></html>';
}

function join_ids($ids)
{
  $tmp = array();
  $i = 0;
  $len = count($ids);
  while ($i < $len) {
    $j = $i;
    while (($j < $len-1) && ($ids[$j] == ($ids[$j+1] - 1))) $j++;
    if ($j == $i) {
      $tmp[] = $ids[$i];
      $i++;
    } else if ($j == ($i + 1)) {
      $tmp[] = $ids[$i];
      $tmp[] = $ids[$j];
      $i += 2;
    } else {
      $tmp[] = $ids[$i] . '-' . $ids[$j];
      $i = $j + 1;
    }
  }
  return join(',', $tmp);
}


function explode_ids($ids)
{
  $tmp = array();
  foreach (explode(',',$ids) as $i) {
    if (preg_match('/^(\d+)-(\d+)$/', $i, $matches)) {
      for ($j = min($matches[1],$matches[2]); $j <= max($matches[1],$matches[2]); $j++)
	$tmp[] = $j;
    } else {
      $tmp[] = $i;
    }
  }
  return $tmp;
}

