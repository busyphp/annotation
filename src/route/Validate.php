<?php

namespace BusyPHP\annotation\route;

use Doctrine\Common\Annotations\Annotation;

/**
 * Validate
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/7 2:21 PM Validate.php $
 * @Annotation
 * @Annotation\Target({"METHOD"})
 */
final class Validate extends Annotation
{
    /**
     * @var string
     */
    public $scene;
    
    /**
     * @var array
     */
    public $message = [];
    
    /**
     * @var bool
     */
    public $batch = true;
}
