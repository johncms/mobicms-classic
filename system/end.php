<?php

/** @var League\Plates\Engine $view */
$view = App::getContainer()->get(League\Plates\Engine::class);

$data['content'] = ob_get_clean();

if (isset($pageTitle)) {
    $data['title'] = $pageTitle;
}

echo $view->render('system::app/legacy', $data);
