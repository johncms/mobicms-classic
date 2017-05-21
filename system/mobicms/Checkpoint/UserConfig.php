<?php
/**
 * mobiCMS (https://mobicms.org/)
 * This file is part of mobiCMS Content Management System.
 *
 * @license     https://opensource.org/licenses/GPL-3.0 GPL-3.0 (see the LICENSE.md file)
 * @link        http://mobicms.org mobiCMS Project
 * @copyright   Copyright (C) mobiCMS Community
 */

namespace Mobicms\Checkpoint;

use Zend\Stdlib\ArrayObject;

/**
 * Class UserConfig
 *
 * @property $directUrl
 * @property $fieldHeight
 * @property $fieldWidth
 * @property $kmess
 * @property $skin
 * @property $timeshift
 * @property $youtube
 */
class UserConfig extends ArrayObject
{
    public function __construct(User $user)
    {
        $input = empty($user->set_user) ? $this->getDefaults() : unserialize($user->set_user);
        parent::__construct($input, parent::ARRAY_AS_PROPS);
    }

    private function getDefaults()
    {
        return [
            'directUrl'   => 0,  // Внешние ссылки
            'fieldHeight' => 3,  // Высота текстового поля ввода
            'fieldWidth'  => 40, // Ширина текстового поля ввода
            'kmess'       => 20, // Число сообщений на страницу
            'skin'        => '', // Тема оформления
            'timeshift'   => 0,  // Временной сдвиг
            'youtube'     => 1,  // Покалывать ли Youtube player
        ];
    }
}
