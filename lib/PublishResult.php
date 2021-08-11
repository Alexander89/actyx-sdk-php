<?php

namespace Actyx;

use stdClass;

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
