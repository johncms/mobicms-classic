<?php
/**
 * mobiCMS (https://mobicms.org/)
 * This file is part of mobiCMS Content Management System.
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GPL-3.0 (see the LICENSE.md file)
 * @link        http://mobicms.org mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 */

namespace Library;

/**
 * Статические методы помошники
 * Class Utils
 * @package Library
 * @author  Koenig(Compolomus)
 */
class Utils
{
    /**
     * редирект на 404
     */
    public static function redir404()
    {
        /** @var \Mobicms\Api\ConfigInterface $config */
        $config = \App::getContainer()->get(\Mobicms\Api\ConfigInterface::class);

        ob_get_level() and ob_end_clean();
        header('Location: ' . $config['homeurl'] . '/?err');
        exit;
    }

    /**
     * Позиция символа в тексте
     * @param $text
     * @param $chr
     * @return int
     */
    public static function position($text, $chr)
    {
        $result = mb_strpos($text, $chr);

        return $result !== false ? $result : 100;
    }

    /**
     * Сортировка по рейтингу
     * @param $a
     * @param $b
     * @return int
     */
    public static function cmprang($a, $b)
    {
        if ($a['rang'] == $b['rang']) {
            return 0;
        }
        return ($a['rang'] > $b['rang']) ? -1 : 1;
    }

    /**
     * Сортировка по алфавиту
     * @param $a
     * @param $b
     * @return int
     */
    public static function cmpalpha($a, $b)
    {
        if ($a['name'] == $b['name']) {
            return 0;
        }
        return ($a['name'] < $b['name']) ? -1 : 1;
    }
}
