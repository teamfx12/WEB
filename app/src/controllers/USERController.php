<?php
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

final class USERController extends BaseController
{
    public function user_post(Request $request, Response $response){
      $input = $request->getParsedBody();
      $function = $input['function'];
      unset($input['function']);
      switch ($function) {
        case 'mail-check':
          $ret_val = $this->check_exist($input['email']);
          return $response->withJson(array("status"=>$ret_val));
          break;
        case 'sign-up':
          return $response->getBody()->write($this->sign_up($input));
        break;
        case 'sign-in':
          return $response->getBody()->write($this->sign_in($input));
        break;
        case 'find-pw':
          return $response->getBody()->write($this->find_pw($input));
        break;
        case 'delet-account':
          return $response->getBody()->write($this->delete_account($input));
        break;
        case 'change-pw' :
          return $response->getBody()->write($this->change_pw($input));
        break;
        default: break;
      }
    }
    private function check_exist($values){
      $sql = "SELECT EXISTS (select * from user where email=:email) as success";
      //$sql = "SELECT EXISTS"."(select * from user where ".$value_keys[0]."=:".$value_keys[0].") as success";
      $ret_val = $this->DB_SQL($sql, array("email"=>$values));
      if(strcasecmp($ret_val['success'],'0')!=0){
        return "true";
      }
      return "ok";
    }
    public function find_pw($input){
      $ret_val = $this->check_exist($input['email']);
      if(strcasecmp($ret_val,'true')!=0){
        return json_encode(array("status"=>"error", "msg"=>"Mail not exist"));
      }
      $random_val = mt_rand(100000,999999);
      $email_address = $input['email'];
      $email_subject = 'Temporary password Mail';
      $email_body    = "Hi! Your temporary password is\n".(string)$random_val."\n";
      if(!send_mail($email_address, $email_subject, $email_body)){
        return json_encode(array("status"=>"error", "msg"=>"Mail Send Error"));
      }
      $sql = "UPDATE user SET is_temp = '1', hash_passwd=:passwd WHERE email=:email";
      $hash_pw = password_hash($random_val, PASSWORD_DEFAULT);
      $this->DB_SQL($sql, array("email"=>$input['email'], "passwd"=>$hash_pw));
      return json_encode(array("status"=>"ok"));
    }
    public function delete_account($input){
      $ret_val['status'] = "error";
      $msg = $this->verify_token($input['token'], $input['passwd']);
      if($msg != null){
        $ret_val['msg'] = $msg;
        return json_encode($ret_val);
      }
      $sql = "DELETE FROM user WHERE token=:token";
      $this->DB_SQL($sql,array("token"=>$input['token']));
      $ret_val['status'] = "ok";
      return json_encode($ret_val);
    }
    public function change_pw($input){
      #currentpw
      #newpw
      $ret_val['status'] = "error";
      $msg = $this->verify_token($input['token'], $input['currentpw']);
      if($msg != null){
        $ret_val['msg'] = $msg;
        return json_encode($ret_val);
      }
      $sql = "UPDATE user SET is_temp = '0', hash_passwd=:hash_passwd WHERE token=:token";
      $this->DB_SQL($sql,array("token"=>$input['token'],"hash_passwd"=>password_hash($input['newpw'],PASSWORD_DEFAULT)));
      $ret_val['status'] = "ok";
      return json_encode($ret_val);
    }
    public function verify_email(Request $request, Response $response, $args){
      $sql = "SELECT email_verify FROM user WHERE email_verify_nonce=:link";
      $dbdata = $this->DB_SQL($sql, array("link"=>$args['id']));
      if($dbdata == null){
        return $response->withJson(array("status"=>"Error","msg"=>"LINK Error"));
      }
      else if($dbdata['email_verify'] == '1'){
        return $response->withJson(array("status"=>"Error","msg"=>"Already Verified"));
      }
      $sql = "UPDATE user SET email_verify = '1' WHERE email_verify_nonce=:verify";
      $this->DB_SQL($sql, array("verify"=>$args['id']));
      return $response->withJson(array("status"=>"ok","msg"=>"email_verify"));
    }

    public function sign_up($input){
      $random_val = mt_rand(100000,999999);
      $URL = $this->server_url."/user/signup/";
      $email_address = $input['email'];
      $email_subject = 'USER Authentication Mail';
      $email_body    = "Hi! Your Authentication Link is\n".(string)$URL.(string)$random_val."\nWelcome!!";
      if(!send_mail($email_address, $email_subject, $email_body)){
        return json_encode(array("status"=>"error", "msg"=>"Mail Send Error"));
      }
      $sql = "INSERT INTO user (usn, email_verify_nonce, fname, lname, email, hash_passwd) VALUES (:usn, :link, :fname, :lname, :email, :passwd)";
      $input['link'] = $random_val;
      $input['usn'] = $random_val;
      $input['passwd'] = password_hash($input['passwd'], PASSWORD_DEFAULT);
      $this->DB_SQL($sql,$input);
      $retval['status'] = "ok";
      return json_encode($retval);
    }
    public function sign_in($input){
      $is_exist = $this->check_exist($input['email']);
      $ret_val['status'] = "error";
      if(strcasecmp($is_exist,"true")!=0){ #if "email" exist in DB
          $ret_val['msg'] = "ID Error";
          return json_encode($ret_val);
      }
      $sql = "SELECT * FROM user WHERE email=:email";
      $dbdata = $this->DB_SQL($sql, array("email"=>$input['email']));
      if(!password_verify($input['passwd'], $dbdata['hash_passwd'])){
        $ret_val['msg'] = "Password Error";
        return json_encode($ret_val);
      }#Password is diff with debug
      else if($dbdata['email_verify']==0){
        $ret_val['msg'] = "verify_email";
        return json_encode($ret_val);
      }
      //if not Wrong for every
      $ret_val["fname"] = $dbdata["fname"];
      $ret_val["lname"] = $dbdata["lname"];
      $ret_val["email"] = $dbdata["email"];
      $ret_val["status"] = "ok";
      //$dbdata["status"] = "ok";
      //$dbdata["token_expire"] = date("Y/m/d H:i:s", strtotime('+1 hours'));
      //$dbdata["token"] = password_hash($ret_val["token_expire"], PASSWORD_DEFAULT);
      $ret_val["token_expire"] = date("Y/m/d H:i:s", strtotime('+1 hours'));
      $ret_val["token"] = password_hash($ret_val["token_expire"], PASSWORD_DEFAULT);
      $sql = "UPDATE user SET token=:token, token_expire=:token_expire WHERE email=:email";
      $this->DB_SQL($sql, array("token"=>$ret_val["token"],"token_expire"=>$ret_val["token_expire"],"email"=>$input['email']));
      if($dbdata["is_temp"] == 1){
          $ret_val["is_temp"] = 1;
      }else{
        $ret_val['is_temp'] = 0;
      }
      return json_encode($ret_val);
    }
}
function send_mail($email_address, $email_subject, $email_body){
  $mail = new PHPMailer;
  try{
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    //$mail->SMTPDebug = 0;
    $mail->SMTPAuth = true;
    $mail->Username = 'qi.iot.teamf@gmail.com';
    $mail->Password = 'wlapdlfxptmxm';#지메일테스트
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->setFrom('qi.iot.teamf@gmail.com', 'No-reply');
    $mail->addAddress($email_address, 'receiver');
    $mail->Subject = $email_subject;
    $mail->Body = $email_body;
    if (!$mail->send()) {
      return false;
    }
    return true;
  } catch (phpmailerException $e) {
    return $e->getMessage();
  }
}
