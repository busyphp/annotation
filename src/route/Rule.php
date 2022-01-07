<?php

namespace BusyPHP\annotation\route;

use Doctrine\Common\Annotations\Annotation;

/**
 * Rule
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/7 2:21 PM Rule.php $
 */
abstract class Rule extends Annotation
{
    /**
     * @var string|array
     */
    public $middleware;
    
    /**
     * 后缀
     * @var string
     */
    public $ext;
    
    /**
     * @var string
     */
    public $deny_ext;
    
    /**
     * @var bool
     */
    public $https;
    
    /**
     * @var string
     */
    public $domain;
    
    /**
     * @var bool
     */
    public $complete_match;
    
    /**
     * @var string|array
     */
    public $cache;
    
    /**
     * @var bool
     */
    public $ajax;
    
    /**
     * @var bool
     */
    public $pjax;
    
    /**
     * @var bool
     */
    public $json;
    
    /**
     * @var array
     */
    public $filter;
    
    /**
     * @var array
     */
    public $append;
    
    
    public function getOptions() : array
    {
        return array_intersect_key(
            get_object_vars($this), array_flip([
                'middleware',
                'ext',
                'deny_ext',
                'https',
                'domain',
                'complete_match',
                'cache',
                'ajax',
                'pjax',
                'json',
                'filter',
                'append',
            ])
        );
    }
}
