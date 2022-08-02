<?php

namespace CoreLib\Core\TestCase;

use PHPUnit\Framework\TestCase;

class HeadersMatcher
{
    private $headers = [];
    private $allowExtra = false;
    private $testCase;
    public function __construct(TestCase $testCase)
    {
        $this->testCase = $testCase;
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    public function allowExtra(): void
    {
        $this->allowExtra = true;
    }

    public function assert(array $headers)
    {
        if (!empty($this->headers)) {
            // Http headers are case-insensitive
            $left = array_change_key_case($this->headers);
            $right = array_change_key_case($headers);
            $message = "Headers do not match";
            if (!$this->allowExtra) {
                $message = "$message strictly";
            }
            $this->testCase->assertTrue(TestHelper::isProperSubsetOf($left, $right, $this->allowExtra), $message);
        }
    }
}
