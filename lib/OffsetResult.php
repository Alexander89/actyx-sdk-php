<?php

namespace Actyx;

use stdClass;

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
