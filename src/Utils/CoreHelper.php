<?php

namespace CoreLib\Utils;

use ArrayIterator;

class CoreHelper
{
    /**
     * Serialize any given mixed value.
     *
     * @param mixed $value Any value to be serialized
     *
     * @return string|null serialized value
     */
    public static function serialize($value): ?string
    {
        if (is_string($value) || is_null($value)) {
            return $value;
        }
        return json_encode($value);
    }

    /**
     * Deserialize a Json string
     *
     * @param string|null $json A valid Json string
     *
     * @return mixed Decoded Json
     */
    public static function deserialize(?string $json, bool $associative = true)
    {
        return json_decode($json, $associative) ?? $json;
    }

    /**
     * Check if an array isAssociative (has string keys)
     *
     * @param  mixed $arr Any value to be tested for associative array
     * @return boolean True if the array is Associative, false if it is Indexed
     */
    public static function isAssociative($arr): bool
    {
        if (!is_array($arr)) {
            return false;
        }
        foreach ($arr as $key => $value) {
            if (is_string($key)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Recursively check whether the left value is a proper subset of the right value
     *
     * @param mixed $left        Left expected value
     * @param mixed $right       Right actual value
     * @param bool  $allowExtra  Are extra elements allowed in right array?
     * @param bool  $isOrdered   Should elements in right be compared in order to the left array?
     * @param bool  $checkValues Check primitive values for equality?
     *
     * @return bool True if leftTree is a subset of rightTree
     */
    public static function equals(
        $left,
        $right,
        bool $allowExtra = true,
        bool $isOrdered = false,
        bool $checkValues = true
    ): bool {
        if (is_null($left)) {
            return !$checkValues || is_null($right);
        }
        if (is_null($right)) {
            return !$checkValues;
        }
        $left = is_object($left) ? (array) $left : $left;
        $right = is_object($right) ? (array) $right : $right;
        // If both values are primitive, check if they are equal
        if (!is_array($left) && !is_array($right)) {
            return !$checkValues || $left === $right;
        }
        // Check if one of the values is primitive and the other is not
        if (!is_array($left) || !is_array($right)) {
            return !$checkValues;
        }
        // Return false if size different and checking was strict
        if (!$allowExtra && count($left) != count($right)) {
            return false;
        }
        $keyNum = 0;
        for ($iterator = new ArrayIterator($left); $iterator->valid(); $iterator->next()) {
            $key = $iterator->key();
            $leftVal = $left[$key];
            // Check if key exists
            if (!array_key_exists($key, $right)) {
                return false;
            }
            if ($isOrdered) {
                $rightKeys = array_keys($right);
                // When $isOrdered, check if key exists at some next position
                if (!in_array($key, array_slice($rightKeys, $keyNum), true)) {
                    return false;
                }
                $keyNum = array_search($key, $rightKeys, true);
            }
            $rightVal = $right[$key];
            $keyNum += 1;

            if (CoreHelper::isAssociative($leftVal)) {
                // If left value is tree, right value should also be tree
                if (!CoreHelper::isAssociative($rightVal)) {
                    return !$checkValues;
                }
                if (!self::equals($leftVal, $rightVal, $allowExtra, $isOrdered, $checkValues)) {
                    return false;
                }
            } elseif ($checkValues) {
                if (is_array($leftVal)) {
                    if (!is_array($rightVal)) {
                        return false;
                    }
                    if (!self::isListProperSubsetOf($leftVal, $rightVal, $allowExtra, $isOrdered)) {
                        return false;
                    }
                } elseif (!self::equals($leftVal, $rightVal, $allowExtra, $isOrdered, $checkValues)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Check whether the list is a subset of another list.
     *
     * @param array $leftList   Expected left list
     * @param array $rightList  Right List to check
     * @param bool  $allowExtra Are extras allowed in the right list to check?
     * @param bool  $isOrdered  Should checking be in order?
     *
     * @return bool True if $leftList is a subset of $rightList
     */
    private static function isListProperSubsetOf(
        array $leftList,
        array $rightList,
        bool $allowExtra = true,
        bool $isOrdered = false
    ): bool {
        if ($isOrdered && !$allowExtra) {
            return $leftList === $rightList;
        } elseif ($isOrdered && $allowExtra) {
            return array_slice($rightList, 0, count($leftList)) === $leftList;
        } elseif (!$isOrdered && !$allowExtra) {
            return count($leftList) == count($rightList) && self::intersectArrays($leftList, $rightList) == $leftList;
        } else { // if (!$isOrdered && $allowExtra)
            return self::intersectArrays($leftList, $rightList) == $leftList;
        }
    }

    /**
     * Computes the intersection of arrays, even for arrays of arrays
     *
     * @param array $leftList  The array with main values to check
     * @param array $rightList An array to compare values against
     *
     * @return array An array containing all the values in the leftList
     *               which are also present in the rightList
     */
    private static function intersectArrays(array $leftList, array $rightList): array
    {
        return array_map(
            function ($param) {
                return CoreHelper::deserialize($param);
            },
            array_intersect(
                array_map([CoreHelper::class, 'serialize'], $leftList),
                array_map([CoreHelper::class, 'serialize'], $rightList)
            )
        );
    }
}
