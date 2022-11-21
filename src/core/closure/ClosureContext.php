<?php

namespace core\closure;

/**
 * Closure context class
 * @internal
 */
class ClosureContext
{
    public ClosureScope $scope;

    public int $locks;

    public function __construct()
    {
        $this->scope = new ClosureScope();
        $this->locks = 0;
    }
}