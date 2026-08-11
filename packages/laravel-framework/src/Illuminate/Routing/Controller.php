<?php

namespace Illuminate\Routing;

use BadMethodCallException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract class Controller
{
    /**
     * The middleware registered on the controller.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * Register middleware on the controller.
     *
     * @param  array|string|\Closure  $middleware
     * @param  array   $options
     * @return \Illuminate\Routing\ControllerMiddlewareOptions
     */
    public function middleware($middleware, array $options = [])
    {
        foreach ((array) $middleware as $m) {
            $this->middleware[] = [
                'middleware' => $m,
                'options' => &$options,
            ];
        }

        return new ControllerMiddlewareOptions($options);
    }

    /**
     * Get the middleware assigned to the controller.
     *
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * Execute an action on the controller.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function callAction($method, $parameters)
    {
        // PHP 8+: call_user_func_array treats string keys as named arguments.
        // Build a positional list that matches the action signature, dropping
        // unused route params and mapping {lang} → $posixLocale style mismatches.
        return call_user_func_array(
            [$this, $method],
            $this->filterActionParameters($method, $parameters)
        );
    }

    /**
     * Build positional arguments for an action from route/DI parameters.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return array
     */
    protected function filterActionParameters($method, array $parameters)
    {
        if (! method_exists($this, $method)) {
            return array_values($parameters);
        }

        $args = [];

        foreach ((new \ReflectionMethod($this, $method))->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($name, $parameters)) {
                $args[] = $parameters[$name];
                unset($parameters[$name]);
                continue;
            }

            $className = null;
            if ($parameter->hasType()) {
                $type = $parameter->getType();
                if ($type instanceof \ReflectionNamedType && ! $type->isBuiltin()) {
                    $className = $type->getName();
                }
            } elseif (method_exists($parameter, 'getClass')) {
                $class = \App\Support\Php83\Reflection::classOf($parameter);
                $className = $class ? $class->getName() : null;
            }

            if ($className) {
                foreach ($parameters as $key => $value) {
                    if ($value instanceof $className) {
                        $args[] = $value;
                        unset($parameters[$key]);
                        continue 2;
                    }
                }
            }

            if (! empty($parameters)) {
                $args[] = array_shift($parameters);
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue();
            }
        }

        return $args;
    }

    /**
     * Handle calls to missing methods on the controller.
     *
     * @param  array   $parameters
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function missingMethod($parameters = [])
    {
        throw new NotFoundHttpException('Controller method not found.');
    }

    /**
     * Handle calls to missing methods on the controller.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        throw new BadMethodCallException("Method [{$method}] does not exist.");
    }
}
