<?php

namespace Iresults\MageTools\Patch;

use Iresults\MageTools\Patch\Block\BlockInterface;
use Iresults\MageTools\Patch\Block\ClassBlock;
use Iresults\MageTools\Patch\Block\ConfigurationBlock;
use Iresults\MageTools\Patch\Block\JavascriptBlock;
use Iresults\MageTools\Patch\Block\PhpBlock;
use Iresults\MageTools\Patch\Block\StylesheetBlock;
use Iresults\MageTools\Patch\Block\TemplateBlock;
use Iresults\MageTools\Patch\Block\TranslationBlock;
use Iresults\MageTools\Patch\Block\UnknownBlock;
use function explode;
use function preg_match;
use function sort;
use function strrchr;
use function substr;

class PatchAnalyser
{
    /**
     * @var PatchReader
     */
    private $reader;

    /**
     * @var BlockInterface[]
     */
    private $blocks = null;

    /**
     * PatchAnalyser constructor.
     *
     * @param PatchReader $reader
     */
    public function __construct(PatchReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @return BlockInterface[]
     */
    public function analyse()
    {
        if (null === $this->blocks) {
            $this->blocks = [];

            foreach ($this->reader->read() as $line) {
                $block = $this->analyseLine($line);
                if ($block) {
                    $this->blocks[] = $block;
                }
            }
        }

        return $this->blocks;
    }

    /**
     * @return ClassBlock[]
     */
    public function getClasses()
    {
        $classBlocks = [];
        foreach ($this->analyse() as $block) {
            if ($block instanceof ClassBlock) {
                $classBlocks[$block->getClassType() . '::' . $block->getName()] = $block;
            }
        }

        return $classBlocks;
    }

    /**
     * @return ClassBlock[][]
     */
    public function getClassesByType()
    {
        $classBlocks = [
            ClassBlock::TYPE_BLOCKS  => [],
            ClassBlock::TYPE_HELPERS => [],
            ClassBlock::TYPE_MODELS  => [],
        ];
        foreach ($this->getClasses() as $block) {
            if ($block instanceof ClassBlock) {
                $classBlocks[$block->getClassType()][$block->getName()] = $block;
            }
        }

        sort($classBlocks[ClassBlock::TYPE_BLOCKS]);
        sort($classBlocks[ClassBlock::TYPE_HELPERS]);
        sort($classBlocks[ClassBlock::TYPE_MODELS]);

        return $classBlocks;
    }

    /**
     * @param $line
     * @return BlockInterface
     */
    private function analyseLine($line)
    {
        if (substr($line, 0, 5) === 'diff ') {
            $parts = explode(' ', $line);
            $filepath = $parts[2];
            $suffix = strrchr($filepath, '.');

            switch ($suffix) {
                case '.phtml':
                case '.html':
                    return new TemplateBlock($filepath, $line);

                case '.xml':
                    return new ConfigurationBlock($filepath, $line);

                case '.css':
                    return new StylesheetBlock($filepath, $line);

                case '.js':
                    return new JavascriptBlock($filepath, $line);

                case '.csv':
                    return new TranslationBlock($filepath, $line);

                case '.php':
                    if ($this->shouldSkipPhpBlock($filepath)) {
                        return null;
                    } else {
                        return new PhpBlock($filepath, $line);
                    }

                default:
                    return new UnknownBlock($filepath, $line);
            }
        }


        preg_match('!\s*class\s(\w+)!', $line, $matches);
        if ($matches) {
            return new ClassBlock($matches[1], $line);
        }

        return null;
    }

    /**
     * @param $filepath
     * @return bool
     */
    private function shouldSkipPhpBlock($filepath)
    {
        return $filepath === 'app/Mage.php'
            || substr($filepath, 0, 11) === 'lib/Varien/'
            || substr($filepath, 0, 9) === 'app/code/';
    }
}
