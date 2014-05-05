<?php

namespace Cti\Core\Application;

use Cti\Core\Exception;
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

        if(!isset($project['path'])) {
            throw new Exception('Cti\Core\Module\Project.path not defined');
        }

        $filename = implode(DIRECTORY_SEPARATOR, array($project['path'], 'build', 'php', 'Build', 'Application.php'));

        $filesystem = new Filesystem();
        $filesystem->dumpFile($filename, $this->renderApplication());
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

    /**
     * @return Manager
     */
    public function getManager()
    {
        return \$this->manager;
    }
HEADER;

        $methods = array('');
        foreach(array($this->core, $this->modules) as $source) {
            foreach($source as $class) {
                $methods[] = $this->renderGetter($class);
            }
        }
        $contents .= implode(PHP_EOL . PHP_EOL, $methods);
        $contents .= PHP_EOL . '}';

        return $contents;
    }

    /**
     * @param $class
     * @return string
     */
    private function renderGetter($class)
    {
        $name = Reflection::getReflectionClass($class)->getShortName();
        $getter = 'get'.$name;
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