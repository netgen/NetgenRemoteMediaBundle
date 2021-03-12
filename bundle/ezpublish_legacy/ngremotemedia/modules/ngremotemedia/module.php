<?php

$Module = array(
'name' => 'ng_remote_provider',
'variable_params' => true
);

$ViewList = array();
$ViewList['browse'] = array(
    'script' => 'browse.php',
    'functions' => 'browse',
);
$ViewList['folders'] = array(
    'script' => 'folders.php',
    'functions' => 'folders',
);
$ViewList['editorinsert'] = array(
    'script' => 'editorinsert.php',
    'functions' => 'editorinsert',
);

$FunctionList['browse'] = array();
$FunctionList['folders'] = array();
$FunctionList['editorinsert'] = array();
