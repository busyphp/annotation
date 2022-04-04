<?php

namespace BusyPHP\annotation\model\field;

use BusyPHP\Model;
use Doctrine\Common\Annotations\Annotation;

/**
 * 绑定模型数据集至属性(一对多)
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/4/3 6:55 PM BindList.php $
 * @Annotation
 * @Annotation\Target({Annotation\Target::TARGET_PROPERTY})
 */
final class BindList extends Annotation
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
    
    /**
     * 字符串分隔符
     * @var string
     */
    public $split = '';
}