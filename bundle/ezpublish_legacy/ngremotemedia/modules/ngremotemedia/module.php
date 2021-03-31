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
$ViewList['facets'] = array(
    'script' => 'facets.php',
    'functions' => 'facets',
);
$ViewList['subfolders'] = array(
    'script' => 'subfolders.php',
    'functions' => 'subfolders',
);
$ViewList['editorinsert'] = array(
    'script' => 'editorinsert.php',
    'functions' => 'editorinsert',
);

$FunctionList['browse'] = array();
$FunctionList['facets'] = array();
$FunctionList['subfolders'] = array();
$FunctionList['editorinsert'] = array();
