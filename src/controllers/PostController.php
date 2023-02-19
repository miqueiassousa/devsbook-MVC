<?php
namespace src\controllers;

use \core\Controller;
use src\handlers\UserHandler;
use src\handlers\PostHandler;

class PostController extends Controller {

    // Armazena usuario que esta logado
    private $loggedUser;

    public function __construct()
    {
        $this->loggedUser = UserHandler::checkLogin();
        if($this->loggedUser === false) {
            $this->redirect('/login');
        }
    }

    // Receber dados e verificar se o usuario estar logado 
    public function new() {
        $body = filter_input(INPUT_POST, 'body');
     
        if($body) {
            PostHandler::addPost(
                $this->loggedUser->id,
                'text',
                $body
            );
        }

        $this->redirect('/');
  
    }
} 