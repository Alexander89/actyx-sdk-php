<?php

namespace Actyx;

use stdClass;

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
