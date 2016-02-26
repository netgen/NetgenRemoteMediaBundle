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
$ViewList['change'] = array(
    'script' => 'change.php',
    'functions' => 'change',
    'params' => array( 'contentobject_id', 'contentobjectattribute_id', 'contentobject_version' )
);

$FunctionList['fetch'] = array();
$FunctionList['save'] = array();
$FunctionList['browse'] = array();
$FunctionList['upload'] = array();
$FunctionList['change'] = array();
