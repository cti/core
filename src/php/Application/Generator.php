<?php

namespace Cti\Core\Application;

use Cti\Core\Exception;
use Cti\Core\String;
use Cti\Di\Configuration;
use Cti\Di\Reflection;
use Symfony\Component\Filesystem\Filesystem;

class Generator
{
    /**
     * core modules
     * @var array
     */
    protected $core = array(
        'Cti\Core\Module\Coffee',
        'Cti\Core\Module\Core',
        'Cti\Core\Module\Console',
        'Cti\Core\Module\Project',
        'Cti\Core\Module\Web',
    );

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
        $project = $configuration->get('Cti\Core\Module\Project');

        $filename = implode(DIRECTORY_SEPARATOR, array($project['path'], 'build', 'php', 'Build', 'Application.php'));

        $filesystem = new Filesystem();
        $filesystem->dumpFile($filename, $this->renderApplication());

        if(!class_exists('Build\Application')) {
            include $filename;
        }
    }

    /**
     * @return string
     */
    private function renderApplication()
    {
        $contents = <<<HEADER
<?php

namespace Build;

use Cti\Di\Manager;

class Application
{
    /**
     * @inject
     * @var Manager
     */
    protected \$manager;


HEADER;

        $bootstrap = array();
        $methods = array('Manager' => <<<MANAGER
    /**
     * @return Manager
     */
    public function getManager()
    {
        return \$this->manager;
    }
MANAGER
);
        foreach(array($this->core, $this->modules) as $source) {
            foreach($source as $alias => $class) {
                if(is_numeric($alias)) {
                    $alias = Reflection::getReflectionClass($class)->getShortName();
                } else {
                    $alias = String::convertToCamelCase($alias);
                }
                if(!isset($methods[$alias])) {
                    if(Reflection::getReflectionClass($class)->implementsInterface('Cti\Core\Application\Bootstrap')) {
                        $bootstrap[] = $alias;
                    }
                    $methods[$alias] = $this->renderGetter($alias, $class);
                }
            }
        }
        if(count($bootstrap)) {
            $methods['-1'] = $this->renderBootstrap($bootstrap);
        }

        ksort($methods);
        $contents .= implode(PHP_EOL . PHP_EOL, $methods);
        $contents .= PHP_EOL . '}';

        return $contents;
    }

    /**
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

    private function renderBootstrap($bootstrap)
    {
        $commands = array();
        foreach($bootstrap as $alias) {
            $commands[] .= '$this->get'.$alias.'()->boot($this);';
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
}