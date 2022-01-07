<?php

namespace BusyPHP\annotation\model\relation;

use Doctrine\Common\Annotations\Annotation;

/**
 * HasOneThrough
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/7 2:25 PM HasOneThrough.php $
 * @Annotation
 * @Annotation\Target({"CLASS"})
 */
final class HasOneThrough extends Annotation
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
    public $through;
    
    /**
     * @var string
     */
    public $foreignKey = '';
    
    /**
     * @var string
     */
    public $throughKey = '';
    
    /**
     * @var string
     */
    public $localKey = '';
    
    /**
     * @var string
     */
    public $throughPk = '';
}
