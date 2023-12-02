<?php

namespace Inok\DTO\Tests;

use DateTimeImmutable;
use Inok\DTO\Helpers\ReflectionHelper;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionProperty;

class ReflectionTest extends TestCase {
  public function testIsFieldDiffBase(): void {
    $isDiff = ReflectionHelper::isFieldDiff('1', '5');
    $this->assertTrue($isDiff);
  }

  public function testIsFieldSameBase(): void {
    $isDiff = ReflectionHelper::isFieldDiff('1', '1');
    $this->assertNotTrue($isDiff);
  }

  public function testIfFieldSame(): void {
    $oldValue = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-11-11 12:35:44');
    $value = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-11-11 12:33:44');
    $isDiff = ReflectionHelper::isFieldDiff($oldValue, $value, false);
    $this->assertTrue($isDiff);
  }

  public function testIfFieldDiff(): void {
    $oldValue = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-11-11 12:33:44');
    $value = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-11-11 12:33:44');
    $isDiff = ReflectionHelper::isFieldDiff($oldValue, $value, false);
    $this->assertNotTrue($isDiff);
  }

  /**
   * @throws ReflectionException
   */
  public function testParseDocFormatIncorrect(): void {
    $dto = new class {
      /** dsafsdf dsafsdf*/
      private string $test = '';
    };
    $rp = new ReflectionProperty($dto, 'test');
    $docFormat = ReflectionHelper::parseDocFormat($rp);
    $this->assertSame([], $docFormat);
  }

  /**
   * @throws ReflectionException
   */
  public function testParseDocFormatIncorrectFormat(): void {
    $dto = new class {
      /** sql; */
      private string $test = '';
    };
    $rp = new ReflectionProperty($dto, 'test');
    $docFormat = ReflectionHelper::parseDocFormat($rp);
    $this->assertSame([], $docFormat);
  }
}
