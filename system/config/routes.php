<?php
/**
 * mobiCMS (https://mobicms.org/)
 * This file is part of mobiCMS Content Management System.
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GPL-3.0 (see the LICENSE.md file)
 * @link        http://mobicms.org mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 */

/** @var Psr\Container\ContainerInterface $container */
$container = App::getContainer();

/** @var Mobicms\Api\UserInterface $systemUser */
$systemUser = $container->get(Mobicms\Api\UserInterface::class);

/** @var FastRoute\RouteCollector $map */
$map = $container->get(FastRoute\RouteCollector::class);

$map->get('/', 'modules/homepage/index.php');                                                     // Главная страница
$map->get('/rss[/]', 'modules/rss/index.php');                                                    // RSS
$map->addRoute(['GET', 'POST'], '/login[/]', 'modules/login/index.php');                          // Вход / выход с сайта
$map->addRoute(['GET', 'POST'], '/album[/[index.php]]', 'modules/album/index.php');               // Фотоальбомы
$map->addRoute(['GET', 'POST'], '/downloads[/[index.php]]', 'modules/downloads/index.php');       // Загрузки
$map->addRoute(['GET', 'POST'], '/forum[/[index.php]]', 'modules/forum/index.php');               // Форум
$map->addRoute(['GET', 'POST'], '/guestbook[/[index.php]]', 'modules/guestbook/index.php');       // Гостевая
$map->addRoute(['GET', 'POST'], '/help[/]', 'modules/help/index.php');                            // Справка
$map->addRoute(['GET', 'POST'], '/language[/]', 'modules/language/index.php');                    // Переключатель языков
$map->addRoute(['GET', 'POST'], '/library[/[index.php]]', 'modules/library/index.php');           // Библиотека
$map->addRoute(['GET', 'POST'], '/mail[/[index.php]]', 'modules/mail/index.php');                 // Почта
$map->addRoute(['GET', 'POST'], '/news[/[index.php]]', 'modules/news/index.php');                 // Новости
$map->addRoute(['GET', 'POST'], '/profile[/[index.php]]', 'modules/profile/index.php');           // Пользовательские профили
$map->addRoute(['GET', 'POST'], '/registration[/[index.php]]', 'modules/registration/index.php'); // Регистрация
$map->addRoute(['GET', 'POST'], '/users[/]', 'modules/users/index.php');                          // Пользователи (актив сайта)

if ($systemUser->isValid() && $systemUser->rights >= 6) {
    $map->addRoute(['GET', 'POST'], '/admin/[index.php]', 'modules/admin/index.php');             // Админ панель
}
