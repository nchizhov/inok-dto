<?php

namespace Inok\DTO\Helpers;

trait toPDO {
  public function toPDO(array $fields = []): array {
    return ReflectionHelper::getData($this, 'sql', $fields);
  }
}
