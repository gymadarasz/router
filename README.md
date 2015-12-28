# Router
Fast and Simple Web Application Router Class

### Why would I use this over illuminate/routing or symfony/routing?
- Could you write a comparison?
- Speed! :) I like the laravel and symfony frameworks but the routing the first every single pageload, so my philosophy that routing have to be quick cause it just a simple regex matcher and this syntax easier for me. Anyway, here is a benchmark comparison e.g with FastRouter:
https://github.com/gymadarasz/router-benchmark
ref: https://github.com/gymadarasz/router/issues/1

## usage and examples

#### Install with composer:
`composer require gymadarasz/router`

#### Add the code below to your `.htaccess` file for user and SEO friendly URLs:
```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]
```

#### Add the code below to your `index.php`:

```php
<?php

include 'vendor/autoload.php';

use gymadarasz\router\Router;

try {
	Router::dispatch([
		Router::regex('GET', '/') => function() {
			echo '<h2>Hello World on default page!</h2>' . PHP_EOL;
		},
	], $base);  
}
catch(RouterException $e) {
	header("HTTP/1.0 404 Not Found");
	echo 'Sorry, here is nothingh to see..' . PHP_EOL;
}

```

#### Grouping
(see in more explanation bellow)


#### The next is a complet example application

```php
<?php

include 'vendor/autoload.php';

use gymadarasz\router\Router;

// use any base for your app e.g. when your dummy page is in a different subfolder in 
// your local then you can call http://localhost/dummy-page/ url
// default set is '/'
$base = '/dummy-page/';

try {
	// here some way how you can use the router
	Router::dispatch([
		
		
	
		Router::regex('GET', 'get/') => function() {
			echo 	'You called the "/get" url. It will match for "/get"' . 
					'or match with "/get/more/parts..." etc.' . 
					' due there is a "/"  or nothing at the end of pattern<br>' . 
					'<form method="post" action="post">' . 
					'  <input type="submit" value="Post it!">' . 
					'</form>' . PHP_EOL;
		},
		
		Router::regex('POST', 'post$') => function() {
			echo 	'You post some data to the "/post" url. It will match ' . 
					'for "/post" but not match with "/post/more/parts..." etc. ' . 
					'due there is a "$" at the end of pattern.<br>' . 
					'<a href="any">Step to next..</a>' . PHP_EOL;
		},
				
		Router::regex('ANY', 'any...') => function() {
			echo 	'You called or post some data to the "/any" url. ' . 
					'It will match for "/any" or "/anyone" or match with "/anything" ' . 
					'or "/any/more/parts..." etc. due there is three dot "..." at ' . 
					'the end of pattern. <a href="user/123/john-doe">Okey, let\'s see more examples..</a>'  . PHP_EOL;
		},
		
		// you can use :num :str and :any regex helpers
				
		Router::regex('GET', 'user/(:num)/(:any)') => function($base, $args) {
			echo 	'You called the "' . $args[0] . '" url ' . 
					'and you get an $args argument couse used the (:num) and ' . 
					'(:any) in parenthesis:' . PHP_EOL;
			var_dump($args);
			echo 'next, you can <a href="' . $base . 'function">call a custom function</a> or <a href="' . $base . 'method">call a custom method..</a>' . PHP_EOL;
		},
		
		// you can call custom functions or class methods
		
		Router::regex('GET', 'function/') => 'your_custom_function',
		
		Router::regex('GET', 'method/') => 'YourCustomController::doSomething',
		
		// be careful you have to add the full namespace to class path if your classes are in nemaspace
		Router::regex('GET', 'dosomething/') => 'Your\\SpecNamespace\\YourCustomController::doSomething',
		
		// OR if it's not enought fou can use custom regex in url pattern
		
		'GET:/^date\/([0-9]{4})-([0-9]{2})-([0-9]{2})$/' => function($base, $args) {
			echo 'You called the "/date" url. Argument was: ' . PHP_EOL;
			var_dump($args);
			
			echo '<a href="' . $base . '">go back to main page</a>' . PHP_EOL;
		},
		
		Router::regex('ANY', '$') => function() {
			echo 'You called the default page on root "/" url. <a href="get">Let\'s see the tests..</a>' . PHP_EOL;
		},
		
	], $base);  
}
catch(RouterException $e) {
	header("HTTP/1.0 404 Not Found");
	echo 'Sorry, you calles the /' . $e->getBase() . ' but here is nothingh to see..' . 
			'<a href="javascript:history.go(-1);">step back</a>' . PHP_EOL;
}


// custom callback function for routing
function your_custom_function($base, $args) {
	echo	'you called a custom function on /' . $args[0] . ' url.<br>' . 
			'<a href="method">Call a custom method...</a><br>' . 
			'<a href="date/' . date('Y-m-d') . '">or step to current date..</a><br>' .
			'<a href="javascript:history.go(-1);">step back</a>' . PHP_EOL;
}


class YourCustomController {
	
	// custom callback method for routing
	public function doSomething($base, $args) {
		echo	'you called a custom method on /' . $args[0] . ' url.<br>' . 
				'<a href="function">Call a custom function...</a><br>' . 
				'<a href="date/' . date('Y-m-d') . '">or step to current date..</a><br>' .
				'<a href="javascript:history.go(-1);">step back</a>' . PHP_EOL;
	}
}

```

#### More explanation

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
// The action handlers can use the $base $route and 
// $matches arguments from Router.
// $base is your specified root, defult set '/'
// $route is a string contains the current pattern
// $matches is an array contains the current URL
// and if you use parenthesis in your regex then
// contains the important url arguments.
function action_handler($base, $matches, $route) {
	var_dump($route);
	var_dump($matches);
}



```
