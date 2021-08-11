<?php

namespace Actyx\Rpc;

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
