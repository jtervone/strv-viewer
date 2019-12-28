<?php

require_once '../config.php';
require_once '../vendor/autoload.php';

session_start();

$action = isset($_GET['action']) ? $_GET['action'] : null;
$code = isset($_GET['code']) ? $_GET['code'] : null;
$user = (isset($_SESSION['user']) && is_array($_SESSION['user'])) ? $_SESSION['user'] : null;

$client = new \Strava\Client([
    'clientId' => STRAVA_CLIENT_ID,
    'clientSecret' => STRAVA_CLIENT_SECRET,
    'redirectUri' => 'http://localhost:8080/'
]);

if ($action === 'logout') {
    unset($_SESSION['user']);
    header('Location: /');
} elseif ($user) {
    echo '<html><pre>';
    var_dump($user);
    echo '</pre><a href="/?action=logout">Logout</a>';
} elseif ($code) {
    $_SESSION['user'] = $client->authenticate($code);

    header('Location: /');
} else {
    $url = $client->getAuthorizationUrl();
    header('Location: '.$url);
}
