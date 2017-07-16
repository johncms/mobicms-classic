<?php

/** @var League\Plates\Engine $view */
$view = App::getContainer()->get(League\Plates\Engine::class);

echo $view->render('system::app/legacy', ['content' => ob_get_clean()]);
