<?php

namespace BusyPHP\annotation;

use BusyPHP\App;
use BusyPHP\Model;
use BusyPHP\model\ArrayOption;
use BusyPHP\model\Entity;
use BusyPHP\model\Field;
use BusyPHP\model\ObjectOption;
use PhpDocReader\AnnotationException;
use PhpDocReader\PhpDocReader;
use ReflectionObject;

/**
 * Trait InteractsWithInject
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/7 2:18 PM InteractsWithInject.php $
 * @property App $app
 */
trait InteractsWithInject
{
    /**
     * @var PhpDocReader
     */
    protected $injectDocReader = null;
    
    
    /**
     * @return PhpDocReader
     */
    protected function getInjectDocReader() : PhpDocReader
    {
        if (empty($this->injectDocReader)) {
            $this->injectDocReader = new PhpDocReader();
        }
        
        return $this->injectDocReader;
    }
    
    
    /**
     * @return App
     */
    protected function getInjectApp() : App
    {
        if (empty($this->app)) {
            $this->app = App::getInstance();
        }
        
        return $this->app;
    }
    
    
    /**
     * 启动属性注入注解
     * @return void
     */
    protected function bootUpAutoInject()
    {
        if ($this->getInjectApp()->config->get('annotation.inject.enable', true)) {
            $this->getInjectApp()->resolving(function($object) {
                if (!$this->isInjectClass(get_class($object))) {
                    return;
                }
                
                $this->executeInject($object);
            });
        }
        
        \think\Model::maker(function($object) {
            $this->executeInject($object);
        });
        
        Model::maker(function($object) {
            $this->executeInject($object);
        });
        
        Field::maker(function($object) {
            $this->executeInject($object);
        });
        
        Entity::maker(function($object) {
            $this->executeInject($object);
        });
        
        ArrayOption::maker(function($object) {
            $this->executeInject($object);
        });
        
        ObjectOption::maker(function($object) {
            $this->executeInject($object);
        });
    }
    
    
    /**
     * 执行属性注入注解
     * @param $object
     * @throws AnnotationException
     */
    protected function executeInject($object)
    {
        $reader    = $this->getInjectDocReader();
        $refObject = new ReflectionObject($object);
        
        foreach ($refObject->getProperties() as $refProperty) {
            if ($refProperty->isDefault() && !$refProperty->isStatic()) {
                $annotation = $this->reader->getPropertyAnnotation($refProperty, Inject::class);
                if ($annotation) {
                    if ($annotation->value) {
                        $value = $this->getInjectApp()->make($annotation->value);
                    } else {
                        //获取@var类名
                        $propertyClass = $reader->getPropertyClass($refProperty);
                        if ($propertyClass) {
                            $value = $this->getInjectApp()->make($propertyClass);
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
            $this->getInjectApp()->invokeMethod([$object, '__injected']);
        }
    }
    
    
    /**
     * @param $name
     * @return bool
     */
    protected function isInjectClass($name) : bool
    {
        $namespaces = array_merge([
            'app\\',
            'core\\',
        ], $this->app->config->get('annotation.inject.namespaces', []));
        
        foreach ($namespaces as $namespace) {
            $namespace = rtrim($namespace, '\\') . '\\';
            
            if (0 === stripos(rtrim($name, '\\') . '\\', $namespace)) {
                return true;
            }
        }
        
        return false;
    }
}
