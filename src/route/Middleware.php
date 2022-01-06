<?php

namespace BusyPHP\annotation\route;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * 路由中间件
 * @package BusyPHP\annotation\route
 * @Annotation
 * @Target({"CLASS","METHOD"})
 */
final class Middleware extends Annotation
{

}
