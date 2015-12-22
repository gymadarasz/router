<?php

namespace gymadarasz\router;

class Router {

	public static function dispatch($routes = [], $base = '/') {
		
		$uri = $_SERVER['REQUEST_URI'];

		$baselen = strlen($base);

		if(substr($uri, 0, $baselen) !== $base) {
			throw new \Exception ('URI base doesn\'t match for route, debug info: ' . $base . ' =/=> ' . $uri . ', set up a valid base!');
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
			if(in_array($_SERVER['REQUEST_METHOD'], $methods) && preg_match($regex, $uri, $matches)) {
				$found = true;
				if(is_string($action)) {
					$action($route, $matches);
				}
				if(is_array($action)) {
					Router::dispatch($action);
				}
				else {
					throw new \Exception ('Illegal action');
				}
				break;
			}
		}

		if(!$found) {
			throw new \Exception ('Not found action handler for ' . $uri . ' URI');
		}
	}
	
	public static function regex($method, $pattern) {
		
		$regex = $method . ':/^' . str_replace(['/', ':num', ':any'], ['\\/', '\\d+', '\\w+'], $pattern) . '/';
		
		return $regex;
	}
	
}