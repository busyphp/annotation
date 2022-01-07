<?php

namespace BusyPHP\annotation\model\relation;

use Doctrine\Common\Annotations\Annotation;

/**
 * MorphToMany
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/7 2:26 PM MorphToMany.php $
 * @Annotation
 * @Annotation\Target({"CLASS"})
 */
final class MorphToMany extends Annotation
{
    /**
     * @var string
     * @Annotation\Required
     */
    public $model;
    
    /**
     * @var string
     * @Annotation\Required
     */
    public $middle;
    
    /**
     * @var string|array
     */
    public $morph = null;
    
    /**
     * @var string
     */
    public $localKey = null;
}
