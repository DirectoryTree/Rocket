<?php

namespace DirectoryTree\Rocket\Deployments;

class NumberConverter
{
    /**
     * The numbers and their English variants.
     *
     * @var array
     */
    protected static $numbers = [
        '0' => 'Zero',
        '1' => 'One',
        '2' => 'Two',
        '3' => 'Three',
        '4' => 'Four',
        '5' => 'Five',
        '6' => 'Six',
        '7' => 'Seven',
        '8' => 'Eight',
        '9' => 'Nine',
    ];

    /**
     * Converts the numbers into their English variants.
     *
     * @param string $numbers
     *
     * @return string
     */
    public static function convert($numbers)
    {
        $converted = array_map(function ($number) {
            return static::$numbers[$number] ?? null;
        }, str_split($numbers));

        return implode(array_filter($converted));
    }
}
