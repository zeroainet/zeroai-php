<?php
/**
 * @Copyright (C), 2013-, King.
 * @Name Session.php
 * @Author King
 * @Version 1.0
 * @Date: 2013-12-3上午02:37:46
 * @Description
 * @Class List
 * @Function
 * @History <author> <time> <version > <desc>
 king 2013-12-3上午02:37:46  1.0  第一次建立该文件
 */
namespace Tiny\Mvc\Web\Session;

use Tiny\Mvc\Web\Session\ISession;
/**
 * 服务器临时变量实例
 * 
 * @package Web
 * @since : Sun Dec 18 22:53 40 CST 2011
 * @final : Sun Dec 18 22:53 40 CST 2011
 */
class HttpSession implements \ArrayAccess,ISession
{

    /**
     * Session实例
     * 
     * @var self
     */
    protected static $_instance;

    /**
     * session驱动数组
     * 
     * @var array
     */
    protected static $_driverMap = array(
        'redis' => 'Tiny\Mvc\Web\Session\Redis' ,
        'memcached' => 'Tiny\Mvc\Web\Session\Memcached'
    );

    /**
     * Session处理的Policy
     * 
     * @var array
     */
    private $_policy = array();

    /**
     * session
     * 
     * @var ISession
     */
    private $_session;

    /**
     * 获取单例
     * 
     * @param void
     * @return self
     */
    public static function getInstance($policy = array())
    {
        if (! self::$_instance)
        {
            self::$_instance = new self($policy);
        }
        return self::$_instance;
    }

    /**
     * 注册session驱动类
     * 
     * @param string $id驱动ID
     * @param string $className 类名
     * @return bool
     */
    public static function regDriver($id, $className)
    {
        if (self::$_driverMap[$id])
        {
            throw new SessionException("注册session驱动失败:ID:{$id}已注册");
        }
        self::$_driverMap[$id] = $className;
    }

    /**
     * 获取session值
     * 
     * @param string $key session KEY
     * @return string
     */
    public function get($key)
    {
        return $_SESSION[$key];
    }

    /**
     * 设置session值
     * 
     * @param string $key
     * @param $value mixed 值
     * @return void
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * 移除session值
     * 
     * @param string $key
     * @return void
     */
    public function remove($key)
    {
        unset($_SESSION[$key]);
    }

    /**
     * 获取Cooike中的Session
     * 
     * @param void
     * @return string
     */
    public function getSessionName()
    {
        return session_name();
    }

    /**
     * 获取cookie中的SessionId
     * 
     * @param void
     * @return string
     */
    public function getSessionId()
    {
        return session_id();
    }

    /**
     * 设置Session句柄
     * 
     * @param string $id 句柄ID标示
     * @return bool
     */
    public function setPolicy(array $policy)
    {
        if (! $policy['type'])
        {
            throw new SessionException('设置session策略失败：没有设置type选项!');
        }
        $this->_policy = $policy;
    }

    /**
     *
     * @param
     *
     * @return
     *
     */
    public function setDomain($domain)
    {
        $this->_getSession()->setDomain($domain);
    }

    /**
     * 打开Session
     * 
     * @param void
     * @return void
     */
    public function open($savePath, $sessionName)
    {
        return $this->_getSession()->open($savePath, $sessionName);
    }

    /**
     * 关闭Session
     * 
     * @param void
     * @return void
     */
    public function close()
    {
        return $this->_getSession()->close();
    }

    /**
     * 读Session
     * 
     * @param string $sessionId Session身份标示
     * @return string
     */
    public function read($sessionId)
    {
        return $this->_getSession()->read($sessionId) ?: '';
    }

    /**
     * 写Session
     * 
     * @param string $sessionId SessionID标示
     * @param string $sessionValue Session值
     * @return bool
     */
    public function write($sessionId, $sessionValue)
    {
     
        return $this->_getSession()->write($sessionId, $sessionValue);
    }

    /**
     * 注销某个变量
     * 
     * @param string $sessionId Session身份标示
     * @return bool
     */
    public function destroy($sessionId)
    {
        return $this->_getSession()->destroy($sessionId);
    }

    /**
     * 自动回收过期变量
     * 
     * @param void
     * @return bool
     */
    public function gc($maxlifetime)
    {
        return $this->_getSession()->gc($maxlifetime);
    }

    /**
     * 实现ArrayAccess接口
     * 
     * @param string $key 键名
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $_SESSION[$key];
    }

    /**
     * 实现ArrayAccess接口
     * 
     * @param string $key 键名
     * @param mixed $value 值
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * 实现ArrayAccess接口
     * 
     * @param string $key 键名
     * @return bool
     */
    public function offsetExists($key)
    {
        return $_SESSION[$key] ? true : false;
    }

    /**
     * 实现ArrayAccess接口
     * 
     * @param string $key 键名
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($_SESSION[$key]);
    }

    /**
     * 初始化并注册session句柄
     * 
     * @param void
     * @return void
     */
    private function __construct(array $policy = array())
    {
        $this->_policy = $policy;
        
        if ($policy['enabled'])
        {
            $this->_init($policy);
        }
        session_start();
    }

    /**
     * 获取实例
     * 
     * @param void
     * @return ISession
     */
    private function _getSession()
    {
        if ($this->_session)
        {
            return $this->_session;
        }
        
        $className = self::$_driverMap[$this->_policy['driver']];
        if (! class_exists($className))
        {
            throw new SessionException('设置Session句柄失败,' . $className . '不存在');
        }
        
        $this->_session = new $className(
        array(
                'lifetime' => $this->_policy['expires'] ,
                'dataid' => $this->_policy['dataid']  
            )
        );
        
        if (! $this->_session instanceof ISession)
        {
            throw new SessionException('设置Session句柄失败,' . $className . '没有实现\SessionHandlerInterface接口');
        }
        
        return $this->_session;
    }

    /**
     * 初始化session构造函数
     * 
     * @param void
     * @return void
     */
    protected function _init($policy)
    {
        $domain = $policy['domain'] ?: '';
        $expires = intval($policy['expires']);
        $path = $policy['path'] ?: '/';
        
        session_set_cookie_params($expires, $path, $domain);
        if (! $policy || ! $policy['driver'])
        {
            return;
        }
        
        $driver = $policy['driver'];
        if (! $driver)
        {
            throw new SessionException("session初始化失败:需要设置session.driver");
        }
        
        if (! self::$_driverMap[$driver])
        {
            throw new SessionException("session初始化失败:session.driver{$driver}没有注册成session驱动类");
        }
        session_set_save_handler($this, true);
    }
}
?>