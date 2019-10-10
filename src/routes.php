<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

return function (App $app) {
    $container = $app->getContainer();

	$app->get('/', function (Request $request, Response $response, array $args) use ($container) {

		$nome_app = $container->get('settings')['application']['name'];

		$file_path = "../src/main.json";

		if(!file_exists($file_path)) {
			return $this->view->render($response, 'BizPage/config_needed.html');
		}

		$file_content = file_get_contents($file_path);
		$data = json_decode($file_content);

		if(!$data)
			return $this->view->render($response, 'BizPage/data_error.html');

        return $this->view->render($response, 'BizPage/index.html', ['data' => $data, 'base_url' => $data->base_url]);
	});
	
	$app->post('/sendMessage', function (Request $request, Response $response, array $args) use ($container) {

		$nome_app = $container->get('settings')['application']['name'];

		$file_path = "../src/main.json";

		if(!file_exists($file_path)) {
			return $this->view->render($response, 'BizPage/config_needed.html');
		}

		$file_content = file_get_contents($file_path);
		$data = json_decode($file_content);

		if(!$data)
			return $this->view->render($response, 'BizPage/data_error.html');

		// var_dump($data->sendMail);

		$mail = new PHPMailer(true);
		try {
			$mail->SMTPDebug = $data->sendMail->SMTPDebug;            // Enable verbose debug output
			$mail->isSMTP();                                          // Send using SMTP
			$mail->Host       = $data->sendMail->smtp_host;           // Set the SMTP server to send through
			$mail->SMTPAuth   = $data->sendMail->smtp_auth;           // Enable SMTP authentication
			$mail->Username   = $data->sendMail->smtp_user;           // SMTP username
			$mail->Password   = $data->sendMail->smtp_pass;           // SMTP password
			$mail->SMTPSecure = $data->sendMail->smtp_secure;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
			$mail->Port       = $data->sendMail->smtp_port;           // TCP port to connect to

			//Recipients
			$mail->setFrom($data->sendMail->email_from, $data->sendMail->email_name_from);
			$mail->addAddress($request->getParam('email'), $request->getParam('name'));     // Add a recipient

			// Content
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = $data->sendMail->email_subject;
			$mail->Body    = 'Sua solicitação foi recebida e será respondida em breve.<br /><br />Obrigado.<br /><br />Equipe Membrax <http://www.membrax.com.br>';
			$mail->AltBody = 'Sua solicitação foi recebida e será respondida em breve';

			$mail->send();

			$mail->addAddress($data->sendMail->email_contato, "Contato Membrax");     // Add a recipient

			// Content
			$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = "Nova mensagem recebida pelo site";
			$mail->Body    = '<h1>Uma nova mensagem foi recebida pelo site da membrax.</h1>
			<h2>De: '.$request->getParam('nome').' ('.$request->getParam('email').')</h2>
			<h2>Título: '.$request->getParam('subject').'</h2>
			<h3>Mensagem: '.$request->getParam('message').'</h3>
			<br />Obrigado.<br /><br />Equipe Membrax <http://www.membrax.com.br>';
			$mail->AltBody = 'Titulo:'.$request->getParam('subject').'|Mensagem:'.$request->getParam('message');

			$mail->send();

			$resultado = ["error" => false, "message" => "Mensagem Recebida com Sucesso"];


		} catch (Exception $e) {
			$resultado = ["error" => true, "message" => $e->getMessage()];
		}

		return $response->withJson(["resultado" => $resultado]);

    });

    // $app->get('/[{name}]', function (Request $request, Response $response, array $args) use ($container) {
    //     // Sample log message
    //     $container->get('logger')->info("Slim-Skeleton '/' route");

    //     // Render index view
    //     return $container->get('renderer')->render($response, 'index.phtml', $args);
    // });

    
};
