<?php
// Routes
$app->get('/user/signup/{id}', 'App\Controller\USERController:verify_email')->setName('verify_email');
$app->post('/user/login', 'App\Controller\USERController:sign_in')->setName('signin');
$app->post('/user/signup', 'App\Controller\USERController:sign_up')->setName('signup');
$app->post('/user','App\Controller\USERController:user_post')->setName('user_post');
#$app->get('/', 'App\Controller\HomeController:dispatch')->setName('homepage');

#$app->Post('/post/', 'App\Controller\USER_controller:PostMsg') ->setName('PostMsg');
#$app->get('/USER_signup', 'App\Controller\HomeController:USER_signup')->setName('USER_signup');

#$app->post('/', 'App\Controller\USERController:POST')->setName('post');
#$app->post('/Authentication', 'App\Controller\USERController:Authentication')->setName('Authentication');

#$app->get('/INSERT', 'App\Controller\USER_controller:INSERT')->setName('signup');
#$app->get('/user/{id}', 'App\Controller\USERController:Test')->setName('TEST');

#$app->post('/user/login', 'App\Controller\USERController:POST')->setName('login');
#$app->post('/user/logout', 'App\Controller\USER_controller:singout')->setName('signout');

#$app->post('/test', 'App\Controller\HomeController:Test')->setName('getest');
#$app->get('/INSERT/{args}', function ($request, $response, $args) {
#    return $response->write("Hello " . $args['name']);
#});
