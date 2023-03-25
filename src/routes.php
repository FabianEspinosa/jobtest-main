<?php
$router->options('/api/.*', function() {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
    http_response_code(200);
});

$router->get('/', 'HomeController@index');

$router->get('/api/user', 'UserController@index');
$router->get('/api/user/{id}', 'UserController@getItem');
$router->post('/api/user', 'UserController@create');
// Login Endpoint
$router->post('/api/login', 'UserController@login');
$router->post('/api/logout', 'UserController@logout');
// Movies Endpoints
$router->get('/api/movies', 'MovieController@index');
$router->get('api/movie/{id}', 'MovieController@show');
$router->post('api/movie/create', 'MovieController@create');
$router->post('api/movie/update/{id}', 'MovieController@update');
$router->delete('api/movie/{id}', 'MovieController@delete');


