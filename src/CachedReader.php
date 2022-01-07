<?php

namespace BusyPHP\annotation;

use Doctrine\Common\Annotations\Reader;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * 缓存读取器
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/1/7 2:06 PM CachedReader.php $
 */
class CachedReader implements Reader
{
    /**
     * @var Reader
     */
    private $delegate;
    
    /**
     * @var array
     */
    private $loadedAnnotations = [];
    
    /**
     * @var CacheInterface
     */
    private $cache;
    
    /**
     * @var boolean
     */
    private $debug;
    
    
    /**
     * @param Reader         $reader
     * @param CacheInterface $cache
     * @param bool           $debug
     */
    public function __construct(Reader $reader, CacheInterface $cache, bool $debug = false)
    {
        $this->delegate = $reader;
        $this->cache    = $cache;
        $this->debug    = $debug;
    }
    
    
    /**
     * {@inheritDoc}
     * @throws InvalidArgumentException
     */
    public function getClassAnnotations(ReflectionClass $class)
    {
        $cacheKey = $class->getName();
        
        if (isset($this->loadedAnnotations[$cacheKey])) {
            return $this->loadedAnnotations[$cacheKey];
        }
        
        if (false === ($annotates = $this->fetchFromCache($cacheKey, $class))) {
            $annotates = $this->delegate->getClassAnnotations($class);
            $this->saveToCache($cacheKey, $annotates);
        }
        
        return $this->loadedAnnotations[$cacheKey] = $annotates;
    }
    
    
    /**
     * {@inheritDoc}
     * @throws InvalidArgumentException
     */
    public function getClassAnnotation(ReflectionClass $class, $annotationName)
    {
        foreach ($this->getClassAnnotations($class) as $annot) {
            if ($annot instanceof $annotationName) {
                return $annot;
            }
        }
        
        return null;
    }
    
    
    /**
     * {@inheritDoc}
     * @throws InvalidArgumentException
     */
    public function getPropertyAnnotations(ReflectionProperty $property)
    {
        $class    = $property->getDeclaringClass();
        $cacheKey = $class->getName() . '$' . $property->getName();
        
        if (isset($this->loadedAnnotations[$cacheKey])) {
            return $this->loadedAnnotations[$cacheKey];
        }
        
        if (false === ($annotates = $this->fetchFromCache($cacheKey, $class))) {
            $annotates = $this->delegate->getPropertyAnnotations($property);
            $this->saveToCache($cacheKey, $annotates);
        }
        
        return $this->loadedAnnotations[$cacheKey] = $annotates;
    }
    
    
    /**
     * {@inheritDoc}
     * @throws InvalidArgumentException
     */
    public function getPropertyAnnotation(ReflectionProperty $property, $annotationName)
    {
        foreach ($this->getPropertyAnnotations($property) as $annot) {
            if ($annot instanceof $annotationName) {
                return $annot;
            }
        }
        
        return null;
    }
    
    
    /**
     * {@inheritDoc}
     * @throws InvalidArgumentException
     */
    public function getMethodAnnotations(ReflectionMethod $method)
    {
        $class    = $method->getDeclaringClass();
        $cacheKey = $class->getName() . '#' . $method->getName();
        
        if (isset($this->loadedAnnotations[$cacheKey])) {
            return $this->loadedAnnotations[$cacheKey];
        }
        
        if (false === ($annotations = $this->fetchFromCache($cacheKey, $class))) {
            $annotations = $this->delegate->getMethodAnnotations($method);
            $this->saveToCache($cacheKey, $annotations);
        }
        
        return $this->loadedAnnotations[$cacheKey] = $annotations;
    }
    
    
    /**
     * {@inheritDoc}
     * @throws InvalidArgumentException
     */
    public function getMethodAnnotation(ReflectionMethod $method, $annotationName)
    {
        foreach ($this->getMethodAnnotations($method) as $annot) {
            if ($annot instanceof $annotationName) {
                return $annot;
            }
        }
        
        return null;
    }
    
    
    public function clearLoadedAnnotations()
    {
        $this->loadedAnnotations = [];
    }
    
    
    /**
     * @throws InvalidArgumentException
     */
    private function fetchFromCache($cacheKey, ReflectionClass $class)
    {
        if ((!$this->debug || $this->isCacheFresh($cacheKey, $class)) && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey, false);
        }
        
        return false;
    }
    
    
    /**
     * @throws InvalidArgumentException
     */
    private function saveToCache($cacheKey, $value)
    {
        $this->cache->set($cacheKey, $value);
        if ($this->debug) {
            $this->cache->set('[C]' . $cacheKey, time());
        }
    }
    
    
    /**
     * @throws InvalidArgumentException
     */
    private function isCacheFresh($cacheKey, ReflectionClass $class) : bool
    {
        if (null === $lastModification = $this->getLastModification($class)) {
            return true;
        }
        
        return $this->cache->get('[C]' . $cacheKey) >= $lastModification;
    }
    
    
    private function getLastModification(ReflectionClass $class)
    {
        $filename = $class->getFileName();
        $parent   = $class->getParentClass();
        
        return max(
            array_merge(
                [$filename ? filemtime($filename) : 0],
                array_map([$this, 'getTraitLastModificationTime'], $class->getTraits()),
                array_map([$this, 'getLastModification'], $class->getInterfaces()),
                $parent ? [$this->getLastModification($parent)] : []
            )
        );
    }
    
    
    private function getTraitLastModificationTime(ReflectionClass $reflectionTrait)
    {
        $fileName = $reflectionTrait->getFileName();
        
        return max(
            array_merge(
                [$fileName ? filemtime($fileName) : 0],
                array_map([$this, 'getTraitLastModificationTime'], $reflectionTrait->getTraits())
            )
        );
    }
}
