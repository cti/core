# Routing
This module provides http processing.  
If you want to create new route, just add method.  
Method consists of two parts (http method, slug).

```php
<?php

class DefaultController
{
    /**
     * GET /
     */
    function get()
    {}
    
    /**
     * GET /hello
     * GET /hello/Dmitry
     */
    function getHello($name = 'World')
    {}
    
    /**
     * POST /upload
     */
    function postUpload()
    {}
}
```

If you have many actions it is not usefull to put all your actions in one controller.  
Default controller mounts to /, other controllers mount is based on controller name.  

```php
<?php

class ExcelController
{
    /**
     * GET /excel/changeling
     */
    function getChangeling()
    {}
    
    /**
     * POST /excel/upload
     */
    function postUpload()
    {}
}
```

# Parameters
Web module uses cti/di, so you can inject any dependencies
```php
<?php

use Cti\Core\Module\Web;

class DefaultController
{
    public function getIndex(Web $web)
    {
        echo $web->getUrl('hello'); // /hello
    }
}
```

# Base url
Set web module base property, to run project in subfolder.  
```php
// config.php (or local.config.php)
<?php
return array(
    'Cti\Core\Module\Web' => array(
        'base' => '/project/'
    )
);
```
Now all your routing and url generation will use this option.

# Dynamic processing
While parsing url web creates chain - it is array of slugs.  
If no method found it can be passed to processChain method:

```
<?php

class DefaultController
{
    /**
     * any url goes here
     */
    function processChain($chain)
    {
        // your relative url is here
        echo implode('/', $chain);
    }
}
```
Let's see when it's useful:

```php
<?php
class BlogController
{
    function get()
    {
        // handle /blog url
    }
    
    function processChain($chain)
    {
        $slug = $chain[0];
        
        if(is_numeric($slug)) {
            // process /blog/:id request
        } else {
            // process /blog/:title request
        }
    }
}
```
