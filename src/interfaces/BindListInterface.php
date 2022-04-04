<?php

namespace BusyPHP\annotation\interfaces;

use BusyPHP\Model;

/**
 * BindListInterface
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/4/4 11:41 AM BindListInterface.php $
 */
interface BindListInterface
{
    /**
     * 绑定模型数据集对象到属性条件处理
     * @param Model  $parentModel 父模型
     * @param array  $parentList 父数据
     * @param string $foreignKey 当前模型外键
     * @param string $splitKey 切割的键名
     * @param string $localKey 当前模型主键
     * @param bool   $extend 是否查询扩展数据
     * @return false|void 返回false阻止默认查询条件
     */
    public function onBindListCondition(Model $parentModel, array $parentList, string $foreignKey, string $splitKey, string $localKey, bool $extend);
}