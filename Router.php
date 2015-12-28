<?php

namespace gymadarasz\router;

use Exception;

class RouterException extends Exception {}

class Router {
	
	public static function dispatch($routes = [], $base = '/', $uri = null, $method = null) {
		
		$result = null;
		
		if(is_null($uri)) {
			$uri = $_SERVER['REQUEST_URI'];
		}
		
		if(is_null($method)) {
			$method = $_SERVER['REQUEST_METHOD'];
		}
		
		$baselen = strlen($base);

		if(substr($uri, 0, $baselen) !== $base) {
			throw new RouterException ('URI base doesn\'t match for route, debug info: ' . $base . ' =/=> ' . $uri . ', set up a valid base!');
		}

		$uri = substr($uri, $baselen);

		$found = false;
		foreach($routes as $route => $action) {
			$splits = explode(':', $route);
			$splits[0] = strtoupper($splits[0]);
			if($splits[0]=='ANY') {
				$splits[0] = 'GET,POST';
			}
			$methods = explode(',', $splits[0]);
			$regex = $splits[1];
			if(in_array($method, $methods) && preg_match($regex, $uri, $matches)) {
				$found = true;
				if(is_string($action) && is_callable($action)) {
					$result = call_user_func_array($action, [$route, $matches]);
				}
				else if(is_string($action)) {
					$result = $action($route, $matches);
				}
				else if(is_callable($action)) {
					$result = $action($route, $matches);
				}
				else if(is_array($action)) {
					$result = Router::dispatch($action, $base);
				}
				else {
					throw new RouterException ('Illegal action');
				}
				break;
			}
		}

		if(!$found) {
			throw new RouterException ('Not found action handler for ' . $uri . ' URI');
		}
		
		return $result;
	}
	
	public static function regex($method, $pattern) {
		
		$regex = preg_replace(['/\/$/', '/\.\.\.$/'], ['[/]?', ''], $pattern);
		$regex = $method . ':/^' . str_replace(['/', ':num', ':any'], ['\\/', '\\d+', '\\w+'], $regex) . '/';
		
		return $regex;
	}
	
}
