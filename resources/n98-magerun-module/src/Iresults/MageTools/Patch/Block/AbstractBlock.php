<?php

namespace Iresults\MageTools\Patch\Block;

use Iresults\MageTools\Assert;
use function get_class;
use function strrchr;
use function strtolower;
use function substr;

abstract class AbstractBlock implements BlockInterface
{
    /**
     * @var string
     */
    private $identifier = '';

    /**
     * @var string
     */
    private $content = '';

    /**
     * Block constructor.
     *
     * @param string $identifier
     * @param string $content
     */
    public function __construct($identifier, $content = '')
    {
        Assert::assertString($identifier);
        Assert::assertString($content);
        $this->identifier = $identifier;
        $this->content = $content;
    }

    public function getType()
    {
        return strtolower(substr(strrchr(get_class($this), '\\'), 1, -5));
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Return the Block's starting line
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
