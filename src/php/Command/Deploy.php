<?php

namespace Cti\Core\Command;

use Build\Application;
use Cti\Core\Application\Factory;
use Cti\Di\Cache;
use Cti\Di\Reflection;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Class Deploy
 * @package Cti\Core\Command
 */
class Deploy extends Command
{
    /**
     * @inject
     * @var Application
     */
    protected $application;

    /**
     * configure deploy command
     */
    protected function configure()
    {
        $this
            ->setName('deploy')
            ->setDescription("Generate application files");
    }

    /**
     * process deploy
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return null
     * @throws \Cti\Di\Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filesystem = new Filesystem();

        // clean up build
        $filesystem->remove($this->getApplication()->getProject()->getPath('build'));

        // generate new application
        Factory::create($this->getApplication()->getProject()->getPath(''))->getApplication();

        // generate cache files
        $this->getApplication()->getCache()->generate();
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }
}
