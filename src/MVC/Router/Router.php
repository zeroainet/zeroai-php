<?php
/**
 *
 * @copyright (C), 2013-, King.
 * @name Router.php
 * @author King
 * @version Beta 1.0
 * @Date 2017年3月8日下午4:20:28
 * @Class List
 * @Function List
 * @History King 2017年3月8日下午4:20:28 0 第一次建立该文件
 *          King 2017年3月8日下午4:20:28 1 上午修改
 *          King 2020年6月1日14:21 stable 1.0.01 审定
 */
namespace ZeroAI\MVC\Router;

use ZeroAI\MVC\Request\Base as Request;

/**
 * 路由器主体类
 *
 * @package ZeroAI.MVC.Router
 * @since : Thu Dec 15 09 22 30 CST 2011
 * @final : Thu Dec 15 09 22 30 CST 2011
 */
class Router
{

    /**
     * 路由驱动类的集合数组
     *
     * @var array
     */
    protected $_driverMaps = [
        'regex' => '\ZeroAI\MVC\Router\RegEx',
        'pathinfo' => '\ZeroAI\MVC\Router\PathInfo'
    ];

    /**
     * 当前Http应用程序的请求对象
     *
     * @var Request
     */
    protected $_req;

    /**
     * 实例化的路由器实例
     *
     * @var array
     */
    protected $_routers = [];
    /**
     * 路由规则集合
     *
     * @var array
     */
    protected $_rules = [];

    /**
     * 是否已经执行过路由检测
     *
     * @var bool
     */
    protected $_isRouted = FALSE;

    /**
     * 匹配的路由规则
     *
     * @var IRouter
     */
    protected $_matchRule;

    /**
     * 解析的参数
     *
     * @var array
     */
    protected $_params = [];

    /**
     * 注册路由驱动
     *
     * @param string $type
     *        路由类型名称
     * @param string $className
     *        路由名称
     * @return bool
     */
    public function regDriver($type, $className)
    {
        if (!key_exists($type, $this->_driverMaps))
        {
            return FALSE;
        }
        $this->_driverMaps[$type] = $className;
    }

    /**
     * 构造函数
     *
     * @param Request $req
     * @return void
     */
    public function __construct(Request $req)
    {
        $this->_req = $req;
    }

    /**
     * 添加路由规则
     *
     * @param string $driverId
     *        驱动器名称
     * @param string $rule
     *        规则
     * @param array $ruledata
     *        规则附带数据
     * @return void
     */
    public function addRule($driverId, $rule, $data = NULL)
    {
        if (!key_exists($driverId, $this->_driverMaps))
        {
            return FALSE;
        }
        $rule['className'] = $this->_driverMaps[$driverId];
        $rule['ruleData'] = $data;
        $this->_rules[] = $rule;
    }

    /**
     * 执行路由动作
     *
     * @return void
     */
    public function route()
    {
        $routerString = $this->_req->getRouterString();
        foreach ($this->_rules as $r)
        {
            $router = $this->_getRouter($r['className']);
            if ($router->checkRule($r, $routerString))
            {
                return $this->resolveRule($router);
            }
        }
        return FALSE;
    }

    /**
     * 解析规则，并注入到当前应用程序的参数中去
     *
     * @param array $params
     *        参数
     * @return void
     */
    public function resolveRule(IRouter $rule)
    {
        $this->_matchRule = $rule;
        $this->_params = $rule->getParams();
        $this->_req->setRouterParam($this->_params);
    }

    /**
     * 获取解析Url而来的参数
     *
     * @return array
     */
    public function getParams()
    {
        return $this->_matchRule ? $this->_params : [];
    }

    /**
     * 获取路由对象
     *
     * @param array $rule
     * @return string 规则
     */
    protected function _getRouter($className)
    {
        static $routers = [];
        $routerId = strtolower($className);

        if (!$routers[$routerId])
        {
            $routers[$routerId] = new $className();
            if (!$routers[$routerId] instanceof IRouter)
            {
                throw new RouterException('router driver:' . $className . ' is not instanceof ZeroAI\MVC\Router\IRouter');
            }
        }
        $router = $routers[$routerId];
        return $router;
    }
}
?>
