<?php

namespace Cti\Core\Application;

use Cti\Core\Exception;
use Cti\Core\String;
use Cti\Di\Configuration;
use Cti\Di\Reflection;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class Generator
 * @package Cti\Core\Application
 */
class Generator
{
    /**
     * core modules
     * @var array
     */
    protected $core = array();

    /**
     * project modules
     * @var array
     */
    protected $modules = array(

    );

    /**
     * @param Configuration $configuration
     * @throws Exception
     */
    public function init(Configuration $configuration)
    {
        $project = $configuration->get('Cti\\Core\\Module\\Project');

        $filename = implode(DIRECTORY_SEPARATOR, array($project['path'], 'build', 'php', 'Build', 'Application.php'));

        $filesystem = new Filesystem();
        $filesystem->dumpFile($filename, $this->renderApplication());

        if(!class_exists('Build\\Application')) {
            include $filename;
        }
    }

    /**
     * rendering full application
     * @return string
     */
    private function renderApplication()
    {
        $contents = $this->renderHeader();

        $bootstrap = $warm = array('Cti\Core\Module\Manager' => 'Manager');
        $methods = array('Manager' => $this->renderManager());

        $core = $this->core;

        if(!count($core)) {
            $modules = scandir(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Module');
            foreach($modules as $line) {
                if(strpos($line, '.php')) {
                    $core[] = 'Cti\\Core\\Module\\' . basename($line, '.php');
                }
            }
        }

        foreach(array($core, $this->modules) as $source) {
            foreach($source as $alias => $class) {
                if(is_numeric($alias)) {
                    $alias = Reflection::getReflectionClass($class)->getShortName();
                } else {
                    $alias = String::convertToCamelCase($alias);
                }

                if(!isset($methods[$alias])) {
                    if(Reflection::getReflectionClass($class)->implementsInterface('Cti\\Core\\Application\\Bootloader')) {
                        $bootstrap[] = $alias;
                    }
                    if(Reflection::getReflectionClass($class)->implementsInterface('Cti\\Core\\Application\\Warmer')) {
                        $warm[$class] = $alias;
                    }
                    $methods[$alias] = $this->renderGetter($alias, $class);
                }
            }
        }
        $methods['-2'] = $this->renderBootstrap($bootstrap);

        $warm = $this->processDependencies($warm);
        $methods['-1'] = $this->renderWarm($warm);

        ksort($methods);
        $contents .= implode(PHP_EOL . PHP_EOL, $methods);
        $contents .= PHP_EOL . '}';

        return $contents;
    }

    /**
     * sort warm by dependency
     * @param array $warm
     * @return array
     */
    function processDependencies($warm)
    {
        $result = array_keys($warm);
        $dependencies = array();
        foreach (array_keys($warm) as $index => $class) {
            if($class == 'Cti\Core\Module\Manager') {
                $last = $index;
            } else {
                $doc = Reflection::getReflectionClass($class)->getDocComment();
                if($doc) {
                    foreach(explode(PHP_EOL, $doc) as $line) {
                        if(stristr($line, '@dependsOn ')) {
                            list($f, $dependency) = explode('@dependsOn ', $line);
                            $dependencies[$class] = trim($dependency);
                        }
                    }
                }
            }
        }
        if(isset($last)) {
            $v = $result[$last];
            unset($result[$last]);
            $result[] = $v;
        }
        $needSort = count($dependencies) > 0;
        $iterations = 0;
        while($needSort) {
            if($iterations ++ > 10) {
                throw new Exception("Sort failed");
            }
            $needSort = false;
            foreach ($dependencies as $class => $dependency) {
                $cIndex = array_search($class, $result);
                $dIndex = array_search($dependency, $result);
                if($dIndex > $cIndex) {
                    // swap keys
                    $result[$dIndex] = $class;
                    $result[$cIndex] = $dependency;
                    $result = array_values($result);
                    $needSort = true;
                    break;
                }
            }
        }
        $aliasList = array();
        foreach($result as $class) {
            $aliasList[] = $warm[$class];
        }
        return $aliasList;
    }


    /**
     * @return string
     */
    private function renderHeader()
    {
        return <<<HEADER
<?php

namespace Build;

use Cti\\Core\\Module\\Manager;

class Application
{
    /**
     * @inject
     * @var Manager
     */
    protected \$manager;


HEADER;
    }

    /**
     * bootstrap method renderer
     * @param $bootstrap
     * @return string
     */
    private function renderBootstrap($bootstrap)
    {
        $commands = array();
        foreach($bootstrap as $alias) {
            $commands[] .= '$this->get' . $alias . '()->boot($this);';
        }
        $commands = implode(PHP_EOL . '        ', $commands);

        return <<<METHOD
    /**
     * initialize application
     */
    public function init()
    {
        $commands
    }
METHOD;
    }

    /**
     * warm method renderer
     * @param $bootstrap
     * @return string
     */
    private function renderWarm($bootstrap)
    {
        $commands = array();
        foreach($bootstrap as $alias) {
            $commands[] .= '$this->get' . $alias . '()->warm($this);';
        }
        $commands = implode(PHP_EOL . '        ', $commands);

        return <<<METHOD
    /**
     * warm application
     */
    public function warm()
    {
        $commands
    }
METHOD;
    }

    /**
     * manager getter renderer
     * @return string
     */
    private function renderManager()
    {
        return <<<MANAGER
    /**
     * @return Manager
     */
    public function getManager()
    {
        return \$this->manager;
    }
MANAGER;
    }

    /**
     * render getter for class with given alias
     * @param $class
     * @return string
     */
    private function renderGetter($alias, $class)
    {
        $getter = 'get'.$alias;

        return <<<METHOD
    /**
     * @return \\$class
     */
    public function $getter()
    {
        return \$this->getManager()->get('$class');
    }
METHOD;
    }
}