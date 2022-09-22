<?php

declare(strict_types=1);

namespace Core\TestCase\BodyMatchers;

class KeysAndValuesBodyMatcher extends KeysBodyMatcher
{
    public static function init(
        $expectedBody,
        bool $matchArrayOrder = false,
        bool $matchArrayCount = false
    ): KeysBodyMatcher {
        $matcher = parent::init($expectedBody, $matchArrayOrder, $matchArrayCount);
        $matcher->checkValues = true;
        $matcher->defaultMessage = 'Response body does not match in keys and/or values';
        return $matcher;
    }
}