<?php

namespace Actyx\Rpc;

class RpcOffsetMessage extends RpcMessage
{
  /**
   * @var OffsetResult|null
   */
  public $payload;
  function __construct(string $rawData)
  {
    parent::__construct($rawData, \Actyx\OffsetResult::class);
  }
}
