<?php

namespace Example;

ini_set('memory_limit', '8M');
include  __DIR__ . '/../lib/AppManifest.php';
include  __DIR__ . '/../lib/Client.php';
include  __DIR__ . '/../lib/NetworkProtocol.php';
require __DIR__ . '/../vendor/autoload.php';

use Actyx;

$manifest = new Actyx\AppManifest('com.example.php.01', 'php test', '0.0.1');
$actyx = new Actyx\Client($manifest);
$actyx->nodeId();
$actyx->manifest();
$actyx->preset();

$actyx->publish(array("alex.php.test"), array('eventType' => 'phpHello', 'sender' => 'alex'));

$events = $actyx->query("FROM allEvents");
foreach ($events as &$event) {
  var_dump($event);
}
