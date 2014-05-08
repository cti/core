<?php

namespace Cti\Core\Module;

use Build\Application;
use Cti\Core\Application\Bootloader;
use Cti\Core\Application\Warmer;
use Cti\Di\Cache;
use Cti\Di\Reflection;
use Symfony\Component\Finder\Finder;

class Manager extends \Cti\Di\Manager implements Bootloader, Warmer
{

    /**
     * bootstrap application
     * @param Application $application
     * @return mixed
     */
    public function boot(Application $application)
    {
        if($application->getCache()->exists(__CLASS__)) {
            /**
             * @var Cache $cache
             */
            $cache = $this->get('Cti\\Di\\Cache');
            $cache->setData($application->getCache()->get(__CLASS__));
        }
    }

    /**
     * warm application
     * @param Application $application
     * @return mixed
     */
    public function warm(Application $application)
    {
        // define available classes
        $coreSource = dirname(__DIR__);
        $buildSource = $application->getProject()->getPath('build php');
        $source = $application->getProject()->getPath('src php');

        $path = array($coreSource, $source);

        if(is_dir($buildSource)) {
            $path[] = $buildSource;
        }

        $finder = new Finder();
        $finder->in($path)->files();

        $inspector = $application->getManager()->getInspector();

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
                if($method->getDeclaringClass()->getName() == $class) {
                    $inspector->getMethodArguments($class, $method->getName());
                    $inspector->getMethodRequiredCount($class, $method->getName());
                }
            }
        }

        $application->getCache()->set(__CLASS__, $this->get('Cti\\Di\\Cache')->getData());
    }
}