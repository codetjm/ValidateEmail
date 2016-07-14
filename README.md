# ValidateEmail
Laravel   ValidateEmail Trait 使用SMTP验证邮箱是否存在
- 使用方法：
- 1、把文件放到 app/Http/Controller 目录下
- 2、在需要使用的controller文件中引入Trait ：
```sh
<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DemoController extends Controller {
use ValidateEmail;
```
- 3、在function中使用：
```sh
	public function checkEmail(Request $request)
	{
		$this->smtp_log=true;
		$email=$request->get('email');
		$result=$this->checkEmailAddress($email,'sender@yourdomain.com');
		return $result;
	}
```
邮箱正确返回：
```sh
{
validate: true,
code: "250",
error: "250 Mail OK ",
response: "250 Mail OK "
}
```
错误返回：
```sh
{
validate: false,
code: "550",
error: "收件人邮箱不存在"
}
```
示例地址：http://api.48m.org/validate/email/?email=1000@qq.com

----
### 特别提醒
如果使用自用域名，请务必通过域名的TXT记录设置SPF

参考链接：[什么是SPF？如何设置企业邮箱的SPF呢？（TXT记录）](http://service.exmail.qq.com/cgi-bin/help?subtype=1&id=20012&no=1000580)

 请将参考链接中的(mail.qq.com) 改成你自己的mail域名即可 
