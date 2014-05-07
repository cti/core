<?php

namespace Cti\Core\Module;

use Build\Application;
use Symfony\Component\Filesystem\Filesystem;

class Cache
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
    public $enabled = true;

    /**
     * initialize cache module
     */
    public function init()
    {
        $this->filesystem = new Filesystem();
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
        $key = str_replace('\\', DIRECTORY_SEPARATOR, $key);
        return $this->getApplication()->getProject()->getPath('build cache ' . $key . '.php');
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