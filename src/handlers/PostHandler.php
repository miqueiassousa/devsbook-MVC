<?php

namespace src\handlers;

use \src\models\Post;
use \src\models\PostLike;
use \src\models\PostComment;
use \src\models\User;
use \src\models\UserRelation;


class PostHandler
{

    public static function addPost($idUser, $type, $body)
    {

        $body = trim($body);

        if (!empty($idUser) && !empty($body)) {

            Post::insert([
                'id_user' => $idUser,
                'type' => $type,
                'created_at' => date('Y-m-d H:i:s'),
                'body' => $body
            ])->execute();
        }
    }



    // Vou manda o post list (Lista de postagem) e receber os objetos desta postagem
    // Funções ajudadoras coloca anderline _

    public static function _postListToObject($postList, $loggedUserId) {
        $posts = [];

        // Criação do post
        foreach ($postList as $postItem) {
            $newPost = new Post();
            $newPost->id = $postItem['id'];
            $newPost->type = $postItem['type'];
            $newPost->created_at = $postItem['created_at'];
            $newPost->body = $postItem['body'];

            $newPost->mine = false;

            if ($postItem['id_user'] == $loggedUserId) {
                $newPost->mine = true;
            }

            // 4. Preencher as informações adicionais no post
            $newUser = User::select()->where('id', $postItem['id_user'])->one();
            $newPost->user = new User();
            $newPost->user->id = $newUser['id'];
            $newPost->user->name = $newUser['name'];
            $newPost->user->avatar = $newUser['avatar'];

            // TODO: 4.1 Preencher informações de LIKE

            // Quantos registro do PostLike esse post tem salvo
            $likes = PostLike::select()->where('id_post', $postItem['id'])->get();

            $newPost->likeCount = count($likes);
            $newPost->liked = self::isLiked($postItem['id'], $loggedUserId);

            // TODO: 4.2 Preencher informações de COMMENTS
            $newPost->comments = PostComment::select()->where('id_post', $postItem['id'])->get();

            foreach($newPost->comments as $key => $comment) {

                // Pegas as informações do usuarios
                $newPost->comments[$key]['user'] = User::select()->where('id', $comment['id_user'])->one();
            }

            $posts[] = $newPost;

           
        }

        return $posts;
    }

    public static function isLiked($id, $loggedUserId) {

        // VErificar se o usuario ja deu like em um determinado post
        $myLike = PostLike::select()
            ->where('id_post', $id)
            ->where('id_user', $loggedUserId)
        ->get();

        if(count($myLike) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function deleteLike($id, $loggedUserId) {
        PostLike::delete()
            ->where('id_post', $id)
            ->where('id_user', $loggedUserId)
        ->execute();
    }

    public static function addLike($id, $loggedUserId) {
        PostLike::insert([
            'id_post' => $id,
            'id_user' => $loggedUserId,
            'created_at' => date('Y-m-d H:i:s')
        ])->execute();
    }

    public static function addComment($id, $txt, $loggedUserId) {
        PostComment::insert([
            'id_post' => $id,
            'id_user' => $loggedUserId,
            'created_at' => date('Y-m-d H-i-s'),
            'body' => $txt

        ])->execute();

    }

    public static function getUserFeed($idUser, $page, $loggedUserId)
    {

        $perPage = 2;

        // Formação dos usuarios

        $postList = Post::select()
            //Postagem que o proprio usuario fez
            ->where('id_user', $idUser)
            ->orderBy('created_at', 'desc')
            // Limite da quantidade de paginas
            ->page($page, $perPage)
            ->get();


        $total = Post::select()
            ->where('id_user', $idUser)
            ->count();

        // Saber a quantidade de paginas 
        $pageCount = ceil($total / $perPage);


        /* 3. Transformar o resultado em objetos do models 
        (Pega os resultatos e tranformar em objetos reais)*/

        $posts = self::_postListToObject($postList, $loggedUserId);



        // 5. Retornar o resultado
        return [
            'posts' => $posts,
            'pageCount' => $pageCount,
            // Pagina atual
            'currentPage' => $page

        ];
    }

    public static function getHomeFeed($idUser, $page)
    {

        //Itens por pagina
        $perPage = 2;

        // 1. Pegar lista de usuarios que EU sigo
        // Quem sou eu ($idUser)

        $userList = UserRelation::select()->where('user_from', $idUser)->get();

        // Preencher a variavel
        $users = [];
        foreach ($userList as $userItem) {
            $users[] = $userItem['user_to'];
        }

        // Pegar meu proprio feed 
        $users[] = $idUser;



        // 2. Pegar os posts dessa galera ordenado pela data
        $postList = Post::select()
            ->where('id_user', 'in', $users)
            ->orderBy('created_at', 'desc')
            // Limite da quantidade de paginas
            ->page($page, $perPage)
            ->get();

        // Saber quantas paginas sera exibida
        $total = Post::select()
            ->where('id_user', 'in', $users)
            ->count();

        // Saber a quantidade de paginas 
        $pageCount = ceil($total / $perPage);


        /* 3. Transformar o resultado em objetos do models 
        (Pega os resultatos e tranformar em objetos reais)*/

        $posts = self::_postListToObject($postList, $idUser);


        // 5. Retornar o resultado
        return [
            'posts' => $posts,
            'pageCount' => $pageCount,
            // Pagina atual
            'currentPage' => $page

        ];
    }

    public static function getPhotosFrom($idUser)
    {
        $photoData = Post::select()
            ->where('id_user', $idUser)
            ->where('type', 'photo')
            ->get();

        $photos = [];

        foreach ($photoData as $photo) {
            $newPost = new Post();
            $newPost->id = $photo['id'];
            $newPost->type = $photo['type'];
            $newPost->created_at = $photo['created_at'];
            $newPost->body = $photo['body'];

            $photos[] = $newPost;
        }

        return $photos;
    }

    public static function delete($id, $loggedUserId) {
        // 1. Verificar se o post existe (e se é seu)

        $post = Post::select()
            ->where('id', $id)
            ->where('id_user', $loggedUserId)
        ->get();

        id(count($post) > 0) {
            $post = $post[0];

            // 2. Deletar os likes e comments

            /* PostLike é pra identificar o banco */
            PostLike::delete()->where('id_post', $id)->execute();
            PostComment::delete()->where('id_post', $id)->execute();
        }
        

        // 3. Se a foto for type == photo, deletar o arquivo

        // 4. deletar o post 
    }
}
