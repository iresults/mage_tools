<?php

namespace Iresults\MageTools\Patch\Analyse;

use Iresults\MageTools\Exception\InvalidPatchFileException;
use Iresults\MageTools\Patch\PatchAnalyser;
use Iresults\MageTools\Patch\PatchReader;
use N98\Util\Console\Helper\Table\Renderer\RendererFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class AbstractAnalyseCommand extends \N98\Magento\Command\Developer\Module\Rewrite\AbstractRewriteCommand
{
    protected function getPatchAnalyser(InputInterface $input)
    {
        $file = $input->getArgument('patch-file');
        InvalidPatchFileException::assert($file);
        $patchReader = new PatchReader($file);

        return new PatchAnalyser($patchReader);
    }

    protected function addDefaultArgumentsAndOptions()
    {
        $this->addArgument('patch-file', InputArgument::REQUIRED, 'Patch file to analyse')
            ->addOption(
                'format',
                null,
                InputOption::VALUE_OPTIONAL,
                'Output Format. One of [' . implode(',', RendererFactory::getFormats()) . ']'
            );

        return $this;
    }
}
