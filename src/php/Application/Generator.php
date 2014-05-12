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

        $bootstrap = $warm = array('Manager');
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
                        $warm[] = $alias;
                    }
                    $methods[$alias] = $this->renderGetter($alias, $class);
                }
            }
        }
        $methods['-2'] = $this->renderBootstrap($bootstrap);

        $methods['-1'] = $this->renderWarm($warm);

        ksort($methods);
        $contents .= implode(PHP_EOL . PHP_EOL, $methods);
        $contents .= PHP_EOL . '}';

        return $contents;
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