<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Deployments\NumberConverter;

class NumberConverterTest extends TestCase
{
    public function test_numbers_are_converted_to_english()
    {
        $this->assertEquals('', NumberConverter::convert(null));
        $this->assertEquals('OneTwoThree', NumberConverter::convert('123'));
        $this->assertEquals('OneTwoThree', NumberConverter::convert('foo-bar .123'));
    }
}
