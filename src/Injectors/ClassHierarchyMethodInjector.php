<?php

namespace Aztech\Phinject\Injectors;

use Aztech\Phinject\Container;
use Aztech\Phinject\Injector;
use Aztech\Phinject\Util\ArrayResolver;
use Aztech\Phinject\Util\MethodNameParser;

class ClassHierarchyMethodInjector implements Injector
{

    private $methodNameParser;

    private $methodInvoker;

    public function __construct()
    {
        $this->methodNameParser = new MethodNameParser();
        $this->methodInvoker = new MethodInvoker();
    }

    function inject(Container $container, ArrayResolver $serviceConfig, $service)
    {
        $config = $container->getGlobalConfig();

        foreach ($config->resolveArray('global.injections', []) as $baseClassName => $calls)
        {
            if (! $this->isInjectionApplicableFor($service, $baseClassName)) {
                continue;
            }

            $this->applyInjection($container, $calls, $service);
        }

        return true;
    }

    private function applyInjection(Container $container, $calls, $service)
    {
        foreach ($calls as $methodName => $parameters) {
            $parameters = $this->wrapParametersIfNecessary($parameters);
            $methodInvocation = $this->methodNameParser->parseInvocation($service, $methodName, $parameters);
            $this->methodInvoker->invoke($container, $service, $methodInvocation);
        }
    }


    private function isInjectionApplicableFor($service, $baseClassName)
    {
        return $service instanceof $baseClassName;
    }

    private function wrapParametersIfNecessary($parameters)
    {
        if (! $parameters instanceof ArrayResolver) {
            $parameters = new ArrayResolver([ $parameters ]);
        }

        return $parameters;
    }
}
