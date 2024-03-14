<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$checkSvg = '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M4 12.6111L8.92308 17.5L20 6.5" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>';
$xMarkSvg = '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M6 6L18 18M18 6L6 18" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>';

function renderItem(int $id): string
{
    $name = $_SESSION['todos'][$id];
    $done = in_array($id, $_SESSION['doneTodos'] ?? []);
    $checked = $done ? 'checked' : '';
    $color = $done ? 'text-sky-600 hover:text-sky-800' : 'text-gray-300 hover:text-gray-500';
    $lineThrough = $done ? 'line-through' : '';

    return <<<EOT
<li id="todo-{$id}" hx-post="/toggle?id={$id}" hx-swap="outerHTML" class="cursor-pointer {$color}">
    <input type="checkbox" {$checked}/>
    <p class="text-xl inline-block my-1 {$lineThrough}">{$name}</p>
</li>
EOT;
}

function renderList(array $todoList): string
{
    $list = '<ul id="todos" class="max-w-md space-y-1 text-gray-500 list-none list-inside dark:text-gray-400">';

    for ($i = 0; $i < count($todoList); $i++) {
        $list .= renderItem($i);
    }

    $list .= '</ul>';

    return $list;
}

function renderButtons(): string
{
    return <<<EOT
<div id="bottom" class="my-2 text-center">
  <button hx-get="/form" hx-target="#bottom" class="bg-blue-700 hover:bg-blue-900 px-5 py-2 mx-1 text-sm leading-5 rounded-full font-semibold text-white my-3">Add todo</button>
  <button hx-post="/clear" hx-target="#todos" class="bg-gray-600 hover:bg-gray-800 px-5 py-2 mx-1 text-sm leading-5 rounded-full font-semibold text-white my-3">Clear</button>
</div>
EOT;
}

function renderForm(): string
{
    global $checkSvg, $xMarkSvg;

    return <<<EOT
<form hx-post="/new" hx-target="#main" hx-swap="outerHTML" class="py-5 flex items-center justify-center">
    <input type="text" name="name" autocomplete="off" autofocus class="mr-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-1 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"/>
    <button class="w-7 h-7">{$checkSvg}</button>
    <button hx-post="/new/cancel" class="w-7 h-7">{$xMarkSvg}</button>
</form>
EOT;
}

$app = AppFactory::create();
session_start();

$app->get('/', function (Request $request, Response $response, $args) {
    $list = renderList($_SESSION['todos'] ?? []);
    $buttons = renderButtons();
    $page = <<<EOT
<html>
  <head>
    <script src="https://unpkg.com/htmx.org@1.9.10" integrity="sha384-D1Kt99CQMDuVetoL1lrYwg5t+9QdHe7NLX/SoJYkXDFfX37iInKRy5xLSi8nO7UC" crossorigin="anonymous"></script>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="p-5 bg-white dark:bg-gray-900 antialiased max-w-2xl m-auto">
    <h1 class="mb-4 text-center text-5xl font-extrabold leading-none tracking-tight text-gray-900 dark:text-gray-300">HTMX Todo List</h1>
    <div id="main">
      {$list}
      {$buttons}
    </div>
  </body>
</html>
EOT;

    $response->getBody()->write($page);
    return $response;
});

$app->get('/form', function (Request $request, Response $response, $args) {
    $form = renderForm();
    $response->getBody()->write($form);
    return $response;
});

$app->post('/new', function (Request $request, Response $response, $args) {
    $data = $request->getParsedBody();

    if ($data['name'] === '') {
        $list = renderList($_SESSION['todos']);
        $form = renderForm();

        $response->getBody()->write('<div id="main">' . $list . $form . '</div>');

        return $response;
    }

    $_SESSION['todos'][] = $data['name'];

    $list = renderList($_SESSION['todos']);
    $buttons = renderButtons();

    $response->getBody()->write('<div id="main">' . $list . $buttons . '</div>');

    return $response;
});

$app->post('/new/cancel', function (Request $request, Response $response, $args) {
    $list = renderList($_SESSION['todos']);
    $buttons = renderButtons();

    $response->getBody()->write('<div id="main">' . $list . $buttons . '</div>');

    return $response;
});

$app->post('/toggle', function (Request $request, Response $response, $args) {
    $params = $request->getQueryParams();

    if (in_array($params['id'], $_SESSION['doneTodos'] ?? [])) {
        $_SESSION['doneTodos'] = array_diff($_SESSION['doneTodos'], [$params['id']]);
    } else {
        $_SESSION['doneTodos'][] = $params['id'];
    }

    $item = renderItem($params['id']);
    $response->getBody()->write($item);

    return $response;
});

$app->post('/clear', function (Request $request, Response $response, $args) {
    $_SESSION['doneTodos'] = [];
    $_SESSION['todos'] = [];
    $list = renderList($_SESSION['todos']);

    $response->getBody()->write($list);

    return $response;
});

$app->run();
