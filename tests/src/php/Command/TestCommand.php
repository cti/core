<?php

namespace Command;

use Symfony\Component\Console\Command\Command as Base;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Base
{
    protected function configure()
    {
        $this
            ->setName('test')
            ->setDescription('Command for console testing')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Test complete");
    }
}
