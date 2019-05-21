## 项目简介
本项目包含两部分：
[php安全编码规范](https://github.com/momosecurity/rhizobia_P/wiki/php%E5%AE%89%E5%85%A8%E7%BC%96%E7%A0%81%E8%A7%84%E8%8C%83
)和PHP安全SDK，SDK介绍详见下述。

## 项目结构
```
├── composer.json
├── readme.md
└── src
    ├── DataSecurity
    │   ├── AESEncryptHelper.php                    //AES加解密
    │   ├── EncryptHelper.php
    │   └── RSAEncryptHelper.php                    //RSA加解密
    ├── EncoderSecurity
    │   ├── BaseEncoder.php
    │   ├── EncoderSecurity.php
    │   ├── HtmlEntityEncoder.php                   //html 实体编码
    │   └── JavaScriptEncoder.php                   //js编码
    ├── FileSecurity                                //上传文件安全校验
    │   ├── FileSecurity.php
    │   └── UploadedFileVerification.php
    ├── HTMLPurifier                                //xss payload过滤
    │   ├── HTMLPurifier
    │   ├── HTMLPurifier.php
    │   ├── HTMLPurifier_Default_config.php
    │   └── LICENSE
    ├── SLIM                                        //pdo增删改查封装
    │   ├── Clause
    │   ├── Database.php
    │   ├── LICENSE
    │   ├── Mysql.php
    │   ├── Statement
    │   ├── Statement.php
    │   └── docs
    ├── SecurityUtil.php
    └── URLSecurity
        ├── DefenseAgainstCSRF.php                  // csrf防护
        ├── DefenseAgainstRedirect.php              // 任意url重定向防护
        ├── DefenseAgainstSSRF.php                  // ssrf防护
        └── URLSecurity.php                         
```
 
## 目录
- [一、 安装](#jump10)
- [二、 调用说明](#jump20)
  - [2.1 CSRF](#jump1)
  - [2.2 XSS](#jump2)
  - [2.3 URL Redirect](#jump3)
  - [2.4 SQL Injection](#jump4)
  - [2.5 SSRF](#jump5)
  - [2.6 AES](#jump6)
  - [2.7 RSA](#jump7)
  - [2.8 上传文件安全校验](#jump8)
 
 
# <span id="jump10">一、 安装</span>  
### 1、composer.json配置依赖:
  ```
 "require": {
     "momosec/rhizobia": "1.1"
 },
"repositories":[
{"type":"vcs","url":"https://github.com/momosecurity/rhizobia_P.git"}]

 
  ```
### 2、安装依赖:
 
 ```
 composer install 
 ```
 
# <span id="jump20">二、 调用说明</span> 

## <span id="jump1">2.1 CSRF</span>

#### 1、前端获取cookie中植入的csrf_token字段使用POST方法提交:
```
function getCookie() {
    var value = "; " + document.cookie;
    var parts = value.split("; csrf_token=");
    if (parts.length == 2) 
        return parts.pop().split(";").shift();
}

$.ajax({
    type: "post",
    url: "/URL",
    data: {csrf_token:getCookie()},
    dataType: "json",
    success: function (data) {
        if (data.ec == 200) {
         //do something
        }
    }
});
```

#### 2、初始化:
```
$this->securityUtil=SecurityUtil::getInstance();
```

#### 3、后端验证token:
```
if(!$this->securityUtil->verifyCSRFToken()){
    return ;   //csrf token 校验失败
}
// 处理业务逻辑
```
**注意：** 受csrf_token生成方式影响，存在XSS问题时，可能会导致全局csrf防护失效。

## <span id="jump2">2.2 XSS</span>

### 1、初始化:
```
$this->securityUtil=SecurityUtil::getInstance();
```

### 2、输入过滤
```
$this->securityUtil->purifier($data);
```
以上会对$data做xss payload过滤。<br>
**说明：** 参考自[ezyang/htmlpurifier](https://github.com/ezyang/htmlpurifier.git)，psr-0改为psr-4，内容调整。<br>
**注意：**
为保证处理速度，还应对相应文件夹赋予写权限，用于保存缓存文件。
```
chmod -R 0755 /src/HTMLPurifier/HTMLPurifier/DefinitionCache/Serializer
```

### 3、输出编码

##### 1）输出数据到html:

```
$this->securityUtil->encodeForHTML($data)
```
以上会对$data做html实体编码

##### 2）输出数据到JavaScript:
```
$this->securityUtil->encodeForJavaScript($data)
```
以上会对$data做javaScript编码

## <span id="jump3">2.3 URL Redirect</span>

#### 1、初始化:

```
$this->securityUtil=SecurityUtil::getInstance();
```

#### 2、校验url:

```
$white=[".protect.domain"];
if(!$this->securityUtil->verifyRedirectUrl($url,$white)){
    return ;   //非法url
}
// 处理业务逻辑

```
其中verifyRedirectUrl函数默认参数$white值为array()，需设置白名单域名。<br>
**说明：** 该封装方法拒绝任何非http、https的URL。
## <span id="jump4">2.4 SQL Injection</span>

#### 1、获取数据库实例:
```
use Security\SQLSecurity\Mysql;  //引入需要的类



/**
 * 数据库连接配置信息
 */
$dbconf = array(
    "hostname" => "127.0.0.1",
    "port" => 3306,
    "database" => "oversold",
    "charset" => "utf8",
    "username" => "root",
    "password" => "toor",
);

$this->db = Mysql::getInstance()->initdb($dbconf);
```

数据库连接配置选项如下：
```
$config["hostname"]                //mysql地址
$config["port"]                    //mysql端口
$config["database"]                //使用的数据库
$config["timeout"]                 //超时时间，默认1s
$config["charset"]                 //字符集，默认UTF8
$config["ATTR_ERRMODE"]            //错误级别，默认PDO::ERRMODE_EXCEPTION
$config["ATTR_DEFAULT_FETCH_MODE"] //数据提取模式，默认PDO::FETCH_ASSOC
$config["ATTR_PERSISTENT"]         //是否启用持久连接，默认不启用
$config["username"]                //用户名
$config["password"]                //密码

```

#### 2、增删改查:

```
//查询
$result=$this->db->select()->from("oversolod")->where("id","=",$id)->execute()->fetchAll();
//删除
$result=$this->db->delete()->from("oversolod")->where("name","like","%".$name."%")->execute();
//插入
$result=$this->db->insert(array("name","age"))->into("oversolod")->values(array($name,$age))->execute();
//更新
$result=$this->db->update(array("name" => $name))->table("oversolod")->where("id", "=", $id)->execute();

```

## <span id="jump5">2.5 SSRF</span>

#### 1、初始化:

```
$this->securityUtil=SecurityUtil::getInstance();
```

#### 2、校验url:

```
if(!$this->securityUtil->verifySSRFURL($url)){
    return ;   //非法url
}
// 开始处理业务逻辑

```

## <span id="jump6">2.6 AES</span>

#### 1、初始化:

```
$this->securityUtil=SecurityUtil::getInstance();
```

#### 2、设置初始化密钥:

```
$this->securityUtil->initAESConfig($key);
```

#### 3、根据初始化密钥生成加密密钥:

```
//$uuid 用户唯一身份标识
$pwd = $this->securityUtil->createSecretKey($uuid); 
```

#### 4、AES加密:

```
$data = $this->securityUtil->aesEncrypt($data, $pwd);
```

#### 5、AES解密:

```
//$pwd为第三步生成的加密密钥
$result = $this->securityUtil->aesDecrypt($data, $pwd); 
```

## <span id="jump7">2.7 RSA</span>

#### 1、初始化:

```
$this->securityUtil=SecurityUtil::getInstance();
```

#### 2、初始化公私钥:

```
$this->securityUtil->initRSAConfig(dirname(__FILE__)."/pri.key",dirname(__FILE__)."/pub.key");

```

#### 3、公钥加密、私钥解密:

```
//公钥加密
$result=$this->securityUtil->rsaPublicEncrypt($data ); 
//私钥解密
$result= $this->securityUtil->rsaPrivateDecrypt($data); 

```

#### 4、私钥加密、公钥解密:

```
//私钥加密
$result=$this->securityUtil->rsaPrivateEncrypt($data ); 
//公钥解密
$result= $this->securityUtil->rsaPublicDecrypt($data); 

```


## <span id="jump7">2.8 上传文件安全校验</span>

#### 1、初始化:

```
$this->securityUtil=SecurityUtil::getInstance();
```

#### 2、校验上传文件:

```
$config=array('limit'=>5 * 1024 * 1024, //允许上传的文件最大大小
    'type'=>array(                      //允许的上传文件后缀及MIME
         "gif"=>"image/gif",
         "jpg"=>"image/jpeg",
         "png"=>"image/png")
);

$file = $_FILES["file"];
$data=$this->securityUtil->verifyUploadFile($file, $config);
if($data['flag']!==true){
    return; //上传失败 
}
//生成新的文件名拼接$data['ext']上传到文件服务器
```




