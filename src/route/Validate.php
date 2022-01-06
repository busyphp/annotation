<?php

namespace BusyPHP\annotation\route;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class Validate
 * @package BusyPHP\annotation\route
 * @Annotation
 * @Annotation\Target({"METHOD"})
 */
final class Validate extends Annotation
{
    /**
     * @var string
     */
    public $scene;

    /**
     * @var array
     */
    public $message = [];

    /**
     * @var bool
     */
    public $batch = true;
}
