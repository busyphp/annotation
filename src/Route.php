<?php

namespace BusyPHP\annotation;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Enum;
use Doctrine\Common\Annotations\Annotation\Target;
use BusyPHP\annotation\route\Rule;

/**
 * 注册路由
 * @package topBusyPHP\annotation
 * @Annotation
 * @Target({"METHOD","CLASS"})
 */
final class Route extends Rule
{
    /**
     * 请求类型
     * @Enum({"GET","POST","PUT","DELETE","PATCH","OPTIONS","HEAD"})
     * @var string
     */
    public $method = "*";

}
