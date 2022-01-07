<?php

namespace BusyPHP\annotation\model\relation;

use Doctrine\Common\Annotations\Annotation;

/**
 * MorphMany
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/7 2:25 PM MorphMany.php $
 * @Annotation
 * @Annotation\Target({"CLASS"})
 */
final class MorphMany extends Annotation
{
    /**
     * @var string
     * @Annotation\Required
     */
    public $model;
    
    /**
     * @var string|array
     */
    public $morph = null;
    
    /**
     * @var string
     */
    public $type = '';
}
