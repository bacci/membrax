<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {
    $container = $app->getContainer();

	$app->get('/home', function (Request $request, Response $response, array $args) use ($container) {

		$nome_app = $container->get('settings')['application']['name'];

		$file_path = "../src/main.json";

		if(!file_exists($file_path)) {
			return $this->view->render($response, 'BizPage/config_needed.html');
		}

		$file_content = file_get_contents($file_path);
		$data = json_decode($file_content);

		if(!$data)
			return $this->view->render($response, 'BizPage/data_error.html');

        return $this->view->render($response, 'BizPage/index.html', ['data' => $data]);
    });

    // $app->get('/[{name}]', function (Request $request, Response $response, array $args) use ($container) {
    //     // Sample log message
    //     $container->get('logger')->info("Slim-Skeleton '/' route");

    //     // Render index view
    //     return $container->get('renderer')->render($response, 'index.phtml', $args);
    // });

    
};
