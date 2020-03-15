<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Nette\Mail\Message;
use Slim\Http\UploadedFile;
require_once('functions.php');
return function (App $app) {
    $container = $app->getContainer();
    //blog routes

    $app->get('/', function (Request $request, Response $response, array $args) use ($container) {

        $container->get('logger')->info("Slim-Skeleton '/' route");
        $data=[];
        $data["post"]=$this->db->connection()->select("SELECT article.title,article.text,article.date,article.sef_link,article.image as 'img',user.name,user.surname,user.image,user.username,category.category_name,category.sef_link as 'cat_sef_link' FROM article INNER JOIN category on article.cat_id=category.category_id INNER JOIN user on user.id=article.user_id WHERE article.status=1 ORDER by article.date DESC LIMIT 0,10");    
        $data["slider"]=$this->db->connection()->select("SELECT article.title,article.text,article.date,article.sef_link,article.image as 'img',user.name,user.surname,user.image,user.username,category.category_name,category.sef_link as 'cat_sef_link' FROM article INNER JOIN category on article.cat_id=category.category_id INNER JOIN user on user.id=article.user_id WHERE article.status=1 and article.slider=1 LIMIT 5");    
        $data["category"]=$this->db->table('category')->get();
        $data["popular"]=$this->db->table('article')->where('status', 1)->orderBy('visitor', 'desc')->take(5)->get();
        $data['len']=$this->db->table('article')->where('status', 1)->count()/10;
        $user=$this->db->table('user')->where('status', 1)->get();
        $data["url"]=$container->get("settings")['url'];
        // Render index view
        return $container->get('renderer')->render($response, 'index.phtml', $data);
    });
    $app->get('/settings', function (Request $request, Response $response, array $args) use ($container) {

        $container->get('logger')->info("Slim-Skeleton '/' route");
        $data=[];
        $data["post"]=$this->db->connection()->select("SELECT article.title,article.text,article.date,article.sef_link,article.image as 'img',user.name,user.surname,user.image,user.username,category.category_name,category.sef_link as 'cat_sef_link' FROM article INNER JOIN category on article.cat_id=category.category_id INNER JOIN user on user.id=article.user_id WHERE article.status=1 ORDER by article.date DESC LIMIT 0,10");    
        $data["slider"]=$this->db->connection()->select("SELECT article.title,article.text,article.date,article.sef_link,article.image as 'img',user.name,user.surname,user.image,user.username,category.category_name,category.sef_link as 'cat_sef_link' FROM article INNER JOIN category on article.cat_id=category.category_id INNER JOIN user on user.id=article.user_id WHERE article.status=1 and article.slider=1 LIMIT 5");    
        $data["category"]=$this->db->table('category')->get();
        $data["popular"]=$this->db->table('article')->where('status', 1)->orderBy('visitor', 'desc')->take(5)->get();
        $data['len']=$this->db->table('article')->where('status', 1)->count()/10;
        $user=$this->db->table('user')->where('status', 1)->get();
        $data["url"]=$container->get("settings")['url'];
        // Render index view
        return $container->get('renderer')->render($response, 'settings.phtml', $data);
    });
    $app->post('/settings/update', function (Request $request, Response $response, array $args) use ($container) {
        $body=$request->getParsedBody();
        $user=$this->db->table('user')->where('id',$_SESSION['userid'])->first();
        if (password_verify($body['oldpassword'],$user->password)==true){
            $this->db->table('user')->where('id',$_SESSION['userid'])->update(['password'=>password_hash($body["newpassword"],PASSWORD_BCRYPT)]);
            return $response->withJson(["success"=>"ok"]);
        }else{
            return $response->withJson(["success"=>"no","password"=>password_verify($body['oldpassword'],$user->password)]);
        }
        
    });
    $app->get('/page/{page}', function (Request $request, Response $response, array $args) use ($container) {
        $page=$args['page']*10;
        $container->get('logger')->info("Slim-Skeleton '/' route");
        $data=[];
        $data["post"]=$this->db->connection()->select("SELECT article.title,article.text,article.date,article.sef_link,article.image as 'img',user.name,user.surname,user.image,user.username,category.category_name,category.sef_link as 'cat_sef_link' FROM article INNER JOIN category on article.cat_id=category.category_id INNER JOIN user on user.id=article.user_id WHERE article.status=1 ORDER by article.date DESC LIMIT ".$page.",10");
        $data["slider"]=$this->db->connection()->select("SELECT article.title,article.text,article.date,article.sef_link,article.image as 'img',user.name,user.surname,user.image,user.username,category.category_name,category.sef_link as 'cat_sef_link' FROM article INNER JOIN category on article.cat_id=category.category_id INNER JOIN user on user.id=article.user_id WHERE article.status=1 and article.slider=1 LIMIT 5");      
        $data["category"]=$this->db->table('category')->get();
        $data["popular"]=$this->db->table('article')->where('status', 1)->orderBy('visitor', 'desc')->take(5)->get();
        $data['len']=$this->db->table('article')->where('status', 1)->count()/10;
        $user=$this->db->table('user')->where('status', 1)->get();
        $data["url"]=$container->get("settings")['url'];
        // Render index view
        return $container->get('renderer')->render($response, 'index.phtml', $data);
    });

    $app->get('/article/{link}', function (Request $request, Response $response, array $args) use ($container) {
        $link=$args['link'];
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");
        $data=[];
        $data["url"]=$container->get("settings")['url'];
        $data["post"]=$this->db->table('article')->where('status', 1)->where('sef_link', $link)->first();
        if (!isset($data['post'])) {
            return $response->withRedirect($container->get("settings")['url']);
        }
        if ($data['post']->status!=1) {
            return $response->withRedirect($container->get("settings")['url']);
        }
        $data['postcategory']=$this->db->table('category')->where("category_id",$data["post"]->cat_id)->first();
        $data['user']=$this->db->table('user')->where("id",$data["post"]->user_id)->first();
        $data["category"]=$this->db->table('category')->get();
        $data["comment"]=$this->db->table('comment')->where("id",$data["post"]->id)->get();
        $data["popular"]=$this->db->table('article')->where('status', 1)->orderBy('visitor', 'desc')->take(5)->get();

        // Render index view
        return $container->get('renderer')->render($response, 'article.phtml', $data);
    });
    $app->get('/category/{link}', function (Request $request, Response $response, array $args) use ($container) {
        $link=$args['link'];
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");
        $data=[];
        $data["onecategory"]=$this->db->table('category')->where('sef_link', $link)->first();
        if (!isset($data['onecategory'])) {
            return $response->withRedirect($container->get("settings")['url']);
        }
        $data["post"]=$this->db->table('article')->where('status', 1)->where('cat_id', $data["onecategory"]->category_id)->skip(0)->take(10)->get();
        $data["category"]=$this->db->table('category')->get();
        $data["popular"]=$this->db->table('article')->where('status', 1)->orderBy('visitor', 'desc')->take(5)->get();
        $data['len']=$this->db->table('article')->where('status', 1)->count()/10;
        $user=$this->db->table('user')->where('status', 1)->get();
        $data["url"]=$container->get("settings")['url'];
        // Render index view


        // Render index view
        return $container->get('renderer')->render($response, 'category.phtml', $data);
    });
    $app->get('/user/{user}', function (Request $request, Response $response, array $args) use ($container) {
        $link=$args['user'];
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");
        $data=[];
        $data["user"]=$this->db->table('user')->where('username', $link)->first();
        if ($data['user']->status==0) {
            return $response->withRedirect($container->get("settings")['url']);
        }
        $data['userdetail']=$this->db->table("user_detail")->where("user_id",$data['user']->id)->first();
        $data["category"]=$this->db->table('category')->get();
        $data["post"]=$this->db->table('article')->where('user_id', $data['user']->id)->where('status', 1)->get();
        $data["url"]=$container->get("settings")['url'];
        // Render index view
        // Render index view
        return $container->get('renderer')->render($response, 'profile.phtml', $data);
    });
    $app->get('/my-question', function (Request $request, Response $response, array $args) use ($container) {
        if(!isset($_SESSION) OR $_SESSION["giris"]!=1){
            return $response->withRedirect($container->get("settings")['url']."/login");    

        }
        $container->get('logger')->info("Slim-Skeleton '/' route");
        $data=[];
        $data["category"]=$this->db->table('category')->get();
        $data["post"]=$this->db->table('question')->where('user_id', $_SESSION['userid'])->get();
        $data["url"]=$container->get("settings")['url'];
        // Render index view
        // Render index view
        return $container->get('renderer')->render($response, 'myquestion.phtml', $data);
    });
    $app->get('/my-post', function (Request $request, Response $response, array $args) use ($container) {
        if(!isset($_SESSION) OR $_SESSION["giris"]!=1){
            return $response->withRedirect($container->get("settings")['url']."/login");    
        }
        $container->get('logger')->info("Slim-Skeleton '/' route");
        $data=[];
        $data["category"]=$this->db->table('category')->get();
        $data["post"]=$this->db->table('article')->where('user_id', $_SESSION['userid'])->get();
        $data["url"]=$container->get("settings")['url'];
        // Render index view
        // Render index view
        return $container->get('renderer')->render($response, 'mypost.phtml', $data);
    });
    //user question answer article route
    $app->get('/question-answer', function (Request $request, Response $response, array $args) use ($container) {

        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");
        $data=[];
        $data["category"]=$this->db->table('category')->get();
        $data["pcategory"]=$this->db->table('question_category')->get();
        $data["post"]=$this->db->table('question')->join('question_category', 'question_category.id', '=', 'question.cat_id')->skip(0)->take(20)->get();
        $data['len']=$this->db->table('article')->where('status', 1)->count()/20;
        $data["url"]=$container->get("settings")['url'];

        return $container->get('renderer')->render($response, 'question.phtml', $data);
    });
    $app->get('/question-answer/page/{page}', function (Request $request, Response $response, array $args) use ($container) {
        $page=$args['page']*10;
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");
        $data=[];
        $data["category"]=$this->db->table('category')->get();
        $data["pcategory"]=$this->db->table('question_category')->get();
        $data["post"]=$this->db->table('question')->join('question_category', 'question_category.id', '=', 'question.cat_id')->skip($page)->take(20)->get();
        $data['len']=$this->db->table('article')->where('status', 1)->count()/20;
        $data["url"]=$container->get("settings")['url'];
        return $container->get('renderer')->render($response, 'question.phtml', $data);
    });
    
    $app->get('/question-answer/category/{link}', function (Request $request, Response $response, array $args) use ($container) {
        $link=$args['link'];
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");
        $data=[];
        $data["onecategory"]=$this->db->table('question_category')->where('sef_link', $link)->first();
        if (!isset($data['onecategory'])) {
            return $response->withRedirect($container->get("settings")['url']);
        }
        $data["post"]=$this->db->table('question')->where('cat_id', $data["onecategory"]->id)->skip(0)->take(10)->get();
        $data["category"]=$this->db->table('category')->get();
        $data["popular"]=$this->db->table('article')->where('status', 1)->orderBy('visitor', 'desc')->take(5)->get();
        $data['len']=$this->db->table('question')->where('cat_id', $data["onecategory"]->id)->count()/10;
        $user=$this->db->table('user')->where('status', 1)->get();
        $data["url"]=$container->get("settings")['url'];
        return $container->get('renderer')->render($response, 'questioncategory.phtml', $data);
    });
    $app->get('/question-answer/{link}', function (Request $request, Response $response, array $args) use ($container) {

        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");
        $data=[];
        $data["category"]=$this->db->table('category')->get();
        $data["pcategory"]=$this->db->table('question_category')->get();
        $data["post"]=$this->db->table('question')->join('question_category', 'question_category.id', '=', 'question.cat_id')->join('user', 'user.id', '=', 'question.user_id')->where("question.question_sef_link",$args['link'])->first();
        $data["comment"]=$this->db->table("answer")->join('user', 'user.id', '=', 'answer.user_id')->where("question_id",$data["post"]->id)->get();
        $data['len']=$this->db->table('article')->where('status', 1)->count()/10;
        $data["url"]=$container->get("settings")['url'];

        return $container->get('renderer')->render($response, 'questiondetail.phtml', $data);
    });
    $app->get('/new-question',function (Request $request, Response $response, array $args) use ($container) {
        if(!isset($_SESSION) OR $_SESSION["giris"]!=1){
            return $response->withRedirect($container->get("settings")['url']."/login");    

        }
        
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");
        $data=[];
        $data["category"]=$this->db->table('category')->get();
        $data["qcategory"]=$this->db->table('question_category')->get();
        $data["url"]=$container->get("settings")['url'];

        return $container->get('renderer')->render($response, 'newquestion.phtml', $data);
    });
    $app->post('/new-question', function (Request $request, Response $response, array $args) use ($container) {
        $body=$request->getParsedBody();
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");
        $insert=$this->db->table("question")->insert(["id"=>null,
            "title"=>$body["title"],
            "text"=>$body["text"],
            "user_id"=>$_SESSION['userid'],
            "question_sef_link"=>str_replace(" ","-",$body['title'])."-".random_int(0,1000000000),
            "cat_id"=>$body['category'],
            "date"=>date("Y-m-d")
        ]);
        if ($insert){
            return $response->withJson(["success"=>"ok"]);
        }else{
            return $response->withJson(["success"=>"no"]);
        }
    });
    $app->post('/answer/add', function (Request $request, Response $response, array $args) use ($container) {
        $bodyparameter=$request->getParsedBody();
        
        if(isset($_SESSION)){
            if($_SESSION["giris"]==1){
               $saveanswer= $this->db->table("answer")->create([
                    'question_id'=>$bodyparameter['qid'],
                    'user_id'=>$_SESSION['userid'],
                    'answer'=>$bodyparameter['answer']
                ]);
               if ($saveanswer){
                   return $response->withJson("ok");
               }else{
                   return $response->withJson("error");
               }
            }else{
                return $response->withJson("error");
            }
        }else{
            return $response->withJson("error");
        }
    });
    $app->get('/new-article', function (Request $request, Response $response, array $args) use ($container) {
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");
        if(!isset($_SESSION['giris'])){
            return $response->withRedirect($container->get("settings")['url']."/login");
        }
        $data=[];
        $data["category"]=$this->db->table('category')->get();
        $data["url"]=$container->get("settings")['url'];

        return $container->get('renderer')->render($response, 'newpost.phtml', $data);

    });
    $app->post('/new-article', function (Request $request, Response $response, array $args) use ($container) {
        $body=$request->getParsedBody();
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");
        $insert=$this->db->table("article")->insert(["id"=>null,
            "title"=>$body["title"],
            "status"=>0,
            "text"=>$body["text"],
            "user_id"=>$_SESSION['userid'],
            "sef_link"=>str_replace("?","",str_replace(" ","-",$body['title'])."-".random_int(0,1000000000)),
            "cat_id"=>$body['category'],
            "image"=>$body["image"],
            "date"=>date("Y-m-d"),
            "status"=>0,
            "visitor"=>0,
            'slider'=>0
        ]);
        if ($insert){
            return $response->withJson(["success"=>"ok"]);
        }else{
            return $response->withJson(["success"=>"no"]);
        }
    });    
    $app->post('/upload-photo', function (Request $request, Response $response, array $args) use ($container) {
        $directory ="templates/images";

        $uploadedFiles = $request->getUploadedFiles();

        // handle single input with single file upload
        $uploadedFile = $uploadedFiles['file'];
        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $filename = moveUploadedFile($directory, $uploadedFile);
             return $response->withJson(["success"=>"ok","file"=>$filename]);
        }else{
            return $response->withJson(["success"=>"no"]);
        }
    });
 
    //login register logout
    $app->get('/login', function (Request $request, Response $response, array $args) use ($container) {
        if(isset($_SESSION["giris"]) and $_SESSION["giris"]==1){
            return $response->withRedirect($container->get("settings")['url']);
        }
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");
        $data=[];
        $data['url']=$container->get("settings")['url'];
        // Render index view
        return $container->get('renderer')->render($response, 'login/login.phtml', $data);
    });
    $app->post('/login', function (Request $request, Response $response, array $args) use ($container){
        $body=$request->getParsedBody();
        $user=$this->db->table("user")->where("email",$body['email'])->first();
            if (password_verify($body['password'],$user->password)){
                $_SESSION["giris"]=1;
                $_SESSION['username']=$user->username;
                $_SESSION['userid']=$user->id;
                $_SESSION['status']=$user->status;
                if ($user->status==1){
                   return $response->withJson(['success'=>'ok',"link"=>"admin"]);
                }else{
                   return $response->withJson(['success'=>'ok',"link"=>""]);
                }

            }else{
                return $response->withJson(['success'=>'no']);
            }
    });

    $app->get('/register', function (Request $request, Response $response, array $args) use ($container) {
        if(isset($_SESSION["giris"])  and $_SESSION["giris"]==1){
                return $response->withRedirect("/");
            }
        
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");
        $data=[];
        $data['url']=$container->get("settings")['url'];
        // Render index view
        return $container->get('renderer')->render($response, 'login/register.phtml', $data);
    });
    $app->post('/register', function (Request $request, Response $response, array $args) use ($container) {
        $body=$request->getParsedBody();
        $user=$this->db->table("user")->where("email",$body['email'])->get();
        $activCode=md5(date('d.m.Y H:i:s'));
        if(count($user)==0){
            $register=$this->db->table("user")->insert([
                "id"=>null,
                "name"=>$body["name"],
                "surname"=>$body["surname"],
                "username"=>$body["username"],
                "email"=>$body['email'],
                "password"=>password_hash($body["password"],PASSWORD_BCRYPT),
                "securitykey"=>$activCode,
                "status"=>1,
                "image"=>"person0.png"
            ]);
            if ($register){
                $user=$this->db->table("user")->where("email",$body['email'])->first();
                $this->db->table("user_detail")->insert([
                    "id"=>null,
                    "user_id"=>$user->id,
                    "about_me"=>"profil sayfasını düzenleyiniz lütfen",
                    "facebook"=>"#",
                    "instagram"=>"#",
                    "twitter"=>"#",
                    "youtube"=>"#" ]);
                /*
                $mail = new Message;
                $mail->setFrom("info@pythonhacisi.com")
                ->addTo($body['email'])
                ->setSubject('HESAP AKTİVASYONU')
                ->setBody("Merhaba Sayın".$body['name']." ".$body['surname'].' sitemize uye oldugunuz için teşekkür ederiz son 
                bir adım kaldı mail adresinizi dogrulamanız lazım.
                mail adresiniz dogrulamak için <br> <a href="'.$container->get("settings")['url'].'/validate-email/'.$activCode.'"> Buraya Tıklayınız</a> <br>');

                if($this->mailer->send($mail)){
                    return $response->withJson(["success"=>"ok"]);
                }*/
                return $response->withJson(["success"=>"ok"]);
            }else{
                return $response->withJson(["success"=>"no","err"=>"kayıtta hata oluştu"]);
            }
        }
        else{
            return $response->withJson(["success"=>"no","err"=>"email daha once kullanılmıştır"]);
        }
    });
    $app->get('/logout', function (Request $request, Response $response, array $args) use ($container) {


        session_destroy();
       return $response->withRedirect($container->get("settings")['url']);
    });
    $app->get('/validate-email/{link}', function (Request $request, Response $response, array $args) use ($container) {
        $link=$args['link'];
        $validate=$this->db->table("user")->where("securitykey",$link)->first();
        $data["url"]=$container->get("settings")['url'];
        if (count($validate)==1){
            $data["success"]=1;
            $data['message']="hesabınız aktif edildi";
        }else{
            $data["success"]=0;
            $data['message']="hesabınız aktif edilemedi.güvenlik kodunuz geçersiz veya süresi dolmuş olabilir.<br>yeni güvenlik kodu alabilirsiniz.";
        }
        return $container->get('renderer')->render($response, 'login/active.phtml', $data);
    });
    $app->get('/forgot-password', function (Request $request, Response $response, array $args) use ($container) {
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");
        $data=[];
        $data['url']=$container->get("settings")['url'];
        // Render index view
        return $container->get('renderer')->render($response, 'login/renewpassword.phtml', $data);
    });
    $app->post('/forgot-password', function (Request $request, Response $response, array $args) use ($container) {
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");

        // Render index view
        return $container->get('renderer')->render($response, 'login/renewpassword.phtml', $args);
    });
    $app->get('/renew-password/{link}', function (Request $request, Response $response, array $args) use ($container) {
        // Sample log message
        $data=[];
        $data['url']=$container->get("settings")['url'];
        // Render index view
        return $container->get('renderer')->render($response, 'login/newpassword.phtml', $data);
    });
    $app->post('/renew-password', function (Request $request, Response $response, array $args) use ($container) {
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");

        // Render index view
        return $container->get('renderer')->render($response, 'index.phtml', $args);
    });

    $mw = function ($request, $response, $next) use ($container) {
        if (!isset($_SESSION)) {
            return $response->withRedirect($container->get("settings")['url']."/login");
        }
        if ($_SESSION['status']!=2) {
            return $response->withRedirect($container->get("settings")['url']);
        }
        return $next($request,$response);
    };
    $app->group('/admin', function () use ($app,$container) {
        /*if (isset($_SESSION)) {
            return $response->withRedicect($container->get("settings")['url']);
        }
        if ($_SESSION['status']!=2) {
            return $response->withRedicect($container->get("settings")['url']);
        }*/

        $app->get('', function ($request, $response, $args) use ($container){
            $container->get('logger')->info("Slim-Skeleton '/' route");
            $args=[];
            $args['url']=$container->get("settings")['url'];
            // Render index view
            return $container->get('renderer')->render($response, 'admin/index.phtml', $args);
        });
        $app->get('/preview/{id}', function ($request, $response, $arg) use ($container){
            $container->get('logger')->info("Slim-Skeleton '/' route");
            $link=$arg['id'];
            $args=[];
            $args['post']=$this->db->table('article')->where('id',$link)->first();
            $args['url']=$container->get("settings")['url'];
            $args['postcategory']=$this->db->table('category')->where("category_id",$args["post"]->cat_id)->first();
            $args['user']=$this->db->table('user')->where("id",$args["post"]->user_id)->first();
            $args["category"]=$this->db->table('category')->get();
            $args["comment"]=$this->db->table('comment')->where("id",$args["post"]->id)->get();
            $args["popular"]=$this->db->table('article')->where('status', 1)->orderBy('visitor', 'desc')->take(5)->get();
            // Render index view
            return $container->get('renderer')->render($response, 'article.phtml', $args);
        });
        $app->get('/post', function ($request, $response, $args) use ($container){
            $container->get('logger')->info("Slim-Skeleton '/' route");
            $args=[];
            $args["post"]=$this->db->connection()->select("SELECT article.id,article.title,article.status,article.text,article.date,user.name,user.surname,user.username,category.category_name FROM article INNER JOIN category on article.cat_id=category.category_id INNER JOIN user on user.id=article.user_id ORDER by article.status Asc,article.date Desc");
            $args['url']=$container->get("settings")['url'];
            // Render index view
            return $container->get('renderer')->render($response, 'admin/post.phtml', $args);
        });
        $app->get('/post/submit/{id}', function ($request, $response, $arg) use ($container){
            $container->get('logger')->info("Slim-Skeleton '/' route");
            $link=$arg['id'];
            $args=[];
            $islem=$this->db->table("article")->where("id",$link)->update(["slider"=>1]);
            if ($islem){
            return $response->withJson(["success"=>"ok"]);
            }else{
            return $response->withJson(["success"=>"no"]);
            };

        });

        $app->get('/slider', function ($request, $response, $args) use ($container){
            $container->get('logger')->info("Slim-Skeleton '/' route");
            $args=[];
            $args["post"]=$this->db->connection()->select("SELECT article.id,article.title,article.status,article.slider,article.date,user.name,user.surname,user.username,category.category_name FROM article INNER JOIN category on article.cat_id=category.category_id INNER JOIN user on user.id=article.user_id WHERE article.status=1 ORDER by article.slider Asc,article.date Desc");
            $args['url']=$container->get("settings")['url'];
            // Render index view
            return $container->get('renderer')->render($response, 'admin/slider.phtml', $args);
        });
        $app->get('/slider/delete/{id}', function ($request, $response, $arg) use ($container){
            $container->get('logger')->info("Slim-Skeleton '/' route");
            $link=$arg['id'];
            $args=[];
            $islem=$this->db->table("article")->where("id",$link)->update(["slider"=>0]);
            if ($islem){
            return $response->withJson(["success"=>"ok"]);
            }else{
            return $response->withJson(["success"=>"no"]);
            };

        });
        $app->get('/slider/submit/{id}', function ($request, $response, $arg) use ($container){
            $container->get('logger')->info("Slim-Skeleton '/' route");
            $link=$arg['id'];
            $args=[];
            $islem=$this->db->table("article")->where("id",$link)->update(["slider"=>1]);
            if ($islem){
            return $response->withJson(["success"=>"ok"]);
            }else{
            return $response->withJson(["success"=>$islem]);
            };

        });
        $app->get('/post/delete/{id}', function ($request, $response, $arg) use ($container){
            $container->get('logger')->info("Slim-Skeleton '/' route");
            $link=$arg['id'];
            $args=[];
            $islem=$this->db->table("article")->where("id",$link)->delete();
            if ($islem){
            return $response->withJson(["success"=>"ok"]);
            }else{
            return $response->withJson(["success"=>"no"]);
            };

        });
        $app->get('/comment', function ($request, $response, $args) use ($container){
            $container->get('logger')->info("Slim-Skeleton '/' route");
            $args=[];
            $args["comment"]=$this->db->connection()->select("SELECT article.title,article.sef_link,comment.status,comment.date,comment.name,comment.comment,comment.id
            FROM comment INNER JOIN article ON comment.article_id=article.id
            ");
            $args['url']=$data["url"]=$container->get("settings")['url'];
            // Render index view
            return $container->get('renderer')->render($response, 'admin/comment.phtml', $args);
        });
        $app->get('/comment/submit/{id}', function ($request, $response, $arg) use ($container){
            $container->get('logger')->info("Slim-Skeleton '/' route");
            $link=$arg['id'];
            $args=[];
            $islem=$this->db->table("comment")->where("id",$link)->update(["status"=>1]);
            if ($islem){
            return $response->withJson(["success"=>"ok"]);
            }else{
            return $response->withJson(["success"=>"no"]);
            };
        });
        $app->get('/comment/delete/{id}', function ($request, $response, $arg) use ($container){
            $container->get('logger')->info("Slim-Skeleton '/' route");
            $link=$arg['id'];

            $islem=$this->db->table("comment")->where("id",$link)->delete();
            if ($islem){
            return $response->withJson(["success"=>"ok"]);
            }else{
            return $response->withJson(["success"=>"no"]);
            };

        });
        $app->get('/user', function ($request, $response, $args) use ($container){
            $container->get('logger')->info("Slim-Skeleton '/' route");
            $args=[];
            $args['url']=$container->get("settings")['url'];
            $args['user']=$this->db->table('user')->get();
            // Render index view
            return $container->get('renderer')->render($response, 'admin/user.phtml', $args);
        });
        $app->get('/authorization', function ($request, $response, $args) use ($container){
            $container->get('logger')->info("Slim-Skeleton '/' route");
            $args=[];
            $args['url']=$container->get("settings")['url'];
            $args['user'] =$this->db->table("user")->where('status',1)->get();
            $args['adminuser'] =$this->db->table("user")->where('status',2)->get();
            return $container->get('renderer')->render($response, 'admin/authorization.phtml', $args);
        });
        $app->get('/authorization/submit/{id}', function ($request, $response, $arg) use ($container){
            $container->get('logger')->info("Slim-Skeleton '/' route");
            $link=$arg['id'];
            $args=[];
            $islem=$this->db->table("user")->where("id",$link)->update(["status"=>2]);
            if ($islem){
            return $response->withJson(["success"=>"ok"]);
            }else{
            return $response->withJson(["success"=>"no"]);
            };
        });
        $app->get('/authorization/delete/{id}', function ($request, $response, $arg) use ($container){
            $container->get('logger')->info("Slim-Skeleton '/' route");
            $link=$arg['id'];
            $args=[];
            $islem=$this->db->table("user")->where("id",$link)->update(["status"=>1]);
            if ($islem){
            return $response->withJson(["success"=>"ok"]);
            }else{
            return $response->withJson(["success"=>"no"]);
            };
        });
        $app->get('/category', function ($request, $response, $args) use ($container){
            $container->get('logger')->info("Slim-Skeleton '/' route");
            $args=[];
            $args['url']=$container->get("settings")['url'];
            $args['category'] =$this->db->table("category")->get();
            // Render index view
            return $container->get('renderer')->render($response, 'admin/category.phtml', $args);
        });
        function moveUploadedFile($directory, UploadedFile $uploadedFile)
        {
            $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
            $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
            $filename = sprintf('%s.%0.8s', $basename, $extension);
    
            $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);
    
            return $filename;
        };
        $app->get('/category/delete/{id}', function ($request, $response, $arg) use ($container){
            $container->get('logger')->info("Slim-Skeleton '/' route");
            $link=$arg['id'];
            $args=[];
            $islem=$this->db->table("category")->where("category_id",$link)->delete();
            if ($islem){
            return $response->withJson(["success"=>"ok"]);
            }else{
            return $response->withJson(["success"=>"no"]);
            };

        });
        $app->post('/category/add', function ($request, $response, $arg) use ($container){
            $body=$request->getParsedBody();
            $container->get('logger')->info("Slim-Skeleton '/' route");
            $insert=$this->db->table("category")->insert(["category_id"=>null,
                'category_name'=>$body['name'],
                'category_top_id'=>1,
                'sef_link'=>$body['name']
                ]);
            if ($insert){
                return $response->withJson(["success"=>"ok"]);
            }else{
                return $response->withJson(["success"=>"no"]);
            }

        });
        $app->get('/question-category', function ($request, $response, $args) use ($container){
            $container->get('logger')->info("Slim-Skeleton '/' route");

            $args=[];
            $args['url']=$container->get("settings")['url'];
           $args['category'] =$this->db->table("question_category")->get();
            // Render index view
            return $container->get('renderer')->render($response, 'admin/qcategory.phtml', $args);
        });
        $app->get('/question-category/delete/{id}', function ($request, $response, $arg) use ($container){
            $container->get('logger')->info("Slim-Skeleton '/' route");
            $link=$arg['id'];
            $args=[];
            $islem=$this->db->table("question_category")->where("id",$link)->delete();
            if ($islem){
            return $response->withJson(["success"=>"ok"]);
            }else{
            return $response->withJson(["success"=>"no"]);
            };

        });
        $app->post('/question-category/add', function ($request, $response, $arg) use ($container){
            $container->get('logger')->info("Slim-Skeleton '/' route");
            $bodyparameter=$request->getParsedBody();
            $link=$arg['id'];
            $args=[];
            $islem=$this->db->table("category")->insert([
                "id"=>null,
                "category_name"=>$bodyparameter['name'],
                "sef_link"=> str_replace(" ","-",$bodyparameter['name'])
            ]);
            if ($islem){
            return $response->withJson(["success"=>"ok"]);
            }else{
            return $response->withJson(["success"=>"no"]);
            };

        });
    })->add($mw);;

};
