<?php
/**
 *
 * @copyright (C), 2013-, King.
 * @name Base.php
 * @author King
 * @version Beta 1.0
 * @Date 2017年3月8日下午4:04:15
 * @Class List
 * @Function List
 * @History King 2017年3月8日下午4:04:15 0 第一次建立该文件
 *          King 2017年3月8日下午4:04:15 1 上午修改
 *          King 2020年6月1日14:21 stable 1.0.01 审定
 */
namespace ZeroAI\MVC;

use ZeroAI\Runtime\IExceptionHandler;
use ZeroAI\Config\Configuration;
use ZeroAI\ZeroAI;
use ZeroAI\Log\Logger;
use ZeroAI\Cache\Cache;
use ZeroAI\Data\Data;
use ZeroAI\Lang\Lang;
use ZeroAI\MVC\Router\IRouter;
use ZeroAI\MVC\Controller\Controller;
use ZeroAI\MVC\Viewer\Viewer;
use ZeroAI\MVC\Plugin\Iplugin;
use ZeroAI\MVC\Bootstrap\Base as BootstrapBase;
use ZeroAI\MVC\Router\Router;
use ZeroAI\MVC\Controller\Base;
use ZeroAI\Runtime\Runtime;
use ZeroAI\Runtime\Environment;
use ZeroAI\Filter\IFilter;
use ZeroAI\Filter\Filter;

/**
 * app实例基类
 *
 * @author King
 * @package ZeroAI.MVC
 * @since 2013-3-21下午04:55:41
 * @final 2017-3-11下午04:55:41
 */
abstract class ApplicationBase implements IExceptionHandler
{

    /**
     * 应用实例的插件触发事件集合
     *
     * @var array
     */
    const PLUGIN_EVENTS = [
        'onbeginrequest',
        'onendrequest',
        'onrouterstartup',
        'onroutershutdown',
        'onpredispatch',
        'onpostdispatch',
        'onexception'
    ];

    /**
     * APP所在的目录路径
     *
     * @var string
     *
     */
    public $path;

    /**
     * App配置文件路径
     *
     * @var string
     *
     */
    public $profile;

    /**
     * 是否为调试模式
     *
     * @var bool
     */
    public $isDebug = FALSE;

    /**
     * 默认语言
     *
     * @var string
     */
    public $charset = 'zh_cn';

    /**
     * 默认时区
     *
     * @var string
     */
    public $timezone = 'PRC';

    /**
     *
     * @var Runtime
     */
    public $runtime;

    /**
     * 运行时参数
     *
     * @var Environment
     */
    public $env;

    /**
     * public
     *
     * @var Configuration App的基本配置类
     *
     */
    public $properties;

    /**
     * 当前请求实例
     *
     * @var string WebRequest
     *
     */
    public $request;

    /**
     * 当前响应实例
     *
     * @var string WebResponse
     *
     */
    public $response;

    /**
     * 当前路由器
     *
     * @var IRouter
     */
    public $router;

    /**
     * 引导类
     *
     * @var BootStrapBase
     *
     */
    protected $_bootstrap;

    /**
     * 路由器实例
     *
     * @var Router
     */
    protected $_router;

    /**
     * 配置实例
     *
     * @var Configuration
     */
    protected $_config;

    /**
     * 缓存实例
     *
     * @var Cache
     */
    protected $_cache;

    /**
     * 设置数据池实例
     *
     * @var Data
     */
    protected $_data;

    /**
     * 语言包实例
     *
     * @var Lang
     */
    protected $_lang;

    /**
     * 日志实例
     *
     * @var Logger
     */
    protected $_logger;

    /**
     * 视图实例
     *
     * @var Viewer
     */
    protected $_viewer;

    /**
     * 过滤
     *
     * @var \ZeroAI\Filter\Filter
     */
    protected $_filter;

    /**
     * 控制器的缓存实例数组
     *
     * @var Controller
     */
    protected $_controllers = [];

    /**
     * 模型实例数组
     *
     * @var \ZeroAI\MVC\Model\Base
     */
    protected $_models = [];

    /**
     * 默认的命名空间
     *
     * @var string
     */
    protected $_namespace = '';

    /**
     * 控制器命名空间
     *
     * @var string
     */
    protected $_cNamespace;

    /**
     * 模型命名空间
     *
     * @var string
     */
    protected $_mNamespace;

    /**
     * 应用程序运行的时间戳
     *
     * @var int timeline
     */
    protected $_startTime = 0;

    /**
     * Application注册的插件
     *
     * @var array
     *
     */
    protected $_plugins = [];

    /**
     * 配置数组
     *
     * @var Array
     */
    protected $_prop;

    /**
     * model加载类名缓存
     *
     * @var array
     */
    protected $_modelPathList = NULL;

    /**
     * 初始化应用实例
     *
     * @param string $profile
     *        配置文件路径
     * @return void
     */
    public function __construct($path, $profile = NULL)
    {
        if (!ZeroAI::getApplication())
        {
            ZeroAI::setApplication($this);
        }
        $this->runtime = Runtime::getInstance();
        $this->env = $this->runtime->env;
        $this->path = $path;
        if (!$profile)
        {
            $profile = $path . DIRECTORY_SEPARATOR . 'profile.php';
        }
        $this->profile = $profile;
        $this->_startTime = microtime(TRUE);
        $this->_init();
    }

    /**
     * 设置引导类
     *
     * @param BootstrapBase $bootStrap
     *        继承了BootstrapBase的引导类实例
     * @return ApplicationBase
     */
    public function setBootstrap(BootstrapBase $bootStrap)
    {
        $this->_bootstrap = $bootStrap;
        return $this;
    }

    /**
     * 设置配置实例
     *
     * @param Configuration $config
     *        配置实例
     * @return ApplicationBase
     */
    public function setConfig(Configuration $config)
    {
        $this->_config = $config;
        return $this;
    }

    /**
     * 设置路由器
     *
     * @param Router $router
     *        路由器
     * @return ApplicationBase
     */
    public function setRouter(Router $router)
    {
        $this->_router = $router;
        return $this;
    }

    /**
     *
     * @return Router
     *
     */
    public function getRouter()
    {
        if (!$this->_router)
        {
            $this->_router = new Router($this->request);
        }
        return $this->_router;
    }

    /**
     * 获取app实例的配置实例
     *
     * @return Configuration
     */
    public function getConfig()
    {
        if ($this->_config)
        {
            return $this->_config;
        }

        $prop = $this->_prop['config'];
        if (!$prop['enabled'])
        {
            throw new ApplicationException("properties.config.enabled is false!");
        }
        if (!$prop['path'])
        {
            throw new ApplicationException("properties.config.path is not allow null!");
        }
        $this->_config = new Configuration($prop['path']);
        if (!$this->isDebug && (!isset($prop['cache']['enable']) || $prop['cache']['enable']))
        {

            $cachekey = ftok($this->env['SCRIPT_NAME'], 'a');
            $cacheId = $prop['cache']['id'] ?: 'default';
            $cacheTtl = (int)$prop['cache']['ttl'] ?: 60;
            $cache = $this->getCache();
            $cacheHandler = $cacheId ? $cache : $cache[$cacheId];
            $data  = $cacheHandler->get($cachekey);
            if($data)
            {
                $this->_config->setData($data);
            }
            else
            {
                $data = $this->_config->get();
                $cacheHandler->set($cachekey, $data, $cacheTtl);
            }
        }
        return $this->_config;
    }

    /**
     * 设置缓存实例
     *
     * @param Cache $cache
     *        缓存实例
     * @return ApplicationBase
     */
    public function setCache(Cache $cache)
    {
        $this->_cache = $cache;
        return $this;
    }

    /**
     * 获取应用实例的缓存对象
     *
     * @return Cache
     */
    public function getCache()
    {
        if ($this->_cache)
        {
            return $this->_cache;
        }
        $prop = $this->_prop['cache'];
        if (!$prop['enabled'])
        {
            throw new ApplicationException("properties.cache.enabled is false!");
        }

        $this->_cache = Cache::getInstance();
        $prop['drivers'] = $prop['drivers'] ?: [];
        $prop['policys'] = $prop['policys'] ?: [];
        foreach ($prop['drivers'] as $type => $className)
        {
            Cache::regDriver($type, $className);
        }
        foreach ($prop['policys'] as $policy)
        {
            $policy['lifetime'] = $policy['lifetime'] ?: $prop['lifetime'];
            $policy['path'] = $policy['path'] ?: $prop['path'];
            $this->_cache->regPolicy($policy);
        }
        return $this->_cache;
    }

    /**
     * 设置数据池实例
     *
     * @param Data $data
     *        数据池实例
     * @return ApplicationBase
     */
    public function setData(Data $data)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * 获取数据库连接池
     *
     * @return Data
     */
    public function getData()
    {
        if ($this->_data)
        {
            return $this->_data;
        }
        $prop = $this->_prop['data'];
        if (!$prop['enabled'])
        {
            throw new ApplicationException("properties.data.enabled is false!");
        }
        $this->_data = Data::getInstance();
        $prop['drivers'] = $prop['drivers'] ?: [];
        $prop['policys'] = $prop['policys'] ?: [];
        $prop['charset'] = $prop['charset'] ?: 'utf8';
        foreach ($prop['drivers'] as $type => $className)
        {
            Data::regDriver($type, $className);
        }
        foreach ($prop['policys'] as $policy)
        {
            $policy['def_charset'] = $prop['charset'];
            $this->_data->addPolicy($policy);
        }
        return $this->_data;
    }

    /**
     * 设置应用过滤器
     *
     * @param IFilter $filter
     *        过滤器实例
     */
    public function setFilter(IFilter $filter)
    {
        $this->_filter = $filter;
        return $this->_filter;
    }

    /**
     * 获取过滤器
     *
     * @throws ApplicationException
     * @return \ZeroAI\Filter\Filter
     */
    public function getFilter()
    {
        if ($this->_filter)
        {
            return $this->_filter;
        }
        $prop = $this->_prop['filter'];
        if (!$prop['enabled'])
        {
            return NULL;
        }

        $this->_filter = Filter::getInstance();
        if ($this->env['RUNTIME_MODE'] == $this->env['RUNTIME_MODE_WEB'] && $prop['web'])
        {
            $this->_filter->addFilter($prop['web']);
        }
        if ($this->env['RUNTIME_MODE'] == $this->env['RUNTIME_MODE_CONSOLE'] && $prop['console'])
        {
            $this->_filter->addFilter($prop['console']);
        }
        if ($this->env['RUNTIME_MODE'] == $this->env['RUNTIME_MODE_RPC'] && $prop['rpc'])
        {
            $this->_filter->addFilter($prop['rpc']);
        }
        if (is_array($prop['filters']))
        {
            foreach ($prop['filters'] as $fname)
            {
                $this->_filter->addFilter($fname);
            }
        }
        return $this->_filter;
    }

    /**
     * 设置语言包实例
     *
     * @param Lang $lang
     *        语言包实例
     * @return self
     */
    public function setLang(Lang $lang)
    {
        $this->_lang = $lang;
        return $this;
    }

    /**
     * 获取语言操作对象
     *
     * @param
     *        void
     * @return Lang
     */
    public function getLang()
    {
        if ($this->_lang)
        {
            return $this->_lang;
        }
        $prop = $this->_prop['lang'];
        if (!$prop['enabled'])
        {
            throw new ApplicationException("properties.lang.enabled is false!");
        }

        $this->_lang = Lang::getInstance();
        $this->_lang->setLocale($prop['locale'])->setLangPath($prop['path']);
        return $this->_lang;
    }

    /**
     * 设置日志实例
     *
     * @param Logger $logger
     *        日志实例
     * @return self
     */
    public function setLogger(Logger $logger)
    {
        $this->_logger = $logger;
        return $this;
    }

    /**
     * 获取日志对象
     *
     * @return Logger
     */
    public function getLogger()
    {
        if ($this->_logger)
        {
            return $this->_logger;
        }
        $prop = $this->_prop['log'];
        if (!$prop['enabled'])
        {
            throw new ApplicationException("properties.log.enabled is false!");
        }
        $this->_logger = Logger::getInstance();
        $prop['drivers'] = $prop['drivers'] ?: [];
        foreach ($prop['drivers'] as $type => $className)
        {
            Logger::regWriter($type, $className);
        }
        $policy = ('file' == $prop['type']) ? [
            'path' => $prop['path']
        ] : [];
        $this->_logger->addWriter($prop['type'], $policy);
        return $this->_logger;
    }

    /**
     * 异常触发事件
     *
     * @param array $exception
     *        异常
     * @param array $exceptions
     *        所有异常
     * @return void
     */
    public function onException($e, $exceptions)
    {
        $isLog = $this->_prop['exception']['log'];
        $logId = $this->_prop['exception']['logid'];
        if ($isLog)
        {
            $logMsg = $e['handle'] . ':' . $e['message'] . ' from ' . $e['file'] . ' on line ' . $e['line'];
            $this->getLogger()->error($logId,$e['level'], $logMsg);
        }
        if ($e['isThrow'])
        {
            $this->onPostDispatch();
            $this->response->output();
        }
    }

    /**
     * 简单获取控制器
     *
     * @param string $cName
     *        模型名称
     * @return Base
     */
    public function getController($cname)
    {
        $cname = $cname ?: $this->request->getController();
        if ($this->_controllers[$cname])
        {
            return $this->_controllers[$cname];
        }
        $cparam = preg_replace_callback("/\b\w/", function ($param) {
            return strtoupper($param[0]);
        }, $cname);

        $cparam = "\\" . preg_replace("/\/+/", "\\", $cparam);
        $controllerName = $this->_cNamespace . $cparam;
        if (!class_exists($controllerName))
        {
            throw new ApplicationException("Dispatch errror:controller,{$controllerName}不存在，无法加载", E_ERROR);
        }

        $this->_controllers[$cname] = new $controllerName();
        $this->_controllers[$cname]->setApplication($this);
        if (!$this->_controllers[$cname] instanceof \ZeroAI\MVC\Controller\Base)
        {
            throw new ApplicationException("Controller:'{$controllerName}' is not instanceof ZeroAI\MVC\Controlller\Controller!", E_ERROR);
        }
        return $this->_controllers[$cname];
    }

    /**
     * 获取动作名称
     * @param string $aname
     */
    public function getAction($aname, bool $isEvent = FALSE)
    {
        $aname = $aname ?: $this->request->getAction();
        $aname = $isEvent ? $aname : $aname . 'Action';
        return $aname;
    }

    /**
     * 简单获取模型
     *
     * @param string $modelName
     *        模型名称
     * @return \ZeroAI\MVC\Model\Base
     */
    public function getModel($mname)
    {
        $mid = strtolower($mname);
        if ($this->_models[$mid])
        {
            return $this->_models[$mid];
        }
        $modelFullName = $this->_searchModel($mname);
        if ($modelFullName)
        {
            $this->_models[$mid] = new $modelFullName();
            return $this->_models[$mid];
        }
    }

    /**
     * 设置视图实例
     *
     * @param Viewer $viewer
     *        视图实例
     * @return Base
     */
    public function setViewer(Viewer $viewer)
    {
        $this->_viewer = $viewer;
        return $this;
    }

    /**
     * 获取视图类型
     *
     * @return Viewer
     */
    public function getViewer()
    {
        if ($this->_viewer)
        {
            return $this->_viewer;
        }
        $prop = $this->_prop['view'];
        $this->_viewer = Viewer::getInstance();

        $assign = $prop['assign'] ?: [];
        $engines = $prop['engines'] ?: [];

        $assign['env'] = $this->runtime->env;
        $assign['request'] = $this->request;
        $assign['response'] = $this->response;
        $assign['properties'] = $this->properties;

        if ($this->_prop['config']['enabled'])
        {
            $assign['config'] = $this->getConfig();
        }

        if ($this->_prop['lang']['enabled'])
        {
            $assign['lang'] = $this->getLang();
            $this->_viewer->setBasePath($this->_prop['lang']['locale']);
        }

        foreach ($engines as $ext => $ename)
        {
            $this->_viewer->bindEngineByExt($ext, $ename);
        }
        $this->_viewer->setTemplatePath($prop['src']);
        $this->_viewer->setCompilePath($prop['compile']);
        $assign['view'] = $this->_viewer;
        $this->_viewer->assign($assign);
        return $this->_viewer;
    }

    /**
     * 设置默认的时区
     *
     *
     * @param string $timezone
     *        时区标示
     * @return bool
     */
    public function setTimezone($timezone)
    {
        return date_default_timezone_set($timezone);
    }

    /**
     * 获取已经设置的默认时区
     *
     *
     * @return string
     */
    public function getTimezone()
    {
        return date_default_timezone_get();
    }

    /**
     * 注册插件
     *
     *
     * @param Iplugin $plugin
     *        实现插件接口的实例
     * @return self
     */
    public function regPlugin(Iplugin $plugin)
    {
        $this->_plugins[] = $plugin;
        return $this;
    }

    /**
     * 执行
     *
     * @return void
     */
    public function run()
    {
        $this->_bootstrap();
        $this->onRouterStartup();
        $this->_route();
        $this->onRouterShutdown();
        $this->_doFilter();
        $this->onPreDispatch();
        $this->dispatch();
        $this->onPostDispatch();
        $this->response->output();
    }


    /**
     * 分发
     *
     * @access protected
     * @param string $cname
     *        控制器名称
     * @param string $aname
     *        动作名称
     * @return mixed
     */
    public function dispatch(string $cname = NULL, string $aname = NULL, array $args = [], bool $isEvent = FALSE)
    {
        //获取控制器实例
        $controller = $this->getController($cname);
        $this->controller = $controller;

        //获取执行动作名称
        $action = $this->getAction($aname, $isEvent);

        if ($args)
        {
            array_push($args, $this->request, $this->response);
        }
        //执行前返回FALSE则不执行派发动作
        $ret = call_user_func_array([$this, 'onBeginExecute'], $args);
        if (FALSE === $ret)
        {
            return FALSE;
        }
        if (!method_exists($controller, $action))
        {
            throw new ApplicationException("Dispatch error: The Action '{$aname}' of Controller '{$cname}' is not exists ");
        }
        $ret = call_user_func_array([
            $controller,
            $action
        ], $args);
        call_user_func_array([$this, 'onEndExecute'], $args);
        return $ret;
    }

    /**
     * 运行插件
     *
     * @param string $method
     *        插件事件
     * @param $params array
     *        参数
     * @return void
     */
    public function __call($method, $params)
    {
        return $this->_onPlugin($method, $params);
    }

    /**
     * 执行初始化
     *
     * @return void
     */
    protected function _init()
    {
        $this->_initResponse();
        $this->_initProperties();
        $this->_initNamespace();
        $this->_initPlugin();
        $this->_initImport();
        $this->_initException();
        $this->_initRequest();
    }

    /**
     * 初始化应用程序的配置对象
     *
     * @return void
     */
    protected function _initProperties()
    {
        $this->properties = new Configuration($this->profile);
        $this->_initPath($this->properties['path']);
        $this->_prop = $this->properties->get();
        $prop = $this->_prop;
        $this->_namespace = $prop['app']['namespace'];
        if (isset($prop['timezone']))
        {
            $this->timezone = $prop['timezone'];
            $this->setTimezone($prop['timezone']);
        }

        if (isset($prop['charset']))
        {
            $this->charset = $prop['charset'];
        }
    }

    /**
     * 初始化命名空间
     *
     * @return void
     */
    protected function _initNamespace()
    {
        $this->_namespace = $this->_prop['app']['namespace'] ?: 'App';
        $cprefix = $this->_prop['controller']['namespace'];
        if (static::class == 'ZeroAI\MVC\ConsoleApplication')
        {
            $cprefix = $this->_prop['controller']['console'];
        }
        elseif (static::class == 'ZeroAI\MVC\RPCApplication')
        {
            $cprefix = $this->_prop['controller']['rpc'];
        }

        $this->_cNamespace = '\\' . $this->_namespace . '\\' . $cprefix;
        $this->_mNamespace = '\\' . $this->_namespace . '\\' . $this->_prop['model']['namespace'];
    }

    /**
     * 初始化debug插件
     *
     * @return void
     */
    protected function _initPlugin()
    {
        if ($this->properties['debug'])
        {
            $this->isDebug = TRUE;
            $this->regPlugin(new \ZeroAI\MVC\Plugin\Debug($this));
        }
    }

    /**
     * 初始化异常处理
     *
     * @return void
     */
    protected function _initException()
    {
        if ($this->properties['exception.enable'])
        {
            $this->runtime->regExceptionHandler($this);
        }
    }

    /**
     * 初始化路径
     *
     * @param array $paths
     *        初始化路径
     * @return void
     *
     */
    protected function _initPath(array $paths)
    {
        $runtimePath = $this->properties['app.runtime'];
        if(!$runtimePath)
        {
            $runtimePath = $this->path . 'runtime/';
        }
        if ($runtimePath && 0 === strpos($runtimePath, 'runtime'))
        {
            $runtimePath = $this->path . $runtimePath;
        }
        foreach ($paths as $p)
        {
            $path = $this->properties[$p];
            if (!$path)
            {
                continue;
            }
            if (0 === strpos($path, 'runtime'))
            {
                $rpath = preg_replace("/\/+/", "/",$runtimePath . substr($path, 7));
                if (!file_exists($rpath))
                {
                    mkdir($rpath, 0777, TRUE);
                }
                $this->properties[$p] = $rpath;
                continue;
            }
            $this->properties[$p] = $this->path . $path;
        }
    }

    /**
     * 初始化加载类库
     *
     * @return void
     */
    protected function _initImport()
    {
        $runtime = Runtime::getInstance();
        $runtime->import($this->path, $this->_namespace);
        if (!is_array($this->_prop['imports']))
        {
            return;
        }

        if ($this->_prop['import_no_replacepath'])
        {
            foreach ($this->_prop['imports'] as $ns => $p)
            {
                $runtime->import($p, $ns);
            }
            return;
        }
        foreach ($this->_prop['imports'] as $ns => $p)
        {
            $runtime->import($this->properties[$p], $ns);
        }
    }

    /**
     * 初始化请求
     *
     * @return void
     */
    protected function _initRequest()
    {
        if (!$this->request)
        {
            return;
        }

        $this->request->setApplication($this);
        $prop = $this->_prop;
        $this->request->setController($prop['controller']['default']);
        $this->request->setControllerParam($prop['controller']['param']);
        $this->request->setAction($prop['action']['default']);
        $this->request->setActionParam($prop['action']['param']);
    }

    /**
     * 初始化响应
     *
     * @return void
     */
    protected function _initResponse()
    {
        $this->response->setApplication($this);
        $this->response->setLocale($this->properties['lang']['locale']);
        $this->response->setCharset($this->properties['charset']);
    }

    /**
     * 通过魔法函数触发插件的事件
     *
     *
     * @param string $method
     *        函数名称
     * @param array $params
     *        参数数组
     * @return void
     */
    protected function _onPlugin($method, $params)
    {
        $method = strtolower($method);
        if (!in_array($method, static::PLUGIN_EVENTS))
        {
            return;
        }

        foreach ($this->_plugins as $plugin)
        {
            $params[] = $this;
            call_user_func_array([
                $plugin,
                $method
            ], $params);
        }
    }

    /**
     * 获取bootstrap实例 考虑到在application的初始化，不提供外部获取方式，避免错误使用。
     * @throws ApplicationException
     * @return \ZeroAI\MVC\Bootstrap\Base
     */
    protected function _getBootstrap()
    {
        if ($this->_bootstrap)
        {
            return $this->_bootstrap;
        }
        if (!$this->_prop['bootstrap']['enable'])
        {
            return FALSE;
        }
        $className = $this->_prop['bootstrap']['class'];
        if(!(class_exists($className) && $className instanceof BootstrapBase))
        {
            throw new ApplicationException(sprintf('bootstrap faild:%s 不存在或没有继承\ZeroAI\Bootstrap\Base基类', $className));
        }
        $this->_bootstrap = new $className();
        return $this->_bootstrap;
    }

    /**
     * 引导
     *
     * @return void
     */
    protected function _bootstrap()
    {
        $bootstrap = $this->_getBootstrap();
        if ($bootstrap)
        {
            $bootstrap->bootstrap($this);
        }
    }

    /**
     * 执行路由
     *
     * @return void
     */
    protected function _route()
    {
        $prop = $this->_prop['router'];
        if (!$prop['enabled'])
        {
            return;
        }
        $routers = $prop['routers'] ?: [];
        $rules = $prop['rules'] ?: [];
        $router = $this->getRouter();

        foreach ($routers as $k => $r)
        {
            $router->regDriver($k, $r);
        }
        foreach ($rules as $rule)
        {
            $router->addRule($rule['router'], $rule['rule'], $rule);
        }
        $router->route();
    }

    /**
     * 搜索模型
     * @param string $modelName
     */
    protected function _searchModel($mname)
    {
        if (FALSE === strpos($mname, "\\"))
        {
            $mname = preg_replace('/([A-Z]+)/', '\\\\$1', ucfirst($mname));
        }
        $params = explode("\\", $mname);
        for ($i = count($params); $i > 0; $i--)
        {
            $modelFullName = join('\\', array_slice($params, 0, $i - 1)) . '\\' . join('', array_slice($params, $i - 1));
            if ($modelFullName[0] != '\\')
            {
                $modelFullName = "\\" . $modelFullName;
            }
            $modelFullName = $this->_mNamespace . $modelFullName;
            if (class_exists($modelFullName))
            {
                return $modelFullName;
            }
        }
    }
}
?>