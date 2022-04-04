<?php

namespace BusyPHP\annotation\model\field;

use BusyPHP\Model;
use Doctrine\Common\Annotations\Annotation;

/**
 * 绑定模型信息至属性(一对一)
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/4/2 8:53 PM Test.php $
 * @Annotation
 * @Annotation\Target({Annotation\Target::TARGET_PROPERTY})
 */
final class BindInfo extends Annotation
{
    /**
     * 绑定的模型类名
     * @var class-string<Model>
     * @Annotation\Required
     */
    public $model;
    
    /**
     * 模型外键
     * @var string
     */
    public $foreignKey = '';
    
    /**
     * 当前模型主键
     * @var string
     */
    public $localKey = '';
    
    /**
     * 是否查询扩展数据
     * @var bool
     */
    public $extend = false;
}