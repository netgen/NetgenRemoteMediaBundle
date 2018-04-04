<?php

$Module = array(
'name' => 'ng_remote_provider',
'variable_params' => true
);

$ViewList = array();
$ViewList['fetch'] = array(
    'script' => 'fetch.php',
    'functions' => 'fetch',
    'params' => array( 'contentobject_id', 'contentobjectattribute_id', 'contentobject_version' )
);
$ViewList['save'] = array(
    'script' => 'save.php',
    'functions' => 'save',
    'params' => array( 'contentobject_id', 'contentobjectattribute_id', 'contentobject_version' )
);
$ViewList['browse'] = array(
    'script' => 'browse.php',
    'functions' => 'browse',
);
$ViewList['upload'] = array(
    'script' => 'upload.php',
    'functions' => 'upload',
    'params' => array( 'contentobject_id' )
);
$ViewList['simple_upload'] = array(
    'script' => 'simple_upload.php',
    'functions' => 'simple_upload',
    'params' => array( 'contentobject_id', 'contentobjectattribute_id', 'contentobject_version' )
);
$ViewList['change'] = array(
    'script' => 'change.php',
    'functions' => 'change',
    'params' => array( 'contentobject_id', 'contentobjectattribute_id', 'contentobject_version' )
);
$ViewList['fetch_ezoe'] = array(
    'script' => 'fetch_ezoe.php',
    'functions' => 'fetch'
);
$ViewList['generate'] = array(
    'script' => 'generate.php',
    'functions' => 'generate',
    'params' => array( 'resource_id' )
);
$ViewList['folders'] = array(
    'script' => 'folders.php',
    'functions' => 'folders',
);

$FunctionList['fetch'] = array();
$FunctionList['fetch_ezoe'] = array();
$FunctionList['save'] = array();
$FunctionList['browse'] = array();
$FunctionList['upload'] = array();
$FunctionList['change'] = array();
$FunctionList['generate'] = array();
$FunctionList['simple_upload'] = array();
$FunctionList['folders'] = array();
