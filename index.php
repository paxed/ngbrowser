<?php
  /*
     num=XXX    show post (also just ?XXX)
     p=XXX      page number
     s=STRING   search string
     header=1   show small post header, only when viewing a post.
     casesense=1 search is case insensitive.
   */

error_reporting(E_ALL);
ini_set('display_errors','On');

include "common.php";

session_start();

$ngpath = '/var/spool/leafnode/rec/games/roguelike/nethack/';
$ngname = 'rec.games.roguelike.nethack';

$ng_timedate_format = 'Y-m-d H:i:s';

$pagesize = 1000;
$curpage = 1;

$threaded_index = (isset($_COOKIE['ng-threaded']) ? $_COOKIE['ng-threaded'] : 1);
$casesense = 0;

$post_headers = (isset($_GET['header']) ? $_GET['header'] : (isset($_COOKIE['ng-postheader']) ? $_COOKIE['ng-postheader'] : 0));

$max_search_results = 200;

$searchable_chars_preg = '/^[a-zA-Z0-9 :;,\.@#_-]+$/';

$overview = $ngpath . '.overview';

$thread_subject = null; /* hacky */

if (isset($_COOKIE['ng-markallread'])) {
    unset($_COOKIE['ng-markallread']);
    mk_cookie('ng-markallread');
    mk_cookie('ng-lastvisit');
    unset($_COOKIE['ng-lastvisit']);
    unset($_SESSION['ng-lastvisit']);
}

$max_index_entry = 0;

$lastvisit = (isset($_SESSION['ng-lastvisit']) ? $_SESSION['ng-lastvisit'] :
	      (isset($_COOKIE['ng-lastvisit']) ? $_COOKIE['ng-lastvisit'] : time()));

$wordwrap_linelen = (isset($_SESSION['ng-wordwrap']) && ($_SESSION['ng-wordwrap']==1) && isset($_SESSION['ng-wordwraplen'])) ? $_SESSION['ng-wordwraplen'] :
    ((isset($_COOKIE['ng-wordwrap']) && ($_COOKIE['ng-wordwrap']==1) && isset($_COOKIE['ng-wordwraplen'])) ? $_COOKIE['ng-wordwraplen'] : 0);

if (!preg_match('/^[0-9]+$/', $wordwrap_linelen)) $wordwrap_linelen = 0;

function searchform($str='', $casesense=0)
{
    if ($casesense) {
	$casesense = ' checked';
    } else {
	$casesense = '';
    }
    print '<div class="searchform">';
    print '<form method="POST" action="./">';
    print 'Search:<input id="search_text_input" type="text" name="searchstr" value="'.$str.'">';
    print '&nbsp;<span class="casesense"><label><input type="checkbox" name="casesense"'.$casesense.'>Case insensitive</label></span>';
    print '</form>';
    print '</div>';
}

function get_topics_array($idxdata)
{
    global $lastvisit;
    $topics = array();
    foreach ($idxdata as $l) {
	$article = explode("\t", $l);
	if (!isset($article[1])) { print $l.'<br>'; }
	$art = preg_replace('/^Re: /', '', trim($article[1]));
	$art = trim(implode(' ', preg_split('/\s+/', $art)));
	if (isset($topics[$art]['articles'])) {
	    $tmp = $topics[$art];
	    unset($topics[$art]);
	    $topics[$art] = $tmp;
	}
	$article[3] = strtotime($article[3]);
	$topics[$art]['articles'][] = $article;
	if ($article[3] >= $lastvisit) {
	    if (!isset($topics[$art]['newer'])) { $topics[$art]['newer'] = 0; }
	    $topics[$art]['newer']++;
	    $topics[$art]['newer_posts'][] =  $article[0];
	}
    }
    $topics = array_reverse($topics, TRUE);
    return $topics;
}


function showindextable($idxdata)
{
    global $ng_timedate_format, $lastvisit, $threaded_index;
    global $max_index_entry;
    print '<table class="idx">';
    $idxnum = 1;

    switch ($threaded_index) {
    case 1:
	$topics = get_topics_array($idxdata);
	print '<tr>';
	print '<th>Posts</th>';
	print '<th>Topic</th>';
	print '<th>Author</th>';
	print '<th>Date</th>';
	print '</tr>';
	foreach ($topics as $t) {
	    $article = $t['articles'][0];
	    $narticles = count($t['articles']);
	    print '<tr id="idx-'.($idxnum++).'" class="idxrow">';
	    print '<td>' . $narticles;
	    if (isset($t['newer'])) {
		print '&nbsp;<b>(';
		if (isset($_COOKIE['ng-newlinks']) && ($_COOKIE['ng-newlinks'] == 1)) {
		    print "<a href='?".join_ids($t['newer_posts'])."'>".$t['newer']."</a>";
		} else {
		    print $t['newer'];
		}
		print ')</b>';
	    }
	    print '</td>';
	    print '<td>';
	    $topic = htmlentities(substr($article[1], 0, 80), ENT_QUOTES, 'ISO-8859-1');
	    $anums = array();
	    for ($i = 0; $i < $narticles; $i++) {
		$anums[] = $t['articles'][$i][0];
	    }
	    print "<a id='pidx-".($idxnum-1)."' href='?".join_ids($anums)."'>".$topic."</a>";
	    print '</td>';

	    print '<td>';
	    $author = $article[2];
	    print htmlentities(preg_replace('/ <.*>\s*$/', '', $author), ENT_QUOTES, 'ISO-8859-1');
	    print '</td>';

	    print '<td>';
	    print date($ng_timedate_format, $article[3]);
	    print '</td>';
	    print '</tr>';
	}
	break;
    default:
	foreach ($idxdata as $l) {
	    $article = explode("\t", $l);
	    $article[3] = strtotime($article[3]);
	    $topics[] = $article;
	}
	$topics = array_reverse($topics);

	print '<tr>';
	print '<th>Topic</th>';
	print '<th>Author</th>';
	print '<th>Date</th>';
	print '</tr>';
	foreach ($topics as $article) {
		$isnewer = ($article[3] >= $lastvisit);
		print '<tr id="idx-'.($idxnum++).'" class="idxrow'.($isnewer ? ' newer' : '').'">';

		print '<td>';
		$topic = htmlentities(substr($article[1], 0, 80), ENT_QUOTES, 'ISO-8859-1');
		print ($isnewer ? '<b>' : '')."<a id='pidx-".($idxnum-1)."' href='?".$article[0]."'>".$topic."</a>".($isnewer ? '</b>' : '');
		print '</td>';

		print '<td>';
		$author = $article[2];
		print htmlentities(preg_replace('/ <.*>\s*$/', '', $author), ENT_QUOTES, 'ISO-8859-1');
		print '</td>';

		print '<td>';
		print date($ng_timedate_format, $article[3]);
		print '</td>';
		print '</tr>';
	}

    }

    print '</table>';

    $max_index_entry = $idxnum;
}

function show_post($adata, $anum, $smallhead=0)
{
    global $wordwrap_linelen, $thread_subject;
    list($aheaders, $abody) = explode("\n\n", htmlentities($adata, ENT_QUOTES, 'ISO-8859-1'), 2);

    if (preg_match("/\nContent-Transfer-Encoding: quoted-printable\n/", $aheaders)) {
	$abody = quoted_printable_decode($abody);
    }

    if (!$thread_subject) {
	if (preg_match("/\nSubject: (.+)\n/", $aheaders, $matches)) {
	    $thread_subject = $matches[1];
	}
    }

    if (!$smallhead) {
	$tmp = explode("\n", $aheaders);
	$tmps = preg_grep("/^(From|Subject|Date): /", $tmp);
	rsort($tmps);
	$aheaders = join("\n", array_values($tmps));
    }

    if (isset($_COOKIE['ng-urllinks']) && ($_COOKIE['ng-urllinks'] == '1')) {
	$abody = preg_replace('/((https?|ftp):\/\/([\w\d\-]+)(\.[\w\d\-]+){1,})([\/\?\w\d\.=&+%~_\-#;:@]+)?/','<A href="\\1\\5">\\1\\5</A>',$abody);
    }

    if (preg_match("/\n-- \n/", $abody)) {
	list($abody, $abodysig) = preg_split("/\n-- \n/", $abody, 2);
    }

    print '<div class="article">';
    print '<pre class="aheader">'.$aheaders.'</pre>';
    print "\n";
    print '<pre class="abody">';
    if ($wordwrap_linelen > 10)
	print wordwrap($abody, $wordwrap_linelen);
    else
	print $abody;
    if (isset($abodysig)) {
	print '<pre class="sig">'."-- \n".$abodysig.'</pre>';
    }
    print '</pre>';
    print '</div>';
}

function toolstrip_index()
{
    print '<div class="tools">';
    print '<a href="settings.php">Settings</a>';
    print '</div>';
}

function toolstrip_post($smallheader)
{
    print '<div class="tools">';
    print '<a href="./">Index</a>&nbsp;';
    if ($smallheader) {
	$tmp = preg_replace('/&header=1/', '', $_SERVER['QUERY_STRING']);
	print '<a href="?'.$tmp.'">Small headers</a>';
    } else {
	$tmp = preg_replace('/&header=0/', '', $_SERVER['QUERY_STRING']);
	print '<a href="?'.$tmp.'&amp;header=1">Full headers</a>';
    }
    print '&nbsp;<a href="settings.php">Settings</a>';
    print '</div>';
}

function show_post_page($anums)
{
    global $ngpath;
    global $post_headers;
    $num_posts = count($anums);

    toolstrip_post($post_headers);

    $i = 1;
    foreach ($anums as $anum) {
	print '<a name="pn'.$i.'"></a>';
	print '<a name="p'.$anum.'"></a>';
	print '<div class="postctrl">';
	if ($num_posts > 1) {
	    print '#'.$i.'&nbsp;';
	}
	if ($i > 1) {
	    print '<a href="#p'.$anums[$i - 2].'">&lt;</a>&nbsp;';
	    print '<a href="#top">^</a>';
	}
	if ($i < $num_posts) {
	    print '&nbsp;<a href="#p'.$anums[$i].'">&gt;</a>';
	}
	print '</div>';
	$article = $ngpath . $anum;
	if (file_exists($article)) {
	    $adata = file_get_contents($article);
	    show_post($adata, $anum, $post_headers);
	} else {
	    print '<p>Post '.$anum.' does not exist.';
	}
	if ($i++ < $num_posts) {
	    print '<hr>';
	}
    }
}

function show_search_page($searchstr, $casesense)
{
    global $overview, $max_search_results;
    toolstrip_index();
    $showit = 0;
    $results = '';

    if (strlen($searchstr) < 3) {
      $results .= 'Sorry, need a longer search string.';
    } else {
	$results .= 'Searched for: "'.$searchstr.'"';
	if ($casesense) {
	  $results .= ' (no case)';
	}
	$searchstr = preg_replace("/\./", '\.', $searchstr);
	if ($casesense) {
	    $casesensestr = ' -i ';
	} else {
	    $casesensestr = '';
	}
	$idxdata = `grep $casesensestr "$searchstr" "$overview"`;
	$idxdata = explode("\n", rtrim($idxdata));
	if ($idxdata[0] == "") {
	    $results .= '<p>No results.';
	} else {
	    $nresults = count($idxdata);
	    $results .= '<br>' . $nresults . ' results.';
	    if ($nresults > $max_search_results) {
		$results .= ' Only showing '.$max_search_results.' newest hits.';
		$idxdata = array_slice($idxdata, -$max_search_results, $max_search_results);
	    }
	    $showit = 1;
	}
    }
    if ($results) {
      print '<table class="searchresult"><tr><td>' . $results . '</td></tr></table>';
    }
    if ($showit) {
      showindextable($idxdata);
    }
    searchform($searchstr, $casesense);
}

function show_index_page($curpage, $searchstr, $casesense)
{
    global $overview, $pagesize;
    if ($curpage < 2) {
	$idxdata = `tail -$pagesize "$overview"`;
	$curpage = 1;
    } else {
	$offset = $curpage * $pagesize;
	$idxdata = `tail -$offset "$overview" | head -$pagesize`;
    }
    $idxdata = explode("\n", rtrim($idxdata));

    toolstrip_index();
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
    searchform($searchstr, $casesense);
}

$searchstr = (isset($_COOKIE['ng-searchstr']) ? $_COOKIE['ng-searchstr'] : '');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['searchstr']) && !isset($_GET['s'])) {
	$_GET['s'] = $_POST['searchstr'];
    }
    if (isset($_POST['flat']) && !isset($_GET['flat'])) {
	$_GET['flat'] = $_POST['flat'];
    }
    if (isset($_POST['casesense']) && !isset($_GET['casesense'])) {
	$_GET['casesense'] = $_POST['casesense'];
    }
}

$action = '';

if (!isset($_GET['num']) && preg_match('/^[0-9]+(,[0-9]+)*/', $_SERVER['QUERY_STRING'])) {
    $tmp = explode("&", $_SERVER['QUERY_STRING']);
    $_GET['num'] = $tmp[0];
    $action = 'showpost';
} else if (isset($_GET['s']) && preg_match($searchable_chars_preg, $_GET['s'])) {
    $action = 'search';
} else if (preg_match($searchable_chars_preg, urldecode($_SERVER['QUERY_STRING']))) {
    $tmp = explode("&", $_SERVER['QUERY_STRING']);
    $_GET['s'] = $tmp[0];
    $action = 'search';
}

if (isset($_GET['flat'])) {
    $threaded_index = (($_GET['flat']) ? 0 : 1);
}

if (isset($_GET['s'])) {
    mk_cookie('ng-searchstr', $_GET['s']);
}


$casesense = ((isset($_GET['casesense']) && ($_GET['casesense']=='on')) ? 1 : (isset($_COOKIE['ng-casesense']) ? ($_COOKIE['ng-casesense'] == 1) : 0));


mk_cookie('ng-threaded', $threaded_index);
mk_cookie('ng-lastvisit', time());
$_SESSION['ng-lastvisit'] = $lastvisit;

switch ($action) {
case 'showpost':
    $anums = array_unique(explode_ids($_GET['num']));
    ob_start();
    show_post_page($anums);
    $pagestr = ob_get_clean();
    page_head($ngname, ($thread_subject ? ($thread_subject) : null));
    print $pagestr;
    break;
case 'search':
    $searchstr = urldecode($_GET['s']);
    page_head($ngname);
    show_search_page($searchstr, $casesense);
    break;
default:
  /*
    if (isset($_GET['p']) && preg_match('/^[0-9]+$/', $_GET['p'])) {
	$curpage = $_GET['p'];
    }
  */
    page_head($ngname);
    show_index_page($curpage, $searchstr, $casesense);
}

page_foot($action, $max_index_entry);

