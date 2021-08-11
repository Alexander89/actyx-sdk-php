<?php

namespace Actyx;

use WebSocket;

function expect($value, $toBe)
{
  if (is_callable($toBe)) {
    if ($toBe($value) === false) {
      throw new \TypeError("wrong type", 1);
    }
  } else {
    if ($value !== $toBe) {
      throw new \TypeError("wrong type", 1);
    }
  }
}
/**
 * Actyx client, wrapping the WebSocket rpc interface
 */
class Client
{
  /**
   * websocket client
   */
  private WebSocket\Client $client;
  /**
   * initialized app manifest
   */
  private AppManifest $manifest;
  /**
   * url to connect to actyx
   */
  private string $url;
  /**
   * counter for actyx requests
   */
  private int $requestId = 0;
  /**
   * auth token to verify all actyx requests
   */
  private string $token;
  /**
   * actyx nodeId
   */
  private string $nodeId;
  /**
   * Create a new actyx client.
   * @param AppManifest $manifest fo verify the app at actyx
   * @param string $url actyx end-point. localhost:4454 is the default
   * @return OffsetResult|null
   */
  public function __construct(AppManifest $manifest, string $url = '127.0.0.1:4454')
  {
    $this->manifest = $manifest;
    $this->url = $url;
    $this->nodeId = $this->getNodeId();
    $res = $this->getToken($manifest);
    if ($res !== "") {
      $this->token = $res;
      // echo "connect to ws://{$url}/events?{$this->token}";
      $this->client = new WebSocket\Client("ws://{$url}/api/v2/events?{$this->token}");
    }
  }
  function __destruct()
  {
    $this->client->close();
  }

  private function getNodeId(): string
  {
    $this->nodeId = file_get_contents("http://{$this->url}/api/v2/node/id");
    return $this->nodeId;
  }
  private function getToken(AppManifest $manifest): string
  {
    $sURL = "http://{$this->url}/api/v2/auth";
    $aHTTP = array(
      'http' => // The wrapper to be used
      array(
        'method'  => 'POST', // Request Method
        'header'  => ['Content-type: application/json', 'Accept: application/json'],
        'content' => $manifest->toJson(),
      )
    );
    $res = file_get_contents($sURL, false, stream_context_create($aHTTP));

    if ($res) {
      return json_decode($res)->token;
    } else {
      return "";
    }
  }

  /**
   * query the current offset and the map of queued events to replicate
   * @return OffsetResult|null
   */
  public function preset(): OffsetResult
  {
    $id = $this->requestId++;
    $this->client->text(json_encode(array(
      'type' => 'request',
      'serviceId' => 'offsets',
      'requestId' => $id,
      'payload' => null,
    )));

    do {
      $res = new RpcOffsetMessage($this->client->receive());
    } while ($res->requestId < $id);

    return $res->payload;
  }
  /**
   * Query events from actyx. Use AQL to build powerful queries to avoid php processing time
   *
   * More details:
   * https://developer.actyx.com/docs/reference/events-api#query-event-streams
   *
   * @param string $aql AQL query https://developer.actyx.com/docs/reference/aql
   * @param string $order could be 'asc', 'desc', or 'stream-asc'
   * @param object|null $lowerBound Specifies the lower bound offset for each stream with the numbers being exclusive
   * @param object|null $upperBound Specifies the upper bound offset for each stream with the numbers being inclusive
   * @param string[] $eventTypes classes to assign the events to. the constructor is required to consume and stdClass. if the value don't fit into the event class, an error should be thrown (First fit)
   * @return ActyxEvent[]
   */
  public function query(
    string $aql,
    string $order = 'asc',
    object $lowerBound = null,
    object $upperBound = null,
    array $eventTypes = array()
  ): array {
    $id = $this->requestId++;

    if ($upperBound === null) {
      $upperBound = $this->preset()->present;
    }
    if ($lowerBound === null) {
      $lowerBound = new class
      {
      };
    }
    $query = json_encode(array(
      'type' => 'request',
      'serviceId' => 'query',
      'requestId' => $id,
      'payload' => array(
        'query' => $aql,
        'order' => $order,
        'lowerBound' => $lowerBound,
        'upperBound' => $upperBound,
      ),
    ));
    $this->client->text($query);

    $res = [];
    do {
      $response = new RpcQueryMessage($this->client->receive(), $eventTypes);
      if ($response->requestId === $id && $response->type === 'next') {
        array_push($res, ...$response->payload);
      }
    } while ($response->requestId !== $id || $response->type === 'next');

    return $res;
  }
  /**
   * Publish a new event to the actyx swarm
   * @param array $tags tags to be assigned to the event
   * @param mixed $payload payload of the event
   * @return PublishResult|null
   */
  public function publish(
    array $tags,
    $payload
  ): ?PublishResult {
    $id = $this->requestId++;

    $query = json_encode(array(
      'type' => 'request',
      'serviceId' => 'publish',
      'requestId' => $id,
      'payload' => array(
        'data' => array(
          array(
            'tags' => $tags,
            'payload' => $payload,
          )
        ),
      ),
    ));
    $this->client->text($query);

    do {
      $response = new RpcPublishResultsMessage($this->client->receive());
      if ($response->requestId === $id && $response->type === 'next' && $response->payload) {
        $res = $response->payload->data[0];
      }
      if ($response->requestId === $id && $response->type === 'error') {
        return null;
      }
    } while ($response->requestId !== $id || $response->type === 'next');
    return $res;
  }

  public function manifest()
  {
    return $this->manifest;
  }
  public function nodeId()
  {
    return $this->nodeId;
  }
}
