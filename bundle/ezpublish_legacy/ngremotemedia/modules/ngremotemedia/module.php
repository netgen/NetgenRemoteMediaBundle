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

$FunctionList['browse'] = array();
$FunctionList['folders'] = array();
