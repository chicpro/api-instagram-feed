<?php

ini_set('opcache.enable', '0');

require __DIR__ . '/common.php';

if (!$access_token) {
	$insta->getAccessToken();

	$access_token = $insta->getLongTermAccessToken();

	$insta->saveAccessToken(__DIR__);
}

if (!$access_token) {
	die('No Access Token');
}

header('Location: ./media.php');
