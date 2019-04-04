<?php

namespace Iresults\MageTools\Patch\Analyse;

use Iresults\MageTools\Patch\Block\ClassBlock;
use N98\Util\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function array_values;
use function strcmp;
use function trim;
use function usort;

class ClassTypeCommand extends AbstractAnalyseCommand
{
    protected function configure()
    {
        $typeArgumentDescription = 'Class-type to analyse. One of ['
            . implode(
                ', ',
                [
                    ClassBlock::TYPE_CONTROLLER,
                    ClassBlock::TYPE_MODELS,
                    ClassBlock::TYPE_BLOCKS,
                    ClassBlock::TYPE_HELPERS,
                    ClassBlock::TYPE_LIB,
                ]
            )
            . ']';
        $this
            ->setName('patch:analyse:class-type')
            ->setDescription('Find class patches by class-type')
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
            $analyser = $this->getPatchAnalyser($input);
            $classesByType = $analyser->getClassesByType();
            $type = $input->getArgument('type');
            if (!$type || '' === trim($type)) {
                $output->writeln('<error>No type given</error>');

                return 2;
            }

            if ($type === 'all') {
                $foundClasses = $this->flattenArray($classesByType);
            } elseif (isset($classesByType[$type])) {
                $foundClasses = $classesByType[$type];
            } else {
                $output->writeln(sprintf('<comment>No patched classes for type "%s" found</comment>', $type));

                return 0;
            }

            $table = [];
            foreach ($foundClasses as $classBlock) {
                /** @var ClassBlock $classBlock */
                $table[] = [
                    $classBlock->getClassType(),
                    $classBlock->getIdentifier(),
                    $classBlock->getName(),
                ];
            }

            /* @var $tableHelper TableHelper */
            $tableHelper = $this->getHelper('table');
            $tableHelper
                ->setHeaders(['Type', 'Class', 'Identifier'])
                ->setRows($table)
                ->renderByFormat($output, $table, $input->getOption('format'));
        }

        return 0;
    }

    /**
     * @param array $classesByType
     * @return array
     */
    protected function flattenArray(array $classesByType)
    {
        $foundClasses = array_merge(...array_values($classesByType));
        usort(
            $foundClasses,
            function (ClassBlock $a, ClassBlock $b) {
                if ($a->getClassType() === $b->getClassType()) {
                    return strcmp($a->getIdentifier(), $b->getIdentifier());
                } else {
                    return strcmp($a->getClassType(), $b->getClassType());
                }
            }
        );

        return $foundClasses;
    }
}
