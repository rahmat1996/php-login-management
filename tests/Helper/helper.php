<?php

namespace Tamhar\PhpLoginManagement\App {
    function header(string $value)
    {
        echo $value;
    }
}

namespace Tamhar\PhpLoginManagement\Service {
    function setcookie(string $name, string $value)
    {
        echo "$name: $value";
    }
}