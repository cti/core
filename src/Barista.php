<?php

namespace Cti\Core;

use Symfony\Component\Filesystem\Filesystem;

class Barista
{
    /**
     * @inject
     * @var Application
     */
    protected $application;

    protected $sources = array();

    public function build($script)
    {
        $result = '';

        $filename = $this->application->getPath("resources coffee $script.coffee");
        $dependencies = $this->getDependencyList($filename);

        foreach(array_reverse($dependencies) as $coffee) {

            $local = $this->getLocalPath($coffee);

            $local = dirname($local) . DIRECTORY_SEPARATOR . basename($local, 'coffee') .'js';
            $javascript = $this->application->getPath(sprintf('build js %s', $local));

            $out = dirname($javascript);
            $command = "coffee -b -o $out -c $coffee";

            $input = $output = array();
            exec($command, $input, $output);

            $result .= file_get_contents($javascript) . PHP_EOL;
        }

        $fs = new Filesystem;
        $filename = $this->application->getPath("public js $script.js");
        $fs->dumpFile($filename, $result);

        return $filename;
    }

    public function init()
    {
        $this->addSource($this->application->getPath('src coffee'));
        $this->addSource($this->application->getPath('resources coffee'));
    }

    public function addSource($location)
    {
        if(!in_array($location, $this->sources)) {
            $this->sources[] = $location;
        }
        return $this;
    }

    public function getClassSource($class)
    {
        $path = str_replace('.', DIRECTORY_SEPARATOR, $class) . '.coffee';
        foreach($this->sources as $location) {
            $filename = $location . DIRECTORY_SEPARATOR . $path;
            if(file_exists($filename)) {
                return $filename;
            }
        }
        throw new Exception(sprintf("Source for %s not found", $class));
    }

    public function getLocalPath($filename)
    {
        foreach($this->sources as $location) {
            if(strpos($filename, $location) === 0) {
                return substr($filename, strlen($location)+1);
            }
        }
        throw new Exception(sprintf('Invalid source search for %s', $filename));
    }

    protected function getDependencyList($script)
    {
        $result = array();
        if(!file_exists($script)) {
            throw new Exception(sprintf('File %s not found', $script));
        }
        $contents = file_get_contents($script);
        $result[] = $script;
        foreach($this->getScriptDependencies($contents) as $class) {
            if(strpos($class, 'Ext.') !== 0) {
                $file = $this->getClassSource($class);
                if(!in_array($file, $result)) {
                    $result[] = $file;
                }
                foreach($this->getDependencyList($file) as $dependency) {
                    if(!in_array($dependency, $result)) {
                        $result[] = $dependency;
                    }
                }
            }
        }
        return $result;
    }

    protected function getScriptDependencies($text)
    {
        return array_merge(
            $this->getRequires($text),
            $this->getMixins($text),
            $this->getCreate($text),
            $this->getExtend($text)
        );
    }

    protected function getRequires($text)
    {
        $requires = array();
        $pregs = array(
            "/Ext.require ['\"]([a-zA-Z0-9.]+)['\"]/",
            "/Ext.syncRequire ['\"]([a-zA-Z0-9.]+)['\"]/",
        );
        foreach ($pregs as $p) {
            preg_match_all($p, $text, $answer);
            $requires = array_merge($requires,$answer[1]);
        }

        $p = "/requires\s*:\s*\[['\"a-zA-Z0-9.,\s]+\]/";
        preg_match_all($p, $text, $output);
        $p = "/['\"]([a-zA-Z0-9.]*)['\"]/";
        $required_classes = array();
        foreach ($output[0] as $require) {
            preg_match_all($p, $require, $match);
            $required_classes = array_merge($required_classes, $match[1]);
        }

        return array_merge($requires,$required_classes);

    }

    protected function getMixins($text)
    {
        $mix = array();
        $p = "/mixins\s*:\s*[\[{][^\[\]}{]+[\]}]/";
        preg_match_all($p, $text, $match);
        $p = "/['\"]([a-zA-Z0-9._]+)['\"]/";
        foreach ($match[0] as $mixin) {
            preg_match_all($p, $mixin, $classes);
            if ($classes[1]) {
                $mix = array_merge($mix, $classes[1]);
            }
        }

        return $mix;
    }

    protected function getExtend($text)
    {
        $p = "/extend\s*:\s*['\"]([a-zA-Z0-9._]+)['\"]/";
        preg_match_all($p, $text, $answer);

        return $answer[1];
    }

    protected function getCreate($text)
    {
        $p = "/Ext.create ['\"]([a-zA-Z0-9.]+)['\"]/";
        preg_match_all($p, $text, $answer);

        return $answer[1];
    }
    
}