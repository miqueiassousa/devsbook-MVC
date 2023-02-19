<?php
namespace src\controllers;

use \core\Controller;
use \src\handlers\UserHandler;

class SearchController extends Controller {

    // Armazena usuario que esta logado
    private $loggedUser;

    public function __construct()
    {
        $this->loggedUser = UserHandler::checkLogin();
        if($this->loggedUser === false) {
            $this->redirect('/login');
        }
    }

    public function index($atts = []) {

        $searchTerm = filter_input(INPUT_GET, 's');

        if(empty($searchTerm)) {
            $this->redirect('/');
        }

        /* Fazer a busca */
        $users = UserHandler::searchUser($searchTerm);

        // Ele chama os views. Por exemplo: O 'seachrTerm' é uma chave e '$searchTerm" é o valor
        // No arquivo search.php é chamado a chave 'searchTerm" nesse ela se transforma e uma variavél
        $this->render('search', [
            'loggedUser' => $this->loggedUser,
            'searchTerm' => $searchTerm,
            /* Mandar a lista de usuarios para 'users' */
            'users' => $users
        ]);
    }

    
} 