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

$setvars = array(array('name' => 'urllinks', 'desc' => 'Clickable links in posts'),
                array('name' => 'newlinks', 'desc' => 'Number of new posts in threaded index are links to the new posts'));

foreach ($setvars as $sv) {
  check_checkbox($sv['name']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    header("Location: ".(isset($_POST['goto']) ? $_POST['goto'] : 'index.php'));
    exit;
}

page_head('Settings');

print '<div class="settingsform">';
print '<form method="POST" action="settings.php">';
foreach ($setvars as $sv) {
  print mk_checkbox($sv['name'], $sv['desc']);
  print '<br>';
}
print '<input type="hidden" name="goto" value="'.$_SERVER['HTTP_REFERER'].'">';
print '<input type="Submit">';
print '</form>';
print '</div>';

page_foot();
