<?php

namespace Actyx;

class AppManifest
{
  private string $appId;
  private string $displayName;
  private string $version;
  private string $signature;

  public function __construct(string $appId, string $displayName, string $version, string $signature = "")
  {
    $this->appId = $appId;
    $this->displayName = $displayName;
    $this->version = $version;
    $this->signature = $signature;
  }
  public function toJson(): string
  {
    return json_encode(array(
      'appId' => $this->appId,
      'displayName' => $this->displayName,
      'version' => $this->version,
      'signature' => $this->signature,
    ));
  }
  public function appId()
  {
    return $this->appId;
  }
  public function displayName()
  {
    return $this->displayName;
  }
  public function version()
  {
    return $this->version;
  }
  public function signature()
  {
    return $this->signature;
  }
}
