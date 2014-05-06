<?php

return array(
    'Cti\Core\Application\Factory' => array(
        'generate' => true,
    ),
    'Cti\Core\Application\Generator' => array(
        'modules' => array(
            'alias' => 'Module\Greet'
        )
    ),
    'Cti\Core\Module\Project' => array(
        'path' => dirname(dirname(__DIR__)),
    ),
    'class' => array(
        'property' => 'value', 
        'property2' => 'value'
    )
);