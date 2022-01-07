<?php

namespace BusyPHP\annotation\model\relation;

use Doctrine\Common\Annotations\Annotation;

/**
 * BelongsToMany
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/7 2:24 PM BelongsToMany.php $
 * @Annotation
 * @Annotation\Target({"CLASS"})
 */
final class BelongsToMany extends Annotation
{
    /**
     * @var string
     * @Annotation\Required
     */
    public $model;
    
    /**
     * @var string
     */
    public $middle = '';
    
    /**
     * @var string
     */
    public $foreignKey = '';
    
    /**
     * @var string
     */
    public $localKey = '';
}
