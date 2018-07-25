<?php
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

final class USERController extends BaseController
{
    private function check_exist($id){
      $sql = "SELECT EXISTS (select * from user where id=:id) as success";
      $values = array("id"=>$id);
      $ret_val = $this->DB_SQL($sql, $values);
      if($ret_val["status"]["success"] == 0){
        return $response->withJson(array("status"=>"true"));
      }
      return $response->withJson(array("status"=>"false"));
    }
    public function user_post(Request $request, Response $response){
      $var = $request->getParsedBody();
      switch ($var['function']) {
        case 'id-check':
          $ret_var = $this->check_exist($var['id']);
          return $response->withJson(array("status"=>$ret_var));
          break;
        case 'sign-up':
          $input = $request->getParsedBody();
          return $response->getBody()->write($this->sign_up($input));
        break;
        case 'login':
          $input = $request->getParsedBody();
          return $response->getBody()->write($this->sign_in($input));
        break;
        case 'sign-out' : break;
        case 'change-pw' : break;
        case 'forgot-pw' : break;
        case 'id-cancel' : break;
        default: break;
      }
    }
    public function forgotten_password(Request $request, Response $response){
      $sql = "SELECT passwd FROM USER WHERE passwd=:passwd";
    }
    public function password_change(Request $request, Response $response){

    }
    public function id_cancel(Request $request, Response $response){

    }
    public function verify_email(Request $request, Response $response, $args){
      $sql = "SELECT email_verify FROM user WHERE email_verify_link=:link";
      $sth = $this->db->prepare($sql);
      $sth->bindParam("link", $args['id']);
      $sth->execute();
      $dbdata = $sth->fetch();
      if($dbdata == null){
        return $response->withJson(array("status"=>"Error","Msg"=>"LINK Error"));
      }
      else if($dbdata['Email_verify'] == '1'){
        return $response->withJson(array("status"=>"Error","Msg"=>"Already Verified"));
      }
      $sql = "UPDATE user SET email_verify = '1' WHERE email_verify_link=:verify";
      $sth = $this->db->prepare($sql);
      $sth->bindParam("verify", $args['id']);
      $sth->execute();
      //return $response->getBody()->write(array("status"=>"Error","Msg"=>"Email_verify");
      return $response->withJson(array("status"=>"OK","Msg"=>"Email_verify"));
    }

    public function sign_up($input){
      $Random_val = mt_rand(100000,999999);
      $URL = $this->server_url."/user/signup/";
      $email_address = $input['email'];
      $email_subject = 'USER Authentication Mail';
      $email_body    = "Hi! Your Authentication Link is\n".(string)$URL.(string)$Random_val."\nWelcome!!";
      if(!send_mail($email_address, $email_subject, $email_body)){
        return json_encode(array("status"=>"Error", "Msg"=>"Mail Send Error"));
      }
      $sql = "INSERT INTO user (email_verify_link, fname, lname, id,  email, hash_passwd) VALUES (:link, :fname, :lname, :id,  :email, :passwd)";
      $sth = $this->db->prepare($sql);
      $hash = password_hash($input['passwd'], PASSWORD_DEFAULT);
      $sth->bindParam("link", $Random_val);
      $sth->bindParam("fname", $input['fname']);
      $sth->bindParam("lname", $input['lname']);
      $sth->bindParam("email", $input['email']);
      $sth->bindParam("id",$input['id']);
      $sth->bindParam("passwd",$hash);
      try{
        $sth->execute();
      }catch(PDOException $e){
        return $e->getMessage();
        #$response->withJson( array("status"=>$e->getMessage()) );
        die;
      }
        $retval['status'] = "OK";
        //$retval['Msg'] = $input['email'];
      return json_encode($retval);
    }
#intval();
    public function sign_in($input){
      $sql = "SELECT * FROM user WHERE id=:id";
      try{
        $sth = $this->db->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_ASSOC);
        $sth->bindParam("id", $input['id']);
        $sth->execute();
        $dbdata = $sth->fetch();
      } catch(PDOException $e){ #Query Error
        $Msg_body = array("status"=>"Error","Msg"=>$e->getMessage());
        return json_encode($Msg_body);
      }
      try{ #Check Error
        if(!$dbdata){ #if "email" exist in DB
          throw new Exception('ID error');
        }if(!$dbdata['hashed_passwd']){ #DB Save Error
          throw new Exception('DB Error');
        }if(!password_verify($input['passwd'], $dbdata['hashed_passwd'])){
          #Password is diff with DB
          throw new Exception('Password Error');
        }if($dbdata['email_verify']==0){
          #if Email Not verified
          throw new Exception('Verify_Email');
        }
        //if not Wrong for every
        $dbdata["status"] = "OK";
        return json_encode($dbdata);
      }catch(Exception $e){
        #if occur some internal errorc
        $Msg_body = array("status"=>"Error","Msg"=>$e->getMessage());
        return json_encode($Msg_body);
      }
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
