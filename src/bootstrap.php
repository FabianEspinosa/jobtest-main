<?php

use App\System as S;

use Monolog\Logger;
use Monolog\ErrorHandler;

use Bramus\Router;

session_start();

// Agregar cabeceras de CORS
function allowCORS()
{
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
}


if (strpos($_SERVER['HTTP_HOST'], 'localhost') === false && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off")) {
    $location = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $location);
    exit;
}

if ((isset($_SERVER['HTTPS']) && (($_SERVER['HTTPS'] == 'on') || ($_SERVER['HTTPS'] == '1'))) || $_SERVER['SERVER_PORT'] == 443) {
    $protocol = 'https://';
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    $protocol = 'https://';
} else {
    $protocol = 'http://';
}

if (empty($_POST)) {
    $_POST = json_decode(file_get_contents('php://input', true));
}

define('BASE_URL', $protocol . $_SERVER['HTTP_HOST'] . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/.\\') . '/');
define('MOD_REWRITE', true);

foreach (['helpers'] as $t) {
    $helpers_dir = ROOT_PATH . 'library' . DIRECTORY_SEPARATOR . $t . '/';
    if ($handle = opendir($helpers_dir)) {
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                require_once $helpers_dir . $entry;
            }
        }

        closedir($handle);
    }
}

require_once APP_PATH . 'vendor/autoload.php';
require_once 'library/common.php';
$router = new \Bramus\Router\Router();

$logger = new Logger('errors.log');
ErrorHandler::register($logger);

try {
    require_once ROOT_PATH . 'routes.php';
    require ROOT_PATH . 'library/system/Loader.php';
    S\Loader::getInstance()->init();
    $router->setNamespace('\App\Controllers');
    // Llamar a la función allowCORS()
    allowCORS();
    $router->run();
} catch (\Exception $oE) {
    echo $oE->getMessage();
}
