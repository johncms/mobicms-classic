<?php
/**
 * mobiCMS (https://mobicms.org/)
 * This file is part of mobiCMS Content Management System.
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GPL-3.0 (see the LICENSE.md file)
 * @link        http://mobicms.org mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 */

defined('MOBICMS') or die('Error: restricted access');
defined('DEBUG') || define('DEBUG', false);

error_reporting(E_ALL & ~E_NOTICE);
date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');

// Check the current PHP version
if (version_compare(PHP_VERSION, '7.0', '<')) {
    die('<div style="text-align: center; font-size: xx-large"><strong>ERROR!</strong><br>Your needs PHP 7.0 or higher</div>');
}

define('START_MEMORY', memory_get_usage());
define('START_TIME', microtime(true));

const DS = DIRECTORY_SEPARATOR;
define('ROOT_PATH', dirname(__DIR__) . DS);
const CACHE_PATH = __DIR__ . DS . 'cache' . DS;
const CONFIG_PATH = __DIR__ . DS . 'config' . DS;
const UPLOAD_PATH = ROOT_PATH . DS . 'uploads' . DS;
const LOG_PATH = __DIR__ . DS . 'logs' . DS;

require __DIR__ . '/vendor/autoload.php';

// Errors handling
if (DEBUG) {
    ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 'On');
    ini_set('log_errors', 'On');
    ini_set('error_log', LOG_PATH . 'errors-' . date('Y-m-d') . '.log');
    new Mobicms\Exception\Handler\Handler;
} else {
    ini_set('error_reporting', E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 'Off');
    ini_set('log_errors', 'Off');
}

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
            foreach (Glob::glob(CONFIG_PATH . 'autoload/' . '{{,*.}global,{,*.}local}.php', Glob::GLOB_BRACE) as $file) {
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

// Счетчик активности IP адресов
App::getContainer()->get(Mobicms\Api\EnvironmentInterface::class);

// Проверка IP адреса на бан
try {
    new Mobicms\System\IpBan(App::getContainer());
} catch (Mobicms\System\Exception\IpBanException $e) {
    header($e->getMessage());
    exit;
}

// Автоочистка системы
new Mobicms\System\Clean(App::getContainer());

session_name('SESID');
session_start();

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

if (extension_loaded('zlib') && !ini_get('zlib.output_compression')) {
    ob_start('ob_gzhandler');
} else {
    ob_start();
}
