<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/globals.php';

$client = new \Github\Client();
$token = file_get_contents('token.txt');
$client->authenticate($token, null, Github\Client::AUTH_HTTP_TOKEN);
$paginator = new \Github\ResultPager($client);

