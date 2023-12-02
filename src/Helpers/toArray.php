<?php

namespace Inok\DTO\Helpers;

trait toArray {
  public function toArray(array $fields = []): array {
    return ReflectionHelper::getData($this, 'show', $fields);
  }
}
