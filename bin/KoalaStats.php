<?php

if (!$loader = include __DIR__ . '/../vendor/autoload.php') {
    die('You must set up the project dependencies.');
}

$app = new \Cilex\Application('KoalaStats', '##development##');
$app->command(new \Koalamon\KoalaStats\Command\RunCommand());
$app->command(new \Koalamon\KoalaStats\Command\CollectCommand());
$app->run();
