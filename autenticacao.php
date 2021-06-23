<?php

$loginUSP = json_decode($result['body'], true);
			
session_destroy();

include("../inc/includes.php");

$auth = new Auth();
$user = new User();

$vinculo = False;
foreach ($loginUSP["vinculo"] as $key){
    if (($key["siglaUnidade"] == $unidade))
        $vinculo = True;		
}

if($vinculo) {
    //adicionando o usuario na base do glpi
    
    //Criação da senha
    $passwd_glpi = $loginUSP["loginUsuario"].$passwd_salt.explode(" ",$loginUSP["nomeUsuario"])[0];

    //Tratar e-mail vazio
    isset($loginUSP["emailUspUsuario"]) ? $email = $loginUSP["emailUspUsuario"] : $email = $loginUSP["emailPrincipalUsuario"];
   
    //Array enviado pelo formulario "Adicionar usuário"
    $dadosUsuario = array(
        'name' => $loginUSP["loginUsuario"],				
        'realname' => explode(" ",$loginUSP["nomeUsuario"])[count(explode(" ",$loginUSP["nomeUsuario"]))-1],		
        'firstname' => explode(" ",$loginUSP["nomeUsuario"])[0],			
        'password' => $passwd_glpi,			
        'password2' => $passwd_glpi,			
        'is_active' => '1',		
        '_useremails' => array(
            $email
        ),
        'begin_date' => '',
        'end_date' => '',
        'phone' => '',
        'authtype' => '1',
        'mobile' => '',
        'usercategories_id' => '0',
        'phone2' => '',
        'comment' => '',
        'registration_number' => '',
        'usertitles_id' => '0',
        '_is_recursive' => '0',
        '_profiles_id' => '1',
        '_entities_id' => '0',
        'add' => "<i class=\'fas fa-plus\'></i> Adicionar",
        '_glpi_csrf_token' => '',
    );
    $user->add($dadosUsuario);

    //autenticando o usuario no glpi				
    $auth->login($loginUSP["loginUsuario"],$passwd_glpi);
    Auth::redirectIfAuthenticated();
}
else{
    //Se achar necessário crie um novo código de erro no index.php do GLPI na linha 211
    $url_app = $url_app."?redirect=1&error=3";
    header("Location: $url_app");
}