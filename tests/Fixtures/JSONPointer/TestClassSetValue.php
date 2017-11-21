<?php declare(strict_types=1);

namespace Kcs\ApiPlatformBundle\Tests\Fixtures\JSONPointer;

class TestClassSetValue
{
    protected $value;

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function __construct($value)
    {
        $this->value = $value;
    }
}
