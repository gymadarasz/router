<?php

/*
 * The MIT License
 *
 * Copyright 2015 Gyula Madarasz <gyula.madarasz at gmail.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace gymadarasz\router;

class Router {
	
	public static function dispatch($routes = [], $base = '/', $uri = null, $method = null) {
		
		$result = null;
		
		if(!$base || substr($base, 0, 1) != '/') {
			$base = '/' . $base;
		}
		
		if(substr($base, -1) != '/') {
			$base .= '/';
		}
		
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
					$result = call_user_func_array($action, [$base, $matches, $route]);
				}
				else if(is_string($action)) {
					$result = $action($base, $matches, $route);
				}
				else if(is_callable($action)) {
					$result = $action($base, $matches, $route);
				}
				else if(is_array($action)) {
					$result = Router::dispatch($base, $matches, $route);
				}
				else {
					throw new RouterException ('Illegal action', 1, null, $base);
				}
				break;
			}
		}

		if(!$found) {
			throw new RouterException ('Not found action handler for ' . $uri . ' URI', 2, null, $base);
		}
		
		return $result;
	}
	
	public static function regex($method, $pattern) {
		
		$regex = preg_replace(['/\/$/', '/\.\.\.$/'], ['[/]?', ''], $pattern);
		$regex = $method . ':/^' . str_replace(['/', ':num', ':str', ':any'], ['\\/', '\\d+', '\\w+', '.+'], $regex) . '/';
		
		return $regex;
	}
	
}
