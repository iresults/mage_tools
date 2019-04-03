<?php

namespace Iresults\MageTools;

abstract class Assert
{
    public static function assertString($input)
    {
        if (!is_string($input)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expected input to be of type string, "%s" given',
                    is_object($input) ? get_class($input) : gettype($input)
                )
            );
        }
    }
}
