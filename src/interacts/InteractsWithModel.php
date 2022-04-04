<?php

namespace BusyPHP\annotation\interacts;

use BusyPHP\annotation\interfaces\BindInfoInterface;
use BusyPHP\annotation\interfaces\BindListInterface;
use BusyPHP\annotation\model\field\BindInfo;
use BusyPHP\annotation\model\field\BindList;
use BusyPHP\annotation\model\relation\BelongsTo;
use BusyPHP\annotation\model\relation\BelongsToMany;
use BusyPHP\annotation\model\relation\HasMany;
use BusyPHP\annotation\model\relation\HasManyThrough;
use BusyPHP\annotation\model\relation\HasOne;
use BusyPHP\annotation\model\relation\HasOneThrough;
use BusyPHP\annotation\model\relation\MorphByMany;
use BusyPHP\annotation\model\relation\MorphMany;
use BusyPHP\annotation\model\relation\MorphOne;
use BusyPHP\annotation\model\relation\MorphTo;
use BusyPHP\annotation\model\relation\MorphToMany;
use BusyPHP\helper\ArrayHelper;
use BusyPHP\helper\StringHelper;
use BusyPHP\Model as BusyModel;
use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use think\App;
use think\helper\Str;
use think\ide\ModelGenerator;
use think\Model;
use think\model\Collection;

/**
 * Trait InteractsWithModel
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/7 2:15 PM InteractsWithModel.php $
 * @property App    $app
 * @property Reader $reader
 * @mixin Model
 */
trait InteractsWithModel
{
    protected $detected = [];
    
    
    protected function detectModelAnnotations()
    {
        if ($this->app->config->get('busy-annotation.model.enable', true)) {
            BusyModel::bindParseClassHandle(function(BusyModel $parentModel, string $class, array $list) {
                $reflectionClass = new ReflectionClass($class);
                foreach ($reflectionClass->getProperties() as $property) {
                    $annotations = $this->reader->getPropertyAnnotations($property);
                    $attr        = $property->getName();
                    foreach ($annotations as $annotation) {
                        switch (true) {
                            case $annotation instanceof BindInfo:  // 一对一
                            case $annotation instanceof BindList:  // 一对多
                                $target = $annotation->model ?: $annotation->value;
                                $model  = new $target;
                                if (!is_subclass_of($model, BusyModel::class)) {
                                    break;
                                }
                                
                                $foreignKey = $annotation->foreignKey;
                                $localKey   = $annotation->localKey;
                                if (!$annotation->foreignKey) {
                                    $foreignKey = $model->getTableWithoutPrefix() . '_id';
                                }
                                if (!$annotation->localKey) {
                                    $localKey = $model->getPk();
                                }
                                
                                // 一对一
                                $values = [];
                                if ($annotation instanceof BindInfo) {
                                    foreach ($list as $item) {
                                        if (!isset($item[$foreignKey])) {
                                            continue;
                                        }
                                        $values[] = $item[$foreignKey];
                                    }
                                    
                                    $result = true;
                                    if ($model instanceof BindInfoInterface) {
                                        $result = $model->onBindInfoCondition($parentModel, $list, $foreignKey, $localKey, $annotation->extend);
                                    }
                                    if (false !== $result) {
                                        $model->where($localKey, 'in', $values);
                                    }
                                    
                                    $queryList = $annotation->extend ? $model->selectExtendList() : $model->selectList();
                                    $queryList = ArrayHelper::listByKey($queryList, $localKey);
                                    foreach ($list as $i => $item) {
                                        $item[$attr] = $queryList[$item[$foreignKey] ?? null] ?? null;
                                        $list[$i]    = $item;
                                    }
                                }
                                
                                //
                                // 一对多
                                else {
                                    $splitForeignKey = '@@split#' . StringHelper::camel($foreignKey);
                                    foreach ($list as $item) {
                                        if (!isset($item[$foreignKey])) {
                                            continue;
                                        }
                                        
                                        $value = $item[$foreignKey];
                                        // 需要切割
                                        if ($annotation->split) {
                                            if (!is_array($value)) {
                                                $value = explode($annotation->split, trim((string) $value, $annotation->split));
                                            }
                                            $item[$splitForeignKey] = $value;
                                            $values                 = array_merge($values, $value);
                                        } else {
                                            $values[] = $value;
                                        }
                                    }
                                    
                                    $result = true;
                                    if ($model instanceof BindListInterface) {
                                        $result = $model->onBindListCondition($parentModel, $list, $foreignKey, $splitForeignKey, $localKey, $annotation->extend);
                                    }
                                    if ($result !== false) {
                                        $model->where($localKey, 'in', $values);
                                    }
                                    
                                    $queryList = [];
                                    foreach ($annotation->extend ? $model->selectExtendList() : $model->selectList() as $item) {
                                        if ($annotation->split) {
                                            $queryList[$item[$localKey]] = $item;
                                        } else {
                                            $queryList[$item[$localKey]][] = $item;
                                        }
                                    }
                                    foreach ($list as $i => $item) {
                                        if (isset($item[$splitForeignKey])) {
                                            $attrList = [];
                                            foreach ($item[$splitForeignKey] as $foreignValue) {
                                                if (isset($queryList[$foreignValue])) {
                                                    $attrList[] = $queryList[$foreignValue];
                                                }
                                            }
                                            if (is_object($item)) {
                                                unset($item->{$splitForeignKey});
                                            } else {
                                                unset($item[$splitForeignKey]);
                                            }
                                            $item[$attr] = $attrList;
                                        } else {
                                            $item[$attr] = $queryList[$item[$foreignKey]] ?? [];
                                        }
                                        
                                        $list[$i] = $item;
                                    }
                                }
                            break;
                        }
                    }
                }
                
                return $list;
            });
            
            
            Model::maker(function(Model $model) {
                $className = get_class($model);
                if (!isset($this->detected[$className])) {
                    $annotations = $this->reader->getClassAnnotations(new ReflectionClass($model));
                    
                    foreach ($annotations as $annotation) {
                        switch (true) {
                            case $annotation instanceof HasOne:
                                $relation = function() use ($annotation) {
                                    return $this->hasOne(
                                        $annotation->model,
                                        $annotation->foreignKey,
                                        $annotation->localKey
                                    );
                                };
                            break;
                            case $annotation instanceof BelongsTo:
                                $relation = function() use ($annotation) {
                                    return $this->belongsTo(
                                        $annotation->model,
                                        $annotation->foreignKey,
                                        $annotation->localKey
                                    );
                                };
                            break;
                            case $annotation instanceof HasMany:
                                $relation = function() use ($annotation) {
                                    return $this->hasMany(
                                        $annotation->model,
                                        $annotation->foreignKey,
                                        $annotation->localKey
                                    );
                                };
                            break;
                            case $annotation instanceof HasManyThrough:
                                $relation = function() use ($annotation) {
                                    return $this->hasManyThrough(
                                        $annotation->model,
                                        $annotation->through,
                                        $annotation->foreignKey,
                                        $annotation->throughKey,
                                        $annotation->localKey,
                                        $annotation->throughPk
                                    );
                                };
                            break;
                            case $annotation instanceof HasOneThrough:
                                $relation = function() use ($annotation) {
                                    return $this->hasOneThrough(
                                        $annotation->model,
                                        $annotation->through,
                                        $annotation->foreignKey,
                                        $annotation->throughKey,
                                        $annotation->localKey,
                                        $annotation->throughPk
                                    );
                                };
                            break;
                            case $annotation instanceof BelongsToMany:
                                $relation = function() use ($annotation) {
                                    return $this->belongsToMany(
                                        $annotation->model,
                                        $annotation->middle,
                                        $annotation->foreignKey,
                                        $annotation->localKey
                                    );
                                };
                            break;
                            case $annotation instanceof MorphOne:
                                $relation = function() use ($annotation) {
                                    return $this->morphOne(
                                        $annotation->model,
                                        $annotation->morph ?: $annotation->value,
                                        $annotation->type
                                    );
                                };
                            break;
                            case $annotation instanceof MorphMany:
                                $relation = function() use ($annotation) {
                                    return $this->morphMany(
                                        $annotation->model,
                                        $annotation->morph ?: $annotation->value,
                                        $annotation->type
                                    );
                                };
                            break;
                            case $annotation instanceof MorphTo:
                                $relation = function() use ($annotation) {
                                    return $this->morphTo($annotation->morph ?: $annotation->value, $annotation->alias);
                                };
                            break;
                            case $annotation instanceof MorphToMany:
                                $relation = function() use ($annotation) {
                                    return $this->morphToMany(
                                        $annotation->model,
                                        $annotation->middle,
                                        $annotation->morph,
                                        $annotation->localKey
                                    );
                                };
                            break;
                            case $annotation instanceof MorphByMany:
                                $relation = function() use ($annotation) {
                                    return $this->morphByMany(
                                        $annotation->model,
                                        $annotation->middle,
                                        $annotation->morph,
                                        $annotation->foreignKey
                                    );
                                };
                            break;
                        }
                        
                        if (!empty($relation)) {
                            call_user_func([$model, 'macro'], $annotation->value, $relation);
                            unset($relation);
                        }
                    }
                    
                    $this->detected[$className] = true;
                }
            });
            
            // ide-helper
            $this->app->event->listen(ModelGenerator::class, function(ModelGenerator $generator) {
                /** @var Annotation[] $annotations */
                $annotations = $this->reader->getClassAnnotations($generator->getReflection());
                
                foreach ($annotations as $annotation) {
                    $property = Str::snake($annotation->value);
                    switch (true) {
                        case $annotation instanceof HasOne:
                            $generator->addMethod($annotation->value, \think\model\relation\HasOne::class, [], '');
                            $generator->addProperty($property, $annotation->model, true);
                        break;
                        case $annotation instanceof BelongsTo:
                            $generator->addMethod($annotation->value, \think\model\relation\BelongsTo::class, [], '');
                            $generator->addProperty($property, $annotation->model, true);
                        break;
                        case $annotation instanceof HasMany:
                            $generator->addMethod($annotation->value, \think\model\relation\HasMany::class, [], '');
                            $generator->addProperty($property, $annotation->model . '[]', true);
                        break;
                        case $annotation instanceof HasManyThrough:
                            $generator->addMethod($annotation->value, \think\model\relation\HasManyThrough::class, [], '');
                            $generator->addProperty($property, $annotation->model . '[]', true);
                        break;
                        case $annotation instanceof HasOneThrough:
                            $generator->addMethod($annotation->value, \think\model\relation\HasOneThrough::class, [], '');
                            $generator->addProperty($property, $annotation->model, true);
                        break;
                        case $annotation instanceof BelongsToMany:
                            $generator->addMethod($annotation->value, \think\model\relation\BelongsToMany::class, [], '');
                            $generator->addProperty($property, $annotation->model . '[]', true);
                        break;
                        case $annotation instanceof MorphOne:
                            $generator->addMethod($annotation->value, \think\model\relation\MorphOne::class, [], '');
                            $generator->addProperty($property, $annotation->model, true);
                        break;
                        case $annotation instanceof MorphMany:
                            $generator->addMethod($annotation->value, \think\model\relation\MorphMany::class, [], '');
                            $generator->addProperty($property, 'mixed', true);
                        break;
                        case $annotation instanceof MorphTo:
                            $generator->addMethod($annotation->value, \think\model\relation\MorphTo::class, [], '');
                            $generator->addProperty($property, 'mixed', true);
                        break;
                        case $annotation instanceof MorphToMany:
                        case $annotation instanceof MorphByMany:
                            $generator->addMethod($annotation->value, \think\model\relation\MorphToMany::class, [], '');
                            $generator->addProperty($property, Collection::class, true);
                        break;
                    }
                }
            });
        }
    }
}
