<?php

$ngversion = 'v0.1';

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
<title>'.($first ? ($first.' - ') : '').$title.'</title></head>';

    print '<body>';
    print '<a name="top"></a>';
    print '<h1>'.$title.'</h1>';
    if ($first) {
       print '<h2>'.$first.'</h2>';
    }
}

function page_foot()
{
    global $ngversion;
    print '<div class="footer"><a href="http://github.com/paxed/ngbrowser">ngbrowser '.$ngversion.'</a></div></body></html>';
}