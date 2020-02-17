<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name Redis.php
 * @Author King
 * @Version 1.0 
 * @Date: 2013-11-29上午09:43:28
 * @Description 
 * @Class List 
 * @Function 
 * @History <author> <time> <version > <desc> 
 king 2013-11-29上午09:43:28  1.0  第一次建立该文件
 */
namespace Tiny\Mvc\Model;

use Tiny\Data\Redis\Schema;

/**
 * Redis的模型类
 * 
 * @package Tiny.Data
 * @since 2013-11-29下午05:07:17
 * @final 2013-11-29下午05:07:17
 */
class Redis extends Base
{
    /**
     * 数据操作实例
     * 
     * @var Schema
     */
    protected $_schema;

    /**
     * 构造函数
     * 
     * 
     * @param $id string data实例ID
     * @return void
     */
    public function __construct($id = 'default')
    {
        if (!(null != $this->_dataId && 'default' == $id))
        {
            $this->_dataId = $id;
        }
    }

    /**
     * 返回连接后的类或者句柄
     * 
     * @param void
     * @return Redis
     */
    public function getConnector()
    {
        return $this->connect();
    }

    /**
     * 关闭或者销毁实例和链接
     * 
     * @param void
     * @return void
     */
    public function close()
    {
        $this->connect()->close();
    }

    /**
     * 获取字符串实例
     * 
     * @param string $key 键
     * @return string
     */
    public function createString($key)
    {
        return $this->_getSchema()->createString($key);
    }

    /**
     * 获取计数器实例
     * 
     * @param string $key 键
     * @return \Tiny\Data\Redis\Counter
     */
    public function createCounter($key)
    {
        return $this->_getSchema()->createCounter($key);
    }

    /**
     * 创建一个队列对象
     * 
     * @param void
     * @return \Tiny\Data\Redis\Queue
     */
    public function createQueue($key)
    {
        return $this->_getSchema()->createQueue($key);
    }

    /**
     * 创建一个哈希表对象
     * 
     * @param void
     * @return \Tiny\Data\Redis\HashTable
     */
    public function createHashTable($key)
    {
        return $this->_getSchema()->createHashTable($key);
    }

    /**
     * 创建一个集合对象
     * 
     * @param void
     * @return \Tiny\Data\Redis\Set
     */
    public function createSet($key)
    {
        return $this->_getSchema()->createSet($key);
    }

    /**
     * 创建一个有序集合对象
     * 
     * @param void
     * @return \Tiny\Data\Redis\Set
     */
    public function createSortSet($key)
    {
        return $this->_getSchema()->createSortSet($key);
    }

    /**
     * 调用Schema自身函数
     * 
     * @param string $method 函数名称
     * @param array $params 参数数组
     * @return
     *
     */
    public function __call($method, $params)
    {
        return call_user_func_array(array($this->_getSchema(), $method
        ), $params);
    }

    /**
     * 获取数据操作实例
     * 
     * @param void
     * @return Schema
     */
    protected function _getSchema()
    {
        if ($this->_schema)
        {
            return $this->_schema;
        }
        $this->_schema = $this->data->getData($this->_dataId);
        if (!$this->_schema instanceof Schema)
        {
            throw new ModelException('Data.Redis.Schema实例加载失败，ID' . $this->_dataId . '不是Tiny\Data\Redis\Schema实例');
        }
        return $this->_schema;
    }
}
?>