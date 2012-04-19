<?php

$ngpath = '/var/spool/news/rec/games/roguelike/nethack/';
$ngname = 'rec.games.roguelike.nethack';

print '<html><head>

 <STYLE type="text/css">

 tr.odd { background-color: #f0f0f0; }
 tr:hover { background-color: #e0e0e0; }

 </STYLE>


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

    print '<th>Topic</th>';
    print '<th>Author</th>';
    print '<th>Date</th>';

    $i = 0;

    foreach ($idxdata as $l) {

	$article = explode("\t", $l);

	if (($i % 2)) {
	    print '<tr>';
	} else {
	    print '<tr class="odd">';
	}

	print '<td>';
	print "<a href='?num=".$article[0]."'>".$article[1]."</a>";
	print '</td>';

	print '<td>';
	print $article[2];
	print '</td>';

	print '<td>';
	print $article[3];
	print '</td>';


	print '<tr>';

	$i++;

    }

    print '</table>';

}

print '</body></html>';