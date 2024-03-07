<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$checkSvg = '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#000000"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M4 12.6111L8.92308 17.5L20 6.5" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>';
$xMarkSvg = '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M6 6L18 18M18 6L6 18" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>';

function renderItem(string $todo, int $index): string
{
    $id = "todo-{$index}";
    $editButton = renderEditButton($todo, $index);
    return <<<EOT
<li id="{$id}">
    {$todo}
    {$editButton}
</li>
EOT;
}

function renderList(array $todoList): string
{
    $list = '<ul class="max-w-md space-y-1 text-gray-500 list-disc list-inside dark:text-gray-400">';

    for ($i = 0; $i < count($todoList); $i++) {
        $list .= renderItem($todoList[$i], $i);
    }

    $list .= '</ul>';

    return $list;
}

function renderButton(): string
{
    return <<<EOT
<button hx-get="/form" hx-swap="outerHTML" class="bg-sky-500 hover:bg-sky-700 px-5 py-2 text-sm leading-5 rounded-full font-semibold text-white my-3">Add todo</button>
EOT;
}

function renderNewForm(): string
{
    global $checkSvg, $xMarkSvg;

    return <<<EOT
<form hx-post="/new" hx-target="#todo-list" hx-swap="outerHTML">
    <input type="text" name="name" autocomplete="off" />
    <button class="w-5 h-5">{$checkSvg}</button>
    <button hx-post="/new/cancel" class="w-5 h-5">{$xMarkSvg}</button>
</form>
EOT;
}

function renderEditButton(string $name, int $index): string
{
    return <<<EOT
<button hx-get="/edit-form?name={$name}&index={$index}" hx-swap="outerHTML" hx-target="#todo-{$index}" class="w-3 h-3"><svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M11.4001 18.1612L11.4001 18.1612L18.796 10.7653C17.7894 10.3464 16.5972 9.6582 15.4697 8.53068C14.342 7.40298 13.6537 6.21058 13.2348 5.2039L5.83882 12.5999L5.83879 12.5999C5.26166 13.1771 4.97307 13.4657 4.7249 13.7838C4.43213 14.1592 4.18114 14.5653 3.97634 14.995C3.80273 15.3593 3.67368 15.7465 3.41556 16.5208L2.05445 20.6042C1.92743 20.9852 2.0266 21.4053 2.31063 21.6894C2.59466 21.9734 3.01478 22.0726 3.39584 21.9456L7.47918 20.5844C8.25351 20.3263 8.6407 20.1973 9.00498 20.0237C9.43469 19.8189 9.84082 19.5679 10.2162 19.2751C10.5343 19.0269 10.823 18.7383 11.4001 18.1612Z" fill="#ffffff"></path> <path d="M20.8482 8.71306C22.3839 7.17735 22.3839 4.68748 20.8482 3.15178C19.3125 1.61607 16.8226 1.61607 15.2869 3.15178L14.3999 4.03882C14.4121 4.0755 14.4246 4.11268 14.4377 4.15035C14.7628 5.0875 15.3763 6.31601 16.5303 7.47002C17.6843 8.62403 18.9128 9.23749 19.85 9.56262C19.8875 9.57563 19.9245 9.58817 19.961 9.60026L20.8482 8.71306Z" fill="#ffffff"></path> </g></svg></button>
EOT;
}

function renderEditForm(int $index): string
{
  global $checkSvg, $xMarkSvg;

  $id= "edit-form-{$index}";

  return <<<EOT
<li id="{$id}">
    <form hx-post="/edit" hx-target="#{$id}" hx-swap="outerHTML" class="inline-flex items-center">
        <input type="text" name="name" autocomplete="off" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-1 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"/>
        <input type="hidden" name="index" value="{$index}" />
        <button class="w-5 h-5">{$checkSvg}</button>
        <button hx-post="/edit/cancel" class="w-5 h-5">{$xMarkSvg}</button>
    </form>
</li>
EOT;
}

$app = AppFactory::create();
session_start();

$app->get('/', function (Request $request, Response $response, $args) {
    $list = renderList($_SESSION['todos'] ?? []);
    $button = renderButton();
    $page = <<<EOT
<html>
  <head>
    <script src="https://unpkg.com/htmx.org@1.9.10" integrity="sha384-D1Kt99CQMDuVetoL1lrYwg5t+9QdHe7NLX/SoJYkXDFfX37iInKRy5xLSi8nO7UC" crossorigin="anonymous"></script>
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="p-5 bg-white dark:bg-gray-900 antialiased">
    <h1 class="mb-4 text-4xl font-extrabold leading-none tracking-tight text-gray-900 md:text-5xl lg:text-6xl dark:text-white">HTMX Todo List</h1>
    <div id="todo-list">
      {$list}
      {$button}
    </div>
  </body>
</html>
EOT;

    $response->getBody()->write($page);
    return $response;
});

$app->get('/form', function (Request $request, Response $response, $args) {
    global $checkSvg, $xMarkSvg;

    $form = <<<EOT
<form hx-post="/new" hx-target="#todo-list" hx-swap="outerHTML">
    <input type="text" name="name" autocomplete="off" />
    <button class="w-5 h-5">{$checkSvg}</button>
    <button hx-post="/new/cancel" class="w-5 h-5">{$xMarkSvg}</button>
</form>
EOT;
    $response->getBody()->write($form);
    return $response;
});

$app->get('/edit-form', function (Request $request, Response $response, $args) {
    global $checkSvg, $xMarkSvg;

    $params = $request->getQueryParams();

    $form = renderEditForm($params['index']);
    $response->getBody()->write($form);
    return $response;
});

$app->post('/new', function (Request $request, Response $response, $args) {
    $data = $request->getParsedBody();

    if ($data['name'] === '') {
        $list = renderList($_SESSION['todos']);
        $form = renderNewForm();

        $response->getBody()->write('<div id="todo-list">' . $list . $form . '</div>');
        return $response;
    }

    $_SESSION['todos'][] = $data['name'];

    $list = renderList($_SESSION['todos']);
    $button = renderButton();

    $response->getBody()->write('<div id="todo-list">' . $list . $button . '</div>');
    return $response;
});

$app->post('/new/cancel', function (Request $request, Response $response, $args) {
    $list = renderList($_SESSION['todos']);
    $button = renderButton();

    $response->getBody()->write('<div id="todo-list">' . $list . $button . '</div>');
    return $response;
});

$app->post('/edit', function (Request $request, Response $response, $args) {
    $data = $request->getParsedBody();

    if ($data['name'] === '') {
        $form = renderEditForm($data['index']);
        $response->getBody()->write($form);
        return $response;
    }

    $_SESSION['todos'][$data['index']] = $data['name'];
    $item = renderItem($data['name'], $data['index']);
    $response->getBody()->write($item);
    return $response;
});

$app->post('/edit/cancel', function (Request $request, Response $response, $args) {
    $data = $request->getParsedBody();
    $name = $_SESSION['todos'][$data['index']];
    $item = renderItem($name, $data['index']);
    $response->getBody()->write($item);

    return $response;
});

$app->run();
