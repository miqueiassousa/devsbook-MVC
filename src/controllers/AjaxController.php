<?php

namespace src\controllers;

use \core\Controller;
use src\handlers\UserHandler;
use src\handlers\PostHandler;

class AjaxController extends Controller
{

    // Armazena usuario que esta logado
    private $loggedUser;

    public function __construct()
    {
        $this->loggedUser = UserHandler::checkLogin();
        if ($this->loggedUser === false) {
            header("Content-Type: application/json");
            echo json_encode(['error' => 'usuario não logado']);
            exit;
        }
    }


    public function like($atts)
    {
        $id = $atts['id'];

        if (PostHandler::isLiked($id, $this->loggedUser->id)) {
            PostHandler::deleteLike($id, $this->loggedUser->id);
        } else {
            PostHandler::addLike($id, $this->loggedUser->id);
        }
    }


    public function comment()
    {
        $array = ['error' => ''];

        $id = filter_input(INPUT_POST, 'id');
        $txt = filter_input(INPUT_POST, 'txt');

        if ($id && $txt) {
            PostHandler::addComment($id, $txt, $this->loggedUser->id);

            $array['link'] = '/perfil/' . $this->loggedUser->id;
            $array['avatar'] = '/media/avatars/' . $this->loggedUser->avatar;
            $array['name'] = $this->loggedUser->name;
            $array['body'] = $txt;
        }

        header("Content-Type: application/json");
        echo json_encode($array);
        exit;
    }

    public function upload()
    {
        $array = ['error' => ''];

        // Se a foto existir e dentro dele tiver um tmp_name e não esta vazio
        if (isset($_FILES['photo']) && !empty($_FILES['photo']['tmp_name'])) {
            $photo = $_FILES['photo'];

            $maxWidth = 800;
            $maxHeigth = 800;

            // Se tiver os três tipos de imagem
            if(in_array($photo['type'], ['image/png', 'image/jpg', 'image/jpeg'])) {

                // Redimensionamento da imagem

                // Pegar altura e largura
                list($widthOrig, $HeightOrig) = getimagesize($photo['tmp_name']);

                $ratio = $widthOrig / $HeightOrig;

                // Criar um tamenho efetivo e adaptar para foto
                $newWidth = $maxWidth;
                $newHeigth = $maxHeigth;
                $ratioMax = $maxWidth / $maxHeigth;

                // Comparações
                if ($ratioMax > $ratio) {
                    //Trocar a largura
                    $newWidth = $newHeigth * $ratio;
                } else {
                    // Trocar a altura
                    $newHeigth = $newWidth / $ratio;
                }

                // Gerar imagem final (em branco)
                $finalImage = imagecreatetruecolor($newWidth, $newHeigth);

                // Pegar imagem original
                switch ($photo['type']) {
                    case 'image/png':
                        $image = imagecreatefrompng($photo['tmp_name']);
                        break;

                    case 'image/jpg':
                    case 'image/jpeg':
                        $image = imagecreatefromjpeg($photo['tmp_name']);
                        break;
                }

                // Pegar a imagem e colocar dentro da outro do tamanho que foi gerado
                imagecopyresampled(
                    $finalImage, $image,
                    0, 0, 0, 0, 
                    $newWidth, $newHeigth, $widthOrig, $HeightOrig
                );

                // Gerar o nome da nova imagem
                $photoName = md5(time().rand(0,9999)).'.jpg';

                // Salvar imagem no servidor
                imagejpeg($finalImage, 'media/uploads/'.$photoName);

                // Criar o post da foto
                PostHandler::addPost(
                    $this->loggedUser->id,
                    'photo',
                    $photoName
                );

            }
        } else {
            $array['error'] = 'Nenhuma imagem enviada';
        }

        header("Content-Type: application/json");
        echo json_encode($array);
        exit;
    }
}
