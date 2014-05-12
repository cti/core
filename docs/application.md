# Application
Application consists of modules, modules are simple php classes.  
To reduce dynamic processing, application is generated based on configuration.

# Interfaces
There are some core module interfaces.  
- Bootstrap  
If your module needs to do some work to initialize application implement Bootstrap interface.  
- Warm  
Implement Warm interface to do hard work (cache warm, code generation, etc..)

# Generator
Generator use core and module properties to generate application class.  
Let's write your own module and see how it should be registered. 

```php
<?php
namespace Acme\Food;

class Chief
{
    function getBurger()
    {
    }
}
```

If you want to use it in you application, you should register it in generator config:

```php
<?php
return array(
    'Cti\Core\Application\Generator' => array(
        'modules' => array(
            'Acme\Food\Chief'
            
            // if you want you can use alias
            // 'Boris' => 'Acme\Food\Chief'
            // and call getBoris method instead of getChief.
        )
    )
);
```

Than your generated application will have getter for this module.  

```php
<?php
use Cti\Core\Application\Factory;

$root = __DIR__;
$burger = Factory::get($root)->getChief()->getBurger();
```

Application use cti\di to instantiate classes.


# Factory
By default, application is generating when destination file is not exists.  
If you need to force generate, you can add generate property in your config:

```php
<?php
return array(
    'Cti\Core\Application\Factory' => array(
        'generate' => true,
    )
);
```
