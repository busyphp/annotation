<?php

namespace BusyPHP\annotation\model\relation;

use Doctrine\Common\Annotations\Annotation;

/**
 * MorphTo
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/7 2:26 PM MorphTo.php $
 * @Annotation
 * @Annotation\Target({"CLASS"})
 */
final class MorphTo extends Annotation
{
    /**
     * @var string|array
     */
    public $morph = null;
    
    /**
     * @var array
     */
    public $alias = [];
}
