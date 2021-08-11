<?php

namespace Actyx;

use stdClass;

/**
 * message from Actyx.
 *
 * Structure is build to have a "type-safe" interpretation of the message
 */
abstract class RpcMessage
{
  /** type could be "next" "complete" "error" */
  public string $type;
  /** reference to the request */
  public int $requestId;
  /** optional payload of the next message */
  // abstract $payload;
  /** optional error message */
  public $kind;

  /**
   * Creates a RpcMessage instance from the raw data and parse the payload to the give class
   */
  function __construct(string $rawData, string $payloadType)
  {
    $data = json_decode($rawData);
    $this->type = $data->type;
    $this->requestId = $data->requestId;
    if (isset($data->payload)) {
      $this->payload = new $payloadType($data->payload[0]);
    } else {
      $this->payload = null;
    }
    if (isset($data->kind)) {
      $this->kind = $data->kind;
    } else {
      $this->kind = null;
    }
  }
}

class RpcPublishResultsMessage extends RpcMessage
{
  public ?PublishResults $payload;
  function __construct(string $rawData)
  {
    parent::__construct($rawData, PublishResults::class);
  }
}
class RpcOffsetMessage extends RpcMessage
{
  /**
   * @var OffsetResult|null
   */
  public $payload;
  function __construct(string $rawData)
  {
    parent::__construct($rawData, OffsetResult::class);
  }
}
class RpcQueryMessage extends RpcMessage
{
  /**
   *
   * @var ActyxEvent[]
   */
  public $payload;
  function __construct(string $rawData, array $payloadTypes)
  {
    $data = json_decode($rawData);
    $this->type = $data->type;
    $this->requestId = $data->requestId;
    if (isset($data->kind)) {
      $this->kind = $data->kind;
    } else {
      $this->kind = null;
    }

    if ($data->type === "next") {
      if (count($payloadTypes) === 0) {
        $this->payload = $data->payload;
      } else {
        $this->payload = array();
        $events = array_filter($data->payload, function ($v, $k) {
          return $v->type === 'event';
        }, ARRAY_FILTER_USE_BOTH);

        $errorLevel = error_reporting();
        error_reporting($errorLevel & ~E_NOTICE);
        foreach ($events as $payload) {
          foreach ($payloadTypes as $type) {
            try {
              array_push($this->payload, new ActyxEvent($payload, $type));
              break;
            } catch (\Error $e) {
              continue;
            }
          }
        }
        error_reporting($errorLevel);
      }
    }
  }
}
/**
 * Summary of all metadata of published events
 */
class PublishResults
{
  /**
   * Array of return result data
   * @var PublishResult[]
   */
  public $data;
  /**
   * Constructs a PublishResults from a stdClass
   * @param stdClass $rawData parsed rawData, to assign them to the instance
   * @throws TypeError if property do not exist/wrong type in $rawData
   * @return PublishResults
   */
  function __construct(stdClass $rawData)
  {
    $this->data = array();
    foreach ($rawData->data as $value) {
      array_push($this->data, new PublishResult($value));
    }
  }
}
/**
 * Summary of the metadata of a single published event
 */
class PublishResult
{
  /** Lamport of the published event */
  public int $lamport;
  /** Stream where te event is published in */
  public string $stream;
  /** Offset of the stream where the event is published in */
  public int $offset;
  /** Assigned timestamp */
  public int $timestamp;
  /**
   * Create a PublishResult instance from a stdClass
   * @param stdClass $rawData parsed rawData, to assign them to the instance
   * @throws TypeError if property do not exist in rawData
   * @return PublishResult
   */
  function __construct(stdClass $rawData)
  {
    $this->lamport = $rawData->lamport;
    $this->stream = $rawData->stream;
    $this->offset = $rawData->offset;
    $this->timestamp = $rawData->timestamp;
  }
}

/**
 * OffsetMap to derive the current state of the node
 */
class OffsetResult
{
  /** Current known local offset */
  public stdClass $present;
  /** Events queued to be replicated */
  public stdClass $toReplicate;
  /**
   * Create a OffsetResult instance from a stdClass
   * @param stdClass $rawData parsed rawData, to assign them to the instance
   * @throws TypeError if property do not exist in rawData
   * @return OffsetResult
   */
  function __construct(stdClass $rawData)
  {
    $this->present = $rawData->present;
    $this->toReplicate = $rawData->toReplicate;
  }
}

class ActyxEvent
{
  /** @var int */
  public int $lamport;
  /** @var string */
  public string $stream;
  /** @var int */
  public int $offset;
  /** @var int */
  public int $timestamp;
  /** @var string[] */
  public array $tags;
  /** @var string */
  public string $appId;

  public $payload;

  function __construct(stdClass $data, string $payloadType)
  {
    $this->lamport = $data->lamport;
    $this->stream = $data->stream;
    $this->offset = $data->offset;
    $this->timestamp = $data->timestamp;
    $this->tags = $data->tags;
    $this->appId = $data->appId;
    if ($payloadType === stdClass::class) {
      $this->payload = $data->payload;
    } else {
      $this->payload = new $payloadType($data->payload);
    }
  }
}
