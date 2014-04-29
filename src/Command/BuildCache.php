<?php

namespace Cti\Core\Command;

use Cti\Core\Application;
use Cti\Di\Cache;
use Cti\Di\Manager;
use Cti\Di\Reflection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class BuildCache extends Command
{
    /**
     * @inject
     * @var Application
     */
    protected $application;

    protected function configure()
    {
        $this
            ->setName('build:cache')
            ->setDescription("Generate di cache file");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $this->application->getPath('build php cache.php');
        $filesystem = new Filesystem();
        if($filesystem->exists($filename)) {
            $filesystem->remove($filename);
        }

        $finder = new Finder();

        $coreSource = dirname(__DIR__);
        $buildSource = $this->application->getPath('build php');
        $source = $this->application->getPath('src php');

        $path = array($coreSource, $source);

        if(is_dir($buildSource)) {
            $path[] = $buildSource;
        }

        $finder->in($path)->files();

        $inspector = $this->application->getManager()->getInspector();

        foreach($finder as $file) {
            $path = $file->getPath();
            if(strpos($path, $coreSource) === 0) {
                $namespace = 'Cti\Core' . substr($path, strlen($coreSource));

            } elseif(strpos($path, $buildSource) === 0) {
                $namespace = substr($path, strlen($buildSource) + 1);

            } elseif(strpos($path, $source) === 0) {
                $namespace = substr($path, strlen($source) + 1);

            }

            $class = str_replace(DIRECTORY_SEPARATOR, '\\', $namespace) . '\\' . $file->getBasename('.php');

            $inspector->getPublicMethods($class);
            $inspector->getClassInjection($class);
            $inspector->getClassProperties($class);

            foreach(Reflection::getReflectionClass($class)->getMethods() as $method) {
                $inspector->getMethodArguments($class, $method->getName());
                $inspector->getMethodRequiredCount($class, $method->getName());
            }
        }

        $namespaces = array(
            'Api',
            'Controller',
            'Direct',
            'Extension',
            'Model',
        );

        foreach($namespaces as $ns) {
            $this->application->getClasses($ns);
        }

        /**
         * @var Cache $cache
         */
        $cache = $this->application->getManager()->get('Cti\Di\Cache');

        $filesystem->dumpFile($filename, '<?php return '. var_export($cache->getData(), true).';');
    }
}
