<?php

namespace Cti\Core\Module;

use Build\Application;
use Cti\Core\Application\Bootstrap;
use Cti\Di\Reflection;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class Cache implements Bootstrap
{
    /**
     * @inject
     * @var Application
     */
    protected $application;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var bool
     */
    public $enabled = false;

    /**
     * bootstrap application
     * @param Application $application
     * @return mixed
     */
    public function boot(Application $application)
    {
        if($this->enabled) {
            if($this->exists('di')) {
                $application->getManager()->get('Cti\\Di\\Cache')->setData($this->get('di'));
            }
        }
    }

    /**
     * initialize cache module
     */
    public function init()
    {
        $this->filesystem = new Filesystem();
    }

    public function generate()
    {
        $this->generateDi();
    }

    public function exists($key)
    {
        return $this->getFilesystem()->exists($this->getFilename($key));
    }

    public function get($key)
    {
        return include $this->getFilename($key);
    }

    public function set($key, $data)
    {
        $this->getFilesystem()->dumpFile(
            $this->getFilename($key),
            '<?php return ' . var_export($data, true) . ';'
        );
    }

    private function getFilename($key)
    {
        return $this->getApplication()->getProject()->getPath('build cache ' . $key . '.php');
    }

    private function generateDi()
    {
        // define available classes
        $coreSource = dirname(__DIR__);
        $buildSource = $this->getApplication()->getProject()->getPath('build php');
        $source = $this->getApplication()->getProject()->getPath('src php');

        $path = array($coreSource, $source);

        if(is_dir($buildSource)) {
            $path[] = $buildSource;
        }

        $finder = new Finder();
        $finder->in($path)->files();

        $inspector = $this->getApplication()->getManager()->getInspector();

        // warm inspector
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

            if(!class_exists($class)) {
                continue;
            }

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
         * @var \Cti\Di\Cache $cache
         */
        $cache = $this->getApplication()->getManager()->get('Cti\\Di\\Cache');
        $this->set('di', $cache->getData());
    }

    /**
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }
}