<?php
  /*
     num=XXX    show post (also just ?XXX)
     p=XXX      page number
     s=STRING   search string
     header=1   show small post header, only when viewing a post.
   */

error_reporting(E_ALL);
ini_set('display_errors','On');


$ngpath = '/var/spool/news/rec/games/roguelike/nethack/';
$ngname = 'rec.games.roguelike.nethack';

$ng_timedate_format = 'Y-m-d H:i:s';

$pagesize = 2500;

$curpage = 1;


date_default_timezone_set('Etc/UTC');

$overview = $ngpath . '.overview';

function get_topics_array($idxdata)
{
    $topics = array();
    foreach ($idxdata as $l) {
	$article = explode("\t", $l);
	$art = preg_replace('/^Re: /', '', $article[1]);
	$topics[$art][] = $article;
    }
    $topics = array_reverse($topics, TRUE);
    return $topics;
}


function showindextable($idxdata, $idxtype=1)
{
    global $ng_timedate_format;
    print '<table>';

    $topics = get_topics_array($idxdata);

    switch ($idxtype) {
    case 1:
	print '<tr>';
	print '<th>Topic</th>';
	print '<th>Author</th>';
	print '<th>Date</th>';
	print '<th>Posts</th>';
	print '</tr>';
	foreach ($topics as $t) {
	    $article = $t[0];
	    $narticles = count($t);
	    print '<tr>';
	    print '<td>';
	    $topic = htmlentities(substr($article[1], 0, 80));
	    $anums = array();
	    for ($i = 0; $i < $narticles; $i++) {
		$anums[] = $t[$i][0];
	    }
	    print "<a href='?".join(",", $anums)."'>".$topic."</a>";
	    print '</td>';

	    print '<td>';
	    $author = $article[2];
	    print htmlentities(preg_replace('/ <.*>\s*$/', '', $author));
	    print '</td>';

	    print '<td>';
	    if (($timestamp = strtotime($article[3])) === false) {
		print '???';
	    } else {
		print date($ng_timedate_format, $timestamp);
	    }
	    print '</td>';
	    print '<td>';
	    print $narticles;
	    print '</td>';
	    print '</tr>';
	}
	break;
    default:
	print '<tr>';
	print '<th>Topic</th>';
	print '<th>Author</th>';
	print '<th>Date</th>';
	print '</tr>';
	foreach ($topics as $t) {
	    foreach ($t as $article) {
		print '<tr>';

		print '<td>';
		$topic = htmlentities(substr($article[1], 0, 80));
		print "<a href='?".$article[0]."'>".$topic."</a>";
		print '</td>';

		print '<td>';
		$author = $article[2];
		print htmlentities(preg_replace('/ <.*>\s*$/', '', $author));
		print '</td>';

		print '<td>';
		if (($timestamp = strtotime($article[3])) === false) {
		    print '???';
		} else {
		    print date($ng_timedate_format, $timestamp);
		}
		print '</td>';
		print '</tr>';
	    }
	}

    }

    print '</table>';
}

function show_post($adata, $anum, $smallhead=0)
{
    list($aheaders, $abody) = explode("\n\n", htmlentities($adata), 2);

    if ($smallhead) {
	$tmp = explode("\n", $aheaders);
	$tmps = preg_grep("/^(From|Subject|Date): .+/", $tmp);
	$aheaders = join("\n", array_values($tmps));
    }

    /*
    if ($anum > 1) {
	    print '<a href="?'.($anum-1).'">prev</a>';
    } else {
	    print 'prev';
    }
    print ' - ';
    print '<a href="?">index</a>';
    print ' - ';
    print '<a href="?'.($anum+1).'">next</a>';
    */
    print '<a name="p'.$anum.'"></a>';
    print '<pre class="article">';
    print '<div class="aheader">'.$aheaders.'</div>';
    print "\n";
    print '<div class="abody">'.$abody.'</div>';
    print '</pre>';
}


print '<html><head>
<link rel="stylesheet" type="text/css" media="screen" href="newsgroup.css">
<title>'.$ngname.'</title></head>';

print '<body>';

print '<h1>'.$ngname.' browser</h1>';

if (!isset($_GET['num']) && preg_match('/^[0-9]+(,[0-9]+)*/', $_SERVER['QUERY_STRING'])) {
    $tmp = explode("&", $_SERVER['QUERY_STRING']);
    $_GET['num'] = $tmp[0];
}

if (isset($_GET['num']) && preg_match('/^[0-9]+(,[0-9]+)*$/', $_GET['num'])) {

    $anums = array_unique(explode(",", $_GET['num']));
    $header = (isset($_GET['header']) ? $_GET['header'] : 0);
    $num_posts = count($anums);

    $i = 1;
    foreach ($anums as $anum) {
	$article = $ngpath . $anum;
	if (file_exists($article)) {
	    $adata = file_get_contents($article);
	    show_post($adata, $anum, $header);
	} else {
	    print '<p>Post '.$anum.' does not exist.';
	}
	if ($i++ < $num_posts) {
	    print '<hr>';
	}
    }

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

    /*
    print '<a href="?p='.($curpage+1).'">prev</a>';
    print ' - ';
    if ($curpage > 1) {
	print '<a href="?p='.($curpage-1).'">next</a>';
    } else {
	print 'next';
    }
    */

    showindextable($idxdata);
}

print '</body></html>';