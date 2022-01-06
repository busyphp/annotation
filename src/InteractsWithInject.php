<?php

namespace BusyPHP\annotation;

use Doctrine\Common\Annotations\Reader;
use PhpDocReader\PhpDocReader;
use ReflectionObject;
use think\App;

/**
 * Trait InteractsWithInject
 * @package BusyPHP\annotation\traits
 * @property App $app
 * @property Reader $reader
 */
trait InteractsWithInject
{
    protected $docReader = null;

    protected function getDocReader()
    {
        if (empty($this->docReader)) {
            $this->docReader = new PhpDocReader();
        }

        return $this->docReader;
    }

    protected function autoInject()
    {
        if ($this->app->config->get('annotation.inject.enable', true)) {
            $this->app->resolving(function ($object, $app) {

                if ($this->isInjectClass(get_class($object))) {

                    $reader = $this->getDocReader();

                    $refObject = new ReflectionObject($object);

                    foreach ($refObject->getProperties() as $refProperty) {
                        if ($refProperty->isDefault() && !$refProperty->isStatic()) {
                            $annotation = $this->reader->getPropertyAnnotation($refProperty, Inject::class);
                            if ($annotation) {
                                if ($annotation->value) {
                                    $value = $app->make($annotation->value);
                                } else {
                                    //获取@var类名
                                    $propertyClass = $reader->getPropertyClass($refProperty);
                                    if ($propertyClass) {
                                        $value = $app->make($propertyClass);
                                    }
                                }

                                if (!empty($value)) {
                                    if (!$refProperty->isPublic()) {
                                        $refProperty->setAccessible(true);
                                    }
                                    $refProperty->setValue($object, $value);
                                }
                            }
                        }
                    }

                    if ($refObject->hasMethod('__injected')) {
                        $app->invokeMethod([$object, '__injected']);
                    }
                }
            });
        }
    }

    protected function isInjectClass($name)
    {
        $namespaces = ['app\\'] + $this->app->config->get('annotation.inject.namespaces', []);

        foreach ($namespaces as $namespace) {
            $namespace = rtrim($namespace, '\\') . '\\';

            if (0 === stripos(rtrim($name, '\\') . '\\', $namespace)) {
                return true;
            }
        }
    }
}
