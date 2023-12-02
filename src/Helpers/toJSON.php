<?php

namespace Inok\DTO\Helpers;

trait toJSON {
  public function toJson(array $fields = []): string {
    $data = ReflectionHelper::getData($this, 'show', $fields);
    return json_encode($data);
  }
}
