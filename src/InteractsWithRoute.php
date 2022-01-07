<?php

namespace BusyPHP\annotation;

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\ClassLoader\ClassMapGenerator;
use BusyPHP\annotation\route\Group;
use BusyPHP\annotation\route\Middleware;
use BusyPHP\annotation\route\Model;
use BusyPHP\annotation\route\Resource;
use BusyPHP\annotation\route\Validate;
use think\App;
use think\event\RouteLoaded;

/**
 * Trait InteractsWithRoute
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/7 2:11 PM InteractsWithRoute.php $
 * @property App    $app
 * @property Reader $reader
 */
trait InteractsWithRoute
{
    /**
     * @var \think\Route
     */
    protected $route;
    
    
    /**
     * 注册注解路由
     */
    protected function registerAnnotationRoute()
    {
        if ($this->app->config->get('annotation.route.enable', true)) {
            $this->app->event->listen(RouteLoaded::class, function() {
                $this->route = $this->app->route;
                
                $dirs = [$this->app->getAppPath() . $this->app->config->get('route.controller_layer')] + $this->app->config->get('annotation.route.controllers', []);
                
                foreach ($dirs as $dir) {
                    if (is_dir($dir)) {
                        $this->scanDir($dir);
                    }
                }
            });
        }
    }
    
    
    /**
     * @throws ReflectionException
     */
    protected function scanDir($dir)
    {
        foreach (ClassMapGenerator::createMap($dir) as $class => $path) {
            $refClass        = new ReflectionClass($class);
            $routeGroup      = false;
            $routeMiddleware = [];
            $callback        = null;
            
            //类
            if ($resource = $this->reader->getClassAnnotation($refClass, Resource::class)) {
                //资源路由
                $callback = function() use ($class, $resource) {
                    $this->route->resource($resource->value, $class)->option($resource->getOptions());
                };
            }
            
            if ($middleware = $this->reader->getClassAnnotation($refClass, Middleware::class)) {
                $routeGroup      = '';
                $routeMiddleware = $middleware->value;
            }
            
            if ($group = $this->reader->getClassAnnotation($refClass, Group::class)) {
                $routeGroup = $group->value;
            }
            
            if (false !== $routeGroup) {
                $routeGroup = $this->route->group($routeGroup, $callback);
                if ($group) {
                    $routeGroup->option($group->getOptions());
                }
                
                $routeGroup->middleware($routeMiddleware);
            } else {
                if ($callback) {
                    $callback();
                }
                $routeGroup = $this->route->getGroup();
            }
            
            //方法
            foreach ($refClass->getMethods(ReflectionMethod::IS_PUBLIC) as $refMethod) {
                if ($route = $this->reader->getMethodAnnotation($refMethod, Route::class)) {
                    //注册路由
                    $rule = $routeGroup->addRule($route->value, "$class@{$refMethod->getName()}", $route->method);
                    
                    $rule->option($route->getOptions());
                    
                    //中间件
                    if ($middleware = $this->reader->getMethodAnnotation($refMethod, Middleware::class)) {
                        $rule->middleware($middleware->value);
                    }
                    //设置分组别名
                    if ($group = $this->reader->getMethodAnnotation($refMethod, Group::class)) {
                        $rule->group($group->value);
                    }
                    
                    //绑定模型,支持多个
                    if (!empty($models = $this->getMethodAnnotations($refMethod, Model::class))) {
                        /** @var Model $model */
                        foreach ($models as $model) {
                            $rule->model($model->var, $model->value, $model->exception);
                        }
                    }
                    
                    //验证
                    if ($validate = $this->reader->getMethodAnnotation($refMethod, Validate::class)) {
                        $rule->validate($validate->value, $validate->scene, $validate->message, $validate->batch);
                    }
                }
            }
        }
    }
    
    
    protected function getMethodAnnotations(ReflectionMethod $method, $annotationName) : array
    {
        $annotations = $this->reader->getMethodAnnotations($method);
        
        return array_filter($annotations, function($annotation) use ($annotationName) {
            return $annotation instanceof $annotationName;
        });
    }
}
