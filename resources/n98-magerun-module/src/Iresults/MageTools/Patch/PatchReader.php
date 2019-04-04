<?php

namespace Iresults\MageTools\Patch;

use Iresults\MageTools\Exception\InvalidPatchFileException;
use function fclose;
use function feof;
use function fgets;
use function fopen;
use function sprintf;
use function trim;

class PatchReader
{
    /**
     * @var bool|resource
     */
    private $handle;

    /**
     * PatchReader constructor.
     *
     * @param $file
     */
    public function __construct($file)
    {
        InvalidPatchFileException::assert($file);
        $this->handle = fopen($file, 'r');
        if (!$this->handle) {
            throw new InvalidPatchFileException(sprintf('Patch file "%s" could not be opened', $file));
        }
        if (!$this->scrollToPatchStart($this->handle)) {
            throw new InvalidPatchFileException(sprintf('File "%s" does not appear to be a valid patch file', $file));
        }
    }

    public function read()
    {
        while (($buffer = fgets($this->handle, 4096)) !== false) {
            yield $buffer;
        }

        if (!feof($this->handle)) {
            throw new \UnexpectedValueException('Unexpected fgets() fail');
        }
    }

    public function __destruct()
    {
        fclose($this->handle);
    }

    /**
     * @param resource $handle
     * @return bool Return if the patch start marker was found
     */
    private function scrollToPatchStart($handle)
    {
        while (($buffer = fgets($handle, 4096)) !== false) {
            if (trim($buffer) === '__PATCHFILE_FOLLOWS__') {
                return true;
            }
        }

        return false;
    }
}
