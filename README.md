# actyx-sdk-php

WebSocket implementation of https://developer.actyx.com/docs/reference/events-api.

## Example:

```php
<?php

namespace Example;

use Actyx;

// create a AppManifest
$manifest = new Actyx\AppManifest('com.example.php.01', 'php test', '0.0.1');
// connect to actyx
$actyx = new Actyx\Client($manifest);

// request some node data
$actyx->nodeId();
$actyx->manifest();
$actyx->preset();

// publish an event
$actyx->publish(
  array("alex.php.test"),
  array('eventType' => 'phpHello', 'sender' => 'alex')
);

// query all events
$events = $actyx->query("FROM allEvents");
```

## Events Typing

You could type the events to get them in your event classes. You could parse in an array of Classes you expect in the stream, not matching events are filtered out.

It is required that the event class consumes a rawEvent at the constructor. The event class should try to construct an instance with the data. If it fails/don't match, it has to throw an error and the next class is used for the next try. If no event matches to the event, it is filtered out.

### Example:

```php
class AddEvent
{
  public string $eventType;
  public int $amount;
  function __construct($rawEvent)
  {
    // throws an exception if it don't matches
    Actyx\expect($rawEvent->eventType, 'add');
    $this->eventType = $rawEvent->eventType;
    $this->amount = $rawEvent->amount;
  }
}
class RemoveEvent
{
  public string $eventType;
  public int $amount;
  function __construct($rawEvent)
  {
    // mor complex expect
    Actyx\expect($rawEvent->eventType, function ($v) { return $v ==='remove' });
    $this->eventType = $rawEvent->eventType;
    $this->amount = $rawEvent->amount;
  }
}
// query typed events
$actyxEvents = $actyx->query(
  // aql query
  "FROM 'ax.demo.add' | 'ax.demo.remove'",
  // event order
  'asc',
  // lowerBound
  null,
  // upperBound
  null,
  // classes to merge the events in
  array(
    AddEvent::class,
    RemoveEvent::class
  )
);

$res = 0;
foreach ($actyxEvents as &$actyxEvent) {
  $event = $actyxEvent->payload;
  switch (get_class($event)) {
    case AddEvent::class:
      $res += $event->amount;
      break;
    case RemoveEvent::class:
      $res -= $event->amount;
      break;
  }
}
echo "The result is {$res} \n";
```
