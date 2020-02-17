<?php
/**
 *
 * @Copyright (C), 2011-, King.$i
 * @Name  memcached.php
 * @Author  King
 * @Version  Beta 1.0
 * @Date: Fri Dec 16 22 48 00 CST 2011
 * @Description
 * @Class List
 *  	1.
 *  @Function List
 *   1.
 *  @History
 *      <author>    <time>                        <version >   <desc>
 *        King      Fri Dec 16 22:48:00 CST 2011  Beta 1.0           第一次建立该文件
 *
 */
namespace Tiny\Cache;

use Tiny\Data\Memcached\Schema;
use Tiny\Tiny;

/**
 * Memcache缓存
 * 
 * @package Tiny.Cache
 * @since : Fri Dec 16 22 48 07 CST 2011
 * @final : Fri Dec 16 22 48 07 CST 2011
 */
class Memcached implements ICache, \ArrayAccess
{

    /**
     * memcached连接实例
     * 
     * @var memcached
     */
    protected $_memcached;

    /**
     * memcached操作实例
     * 
     * @var Schema
     */
    protected $_schema;

    /**
     * 缓存策略数组
     * 
     * @var array
     */
    protected $_policy = array('lifetime' => 3600);

    /**
     * 初始化构造函数
     * 
     * @param array $policy 代理数组
     * @return void
     */
    function __construct(array $policy = array())
    {
        $this->_policy = array_merge($this->_policy, $policy);
        if (! $this->_policy['dataid'])
        {
            throw new CacheException('Cache.Memcached实例化失败:dataid没有设置');
        }
    }

    /**
     * 获取策略数组
     * 
     * @param void
     * @return array
     */
    public function getPolicy()
    {
        return $this->_policy;
    }

    /**
     * 获取链接
     * 
     * @param void
     * @return Schema
     */
    public function getConnector()
    {
        if ($this->_memcached)
        {
            return $this->_memcached;
        }
        
        $data = Tiny::getApplication()->getData();
        $dataId = $this->_policy['dataid'];
        $schema = $data[$dataId];
        if (! $schema instanceof Schema)
        {
            throw new CacheException("dataid:{$dataId}不是Tiny\Data\Memcached\Schema的实例");
        }
        $this->_schema = $schema;
        $this->_memcached = $schema->getConnector();
        return $this->_memcached;
    }

    /**
     * 获取缓存
     * 
     * @param string || array $key 获取缓存的键名 如果$key为数组 则可以批量获取缓存
     * @return mixed
     */
    public function get($key)
    {
        return $this->_getSchema()->get($key);
    }

    /**
     * 设置缓存
     * 
     * @param string $key 缓存的键 $key为array时 可以批量设置缓存
     * @param mixed $value 缓存的值 $key为array时 为设置生命周期的值
     * @param int $life 缓存的生命周期
     * @return bool
     */
    public function set($key, $value = null, $life = null)
    {
        if (is_array($key))
        {
            $value = (int)$value ?: $this->_policy['lifetime'];
            $life = null;
        }
        return $this->_getSchema()->set($key, $value, $life);
    }

    /**
     * 判断缓存是否存在
     * 
     * @param string $key 键
     * @return bool
     */
    public function exists($key)
    {
        return $this->_getSchema()->get($key) ? true : false;
    }

    /**
     * 移除缓存
     * 
     * @param string $key 缓存的键 $key为array时 可以批量删除
     * @return bool
     */
    public function remove($key)
    {
        return $this->_getSchema()->delete($key);
    }

    /**
     * 清除所有缓存
     * 
     * @param void
     * @return bool
     */
    public function clean()
    {
        return $this->_getSchema()->flush();
    }

    /**
     * 数组接口之设置
     * 
     * @param $key string 键
     * @param $value mixed 值
     * @return
     *
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * 数组接口之获取缓存实例
     * 
     * @param $key string  键
     * @return array
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * 数组接口之是否存在该值
     * 
     * @param $key string 键
     * @return boolean
     */
    public function offsetExists($key)
    {
        return $this->exists($key);
    }

    /**
     * 数组接口之移除该值
     * 
     * @param $key string 键
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->remove($key);
    }

    /**
     * 代理默认的Redis实例
     * 
     * @param string $method 默认实例的函数实例
     * @param array $params 参数数组
     * @return
     *
     */
    public function __call($method, $params)
    {
        return call_user_func_array(array($this->getConnector() ,$method), $params);
    }

    /**
     * 获取memcached操作实例
     * 
     * @param void
     * @return Schema
     */
    protected function _getSchema()
    {
        if (! $this->_schema)
        {
            $this->getConnector();
        }
        return $this->_schema;
    }
}