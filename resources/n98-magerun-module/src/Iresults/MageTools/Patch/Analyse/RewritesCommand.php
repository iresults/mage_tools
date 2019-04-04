<?php

namespace Iresults\MageTools\Patch\Analyse;

use Iresults\MageTools\Patch\Block\ClassBlock;
use Iresults\MageTools\Patch\PatchAnalyser;
use N98\Util\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RewritesCommand extends AbstractAnalyseCommand
{
    protected function configure()
    {
        $this
            ->setName('patch:analyse:rewrites')
            ->setDescription('Find rewritten classes in the patch')
            ->addDefaultArgumentsAndOptions();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output);
        if ($this->initMagento(false)) {
            $criticalClasses = $this->analyseClasses($this->getPatchAnalyser($input));
            if (empty($criticalClasses)) {
                $output->writeln('<info>No critical classes found</info>');

                return;
            }

            $output->writeln('<error>Please check the following classes:</error>');

            $table = [];
            foreach ($criticalClasses as $match) {
                /** @var ClassBlock $classBlock */
                $classBlock = $match[0];
                $rewriteClass = $match[1];
                $table[] = [
                    $classBlock->getClassType(),
                    $classBlock->getIdentifier(),
                    $classBlock->getName(),
                    implode(', ', $rewriteClass),
                ];
            }

            /* @var $tableHelper TableHelper */
            $tableHelper = $this->getHelper('table');
            $tableHelper
                ->setHeaders(['Type', 'Class', 'Identifier', 'Rewrite'])
                ->setRows($table)
                ->renderByFormat($output, $table, $input->getOption('format'));
        }
    }

    private function analyseClasses(PatchAnalyser $analyser)
    {
        $criticalClasses = [];
        $rewritesByType = $this->loadRewrites();
        foreach ($analyser->getClassesByType() as $type => $rewrites) {
            /** @var ClassBlock $classBlock */
            foreach ($rewrites as $classBlock) {

                $matchingRewrite = $this->getRewrite($rewritesByType, $classBlock);
                if ($matchingRewrite) {
                    $criticalClasses[] = [$classBlock, $matchingRewrite];
                }
            }
        }

        return $criticalClasses;
    }

    private function getRewrite(array $rewritesByType, ClassBlock $classBlock)
    {
        $type = $classBlock->getClassType();
        if (!isset($rewritesByType[$type])) {
            return null;
        }
        $rewrites = $rewritesByType[$type];
        if (isset($rewrites[$classBlock->getName()])) {
            return $rewrites[$classBlock->getName()];
        }

        $nameWithoutSlash = str_replace('/', '_', $classBlock->getName());
        foreach ($rewrites as $rewrite) {
            if ($nameWithoutSlash === str_replace('/', '_', $rewrite)) {
                return $rewrite;
            }
        }

        return null;
    }
}
