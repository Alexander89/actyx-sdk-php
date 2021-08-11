<?php

namespace Actyx\Rpc;

class RpcPublishResultsMessage extends RpcMessage
{
  public ?\Actyx\PublishResults $payload;
  function __construct(string $rawData)
  {
    parent::__construct($rawData, \Actyx\PublishResults::class);
  }
}
