<?php

namespace Cti\Core\Module;

use Build\Application;
use Cti\Core\Application\Warmer;
use Cti\Core\Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Cti\Core\String;

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
     */
    public function display($template, $data = array())
    {
        echo $this->render($template, $data);
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
                return $this->getEngine($index)->fetch($template, $data);
            }
        }
        throw new Exception(sprintf("Template %s not found", $template));
    }

    /**
     * @return \Fenom
     */
    private function getEngine($index)
    {
        if(!isset($this->instances[$index])) {

            $source = $this->source[$index];
            $build = $this->build . DIRECTORY_SEPARATOR . md5($this->source[$index]);

            $filesystem = new Filesystem();
            $filesystem->mkdir($source);
            $filesystem->mkdir($build);

            $fenom = $this->instances[$index] = \Fenom::factory(
                $source,
                $build,
                \Fenom::AUTO_RELOAD
            );
            $this->initEngine($fenom);
        }
        return $this->instances[$index];
    }

    /**
     * Init Fenom engine with modifiers and functions
     * @param $fenom
     */
    private function initEngine($fenom)
    {
        $fenom->addModifier('pluralize', function($string){
            return String::pluralize($string);
        });

        $fenom->addModifier('camelcase', function($string){
            return String::convertToCamelCase($string);
        });
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

            $fenom = $this->getEngine($index);
            $start = strlen($source) + 1;

            $finder = new Finder();
            $cnt = 0;
            foreach($finder->files()->name('*.tpl')->in($source) as $file) {
                $cnt++;
                $name = substr($file, $start);
                if(!isset($compiled[$name])) {
                    $fenom->compile($name);
                    $compiled[$name] = true;
                }
            }

            echo "- found $cnt template(s) in $source" . PHP_EOL;
        }
    }
}