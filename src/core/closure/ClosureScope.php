<?php


namespace core\closure;

/**
 * Closure scope class
 * @internal
 */
class ClosureScope extends \SplObjectStorage
{

    public int $serializations = 0;

    public int $toserialize = 0;
}