<?php

$router->get('/', 'HomeController@index');

$router->get('/api/user', 'UserController@index');
$router->get('/api/user/{id}', 'UserController@getItem');
$router->post('/api/user', 'UserController@create');
