<?php
ini_set('opcache.enable', '0');

require __DIR__ . '/common.php';

if (!$access_token) {
    try {
        $insta->getAccessToken();

        $access_token = $insta->getLongTermAccessToken();

        $insta->saveAccessToken();
    } catch (Exception $e) {
        die($e->getMessage());
    }
}

if (!$access_token) {
    die('No Access Token');
}

header('Location: ./media.php');
