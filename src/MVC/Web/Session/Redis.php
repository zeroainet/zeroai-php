<?php
/**
 *
 * @copyright (C), 2011-, King.
 * @name SessionMemcache.php
 * @author King
 * @version Beta 1.0
 * @Date: Sat Nov 12 23:16 52 CST 2011
 * @Description
 * @Class List
 *        1.
 * @History <author> <time> <version > <desc>
 *          King 2012-5-14上午08:22:34 Beta 1.0 第一次建立该文件
 *          King 2020年6月1日14:21 stable 1.0.01 审定
 *
 */
namespace ZeroAI\MVC\Web\Session;

use ZeroAI\Data\Redis\Redis as RedisSchema;
use ZeroAI\ZeroAI;

/**
 * Session后端Redis适配器
 *
 * @package ZeroAI.MVC.Http.Session
 * @since : 2013-4-13上午02:27:53
 * @final : 2013-4-13上午02:27:53
 */
class Redis implements ISession
{

    /**
     * Redis的data操作实例
     *
     * @var RedisSchema
     */
    protected $_schema;

    /**
     * 默认的服务器缓存策略
     *
     * @var array
     */
    protected $_policy = [
        'lifetime' => 3600
    ];

    /**
     * 初始化构造函数
     *
     * @param array $policy
     *        配置
     * @return void
     */
    function __construct(array $policy = [])
    {
        $this->_policy = array_merge($this->_policy, $policy);
    }

    /**
     * 打开Session
     *
     * @param string $savePath
     *        保存路径
     * @param string $sessionName
     *        session名称
     * @return void
     */
    public function open($savePath, $sessionName)
    {
        return TRUE;
    }

    /**
     * 关闭Session
     *
     * @return void
     */
    public function close()
    {
        return TRUE;
    }

    /**
     * 读Session
     *
     * @param string $sessionId
     *        Session身份标示
     * @return string
     */
    public function read($sessionId)
    {
        return $this->_getSchema()->get($sessionId);
    }

    /**
     * 写Session
     *
     * @param string $sessionId
     *        SessionID标示
     * @param string $sessionValue
     *        Session值
     * @return bool
     */
    public function write($sessionId, $sessionValue)
    {
        return $this->_getSchema()->set($sessionId, $sessionValue, $this->_policy['lifetime']);
    }

    /**
     * 注销某个变量
     *
     * @param string $sessionId
     *        Session身份标示
     * @return bool
     */
    public function destroy($sessionId)
    {
        return $this->_getSchema()->delete($sessionId);
    }

    /**
     * 自动回收过期变量
     *
     * @param int $maxlifetime
     *        最大生存时间
     * @return bool
     */
    public function gc($maxlifetime)
    {
        return TRUE;
    }

    /**
     * 获取redis操作实例
     *
     * @return RedisSchema
     */
    protected function _getSchema()
    {
        if (!$this->_schema)
        {
            $this->_schema;
        }
        $data = ZeroAI::getApplication()->getData();
        $dataId = $this->_policy['dataid'];
        $schema = $data[$dataId];
        if (!$schema instanceof RedisSchema)
        {
            throw new SessionException(sprintf('dataid:%s不是ZeroAI\Data\Redis\Schema的实例', $dataId));
        }
        $this->_schema = $schema;

        return $schema;
    }
}
?>