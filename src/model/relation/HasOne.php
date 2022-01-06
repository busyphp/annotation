<?php

namespace BusyPHP\annotation\model\relation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Annotation\Target({"CLASS"})
 */
final class HasOne extends Annotation
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
