<?php

namespace BusyPHP\annotation\model\relation;

use Doctrine\Common\Annotations\Annotation;

/**
 * BelongsTo
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/7 2:24 PM BelongsTo.php $
 * @Annotation
 * @Annotation\Target({"CLASS"})
 */
final class BelongsTo extends Annotation
{
    /**
     * @var string
     * @Annotation\Required
     */
    public $model;
    
    /**
     * @var string
     */
    public $foreignKey = '';
    
    /**
     * @var string
     */
    public $localKey = '';
}
