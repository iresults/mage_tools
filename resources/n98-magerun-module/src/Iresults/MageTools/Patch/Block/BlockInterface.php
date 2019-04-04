<?php

namespace Iresults\MageTools\Patch\Block;

interface BlockInterface
{
    /**
     * Return the Block's identifier (e.g. class name, template path)
     *
     * @return string
     */
    public function getIdentifier();

    /**
     * Return the Block's type (e.g. 'class', 'javascript', 'configuration')
     *
     * @return string
     */
    public function getType();
}
