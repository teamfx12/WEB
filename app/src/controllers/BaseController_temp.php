<?php
namespace App\Controller;

use Slim\Container;


class BaseController
{
    protected $view;
    protected $logger;
    protected $flash;
    protected $em;  // Entities Manager
    protected $db;
    protected $server_url;
    private $private_key;
    public function __construct(Container $c)
    {
        $this->private_key = "teamf-iot";
        $this->view = $c->get('view');
        $this->logger = $c->get('logger');
        $this->flash = $c->get('flash');
        $this->em = $c->get('em');
        $this->db = $c->get('db');
        $this->server_url = "teamf-iot.calit2.net";
    }

    protected function DB_SQL($sql, $values){
      $sth = $this->db->prepare($sql);
      foreach($values as $key => &$value){
        $sth->bindParam($key, $value);
      }
      $sth->execute();
      if(strncasecmp($sql,"SELECT",6)==0){
        return $sth->fetch();
        //return;
      }
      return null;

    }
}
