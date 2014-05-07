<?php

namespace Cti\Core\Command;

use Build\Application;
use Cti\Core\Application\Factory;
use Cti\Core\Exception;
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
        $filesystem->mkdir($this->getApplication()->getProject()->getPath('build php'));

        // check cache module configuration
        $configuration = $this->getApplication()->getManager()->getConfiguration();
        $enabled = $configuration->get('Cti\\Core\\Module\\Cache', 'enabled', true);
        if(!$enabled) {
            throw new Exception("Module\\Cache should be enabled");
        }

        $this->getApplication()->warm();
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }
}
