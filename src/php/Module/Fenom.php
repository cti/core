<?php

namespace Cti\Core\Module;

use Build\Application;
use Cti\Core\Application\Warmer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class Fenom implements Warmer
{
    /**
     * @var array
     */
    private $source = array();

    /**
     * @var \Fenom[]
     */
    private $instances = array();

    /**
     * @var string
     */
    private $build;

    /**
     * @param Project $project
     */
    public function init(Project $project)
    {
        $this->build = $project->getPath('build fenom');
        $filesystem = new Filesystem();
        $filesystem->mkdir($this->build);

        $this->source[] = $project->getPath('resources fenom');
    }

    /**
     * @param $path
     * @return Fenom
     */
    public function addSource($path)
    {
        $this->source[] = $path;
        return $this;
    }

    /**
     * @param string $template
     * @param array $data
     * @return string
     */
    public function render($template, $data = array())
    {
        $template = implode(DIRECTORY_SEPARATOR, explode(' ', $template)) . '.tpl';
        foreach($this->source as $index => $resource) {
            if(file_exists($resource . DIRECTORY_SEPARATOR . $template)) {
                return $this->getFenom($index)->fetch($template, $data);
            }
        }
    }

    /**
     * @return \Fenom
     */
    private function getFenom($index)
    {
        if(!isset($this->instances[$index])) {
            $filesystem = new Filesystem();
            $filesystem->mkdir($this->source[$index]);
            $this->instances[$index] = \Fenom::factory(
                $this->source[$index],
                $this->build,
                \Fenom::AUTO_RELOAD
            );
        }
        return $this->instances[$index];
    }

    /**
     * warm application
     * @param Application $application
     * @return mixed
     */
    public function warm(Application $application)
    {
        $compiled = array();
        foreach($this->source as $index => $source) {

            $fenom = $this->getFenom($index);
            $start = strlen($source) + 1;

            $finder = new Finder();
            foreach($finder->files()->name('*.tpl')->in($source) as $file) {
                $name = substr($file, $start);
                if(!isset($compiled[$name])) {
                    $fenom->compile($name);
                    $compiled[$name] = true;
                }
            }
        }
    }
}