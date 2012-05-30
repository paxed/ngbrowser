<?php

include "common.php";

function mk_checkbox($name, $desc)
{
  $checked = (($_POST[$name] == '1') ? ' checked' : '');
  return '<span class="set-'.$name.'"><label><input type="checkbox" name="'.$name.'"'.$checked.'>'.$desc.'</label></span>';
}

function check_checkbox($name)
{
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST[$name])) {
      $_POST[$name] = 0;
    } else {
      $_POST[$name] = 1;
    }
  } else {
    if ($_COOKIE['ng-'.$name]) {
      $_POST[$name] = (($_COOKIE['ng-'.$name] == '1') ? 1 : 0);
    } else {
      $_POST[$name] = 0;
    }
  }

  if (isset($_POST[$name]) && ($_POST[$name] == 1)) {
    mk_cookie('ng-'.$name, 1);
  } else {
    mk_cookie('ng-'.$name, 0);
  }
}

function mk_input($name, $desc, $value=NULL)
{
  $val = (isset($_POST[$name]) ? $_POST[$name] : $value);
  $len = (isset($value) ? strlen($value) : 10);
  return '<span class="set-'.$name.'"><label>'.$desc.'<input type="text" name="'.$name.'" value="'.$val.'" size="'.$len.'"></label></span>';
}

function check_input($name)
{
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  } else {
    if ($_COOKIE['ng-'.$name]) {
      $_POST[$name] = $_COOKIE['ng-'.$name];
    }
  }
  if (isset($_POST[$name])) {
    mk_cookie('ng-'.$name, $_POST[$name]);
  }
}


$setvars = array(array('name' => 'urllinks', 'type' => 'checkbox', 'desc' => 'Clickable links in posts'),
		 '<br>',
		 array('name' => 'postheader', 'type' => 'checkbox', 'desc' => 'Show post full headers'),
		 '<br>',
		 array('name' => 'threaded', 'type' => 'checkbox', 'desc' => 'Use a threaded index'),
		 '<br>',
		 array('name' => 'newlinks', 'type' => 'checkbox', 'desc' => 'Number of new posts in threaded index are links to the new posts'),
		 '<br>',
		 array('name' => 'wordwrap', 'type' => 'checkbox', 'desc' => 'Word wrap posts at column '),
		 array('name' => 'wordwraplen', 'type' => 'input', 'desc' => '', 'value' => '79'),
		 '<br>',
		 array('name' => 'casesense', 'type' => 'checkbox', 'desc' => 'Searching is case insensitive'),
		 '<p>',
		 array('name' => 'markallread', 'type' => 'checkbox', 'desc' => 'Mark all posts read'),
		 '<p>'
		 );

foreach ($setvars as $sv) {
    switch ($sv['type']) {
    case 'checkbox':
	check_checkbox($sv['name']);
	break;
    case 'input':
	check_input($sv['name']);
	break;
    default:
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header("Location: ".(isset($_POST['goto']) ? $_POST['goto'] : 'index.php'));
    exit;
}

page_head('Settings');

print '<div class="settingsform">';
print '<form method="POST" action="settings.php">';
foreach ($setvars as $sv) {
    if (is_array($sv)) {
	switch ($sv['type']) {
	case 'checkbox':
	    print mk_checkbox($sv['name'], $sv['desc']);
	    break;
	case 'input':
	    print mk_input($sv['name'], $sv['desc'], (isset($sv['value']) ? $sv['value'] : NULL));
	    break;
	default:
	}
    } else print $sv;
}

if (isset($_SERVER['HTTP_REFERER']))
    print '<input type="hidden" name="goto" value="'.$_SERVER['HTTP_REFERER'].'">';
print '<input type="Submit" value="Save">';
print '</form>';
print '</div>';

page_foot();
