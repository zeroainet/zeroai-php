<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name SortSet.php
 * @Author King
 * @Version 1.0 
 * @Date: 2013-11-30下午05:18:23
 * @Description 
 * @Class List 
 * @Function 
 * @History <author> <time> <version > <desc> 
 king 2013-11-30下午05:18:23  1.0  第一次建立该文件
 */
namespace Tiny\Data\Redis;

/**
 * redis的有序集合列表
 * 
 * @package Tiny.Data.Redis
 *
 * @since 2013-11-30下午05:18:59
 * @final 2013-11-30下午05:18:59
 */
class SortSet extends Base
{

    /**
     * 添加值到集合中
     * 
     * @param int $score 排序的值
     * @param $val mixed 值
     * @return bool
     */
    public function add($score, $val)
    {
        return $this->_redis->zAdd($this->_key, $score, $val);
    }

    /**
     * 移除集合里的某个值
     * 
     * @param $val mixed 值
     * @return bool
     */
    public function remove($val)
    {
        return $this->_redis->zRem($this->_key, $val);
    }

    /**
     * 按index移除集合里的某个值
     * 
     * @param $val mixed 值
     * @return bool
     */
    public function removeByRank($start, $end)
    {
        return $this->_redis->zRemRangeByRank($this->_key, $start, $end);
    }

    /**
     * 按score移除集合里的某个值
     * 
     * @param $val mixed 值
     * @return bool
     */
    public function removeByScore($start, $end)
    {
        return $this->_redis->zRemRangeByScore($this->_key, $start, $end);
    }

    /**
     * 集合的元素数目
     * 
     * @param void
     * @return int
     */
    public function size()
    {
        return $this->_redis->zSize($this->_key);
    }

    /**
     * 统计在score区间的值的个数
     * 
     * @param int $start 开始的score
     * @param int $end 结束的score
     * @return int
     */
    public function count($start, $end)
    {
        return $this->_redis->zCount($this->_key, $start, $end);
    }

    /**
     * 自增有序集合里指定值的score
     * 
     * @param string $val 值
     * @return int
     */
    public function incr($val, $step = 1)
    {
        return $this->_redis->zIncrBy($this->_key, $step, $val);
    }

    /**
     * 返回index从$start到$end并按从小到大排序的元素
     * 
     * @param int $start 开始的score值
     * @param int $end 结束的score值
     * @param bool $withScores 是否输出score值，默认不输出
     * @return array
     */
    public function range($start, $end, $withScores = false)
    {
        return $this->_redis->zRange($this->_key, $start, $end, $withScores);
    }

    /**
     * 返回index$start到$end并按从大到小排序的元素
     * 
     * @param int $start 开始的索引
     * @param int $end 结束的索引
     * @param bool $withScores 是否输出score值，默认不输出
     * @return array
     */
    public function revRange($start, $end, $withScores = false)
    {
        return $this->_redis->zRevRange($this->_key, $start, $end, $withScores);
    }

    /**
     * 返回名称为key的zset中score >= star且score <= end的所有元素
     * 
     * @param int $start 开始的score值
     * @param int $end 结束的score值
     * @param array $options 选项
     * @return array
     */
    public function rangeByScore($start, $end, array $options = array())
    {
        return $this->_redis->zRangeByScore($this->_key, $start, $end, $options);
    }

    /**
     * 根据值返回按score从小到大排序好的index
     * 
     * @param $val
     * @return array
     */
    public function rank($val)
    {
        return $this->_redis->zRank($this->_key, $val);
    }

    /**
     * 根据值返回按score从大到小排序好的index
     * 
     * @param $val
     * @return array
     */
    public function revRank($val)
    {
        return $this->_redis->zRevRank($this->_key, $val);
    }

    /**
     * 求交集
     * 
     * @param $key mixed 其他需要求交集的键
     * @return void
     */
    public function inter($key)
    {
        $args = func_get_args();
        array_unshift($args, $this->_key);
        return $this->_redis->zInter($args);
    }

    /**
     * 求并集
     * 
     * @param $key mixed 其他需要求并集的键
     * @return void
     */
    public function union(...$args)
    {
        array_unshift($args, $this->_key);
        return $this->_redis->zUnion($args);
    }

    /**
     * 获取某个值的Score
     * 
     * @param $val mixed 值
     * @return float
     */
    public function score($val)
    {
    	return $this->_redis->ZScore($this->_key, $val);
    }


    /**
     * 获取集合的所有成员
     * 
     * @param void
     * @return array
     */
    public function getMembers()
    {
    	return $this->_redis->sGetMembers($this->_key);
    }
}
?>