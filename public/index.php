<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->get('/', function (Request $request, Response $response, $args) {
    $file = readfile('index.html');
    $response->getBody()->write($file);
    return $response;
});

$app->get('/form', function (Request $request, Response $response, $args) {
    $response->getBody()->write("<input />");
    return $response;
});

$app->run();
