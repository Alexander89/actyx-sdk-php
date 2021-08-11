<?php

namespace Example;

ini_set('memory_limit', '8M');
require __DIR__ . '/../vendor/autoload.php';
spl_autoload_register(function ($class_name) {
  include __DIR__ . '/../lib/' . str_replace('\\', '/', substr($class_name, 6)) . '.php';
});

use Actyx\AppManifest;
use Actyx\Client;

$manifest = new AppManifest('com.example.php.01', 'php test', '0.0.1');
$actyx = new Client($manifest);
$actyx->nodeId();
$actyx->manifest();
$actyx->preset();

$actyx->publish(array("alex.php.test"), array('eventType' => 'phpHello', 'sender' => 'alex'));

$events = $actyx->query("FROM allEvents");
foreach ($events as &$event) {
  var_dump($event);
}
