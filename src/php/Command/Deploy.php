<?php

namespace Cti\Core\Command;

use Build\Application;
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
            ->setDescription("Generate di cache file");
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
        $filename = $this->getApplication()->getProject()->getPath('build php cache.php');
        $filesystem = new Filesystem();
        if($filesystem->exists($filename)) {
            $filesystem->remove($filename);
        }

        $finder = new Finder();

        $coreSource = dirname(__DIR__);
        $buildSource = $this->getApplication()->getProject()->getPath('build php');
        $source = $this->getApplication()->getProject()->getPath('src php');

        $path = array($coreSource, $source);

        if(is_dir($buildSource)) {
            $path[] = $buildSource;
        }

        $finder->in($path)->files();

        $inspector = $this->getApplication()->getManager()->getInspector();

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
            'Module',
            'Model',
        );

        foreach($namespaces as $ns) {
            $this->getApplication()->getProject()->getClasses($ns);
        }

        /**
         * @var Cache $cache
         */
        $cache = $this->getApplication()->getManager()->get('Cti\\Di\\Cache');

        $filesystem->dumpFile($filename, '<?php return '. var_export($cache->getData(), true).';');
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }
}
