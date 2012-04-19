<?php

$ngpath = '/var/spool/news/rec/games/roguelike/nethack/';
$ngname = 'rec.games.roguelike.nethack';

print '<html><head>
<link rel="stylesheet" type="text/css" media="screen" href="newsgroup.css">
<title>'.$ngname.'</title></head>';

print '<body>';

if (isset($_GET['num']) && preg_match('/^[0-9]+$/', $_GET['num'])) {

    $article = $ngpath . $_GET['num'];
    $adata = file_get_contents($article);

    print '<pre>';
    print $adata;
    print '</pre>';

} else {

    $overview = $ngpath . '.overview';

    $idxdata = file($overview);

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
	print "<a href='?num=".$article[0]."'>".htmlentities($article[1])."</a>";
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

print '</body></html>';