<?php
  /*
     num=XXX    show post
     p=XXX      page number
     s=STRING   search string
   */

error_reporting(E_ALL);
ini_set('display_errors','On');


$ngpath = '/var/spool/news/rec/games/roguelike/nethack/';
$ngpath = '/home/paxed/rgrn/nethack/';
$ngname = 'rec.games.roguelike.nethack';

$pagesize = 1000;

$curpage = 1;

$overview = $ngpath . '.overview';


function showindextable($idxdata)
{
    print '<table>';
    
    print '<tr>';
    print '<th>Topic</th>';
    print '<th>Author</th>';
    print '<th>Date</th>';
    print '</tr>';

    foreach ($idxdata as $l) {

	$article = explode("\t", $l);

	print '<tr>';

	print '<td>';
	$topic = htmlentities(substr($article[1], 0, 80));
	print "<a href='?num=".$article[0]."'>".$topic."</a>";
	print '</td>';

	print '<td>';
	$author = $article[2];
	print htmlentities(preg_replace('/ <.*>\s*$/', '', $author));
	print '</td>';

	print '<td>';
	print $article[3];
	print '</td>';

	print '</tr>';

    }

    print '</table>';
}


print '<html><head>
<link rel="stylesheet" type="text/css" media="screen" href="newsgroup.css">
<title>'.$ngname.'</title></head>';

print '<body>';

print '<h1>'.$ngname.' browser</h1>';

if (isset($_GET['num']) && preg_match('/^[0-9]+$/', $_GET['num'])) {

    $article = $ngpath . $_GET['num'];
    $adata = file_get_contents($article);

    print '<pre>';
    print $adata;
    print '</pre>';

} else if (isset($_GET['s']) && preg_match('/^[a-zA-Z0-9]+$/', trim($_GET['s']))) {
    $searchstr = trim(urldecode($_GET['s']));
    if (strlen($searchstr) < 3) {
	print '<p>Sorry, need a longer search string.';
    } else {
	$idxdata = `grep "$searchstr" "$overview"`;
	$idxdata = explode("\n", rtrim($idxdata));
	showindextable($idxdata);
    }

} else {

    if (isset($_GET['p']) && preg_match('/^[0-9]+$/', $_GET['p'])) {
	$curpage = $_GET['p'];
    }

    if ($curpage < 2) {
	$idxdata = `tail -$pagesize "$overview"`;
	$curpage = 1;
    } else {
	$offset = $curpage * $pagesize;
	$idxdata = `tail -$offset "$overview" | head -$pagesize`;
    }
    $idxdata = explode("\n", rtrim($idxdata));

    print '<a href="?p='.($curpage+1).'">prev</a>';
    print ' - ';
    if ($curpage > 1) {
	print '<a href="?p='.($curpage-1).'">next</a>';
    } else {
	print 'next';
    }

    showindextable($idxdata);
}

print '</body></html>';