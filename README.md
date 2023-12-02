# Используется для получения DTO-объекта из PDO-запроса

На данный момент поддерживаются типы: **string**, **bool**, **int**, **float**, **DateTimeImmutable**.
Трейты:
- `toJSON` - преобразование в JSON, можно передать список нужных полей массивом
- `toArray` - преобразование в массив, можно передать список нужных полей массивом
- `toPDO` - преобразование в массив, пригодный для PDO, можно передать список нужных полей массивом
- `updateDTO` - возможность получения изменённых полей:
  - `switchUpdate` - смена режима вставки/добавления DTO
  - `resetUpdate` - сброс измененных полей на текущие значения
  - `getUpdateFields` - получение списка измененных полей, если передается значение:
    - `true` (по-умолчанию) - получение массива только названий полей
    - `false` - получение массива полей, дополнительно со значениями `old` и `current`

## Пример DTO:
```php
/**
 * @property int $id
 * @property string $name
 * @property string $workgroup
 * @property int|null $workplace_id
 * @property DateTimeImmutable|null $created_at
 */
class ComputerDTO extends HydrateDTO {
  private int $id;
  /** modify=mb_strtolower */
  private string $name;
  private string $workgroup;
  private ?int $workplace_id = null;
  /** sql=Y-m-d H:i:s; show=Y-m-d */
  private ?DateTimeImmutable $created_at = null;
}
``` 
, где комментарии:
- `modify` - функция для модификации исходного значения **ДО** гидрации
- `sql` - для SQL-формата даты. Если не указано, то используется формат `Y-m-d H:i:s`
- `show` - для остального (`toArray`, `toJSON`).  Если не указано, то используется формат `Y-m-d H:i:s`
