<?php

defined('MOBICMS') or die('Error: restricted access');

error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

// Check the current PHP version
if (version_compare(PHP_VERSION, '5.6', '<')) {
    die('<div style="text-align: center; font-size: xx-large"><strong>ERROR!</strong><br>Your needs PHP 5.6 or higher</div>');
}

define('START_MEMORY', memory_get_usage());
define('START_TIME', microtime(true));
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('CONFIG_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR);
define('CACHE_PATH', ROOT_PATH . 'files' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR);

require __DIR__ . '/vendor/autoload.php';

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\Glob;

class App
{
    /**
     * @var ServiceManager
     */
    private static $container;

    /**
     * @var Zend\I18n\Translator\Translator
     */
    private static $translator;

    /**
     * @return ServiceManager
     */
    public static function getContainer()
    {
        if (null === self::$container) {
            $config = [];

            // Read configuration
            foreach (Glob::glob(CONFIG_PATH . '{{,*.}global,{,*.}local}.php', Glob::GLOB_BRACE) as $file) {
                $config = ArrayUtils::merge($config, include $file);
            }

            $container = new ServiceManager;
            (new Config($config['dependencies']))->configureServiceManager($container);
            $container->setService('config', $config);
            self::$container = $container;
        }

        return self::$container;
    }

    public static function getTranslator()
    {
        if (null === self::$translator) {
            /** @var Mobicms\Api\ConfigInterface $config */
            $config = self::getContainer()->get(Mobicms\Api\ConfigInterface::class);

            /** @var Mobicms\Checkpoint\UserConfig $userConfig */ //TODO: Переделать на UserConfigInterface
            $userConfig = self::getContainer()->get(Mobicms\Api\UserInterface::class)->getConfig();

            if (isset($_POST['setlng']) && array_key_exists($_POST['setlng'], $config->lng_list)) {
                $locale = trim($_POST['setlng']);
                $_SESSION['lng'] = $locale;
            } elseif (isset($_SESSION['lng']) && array_key_exists($_SESSION['lng'], $config->lng_list)) {
                $locale = $_SESSION['lng'];
            } elseif (isset($userConfig['lng']) && array_key_exists($userConfig['lng'], $config->lng_list)) {
                $locale = $userConfig['lng'];
                $_SESSION['lng'] = $locale;
            } else {
                $locale = $config->lng;
            }

            /** @var Zend\I18n\Translator\Translator $translator */
            self::$translator = self::getContainer()->get(Zend\I18n\Translator\Translator::class);
            self::$translator->setLocale($locale);
        }

        return self::$translator;
    }
}

// Проверка IP адреса на бан
try {
    new Mobicms\Tools\IpBan(App::getContainer());
} catch (Mobicms\Tools\Exception\IpBanException $e) {
    header($e->getMessage());
    exit;
}

session_name('SESID');
session_start();

call_user_func(function () {
    /** @var Psr\Container\ContainerInterface $container */
    $container = App::getContainer();

    /** @var PDO $db */
    $db = $container->get(PDO::class);

    // Автоочистка системы
    $cacheFile = CACHE_PATH . 'cleanup.dat';

    if (!file_exists($cacheFile) || filemtime($cacheFile) < (time() - 86400)) {
        $db->exec('DELETE FROM `cms_sessions` WHERE `lastdate` < ' . (time() - 86400));
        $db->exec("DELETE FROM `cms_users_iphistory` WHERE `time` < " . (time() - 7776000));
        $db->query('OPTIMIZE TABLE `cms_sessions`, `cms_users_iphistory`, `cms_mail`, `cms_contact`');
        file_put_contents($cacheFile, time());
    }
});

/**
 * Translate a message
 *
 * @param string $message
 * @param string $textDomain
 * @return string
 */
function _t($message, $textDomain = 'default')
{
    return App::getTranslator()->translate($message, $textDomain);
}

/**
 * Translate a plural message
 *
 * @param string $singular
 * @param string $plural
 * @param int    $number
 * @param string $textDomain
 * @return string
 */
function _p($singular, $plural, $number, $textDomain = 'default')
{
    return App::getTranslator()->translatePlural($singular, $plural, $number, $textDomain);
}

$kmess = App::getContainer()->get(Mobicms\Api\UserInterface::class)->getConfig()->kmess;
$page = isset($_REQUEST['page']) && $_REQUEST['page'] > 0 ? intval($_REQUEST['page']) : 1;
$start = isset($_REQUEST['page']) ? $page * $kmess - $kmess : (isset($_GET['start']) ? abs(intval($_GET['start'])) : 0);

if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
    ob_start('ob_gzhandler');
} else {
    ob_start();
}
