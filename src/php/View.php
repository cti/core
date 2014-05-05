<?php

namespace Cti\Core;
use Cti\Core\Module\Project;

/**
 * Template engine
 * @package Cti\Core
 */
class View
{
    /**
     * @var Module\Project
     */
    protected $project;

    /**
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * render template
     * @param string $name
     * @param array $data 
     * @return string 
     */
    public function render($name, $data = array())
    {
        try {
            extract($data);
            ob_start();
            include $this->project->getPath('resources php view '.func_get_arg(0).'.php');
            return ob_get_clean();
        } catch(\Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * show rendered template
     * @param string $name 
     * @param array $data 
     */
    public function show($name, $data = array()) 
    {
        echo $this->render($name, $data);
    }
}
