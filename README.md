注解扩展
===============

> 基于ThinkPHP改进的注解扩展，方便高效的开发项目

## 安装方式

```shell script
composer require busyphp/annotation
```

## 使用

### 属性依赖注入注解 `@BusyPHP\annotation\Inject`

支持类：

- think\Model
- BusyPHP\Model\Model
- BusyPHP\Model\Field
- BusyPHP\model\ArrayOption
- BusyPHP\model\ObjectOption
- BusyPHP\model\Entity
- `app\*` 命名空间下通过 `\think\Container` 管理的类
- `core\*` 命名空间下通过 `\think\Container` 管理的类
- `config/annotation.php 中设置 inject.namespaces` 命名空间下通过 `\think\Container` 管理的类

#### 示例
```php
<?php
namespace app\home\controller;

class HomeController {

    /**
     * 通过为属性指定var类型，实现依赖注入
     * 
     * @var BusyPHP\App 
     * @BusyPHP\annotation\Inject 
     */
    protected $test;
    
    /**
     * Inject(类路径)，实现依赖注入
     * 
     * @BusyPHP\annotation\Inject(\BusyPHP\App::class)
     */
    protected $test1;

    public function index() {
        var_dump($this->test instanceof BusyPHP\App); // true
        var_dump($this->test1 instanceof BusyPHP\App); // true
    }
}
```