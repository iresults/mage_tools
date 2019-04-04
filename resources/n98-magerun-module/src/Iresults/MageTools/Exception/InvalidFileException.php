<?php

namespace Iresults\MageTools\Exception;

class InvalidFileException extends \InvalidArgumentException
{
    /**
     * @param $file
     */
    public static function assert($file)
    {
        if (!is_string($file) || '' === trim($file)) {
            throw new static('File must be an non-empty string');
        }
        if (!is_readable($file)) {
            if (!file_exists($file)) {
                throw new static(sprintf('File "%s" could not be found', $file));
            } else {
                throw new static(sprintf('File "%s" is not readable', $file));
            }
        }
    }
}
