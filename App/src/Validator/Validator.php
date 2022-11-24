<?php

namespace App\Validator;

class Validator
{
     /**
     * Assures that provided value is a valid integer, throws exception otherwise.
     * Negative values are allowed.
     *
     * @param string $name  Argument name for reference.
     * @param mixed  $value Value that needs validation.
     *
     * @return int Validated integer.
     */
    public static function validateInteger(string $name, mixed $value): int
    {
        if (!is_numeric($value) || empty($value) || $value != (int) $value) {
            throw new \Exception("Invalid input parameter \"" . $name . "\"", 400);
        }
        return (int) $value;
    }

    /**
     * Assures that provided value is a valid price, throws exception otherwise.
     * Fractional part can be separated by dot or comma. Negative values are prohibited.
     *
     * @param string $name  Argument name for reference.
     * @param mixed  $value Value that needs validation.
     *
     * @return int Validated price.
     */
    public static function validatePrice(string $name, mixed $value): int
    {
        if (!is_numeric($value) || empty($value)) {
            throw new \Exception("Invalid input parameter \"" . $name . "\"", 400);
        }
        $value = str_replace(',', '.', (string) $value);
        $value = round($value * 100);
        if ($value < 0) {
            throw new \Exception("Price cannot be a negative number", 400);
        }
        return (int) $value;
    }

    /**
     * Assures that provided value is a valid string, throws exception otherwise.
     * Length should be in 1-255 range.
     *
     * @param string $name  Argument name for reference.
     * @param mixed  $value Value that needs validation.
     *
     * @return string Validated string.
     */
    public static function validateString(string $name, mixed $value): string
    {
        $value = (string) $value;
        if (empty($value)) {
            throw new \Exception("Invalid input parameter \"" . $name . "\"", 400);
        }
        if (strlen($value) > 255) {
            throw new \Exception("String too long", 400);
        }
        return (string) $value;
    }
}
