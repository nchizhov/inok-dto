<?php

namespace Inok\DTO\Helpers;

use DateTimeInterface;
use DateTimeZone;
use Exception;
use Inok\DTO\HydrateDTO;
use ReflectionException;
use ReflectionObject;
use ReflectionProperty;

class ReflectionHelper {
  public static function isFieldDiff($oldValue, $value, bool $isBaseType = true): bool {
    return ($isBaseType) ? ($oldValue !== $value) : ($oldValue != $value);
  }

  public static function getData(HydrateDTO $class, string $type = 'sql', array $fields = []): array {
    $rClass = new ReflectionObject($class);
    $properties = $rClass->getProperties(ReflectionProperty::IS_PRIVATE);
    $data = [];
    $isEmptyFields = empty($fields);
    $getFieldFormat = $rClass->getMethod('getFieldFormat');
    $getFieldFormat->setAccessible(true);
    foreach ($properties as $property) {
      $name = $property->getName();
      if (!$isEmptyFields && !in_array($name, $fields)) {
        continue;
      }
      $property->setAccessible(true);
      $value = $property->getValue($class);
      if ($property->getType()->getName() === 'bool' && $type === 'sql') {
        $value = (int) $value;
      } else if ($value instanceof DateTimeInterface) {
        try {
          $docFormat = $getFieldFormat->invoke($class, $property->getName());
        } catch (ReflectionException $e) {
          $docFormat = [];
        }
        $value = $value->format(self::getDateFormat($docFormat, $type));
      }
      $data[$name] = $value;
    }
    $getFieldFormat->setAccessible(false);
    return $data;
  }

  public static function getDateFormat(array $docFormat,
                                       string $type = 'sql',
                                       string $defaultFormat='Y-m-d H:i:s'): string {
    return array_key_exists($type, $docFormat) ? $docFormat[$type] : $defaultFormat;
  }

  public static function getDateTimezone(array $docFormat): ?DateTimeZone {
    if (!array_key_exists('timezone', $docFormat)) {
      return null;
    }
    try {
      return new DateTimeZone($docFormat['timezone']);
    } catch (Exception $e) {
      return null;
    }
  }

  public static function parseDocFormat(ReflectionProperty $rProperty): array {
    $comment = $rProperty->getDocComment();
    if ($comment === false) {
      return [];
    }
    $pregMatch = preg_match('/^\/\*{2} (.+) \*\/$/i', $comment, $matches);
    if (!$pregMatch) {
      return [];
    }
    $vars = explode('; ', $matches[1]);
    if (!count($vars)) {
      return [];
    }
    $retData = [];
    foreach ($vars as $var) {
      $varInfo = explode('=', $var);
      if (count($varInfo) !== 2) {
        continue;
      }
      $retData[$varInfo[0]] = $varInfo[1];
    }
    return $retData;
  }
}
