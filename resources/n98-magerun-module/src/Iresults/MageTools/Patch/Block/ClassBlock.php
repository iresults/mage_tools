<?php

namespace Iresults\MageTools\Patch\Block;

class ClassBlock extends AbstractBlock
{
    const TYPE_CONTROLLER = 'controllers';
    const TYPE_MODELS = 'models';
    const TYPE_BLOCKS = 'blocks';
    const TYPE_HELPERS = 'helpers';
    const TYPE_LIB = 'lib';

    /**
     * @return string
     */
    public function getClassType()
    {
        $identifier = $this->getIdentifier();
        if (strtolower(substr($identifier, -10)) === 'controller') {
            return self::TYPE_CONTROLLER;
        }
        if (strpos($identifier, '_Helper_') !== false) {
            return self::TYPE_HELPERS;
        }
        if (strpos($identifier, '_Block_') !== false) {
            return self::TYPE_BLOCKS;
        }
        if (strpos($identifier, '_Model_') !== false) {
            return self::TYPE_MODELS;
        }
        if (substr($identifier, 0, 5) === 'Zend_' || substr($identifier, 0, 7) === 'Varien_') {
            return self::TYPE_LIB;
        }

        return ($identifier);
    }

    /**
     * @return string
     */
    public function getName()
    {
        $identifier = $this->getIdentifier();
        if (substr($identifier, 0, 5) === 'Zend_' || substr($identifier, 0, 7) === 'Varien_') {
            return $this->lcWords($identifier);
        }
        if ($this->getClassType() === self::TYPE_CONTROLLER) {
            return $this->lcWords($identifier);
        }

        if (substr($identifier, 0, 5) === 'Mage_') {
            /** @noinspection PhpUnusedLocalVariableInspection */
            list($group, $_, $class) = explode('_', substr($identifier, 5), 3);

            return $this->lcWords($group . '/' . lcfirst($class));
        }

        return $this->lcWords($identifier);
    }

    private function lcWords($text)
    {
        return implode('_', array_map('lcfirst', explode('_', $text)));
    }
}
