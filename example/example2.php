<?php

namespace Example;

ini_set('memory_limit', '8M');
include  __DIR__ . '/../lib/AppManifest.php';
include  __DIR__ . '/../lib/Client.php';
include  __DIR__ . '/../lib/NetworkProtocol.php';
require __DIR__ . '/../vendor/autoload.php';

use Actyx;

class AddEvent
{
  public string $eventType;
  public string $amount;
  function __construct($rawEvent)
  {
    Actyx\expect($rawEvent->eventType, 'add');

    $this->eventType = $rawEvent->eventType;
    $this->amount = $rawEvent->amount;
  }
}
class RemoveEvent
{
  public string $eventType;
  public string $amount;
  function __construct($rawEvent)
  {
    Actyx\expect($rawEvent->eventType, 'remove');
    $this->eventType = $rawEvent->eventType;
    $this->amount = $rawEvent->amount;
  }
}

$manifest = new Actyx\AppManifest('com.example.php.01', 'php test', '0.0.1');
$actyx = new Actyx\Client($manifest);

$actyxEvents = $actyx->query(
  "FROM 'ax.demo.add' | 'ax.demo.remove'",
  'asc',
  null,
  null,
  array(
    AddEvent::class,
    RemoveEvent::class
  )
);

$res = 0;
$eventCount = 0;

foreach ($actyxEvents as &$actyxEvent) {
  $event = $actyxEvent->payload;
  $eventCount++;
  switch (get_class($event)) {
    case AddEvent::class:
      $res += $event->amount;
      break;
    case RemoveEvent::class:
      $res -= $event->amount;
      break;
  }
}
echo "The result of {$eventCount} events is {$res} \n";
