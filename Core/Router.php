<?php

//                  REGEX EXPRESSION HELP GUIDE

//        .                   means match any single charager, number, whitespace
//        .*                  means any number of any characters even 0 times
//        +                   means - look for the character 1 or more times
//        *                   means - look for the character 0 or more times
//        [^123]              Negate the character - meaning looking for any digit BUT 123

// The first part
//        /                   start of regular expression
//        /\{                 match a curly brace (the curly brace is escaped with a backslash)
//        /\{(                start of a "capture group" which we'll use later
//        /\{([a-z]+          match a string of at least one character from a-z
//        /\{([a-z]+)         end of the capture group
//        /\{([a-z]+)\}       match a closing curly brace (again escaped with a backslash)
//        /\{([a-z]+)\}/      end of the regular expression

// The second part
//        (?P<                start a named capture group
//        (?P<\1              add the value of the first capture group from the other regular expression
//        (?P<\1>             end the named capture group
//        (?P<\1>[a-z-]+      add a regular expression for the named capture group
//        (?P<\1>[a-z-]+)     end the capture group

namespace Core;

class Router
{

    protected $routes = [];
    protected $params = [];

    public function add($route, $params = [])
    {

        // Convert the route to a regular expression: escape forward slahes
        // Replaces / with \/
        $route = preg_replace('/\//', '\\/', $route);
        // '{controller}\\/{action}' (length=22)

        //Convert variables e.g {controller}
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z-]+)', $route);
        // '(?P<controller>[a-z-]+)\/(?P<action>[a-z-]+)'

        //Convert variables e.g {category}
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);

        // Add start and end delimiters, and case insensitive flag
        $route = '/^' . $route . '$/i';
        // '/^(?P<controller>[a-z-]+)\/(?P<action>[a-z-]+)$/i'

        // Creates the array object with the key as a regular expression
        $this->routes[$route] = $params;


    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function dispatch($url)
    {

        // Remove the query string from the URL
        $url = $this->removeQueryStringVariables($url);

        // If the current URL matches any of the routes in the routing table
        if ($this->match($url)) {

            $controller = $this->params['controller'];
            // Convert contact -> Contact
            $controller = $this->convertToStudlyCaps($controller);
            $controller = $this->getNamespace() . $controller;

            if (class_exists($controller)) {
                $controller_object = new $controller($this->params);

                $action = $this->params['action'];
                $action = $this->convertToCamelCase($action);

                if (preg_match('/action$/i', $action) == 0) {
                    // Call the method in the class
                    $controller_object->$action();
                } else {
                    throw new \Exception("Method $action (in controller $controller) not found");
                }

            } else {
                throw new \Exception("Controller class $controller not found");
            }
        } else {
            throw new \Exception("No route matched", 404);
        }
    }

    /*
    * Breadcrumb: Router/add -> Router/dispatch -> (Router/removeQueryStringVariables)
    * Action -> Remove the ?query= query string from the end of the URL
    */
    protected function removeQueryStringVariables($url)
    {
        if ($url != '') {
            $parts = explode('&', $url, 2);

            if (strpos($parts[0], '=') === false) {
              // If the first value of the array does not contain the query string example - ?id=1
                $url = $parts[0];
            } else {
                $url = '';
            }

            return $url;
        }
    }

    public function match($url)
    {

      // Check the URL against the regulat expressions in the routing table
        foreach ($this->routes as $route => $params) {

            if (preg_match($route, $url, $matches)) {
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = $match;
                        // 'controller' => 'url/'
                    }
                }

                $this->params = $params;

                return true;

            }
        }

        return false;

    }

    public function getParams()
    {
        return $this->params;
    }

    // ucwords — Uppercase the first character of each word in a string
    public function convertToStudlyCaps($string)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    // lcfirst — Make a string's first character lowercase
    public function convertToCamelCase($string)
    {
        return lcfirst($this->convertToStudlyCaps($string));
    }

    protected function getNamespace()
    {

        $namespace = 'App\Controllers\\';

        if (array_key_exists('namespace', $this->params)) {
            $namespace .= $this->params['namespace'] . '\\';
        }

        return $namespace;

    }

}
