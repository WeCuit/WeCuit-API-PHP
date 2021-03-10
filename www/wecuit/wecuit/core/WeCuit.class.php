<?php
// wecuit/core/WeCuit.class.php
class WeCuit
{
    public static function run()
    {
        date_default_timezone_set('Asia/Shanghai');
        // echo "run()";
        self::setReporting();
        self::init();
        if ("Api" === PLATFORM) {
            header("content-type:application/json");
        }
        try {
            self::autoload();
            self::dispatch();
        } catch (CuitException $e) {
            $ret = array(
                'status' => $e->getCode(),
                'errorCode' => $e->getCode(),
                'errMsg' => $e->getMessage(),
            );
            echo json_encode($ret);
        } catch (Exception $e) {
            $ret = array(
                'status' => $e->getCode(),
                'errorCode' => $e->getCode(),
                'errMsg' => $e->getMessage(),
            );
            echo json_encode($ret);
        }
    }

    // Initialization
    private static function init()
    {
        // Define path constants
        define("DS", DIRECTORY_SEPARATOR);
        define("ROOT", getcwd() . DS);
        define("APP_PATH", ROOT . 'application' . DS);
        define("FRAMEWORK_PATH", ROOT . "wecuit" . DS);
        define("PUBLIC_PATH", ROOT . "public" . DS);
        define("RUNTIME_PATH", ROOT . "runtime" . DS);
        define("CONFIG_PATH", APP_PATH . "config" . DS);
        define("CONTROLLER_PATH", APP_PATH . "controllers" . DS);
        define("MODEL_PATH", APP_PATH . "models" . DS);
        define("VIEW_PATH", APP_PATH . "views" . DS);
        define("CORE_PATH", FRAMEWORK_PATH . "core" . DS);
        define('DB_PATH', FRAMEWORK_PATH . "database" . DS);
        define("LIB_PATH", FRAMEWORK_PATH . "libraries" . DS);
        define("HELPER_PATH", FRAMEWORK_PATH . "helpers" . DS);
        define("UPLOAD_PATH", PUBLIC_PATH . "uploads" . DS);
        define("CACHE_PATH", RUNTIME_PATH . "caches" . DS);
        define("LOG_PATH", RUNTIME_PATH . "logs" . DS);
        define("SESSION_PATH", RUNTIME_PATH . "sessions" . DS);

        // Define platform, controller, action, for example:
        // index.php?p=admin&c=Goods&a=add
        self::route();
        // define("PLATFORM", isset($_REQUEST['p']) ? $_REQUEST['p'] : 'home');
        // define("CONTROLLER", isset($_REQUEST['c']) ? $_REQUEST['c'] : 'Index');
        // define("ACTION", isset($_REQUEST['a']) ? $_REQUEST['a'] : 'index');
        define("CURR_CONTROLLER_PATH", CONTROLLER_PATH . PLATFORM . DS);
        define("CURR_VIEW_PATH", VIEW_PATH . PLATFORM . DS);

        // Load core classes
        require CORE_PATH . "Controller.class.php";
        require CORE_PATH . "Loader.class.php";
        require DB_PATH . "DB.class.php";
        require CORE_PATH . "Model.class.php";
        require CORE_PATH . "CuitException.class.php";

        // Load configuration file
        $GLOBALS['config'] = include CONFIG_PATH . "config.php";

        // Start session
        // session_start();
    }

    // 路由处理
    public static function route()
    {
        // $controllerName = $this->config['defaultController'];
        // $actionName = $this->config['defaultAction'];
        $param = $_GET;
        foreach ($_POST as $key => $value) {
            $param[$key] = $value;
        }

        if(isset($_SERVER['CONTENT_TYPE']) && "application/json" === $_SERVER['CONTENT_TYPE']){
            $data = json_decode(file_get_contents('php://input'), true);
            if($data)
                foreach ($data as $key => $value) {
                    $param[$key] = $value;
                }
        }

        $url = $_SERVER['REQUEST_URI'];
        // 清除?之后的内容
        $position = strpos($url, '?');
        $url = $position === false ? $url : substr($url, 0, $position);
        // 删除前后的"/"
        $url = trim($url, '/');

        $platformName = $controllerName = 'Index';
        $actionName = 'index';
        if ($url) {
            // 使用"/"分割字符串，并保存在数组中
            $urlArray = explode('/', $url);

            // 删除空的数组元素
            $urlArray = array_filter($urlArray);

            // 获取平台
            $platformName = $urlArray ? ucfirst($urlArray[0]) : 'Index';

            // 获取控制器名
            array_shift($urlArray);
            $controllerName = $urlArray ? ucfirst($urlArray[0]) : 'Index';

            // 获取动作名
            array_shift($urlArray);
            $actionName = $urlArray ? $urlArray[0] : 'index';

            // 获取URL参数
            array_shift($urlArray);
            // $param = $urlArray ? $urlArray : array();
            while ($urlArray) {
                $param[$urlArray[0]] = isset($urlArray[1]) ? $urlArray[1] : '';
                array_shift($urlArray);
                array_shift($urlArray);
            }
        }

        // --------------------
        define("PLATFORM", $platformName);
        define("CONTROLLER", $controllerName);
        define("ACTION", $actionName);
        define("PARAM", $param);
        // echo PLATFORM . "----" . CONTROLLER . "----" . ACTION . "----" . PARAM;
        // ------------

        // 判断控制器和操作是否存在
        // $controller = 'app\\controllers\\' . $controllerName . 'Controller';
        // if (!class_exists($controller)) {
        //     exit($controller . '控制器不存在');
        // }
        // if (!method_exists($controller, $actionName)) {
        //     exit($actionName . '方法不存在');
        // }

        // 如果控制器和操作名存在，则实例化控制器，因为控制器对象里面
        // 还会用到控制器名和操作名，所以实例化的时候把他们俩的名称也
        // 传进去。结合Controller基类一起看
        // $dispatch = new $controller($controllerName, $actionName);

        // $dispatch保存控制器实例化后的对象，我们就可以调用它的方法，
        // 也可以像方法中传入参数，以下等同于：$dispatch->$actionName($param)
        // call_user_func_array(array($dispatch, $actionName), $param);
    }

    // 检测开发环境
    public static function setReporting()
    {
        if (defined('APP_DEBUG') && APP_DEBUG === true) {
            error_reporting(E_ALL);
            ini_set('display_errors', 'On');
        } else {
            error_reporting(E_ALL);
            ini_set('display_errors', 'Off');
        }
        ini_set('log_errors', 'On');
        ini_set('error_log', 'runtime/logs/error_log.log');
    }

    // Autoloading
    private static function autoload()
    {
        spl_autoload_register(array(__CLASS__, 'load'));
    }

    // Define a custom load method
    private static function load($className)
    {
        // Here simply autoload app&rsquo;s controller and model classes
        if (substr($className, -10) == "Controller") {
            // Controller
            if (file_exists(CURR_CONTROLLER_PATH . "{$className}.class.php"))
                require_once CURR_CONTROLLER_PATH . "{$className}.class.php";
            else
                throw new CuitException("{$className}控制器不存在");
        } elseif (substr($className, -5) == "Model") {
            // Model
            if (file_exists(MODEL_PATH . "{$className}.class.php"))
                require_once  MODEL_PATH . "{$className}.class.php";
            else
                throw new CuitException("{$className}模型不存在");
        }
    }

    // Routing and dispatching
    private static function dispatch()
    {
        // Instantiate the controller class and call its action method
        $controller_name = CONTROLLER . "Controller";
        $action_name = ACTION . "Action";
        // 判断控制器和操作是否存在
        // $controller = 'app\\controllers\\' . $controllerName . 'Controller';
        if (!class_exists($controller_name)) {
            throw new CuitException($controller_name . '控制器不存在', 20404);
        }
        if (!method_exists($controller_name, $action_name)) {
            throw new CuitException($action_name . '方法不存在', 20404);
        }
        $controller = new $controller_name;
        $controller->$action_name();
    }
}
