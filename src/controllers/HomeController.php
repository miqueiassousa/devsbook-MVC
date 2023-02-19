<?php
namespace src\controllers;

use \core\Controller;
use \src\handlers\UserHandler;
use \src\handlers\PostHandler; 

class HomeController extends Controller {

    // Armazena usuario que esta logado
    private $loggedUser;

    public function __construct()
    {
        $this->loggedUser = UserHandler::checkLogin();
        if($this->loggedUser === false) {
            $this->redirect('/login');
        }
    }

    public function index() {
        
        $page = intval(filter_input(INPUT_GET, 'page'));

        $feed = PostHandler::getHomeFeed(
        // Usario logado
            $this->loggedUser->id,
        // PAgina
            $page
        );

        $this->render('home', [
            'loggedUser' => $this->loggedUser,
            'feed' => $feed
        ]);
    }
} 