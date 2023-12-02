<?php

namespace Inok\DTO\Tests;

use DateTimeImmutable;
use DateTimeZone;
use Inok\DTO\DTOInterface;
use Inok\DTO\Helpers\toArray;
use Inok\DTO\Helpers\toJSON;
use Inok\DTO\Helpers\toPDO;
use Inok\DTO\Helpers\updateDTO;
use Inok\DTO\HydrateDTO;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class HydrateDTOTest extends TestCase {
  private DTOInterface $dto;
  private DTOInterface $dto2;

  public function setUp(): void {
    $this->dto = new class extends HydrateDTO {
      use toArray, toJSON, toPDO, updateDTO;

      private ?int $id = null;
      private int $workplace_id;
      /** modify=mb_strtolower2 */
      private string $name;
      /** modify=mb_strtolower */
      private ?string $workgroup = null;
      /** sql=Y-m-d H:i:s; show=Y-m-d; timezone=Europe/Moscow */
      private ?DateTimeImmutable $created_at = null;
      private float $weight;
      private bool $disabled;
    };
    $this->dto->workplace_id = '123';
    $this->dto->name = 'AllOk';
    $this->dto->workgroup = 'inok.local';
    $this->dto->created_at = '2022-11-11 12:33:44';
    $this->dto->weight = '22.11';
    $this->dto->disabled = 1;

    $this->dto2 = new class extends HydrateDTO {
      /** sql=Y-m-d H:i:s; show=Y-m-d */
      private ?DateTimeImmutable $date1 = null;
      /** sql=Y-m-d H:i:s; show=Y-m-d; timezone=Europe/Incorrect */
      private ?DateTimeImmutable $date2 = null;
    };
    $this->dto2->date1 = '2022-11-11 12:33:44';
    $this->dto2->date2 = '2022-11-11 12:33:44';
  }

  public function testGetNotExistsFieldGetValue(): void {
    $this->assertNull($this->dto->lalala);
  }

  public function testGetNullValue(): void {
    $this->assertNull($this->dto->id);
  }

  public function testGetIntValue(): void {
    $this->assertSame(123, $this->dto->workplace_id);
  }

  public function testGetStringValue(): void {
    $this->assertSame('AllOk', $this->dto->name);
  }

  public function testGetBoolValue(): void {
    $this->assertSame(true, $this->dto->disabled);
  }

  public function testGetFloatValue(): void {
    $this->assertSame(22.11, $this->dto->weight);
  }

  public function testGetDateTimeImmutableValue(): void {
    $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-11-11 12:33:44', new DateTimeZone('Europe/Moscow'));
    $this->assertEquals($date, $this->dto->created_at);
  }

  public function testNotExistsFieldSetValue(): void {
    $this->expectException(UnexpectedValueException::class);
    $this->dto->lalala = 123;
  }

  public function testSetNullValue(): void {
    $this->dto->id = null;
    $this->assertNull($this->dto->id);
  }

  public function testSetErrorNullValue(): void {
    $this->dto->name = null;
    $this->assertSame('AllOk', $this->dto->name);
  }

  public function testSetIntValue(): void {
    $this->dto->id = '44';
    $this->assertSame(44, $this->dto->id);
  }

  public function testSetStringValue(): void {
    $this->dto->name = 3424355;
    $this->assertSame('3424355', $this->dto->name);
  }

  public function testSetBoolValue(): void {
    $this->dto->disabled = 0;
    $this->assertSame(false, $this->dto->disabled);
  }

  public function testSetFloatValue(): void {
    $this->dto->weight = '233.44';
    $this->assertSame(233.44, $this->dto->weight);
  }

  public function testSetDateTimeImmutableStringValue(): void {
    $dateString = '2022-12-12 12:33:44';
    $this->dto->created_at = $dateString;
    $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $dateString, new DateTimeZone('Europe/Moscow'));
    $this->assertEquals($date, $this->dto->created_at);
  }

  public function testSetDateTimeImmutableObjectValue(): void {
    $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-12-12 12:33:44', new DateTimeZone('Europe/Moscow'));
    $this->dto->created_at = $date;
    $this->assertEquals($date, $this->dto->created_at);
  }

  public function testSetModifyFunctionValue(): void {
    $this->dto->workgroup = 'TEST.GROUp';
    $this->assertSame('test.group', $this->dto->workgroup);
  }

  public function testToArrayAllFields(): void {
    $dtoArray = $this->dto->toArray();
    $dataArray = ['id' => null,
                  'workplace_id' => 123,
                  'name' => 'AllOk',
                  'workgroup' => 'inok.local',
                  'created_at' => '2022-11-11',
                  'weight' => 22.11,
                  'disabled' => true];
    $this->assertSame($dataArray, $dtoArray);
  }

  public function testToArrayFields(): void {
    $dtoArray = $this->dto->toArray(['id', 'disabled']);
    $dataArray = ['id' => null,
                  'disabled' => true];
    $this->assertSame($dataArray, $dtoArray);
  }

  public function testToJSONAllFields(): void {
    $dtoJSON = $this->dto->toJson();
    $dataJSON = ['id' => null,
                 'workplace_id' => 123,
                 'name' => 'AllOk',
                 'workgroup' => 'inok.local',
                 'created_at' => '2022-11-11',
                 'weight' => 22.11,
                 'disabled' => true];
    $this->assertSame(json_encode($dataJSON), $dtoJSON);
  }

  public function testToJSONFields(): void {
    $dtoJSON = $this->dto->toJson(['id', 'disabled']);
    $dataJSON = ['id' => null,
                 'disabled' => true];
    $this->assertSame(json_encode($dataJSON), $dtoJSON);
  }

  public function testToPDOAllFields(): void {
    $dtoPDO = $this->dto->toPDO();
    $dataPDO = ['id' => null,
                'workplace_id' => 123,
                'name' => 'AllOk',
                'workgroup' => 'inok.local',
                'created_at' => '2022-11-11 12:33:44',
                'weight' => 22.11,
                'disabled' => 1];
    $this->assertSame($dataPDO, $dtoPDO);
  }

  public function testToPDOFields(): void {
    $dtoPDO = $this->dto->toPDO(['id', 'disabled']);
    $dataPDO = ['id' => null,
                'disabled' => 1];
    $this->assertSame($dataPDO, $dtoPDO);
  }

  public function testUpdateSwitchData(): void {
    $dtoUpdated = $this->dto->getUpdateFields();
    $this->assertSame([], $dtoUpdated);
  }

  public function testUpdateChangeFieldData(): void {
    $this->dto->switchUpdate(true);
    $this->dto->id = 3;
    $dtoUpdated = $this->dto->getUpdateFields();
    $dataUpdated = ['id'];
    $this->assertSame($dataUpdated, $dtoUpdated);
  }

  public function testUpdateChangeData(): void {
    $this->dto->switchUpdate(true);
    $this->dto->id = 3;
    $dtoUpdated = $this->dto->getUpdateFields(false);
    $dataUpdated = ['id' => ['old' => null,
                             'current' => 3]];
    $this->assertSame($dataUpdated, $dtoUpdated);
  }

  public function testResetDisabledUpdateFields(): void {
    $this->dto->resetUpdate();
    $this->dto->id = 3;
    $dtoUpdated = $this->dto->getUpdateFields();
    $this->assertSame([], $dtoUpdated);
  }

  public function testResetUpdateFields(): void {
    $this->dto->switchUpdate(true);
    $this->dto->resetUpdate();
    $dtoUpdated = $this->dto->getUpdateFields();
    $this->assertSame([], $dtoUpdated);
  }

  public function testNoTimezoneDateTimeImmutable(): void {
    $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-11-11 12:33:44');
    $this->assertEquals($date, $this->dto2->date1);
  }

  public function testIncorrectTimezoneDateTimeImmutable(): void {
    $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2022-11-11 12:33:44');
    $this->assertEquals($date, $this->dto2->date2);
  }
}
