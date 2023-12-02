<?php

namespace Inok\DTO\Helpers;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

trait updateDTO {
  public function switchUpdate($mode = false): void {
    $this->isUpdate = $mode;
  }

  public function resetUpdate(): void {
    if (!$this->isUpdate) {
      return;
    }
    $this->defaultValues = [];
    $rClass = new ReflectionClass($this);
    $rProperties = $rClass->getProperties(ReflectionProperty::IS_PRIVATE);
    foreach ($rProperties as $property) {
      $property->setAccessible(true);
      $this->defaultValues[$property->getName()] = $property->getValue($this);
    }
  }

  public function getUpdateFields($onlyFields = true): array {
    if (!$this->isUpdate) {
      return [];
    }
    $updatedFields = [];
    foreach ($this->defaultValues as $field => $value) {
      try {
        $rProperty = new ReflectionProperty($this, $field);
      } catch (ReflectionException $e) {
        continue;
      }
      if (ReflectionHelper::isFieldDiff($value, $this->{$field}, in_array($rProperty->hasType(), $this->baseTypes))) {
        $updatedFields[$field] = ['old' => $value,
                                  'current' => $this->{$field}];
      }
    }
    return $onlyFields ? array_keys($updatedFields) : $updatedFields;
  }
}
