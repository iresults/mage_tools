<?php

namespace Iresults\MageTools\Patch;

use function fclose;
use function feof;
use function fgets;
use function file_exists;
use function fopen;
use function is_readable;
use function is_string;
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
        if (!is_string($file) || '' === trim($file)) {
            throw new \InvalidArgumentException('Patch file must be an non-empty string');
        }
        if (!is_readable($file)) {
            if (!file_exists($file)) {
                throw new \InvalidArgumentException(sprintf('Patch file "%s" could not be found', $file));
            } else {
                throw new \InvalidArgumentException(sprintf('Patch file "%s" is not readable', $file));
            }
        }
        $this->handle = fopen($file, 'r');
        if (!$this->handle) {
            throw new \InvalidArgumentException(sprintf('Patch file "%s" could not be opened', $file));
        }
        $this->scrollToPatchStart($this->handle);
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
     * @param $handle
     */
    private function scrollToPatchStart($handle)
    {
        while (($buffer = fgets($handle, 4096)) !== false) {
            if (trim($buffer) === '__PATCHFILE_FOLLOWS__') {
                return;
            }
        }
    }

}
