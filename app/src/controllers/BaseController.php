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
    protected function verify_token($token, $passwd){
      $sql = "SELECT token_expire, hash_passwd FROM user WHERE token=:token";
      $dbdata = $this->DB_SQL($sql, array("token"=>$token));
      if($dbdata == null){
        return "Wrong token";
      }
      $time_now = date("Y-m-d H:i:s");
      $time_target = $dbdata["token_expire"];
      if(strtotime($time_now) > strtotime($time_target)){
        return "Token expired";
      }
      if($passwd == null){
        return null;
      }
      if(!password_verify($passwd,$dbdata['hash_passwd'])){
        return "Current Passwd Error";
      }
      return null;
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
