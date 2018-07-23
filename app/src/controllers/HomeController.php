<?php
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class HomeController extends BaseController
{
    public function dispatch(Request $request, Response $response, $args)
    {
        $this->logger->info("Home page action dispatched");

        $this->flash->addMessage('info', 'Sample flash message');

        $this->view->render($response, 'home.twig');
        return $response;
    }
    public function viewPost(Request $request, Response $response, $args)
    {
        $this->logger->info("View post using Doctrine with Slim 3");

        $messages = $this->flash->getMessage('info');

        try {
            $post = $this->em->find('App\Model\Post', intval($args['id']));
        } catch (\Exception $e) {
            echo $e->getMessage();
            die;
        }

        $this->view->render($response, 'post.twig', ['post' => $post, 'flash' => $messages]);
        return $response;
    }
}


    /*public function POST(Request $request, Response $response)
    {
      $request_method = $request->getMethod();
      if((string)$request_method != "POST"){
        $response->getBody()->write("Wrong Method ONLY = POST".(string)$request_method);
        return -1;
      }
      #switch($request_method){
      $Body = $request->getParsedBody();

      if (json_decode($request->getBody()) == null){ #Handler for Wrong JSON Msg in Request Msg.
        $response->getBody()->write("Wrong JSON Msg");
        return -1;
      }
      #var_dump($Body['email']);
      $Random_val = mt_rand(100000,999999);
      $email_subject = 'USER Authentication Mail';
      $email_body    = "Hi! Your Authentication Link is ". "123123" ."\nWelcome!!";
      $Random_val = USER_mailing((string)$Body['email'], $email_subject, $email_body);
      #$Random_val = USER_mailing("Error");
      $Body['Random_val'] = $Random_val;
      $response->getBody()->write(json_encode($Body));
      $response->withHeader('Content-Type', 'application/json; charset=utf-8');
      //echo (string)$ParsedBody->"key1";
    }*/
