<?php

namespace BusyPHP\annotation\route;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Annotation\Target;

/**
 * 注入模型
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/7 2:20 PM Model.php $
 * @Annotation
 * @Target({"METHOD"})
 */
final class Model extends Annotation
{
    /**
     * @var string
     */
    public $var = 'id';
    
    /**
     * @var boolean
     */
    public $exception = true;
}
