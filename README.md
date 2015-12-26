# router
Simple Web Application Router Class

## usage and example

- Install with composer:
`composer require gymadarasz/router`

- Add the code below to your `.htaccess` file for user and SEO friendly URLs:
```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]
```

- Add the code below to your `index.php`:
 
```php
<?php

include 'vendor/autoload.php';

use gymadarasz\router\Router;

// example handlers:

function action_handler($route, $matches) {
	echo 'custom action handler...' . PHP_EOL;
}

function action_handler_numeric($route, $matches) {
	echo 'custom action num handler...' . PHP_EOL;
}

function action_test($route, $matches) {
	echo 'custom action handler for /test url...' . PHP_EOL;
}

// or you can use a controller class for callbacks..
class MyDefaultControllerClass {
	
	public function indexMethod($route, $matches) {
		echo 'default route...' . PHP_EOL;
	}
	
}

// usage:


$base = '/';
$routes = [
	Router::regex('GET,POST', 'test$') => 'action_test',
	Router::regex('GET,POST', 'test/(:num)$') => 'action_handler_numeric',
	Router::regex('GET,POST', 'test/(:any)$') => 'action_handler',
];
try {
	Router::dispatch($routes, $base);
}
catch(RouterException $e) {
	MyDefaultControllerClass::indexMethod(null, null);
}

/* call the next example urls:
http://localhost/
http://localhost/test
http://localhost/test/keyword
http://localhost/test/123
*/

```

### explanation

```php
<?php

include 'vendor/autoload.php';

use gymadarasz\router\Router;

// Default request uri base is '/' but you can override the default.

$base = '/path/to/your/app/';


// Create an array for your application's routes.

$routes = [


  // Use regex helper to easy definiate the routes.
  // Use parentheses for sing the important data in request
  // cause your action handlers will give these information from Router.
  // (The follow example need an 'action_handler' function.)
	
	Router::regex('GET,POST', ':any/(:num)') => 'action_handler',
	
	
	// You can use simple regex for routing.
	// (The follow example need an 'action_test' function.)
	
	'GET,POST:/^test\/(\d+)/' => 'action_test',	
	
	
	// Use multi-level routing for more performance!
	// Don't able to Router that try to search in all subpage if it's unnecessary.
	Router::regex('ANY', 'high/level/route/') => [
	
	  // The next routes evaluated only if the parent route matched!
	  
	  Router::regex('ANY', 'high/level/route/subpage1') => 'subpage1',
	  Router::regex('ANY', 'high/level/route/subpage2') => 'subpage2',
	  Router::regex('ANY', 'high/level/route/moresubpages') => [
	
	    // ... 
	    // more subpages here..
	    
	  ],
	],
	
	// Set a default route pattern which can catch everything:
	// (ANY or GET,POST) method definitions are same!
	// You can use object oriented handlers also.
	// (The follow example need a MyDefaultControllerClass class 
	// in MyNamespace namespace within a method named indexMethod)
	// The default route have to be the last place of this array
	// cause the Router looks the routes until found one and 
	// this pattern will matching for everithing.
	'ANY:/.*/' => 'MyNamespace\\MyDefaultControllerClass::indexMethod',
	
];


// Call the dispatch..

Router::dispatch($routes, $base);


// This is an example route action handler. 
// The action handlers can use the $route and 
// $matches arguments from Router.
// $route is a string contains the current pattern
// $matches is an array contains the current URL
// and if you use parenthesis in your regex then
// contains the important url arguments.
function action_handler($route, $matches) {
	var_dump($route);
	var_dump($matches);
}



```
