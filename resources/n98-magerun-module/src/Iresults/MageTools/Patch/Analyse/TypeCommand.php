<?php

namespace Iresults\MageTools\Patch\Analyse;

use Iresults\MageTools\Patch\Block\BlockInterface;
use Iresults\MageTools\Patch\Block\ClassBlock;
use Iresults\MageTools\Patch\Block\ConfigurationBlock;
use Iresults\MageTools\Patch\Block\JavascriptBlock;
use Iresults\MageTools\Patch\Block\StylesheetBlock;
use Iresults\MageTools\Patch\Block\TemplateBlock;
use Iresults\MageTools\Patch\Block\UnknownBlock;
use N98\Util\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function array_filter;
use function ksort;
use function trim;

class TypeCommand extends AbstractAnalyseCommand
{
    protected function configure()
    {
        $typeArgumentDescription = 'Type to analyse. One of ['
            . implode(
                ', ',
                [
                    'class',
                    'configuration',
                    'javascript',
                    'stylesheet',
                    'template',
                    'unknown',
                ]
            )
            . ']';
        $this
            ->setName('patch:analyse:type')
            ->setDescription('Find all patches by type')
            ->addDefaultArgumentsAndOptions()
            ->addArgument('type', InputArgument::OPTIONAL, $typeArgumentDescription, 'all');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if ($this->initMagento(false)) {
            $type = $input->getArgument('type');
            if (!$type || '' === trim($type)) {
                $output->writeln('<error>No type given</error>');

                return 2;
            }
            $analyser = $this->getPatchAnalyser($input);
            $blocks = $this->filterBlocks($analyser->analyse(), $type);

            if (empty($blocks)) {
                $output->writeln(sprintf('<comment>No patched classes for type %s found</comment>', $type));

                return 0;
            }

            $table = [];
            foreach ($blocks as $block) {
                /** @var BlockInterface $block */
                if ($block instanceof ClassBlock) {
                    $extra = $block->getName();
                } else {
                    $extra = null;
                }

                $key = $block->getType() . '_' . $block->getIdentifier();
                $table[$key] = [
                    $block->getType(),
                    $block->getIdentifier(),
                    $extra,
                ];
            }

            ksort($table);

            /* @var $tableHelper TableHelper */
            $tableHelper = $this->getHelper('table');
            $tableHelper
                ->setHeaders(['Type', 'Identifier', 'Extra'])
                ->setRows($table)
                ->renderByFormat($output, $table, $input->getOption('format'));
        }

        return 0;
    }

    private function filterBlocks(array $blocks, $type)
    {
        if ($type === 'all') {
            return $blocks;
        }

        return array_filter(
            $blocks,
            function (BlockInterface $block) use ($type) {
                switch ($type) {
                    case 'class':
                        return $block instanceof ClassBlock;
                    case 'configuration':
                        return $block instanceof ConfigurationBlock;
                    case 'javascript':
                    case 'js':
                        return $block instanceof JavascriptBlock;
                    case 'stylesheet':
                    case 'css':
                        return $block instanceof StylesheetBlock;
                    case 'template':
                        return $block instanceof TemplateBlock;
                    case 'unknown':
                    default:
                        return $block instanceof UnknownBlock;
                }
            }
        );
    }
}
