<?php

namespace Tamhar\PhpLoginManagement\Middleware;

interface Middleware
{
    function before(): void;
}
