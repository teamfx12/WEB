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
    public function __construct(Container $c)
    {
        $this->view = $c->get('view');
        $this->logger = $c->get('logger');
        $this->flash = $c->get('flash');
        $this->em = $c->get('em');
        $this->db = $c->get('db');
        $this->server_url = "teamf-iot.calit2.net";
    }
    protected function Query_Msg($CMD, $TABLE, $COND){
      $SQL = $CMD + "" + "FROM" + "W";
      return $SQL;
    }
    protected function DB_SQL($SQL, $Values){
      $sth = $this->db->prepare($SQL);
      $sth->bindParam("link", $args['id']);
      $sth->execute();
      $dbdata = $sth->fetch();
      return null;
    }
}
