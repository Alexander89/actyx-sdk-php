<?php

namespace Actyx\Rpc;

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
              array_push($this->payload, new \Actyx\ActyxEvent($payload, $type));
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
