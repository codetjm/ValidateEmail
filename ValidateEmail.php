<?php namespace App\Http\Controllers;
use Log;
trait ValidateEmail
{
  
  public $smtp_log=false;
  public $smtp_socket;
  public $smtp_status;



  protected $smtp_error=[
      "421 Service Temporarily Unavailable"=>"服务器繁忙，请稍后重试。",
      "450 4.1.0"=>"请不要使用备用mx服务器，无法确认是否发送成功，请手动检查邮件是否接收成功。",
      "450 mailbox temporarily refuse"=>"邮箱服务器暂时拒绝接收此邮件，请稍后重试",
      "451 4.3.2"=>"邮箱服务器没有响应发送请求，请稍后重试",
      "451 4.7.1"=>"对方服务器采用了灰名单技术，要验证发件人身份，请等待5分钟发信 ，或联系对方加入白名单",
      "421 HL:ICC"=>"该IP同时并发连接数过大，超过了系统的限制，被临时禁止连接。请检查是否有用户发送病毒或者垃圾邮件，并降低IP并发连接数量",
      "421 HL:IHU"=>"该IP的发送频率过高，被临时挂起。请检查是否有用户不正当的发送行为。",
      "421 HL:IPB"=>"该IP在系统的临时黑名单中。请检查是否有用户不正当的发送行为。",
      "554 HL:IPB"=>"该IP不在系统的允许地址列表中。",
      "421 HL:TLD"=>"系统负载过高，暂停服务，请稍后重试。",
      "451 Time Out"=>"数据传输超时。",
      "550 MI:IMF"=>"发信人电子邮件地址不合规范。请参考http://www.rfc-editor.org/关于电子邮件规范的定义。",
      "503 MI:ANL"=>"本地用户发信需要身份认证",
      "553 MI:SUM"=>"发信人和认证用户不相同。",
      "553 MI:SPB"=>"此用户不在系统允许的发信用户列表里。",
      "452 MI:SPM"=>"被系统的反垃圾邮件策略禁止。",
      "452 MI:SFK"=>"伪造的发件人身份信息。",
      "451 MI:RBL"=>"发信IP位于一个或多个RBL里。请参考http://www.rbls.org/关于RBL的相关信息。",
      "450 MI:IPB"=>"发信IP被暂时禁止，请检查是否有用户发送病毒或者垃圾邮件。请访问：http://www.commtouch.com/Site/Resources/Check_IP_Reputation.asp解封IP。",
      "550 MI:IPB"=>"发信IP被永久禁止，请检查是否有用户发送病毒或者垃圾邮件。请访问：http://www.commtouch.com/Site/Resources/Check_IP_Reputation.asp解封IP。",
      "550 MI:SPF"=>"发信IP未被发送域的SPF许可。请参考http://www.openspf.org/关于SPF规范的定义。",
      "452 RP:RCL"=>"群发收件人数量超过了系统限额。",
      "553 RP:DRL"=>"转发服务被禁止。",
      "552 RP:SMS"=>"发送的信件大小超过了系统允许接收的最大限制。",
      "552 RP:UMS"=>"发送的信件大小超过了用户允许接收的最大限制。",
      "552 RP:UQT"=>"收件人信箱已满，您可以发送小于1KB的信件提醒收件人。",
      "553 RP:RPB"=>"收件人在系统的黑名单中。",
      "553 RP:RDN"=>"收件人被拒绝，用户不存在或禁止转发。",
      "451 RP:TRC"=>"该用户短期内发送了大量信件，超过了系统的限制，被临时禁止发信。请检查是否有用户发送病毒或者垃圾邮件。",
      "451 RP:RPH"=>"该用户短期内发送了大量信件，超过了系统的限制，被临时禁止发信。请检查是否有用户发送病毒或者垃圾邮件。",
      "451 DT:MRC"=>"该用户短期内发送了大量信件，超过了系统的限制，被临时禁止发信。请检查是否有用户发送病毒或者垃圾邮件。",
      "554 DT:MTH"=>"邮件的中转跳数超限，可能是邮件死循环。",
      "554 DT:SPM"=>"发送的邮件内容包含了未被许可的信息，或者被反垃圾邮件系统识别为垃圾邮件。请检查是否有用户发送病毒或者垃圾邮件。",
      "451 DT:OSL"=>"一段时间内发送了过多相同标题的信件，被临时禁止。",
      "552 DT:SMS"=>"发送的信件大小超过了系统允许接收的最大限制。",
      "552 DT:UMS"=>"发送的信件大小超过了用户允许接收的最大限制。",
      "552 DT:UQT"=>"收件人信箱已满，您可以发送小于1KB的信件提醒收件人。",
      "552 DT:MTS"=>"发送的信件大小小于系统允许接收的最小限制。",
      "451 DT:SUR"=>"信件正文包含了被SURBL禁止的URL链接地址，请参考http://www.surbl.org关于SURBL的相关信息。 ",
      "554 DT:SUM"=>"信封发件人和信头发件人不匹配。",
      "421-4.4.5"=>"服务器繁忙，请稍后重试。",
      "421-4.7.0"=>"IP 未列入 RCPT 网域的白名单，正在断开连接。",
      "450-4.2.1"=>"您尝试联系的用户目前接收邮件过于频繁。请稍后重发您的邮件。如果届时用户可以接收邮件，则系统将递送您的邮件。",
      "451-4.3.0"=>"邮件服务器暂时拒绝接收邮件。",
      "451-4.4.2"=>"超时 - 正在断开连接。",
      "451-4.5.0"=>"违反 SMTP 协议，请参阅 RFC 2821。",
      "452-4.2.2"=>"您尝试向其发送邮件的电子邮件帐户已超过配额。。",
      "452-4.5.3"=>"您的邮件包含的收件人过多。",
      "454-4.5.0"=>"违反 SMTP 协议，不允许在 STARTTLS 之后发出任何命令，请参阅 RFC 3207。",
      "454-4.7.0"=>"临时系统问题导致无法进行身份验证。请稍后重试。",
      "454-5.5.1"=>"不可重复执行 STARTTLS。",
      "501-5.5.2"=>"无法对响应进行解码。",
      "502-5.5.1"=>"未知命令过多，正在断开连接。",
      "503-5.5.1"=>"先执行 EHLO/HELO。",
      "503-5.7.0"=>"不允许更改身份。",
      "504-5.7.4"=>"无法识别身份验证类型。",
      "530-5.5.1"=>"需要进行身份验证。请点击此处以了解详情。",
      "530-5.7.0"=>"必须先发出 STARTTLS 命令。",
      "535-5.5.4"=>"该身份验证模式不允许使用可选参数。",
      "535-5.7.1"=>"需要应用专用密码。请点击此处以了解详情。",
      "550-5.1.1"=>"您尝试向其发送邮件的电子邮件帐户不存在。请再次仔细检查收件人的电子邮件地址，确保没有拼写错误或多余空格。",
      "550-5.2.1"=>"您尝试向其发送邮件的电子邮件帐户已被停用。",
      "550-5.4.5"=>"用户的每日 SMTP 中继量超出限制。有关 SMTP 中继发送限制的详细信息，请联系您的管理员或查看此文章。",
      "550-5.7.0"=>"已拒绝发送邮件。如果发件人帐户被停用，或没有在您的 Google Apps 网域中注册，就会出现此错误。",
      "550-5.7.1"=>"您用于发送邮件的 IP 无权直接向我们的服务器发送电子邮件。",
      "552-5.2.2"=>"您尝试向其发送邮件的电子邮件帐户已超过配额。",
      "552-5.2.3"=>"您的邮件超出了 Google 的邮件大小限制。请查看我们的邮件大小准则。",
      "553-5.1.2"=>"我们无法找到收件人的域名。请检查是否存在拼写错误，并确保您未在收件人电子邮件地址末尾输入空格、句号或其他标点符号。",
      "554-5.6.0"=>"邮件格式错误。未接受。",
      "554-5.7.0"=>"未经身份验证的命令过多。",
      "555-5.5.2"=>"语法错误。",
      "520 ip and spf record not match"=>"触犯spf规则，邮件被拒收",
      "554 Sender address rejected Access denied"=>"触犯同域认证规则，邮件被拒收。",
      "550 user not exist"=>"收信人地址不存在。请检查所发邮件收信人邮箱地址的准确性",
      "550 mailbox limit exceeded"=>"收信人邮箱已满。",
      "550 mail size limit exceeded"=>"发送的信件大小超过了对方的接收限制。",
      "550 sender in prision"=>"发信人邮箱地址被临时挂起,等15分钟后再重新发送邮件。",
      "550 #5.1.0"=>"收件人邮箱不存在",
      "550 User not found"=>"收件人邮箱不存在",
      "550 RC:LD"=>"收件人邮箱不存在",
      "551 5.1.1"=>"收件人邮箱不存在",
      "550 abcd"=>"收件人邮箱不存在",
      "550 Local User"=>"收件人邮箱不存在",
      "550 no such user"=>"收件人邮箱不存在",
      "550 5.7.0"=>"收件人邮箱不存在",
      "550 RC:NE"=>"收件人邮箱不存在",
      "550 no such user here"=>"收件人邮箱不存在",
      "550 5.1.1"=>"收件人邮箱不存在",
      "550 5.2.1"=>"收件人邮箱不存在",
      "550 5.4.1"=>"收件人邮箱不存在",
      "550 Requested action not taken"=>"收件人邮箱不存在",
      "550 no mailbox by that name is currently available"=>"收件人邮箱不存在",
      "550 No Such User Here"=>"收件人邮箱不存在",
      "550 Invalid recipient"=>"收件人邮箱不存在",
      "550 User suspended"=>"收件人用户邮箱被禁用或者不可以使用",
      "550 5.7.1"=>"中继限制,SMTP服务器拒绝服务,您可以没有权限向此服务器发送邮件",
      "550 Mailbox not found"=>"收件人邮箱不存在",
      "550 Not verified by SPF"=>"触犯spf规则，邮件被拒收",
      "554 SPF Failed"=>"触犯spf规则，邮件被拒收",
      "570 5.7.0"=>"触犯spf规则，邮件被拒收",
      "554 5.0.0 User no found"=>"收件人邮箱不存在",
      "554 delivery error"=>"收件人邮箱不存在"
  ];
  
  public function checkEmailAddress($email, $sender) {
    $log='';

    $senderHost=explode('@',$sender)[1];
    preg_match('/@(.*?)$/',$email,$match);
    if($match && $match[1]){
      $domain=$match[1];
    }else{
      return ['validate'=>false,'code'=>1001,'error'=>'邮箱地址格式不正确，邮箱地址必须包含@'];
    }
    if(!getmxrr($domain,$mxhosts,$weight)){
      return ['validate'=>false,'code'=>1002,'error'=>'邮箱域名不正确'];
    }
    if($mxhosts==false){
      $mxhosts=['163mx01.mxmail.netease.com'];
    }
//   使用 socket 连接邮箱服务器
    $connectStatus=false;
    foreach ($mxhosts as $mxhost){
        $socketAddress = $mxhost.':25';
        $errorNo = 0;
        $errorMsg = '';
        $this->smtp_socket = @stream_socket_client($socketAddress,$errorNo,$errorMsg,10,STREAM_CLIENT_CONNECT);
        if (is_resource($this->smtp_socket)) {
          $response = fgets($this->smtp_socket,2048);
          if($this->smtp_log){
            Log::info('create socket to:'.$socketAddress.'>>[success]');
            Log::info('create socket response:'.$response);
          }

          $connectStatus=true;
          $this->smtp_status='connect';
          break;
        }else{
          if($this->smtp_log){
            Log::error('create socket to:'.$socketAddress.'>>[error]');
          }
        }
    }

    if(!$connectStatus){
      if($this->smtp_log){
        Log::error('create socket for host fail ');
      }
      return ['validate'=>false,'code'=>1003,'error'=>'连接用户邮箱邮件服务器失败'];
    }else{
        $wait=false;
        $responseHELO=$this->sendCommand("helo ".$senderHost."\r\n",120,'helo');
        $wait=$this->checkSmtpStatus('helo');
        if($wait && $responseHELO && preg_match('/(\d+).*/is',$responseHELO,$matchHELO) && $matchHELO[1]==250){
          $responseFROM=$this->sendCommand("mail from:<" .$sender. ">\r\n",300,'mail');
          $wait=false;
          $wait=$this->checkSmtpStatus('mail');
          if($wait && $responseFROM && preg_match('/(\d+).*/is',$responseFROM,$matchFROM) && $matchFROM[1]==250){
            $responseRCPT=$this->sendCommand("RCPT TO:<" .$email. ">\r\n",300,'rcpt');
            $wait=false;
            $wait=$this->checkSmtpStatus('rcpt');
            if($wait && $responseRCPT && preg_match('/(\d+).*/is',$responseRCPT,$matchRCPT)){
              $this->sendCommand("RSET \r\n",300,'rest');
              $this->sendCommand("QUIT \r\n",30,'quit');
              $this->socket_close();
              $result=preg_match('/^(\d+)[ -]?(\w+:\w+|[#\d\.]+|[ a-zA-Z]+)/is',$responseRCPT,$matchResult);
              if($result && array_key_exists($matchResult[0],$this->smtp_error)){
                return ['validate'=>false,'code'=>$matchRCPT[1],'error'=>$this->smtp_error[$matchResult[0]]];
              }else{
                $firstCode=substr($responseRCPT,0,1);
                if($firstCode==2){
                  return ['validate'=>true,'code'=>$matchRCPT[1],'error'=>$matchRCPT[0],'response'=>$responseRCPT];
                }else{
                  return ['validate'=>false,'code'=>$matchRCPT[1],'error'=>$matchRCPT[0],'response'=>$responseRCPT];
                }
              }

            }else{
              $this->socket_close();
             return ['validate'=>false,'code'=>'1004','error'=>$responseRCPT];
            }
          }else{
            $this->socket_close();
            return ['validate'=>false,'code'=>$matchFROM[1],'error'=>$matchFROM[0]];
          }
        }else{

//        send   helo command error or helo response not 250
          $this->socket_close();
          return ['validate'=>false,'code'=>$matchHELO[1],'error'=>$matchHELO[0]];
        }
    }
  }

  protected function checkSmtpStatus($current,$timeout=1,$retry=5){

    if($this->smtp_status==false || $retry==0){
      if($this->smtp_log){
        Log::info('check smtp status is '.$current.'  : false');
      }
      return false;
    }
    if($this->smtp_status!=$current){
      $retry=$retry-1;
      if($this->smtp_log){
        Log::info('check smtp status is '.$current.'  : wait');
      }
      sleep($timeout);
      return $this->checkSmtpStatus($current,$timeout,$retry);
    }else{
      if($this->smtp_log){
        Log::info('check smtp status is '.$current.'  : true');
      }
      return true;
    }
  }

  protected function socket_close(){
    fclose($this->smtp_socket);
  }

  protected function sendCommand($cmd,$timeout,$status){
    stream_set_timeout($this->smtp_socket, $timeout);
    $result=fwrite($this->smtp_socket, $cmd,1024);
    if ($result === false) {
      $this->smtp_status=false;
      if($this->smtp_log){
        Log::info('request : '.preg_replace('/\r\n/is','',$cmd).' ; timeout : '.$timeout.'  : status: '.$status.' result  : false');
      }
      return false;
    }else{
      Log::info('request : '.preg_replace('/\r\n/is','',$cmd).' ; timeout : '.$timeout.'  : status: '.$status.' result  : true');
      $response = fgets($this->smtp_socket,2048);
      if ($response === false) {
        $this->smtp_status=false;
        if($this->smtp_log){
          Log::info('response : '.preg_replace('/\r\n/is','',$cmd).'  ; timeout : '.$timeout.'  : status: '.$status.' result  : false');
        }
        return false;
      }else{
        if($this->smtp_log){
          Log::info('response : '.preg_replace('/\r\n/is','',$cmd).'  ; timeout : '.$timeout.'  ; status: '.$status.'; result  : true ;response  : '.$response);
        }
        $this->smtp_status=$status;
        return $response;
      }
    }

  }
}

?>
