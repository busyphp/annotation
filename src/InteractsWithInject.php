<?php

namespace BusyPHP\annotation;

use Doctrine\Common\Annotations\Reader;
use PhpDocReader\PhpDocReader;
use ReflectionObject;
use think\App;

/**
 * Trait InteractsWithInject
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/7 2:18 PM InteractsWithInject.php $
 * @property App    $app
 * @property Reader $reader
 */
trait InteractsWithInject
{
    protected $docReader = null;
    
    
    protected function getDocReader() : PhpDocReader
    {
        if (empty($this->docReader)) {
            $this->docReader = new PhpDocReader();
        }
        
        return $this->docReader;
    }
    
    
    protected function autoInject()
    {
        if ($this->app->config->get('annotation.inject.enable', true)) {
            $this->app->resolving(function($object, $app) {
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
    
    
    /**
     * @param $name
     * @return bool
     */
    protected function isInjectClass($name) : bool
    {
        $namespaces = ['app\\'] + $this->app->config->get('annotation.inject.namespaces', []);
        
        foreach ($namespaces as $namespace) {
            $namespace = rtrim($namespace, '\\') . '\\';
            
            if (0 === stripos(rtrim($name, '\\') . '\\', $namespace)) {
                return true;
            }
        }
        
        return false;
    }
}
