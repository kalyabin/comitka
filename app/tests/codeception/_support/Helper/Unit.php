<?php
namespace Helper;

use Codeception\Module;
use Codeception\Test\Unit as UnitTest;
use yii\base\Model;

/**
 * Helper for unit tests.
 *
 * Use this in TestCase:
 *
 * $this->getModule('\Helper\Unit')->...
 */
class Unit extends Module
{
    /**
     * Валидация атрибутов модели.
     *
     * На вход передается модель для валидации и массив значений для валидации.
     * Массив значений должен иметь следующий формат:
     * ```php
     * array(
     *     '<attribute1>' => array(
     *         array(
     *             'value' => <mixed>, // значение для валидации
     *             'isValid' => <boolean>, // true, если значение должно проходить валидацию
     *         ),
     *     ),
     * )
     * ```
     *
     * Проверяет, что атрибут либо должен проходить проверку валидации, либо не должен.
     *
     * @param Model $model проверяемая модель
     * @param array $attributes массив значений атрибутов для валидации
     * @param UnitTest $test Тест, в котором происходит проверка
     */
    public function validateModelAttributes(Model $model, $attributes, UnitTest $test)
    {
        foreach ($attributes as $attribute => $values) {
            $attributeTitle = $model->getAttributeLabel($attribute);
            foreach ($values as $v) {
                $value = $v['value'];
                $isValid = $v['isValid'];
                $model->{$attribute} = $value;

                if ($isValid) {
                    $message = $attributeTitle . ' validation error: ' . implode("\n", $model->getErrors($attribute));
                    $message .= "\nAsserting value: " . print_r($value, true);
                    $test->assertTrue($model->validate([$attribute]), $message);
                }
                else {
                    $message = $attributeTitle . ' must be invalid' . "\n";
                    $message .= 'Asserting value: ' . print_r($value, true);
                    $test->assertFalse($model->validate([$attribute]), $message);
                }
            }
        }
    }
}
