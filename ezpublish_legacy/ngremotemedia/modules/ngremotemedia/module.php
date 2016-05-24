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
$ViewList['tags'] = array(
    'script' => 'change_tags.php',
    'functions' => 'tags',
    'params' => array( 'contentobject_id', 'contentobjectattribute_id', 'contentobject_version' )
);
$ViewList['tags_delete'] = array(
    'script' => 'remove_tags.php',
    'functions' => 'tags',
    'params' => array( 'contentobject_id', 'contentobjectattribute_id', 'contentobject_version' )
);
$ViewList['simple_fetch'] = array(
    'script' => 'simple_fetch.php',
    'functions' => 'fetch'
);
$ViewList['generate'] = array(
    'script' => 'generate.php',
    'functions' => 'generate',
    'params' => array( 'resource_id' )
);
$ViewList['simple_upload'] = array(
    'script' => 'simple_upload.php',
    'functions' => 'simple_upload',
);

$FunctionList['fetch'] = array();
$FunctionList['save'] = array();
$FunctionList['browse'] = array();
$FunctionList['upload'] = array();
$FunctionList['change'] = array();
$FunctionList['tags'] = array();
$FunctionList['generate'] = array();
$FunctionList['simple_upload'] = array();
