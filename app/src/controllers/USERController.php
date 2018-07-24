<?php
namespace App\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

final class USERController extends BaseController
{
    #$app->delete('/todo/[{id}]', function ($request, $response, $args)
    /*public function Delete_User(Request $request, Response $response, $args){
      $sth = $this->db->prepare("DELETE FROM USER WHERE USER_LNAME=:LNAME");
      echo $args['id'];
      $sth->bindParam("LNAME", $args['id']);
      $sth->execute();
      $todos = $sth->fetchAll();
      return $response->withJson($todos);
    }*/
    public function user_post(Request $request, Response $response){
      $var_sel = $request->getParsedBody()['req'];
      switch ($var_sel) {
        case 'sign-up': break;
        case 'sign-in': break;
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

    public function sign_up(Request $request, Response $response){
      $input = $request->getParsedBody();
      $Random_val = mt_rand(100000,999999);
      $URL = $this->server_url + "/user/signup/";
      $email_address = $input['email'];
      $email_subject = 'USER Authentication Mail';
      $email_body    = "Hi! Your Authentication Link is\n".(string)$URL.(string)$Random_val."\nWelcome!!";
      if(!send_mail($email_address, $email_subject, $email_body)){
        return $response->withJson(array("status"=>"Error", "Msg"=>"Mail Send Error"));
      }
      $sql = "INSERT INTO user (email_verify_link, fname, lname, email, hashed_passwd) VALUES (:link, :fname, :lname, :email, :passwd)";
      $sth = $this->db->prepare($sql);
      $hash = password_hash($input['passwd'], PASSWORD_DEFAULT);
      $sth->bindParam("link", $Random_val);
      #$sth->bindParam("id", $input['account'], \PDO::PARAM_INT);
      $sth->bindParam("fname", $input['firstName']);
      $sth->bindParam("lname", $input['lastName']);
      $sth->bindParam("email", $input['email']);
      $sth->bindParam("hashed_passwd",$hash);
      #$sth->bindParam("email_verify",0);
      try{
        $sth->execute();
      }catch(PDOException $e){
        $response->getBody()->write($e);
        #$response->withJson( array("status"=>$e->getMessage()) );
        die;
      }
        $retval['status'] = "OK";
        $retval['Msg'] = $input['email'];
      return $response->withJson($retval);
    }
#intval();
    public function sign_in(Request $request, Response $response){
      $input = $request->getParsedBody();
      $sql = "SELECT * FROM user WHERE email=:email";
      try{
        $sth = $this->db->prepare($sql);
        $sth->setFetchMode(\PDO::FETCH_ASSOC);
        $sth->bindParam("email", $input['account']);
        $sth->execute();
        $dbdata = $sth->fetch();
      } catch(PDOException $e){ #Query Error
        $Msg_body = array("status"=>"Error","Msg"=>$e->getMessage());
        return $response->withJson($Msg_body);
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
        return $response->withJson($dbdata);
      }catch(Exception $e){
        #if occur some internal errorc
        $Msg_body = array("status"=>"Error","Msg"=>$e->getMessage());
        return $response->withJson($Msg_body);
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
