<?php

namespace Inok\DTO;

use Exception;
use Inok\DTO\Helpers\ReflectionHelper;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use UnexpectedValueException;

abstract class HydrateDTO implements DTOInterface {
  protected array $baseTypes = ['string', 'int', 'bool', 'float'];
  private array $specialTypes = ['DateTimeImmutable'];
  private array $fieldsFormats = [];

  protected array $defaultValues = [];
  protected bool $isUpdate = false;

  public function __construct() {
    $this->setFieldsFormats();
  }

  public function __get($name) {
    return $this->getProperty($name);
  }

  public function __set($name, $value) {
    try {
      $rp = new ReflectionProperty($this, $name);
    } catch (ReflectionException $exception) {
      throw new UnexpectedValueException();
    }
    $this->setValue($rp, $value);
  }

  private function updateField(ReflectionProperty $rProperty, $value): void {
    $name = $rProperty->getName();
    if (!$this->isUpdate) {
      $this->defaultValues[$name] = $value;
      return;
    }
    if (!array_key_exists($name, $this->defaultValues)) {
      try {
        $this->defaultValues[$name] = $rProperty->getValue($this);
      } catch (Exception $e) {
        return;
      }
    }
  }

  private function getProperty($name) {
    try {
      $rp = new ReflectionProperty($this, $name);
    } catch (ReflectionException $exception) {
      return null;
    }
    $rp->setAccessible(true);
    return $rp->getValue($this);
  }

  private function setProperty(ReflectionProperty $rProperty, $value): void {
    $rProperty->setAccessible(true);
    $this->updateField($rProperty, $value);
    $rProperty->setValue($this, $value);
    $rProperty->setAccessible(false);
  }

  private function setValue(ReflectionProperty $rProperty, $value): void {
    if (!$rProperty->isPrivate()) {
      return;
    }
    $rpType = $rProperty->getType();
    if (is_null($value)) {
      if ($rpType->allowsNull()) {
        $this->setProperty($rProperty, null);
      }
      return;
    }
    $value = $this->modifyValue($rProperty->getName(), $value);
    $type = $rpType->getName();
    if (in_array($type, $this->baseTypes)) {
      $this->setBaseProperty($rProperty, $type, $value);
      return;
    }
    if (in_array($type, $this->specialTypes)) {
      $this->setSpecialProperty($rProperty, $type, $value);
    }
  }

  private function setBaseProperty(ReflectionProperty $rProperty, string $type, $value) {
    settype($value, $type);
    $this->setProperty($rProperty, $value);
  }

  private function setSpecialProperty(ReflectionProperty $rProperty, string $type, $value) {
    if (is_a($value, $type)) {
      $this->setProperty($rProperty, $value);
      return;
    }
    $docFormat = $this->fieldsFormats[$rProperty->getName()];
    $data = call_user_func_array($type."::createFromFormat", [
      ReflectionHelper::getDateFormat($docFormat), $value, ReflectionHelper::getDateTimezone($docFormat)
    ]);
    if ($data !== false) {
      $this->setProperty($rProperty, $data);
    }
  }

  private function modifyValue(string $field, $value) {
    if (empty($this->fieldsFormats[$field]['modify'])) {
      return $value;
    }
    $newValue = call_user_func($this->fieldsFormats[$field]['modify'], $value);
    return ($newValue === false) ? $value : $newValue;
  }

  private function getFieldFormat(string $name): array {
    return array_key_exists($name, $this->fieldsFormats) ? $this->fieldsFormats[$name] : [];
  }

  private function setFieldsFormats(): void {
    $rClass = new ReflectionClass($this);
    $properties = $rClass->getProperties(ReflectionProperty::IS_PRIVATE);
    foreach ($properties as $property) {
      $parsedFormat = ReflectionHelper::parseDocFormat($property);
      if (array_key_exists('modify', $parsedFormat) && !function_exists($parsedFormat['modify'])) {
        unset($parsedFormat['modify']);
      }
      $this->fieldsFormats[$property->getName()] = $parsedFormat;
    }
    unset($rClass);
  }
}
