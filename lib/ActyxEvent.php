<?php

namespace Actyx;

use stdClass;

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
