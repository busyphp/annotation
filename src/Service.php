<?php

namespace BusyPHP\annotation;

use BusyPHP\annotation\interacts\InteractsWithInject;
use BusyPHP\annotation\interacts\InteractsWithModel;
use BusyPHP\annotation\interacts\InteractsWithRoute;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\Reader;
use think\App;
use think\Cache;
use think\Config;

/**
 * 注解服务类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/7 2:08 PM Service.php $
 */
class Service extends \think\Service
{
    use InteractsWithRoute, InteractsWithInject, InteractsWithModel;
    
    /** @var Reader */
    protected $reader;
    
    
    public function register()
    {
        AnnotationReader::addGlobalIgnoredName('mixin');
        
        // TODO: this method is deprecated and will be removed in doctrine/annotations 2.0
        AnnotationRegistry::registerLoader('class_exists');
        
        $this->app->bind(Reader::class, function(App $app, Config $config, Cache $cache) {
            $store = $config->get('busy-annotation.store');
            
            return new CachedReader(new AnnotationReader(), $cache->store($store), $app->isDebug());
        });
    }
    
    
    public function boot(Reader $reader)
    {
        $this->reader = $reader;
        
        // 注解路由
        $this->registerAnnotationRoute();
        
        // 自动注入
        $this->bootUpAutoInject();
        
        // 模型注解
        $this->detectModelAnnotations();
    }
}
