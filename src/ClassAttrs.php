<?php
/*
 * @Author       : lovefc
 * @Date         : 2022-12-06 17:01:28
 * @LastEditTime : 2022-12-07 15:39:51
 */

namespace lovefc\LaravelRouteNotes;

class ClassAttrs
{
    // 当前读取的对象
    private $reflector;
    private $attributes;
    private $namespaceName;
    private $className;

    // 初始化设置
    function __construct($className)
    {
        if ($this->isClass($className)) {
            $this->reflector = $this->constructor($className);
            $this->namespaceName =  $this->reflector->getNamespaceName();
            $this->className = $this->reflector->getShortName();
        }
    }

    //判读字符串是否为一个可以实例化类
    public function isClass($class)
    {
        try {
            $reflectionClass = new \ReflectionClass($class);
            if ($reflectionClass->isInstantiable()) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }
    }

    //获取类注解
    public function getClassAnnotate()
    {
        $getAttrs = [];
        $attr = $this->reflector->getAttributes();
        foreach ($attr as $attr2) {
            $getAttrs[$this->getAttrName($attr2)] = $attr2->getArguments();
        }
        $getAttrs['class'] = $this->className;
        $getAttrs['namespace'] = $this->namespaceName;
        return $getAttrs;
    }


    //获取方法注解
    public function getMethodAnnotate()
    {
        $arr = $this->reflector->getMethods();
        $getAttrs = [];
        foreach ($arr as $arrs) {
            $method = $arrs->name;
            $r = new \ReflectionMethod($arrs->class, $method);
            $attr = $r->getAttributes();
            foreach ($attr as $attr2) {
                $getAttrs[$method][$this->getAttrName($attr2)] = $attr2->getArguments();
            }
        }
        return $getAttrs;
    }

    // 获取注解名称
    public function getAttrName($attr)
    {
        $name = $attr->getName();
        return str_ireplace($this->namespaceName . "\\", '', $name);
    }

    // 反射类库
    public function constructor($className)
    {
        $reflector = new \ReflectionClass($className); //反射这个类
        // 检查类是否可实例化, 排除抽象类abstract和对象接口interface
        if (!$reflector->isInstantiable()) {
            return false;
        }
        return $reflector;
    }

    // 获取所有的方法
    public function getMethods()
    {
        return $this->reflector->getMethods();
    }

    // 获取对象属性
    public function getAttributes($reflector)
    {
        return $reflector->getAttributes();
    }
}
