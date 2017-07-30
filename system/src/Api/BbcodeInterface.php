<?php
/**
 * mobiCMS (https://mobicms.org/)
 * This file is part of mobiCMS Content Management System.
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GPL-3.0 (see the LICENSE.md file)
 * @link        http://mobicms.org mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 */

namespace Mobicms\Api;

/**
 * @deprecated
 */
interface BbcodeInterface
{
    /**
     * Обработка тэгов и ссылок
     *
     * @param $string
     * @return mixed
     */
    public function tags($string);

    /**
     * Удаление BBcode тэгов
     *
     * @param $string
     * @return mixed
     */
    public function noTags($string);

    /**
     * Панель кнопок для форматирования текстов в полях ввода
     *
     * @param $form
     * @param $field
     * @return mixed
     */
    public function buttons($form, $field);
}
