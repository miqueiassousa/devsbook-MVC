<div class="box feed-new">
    <div class="box-body">
        <div class="feed-new-editor m-10 row">
            <div class="feed-new-avatar">
                <img src="<?=$base;?>/media/avatars/<?=$user->avatar;?>" />
            </div>
            <div class="feed-new-input-placeholder">O que você está pensando, <?= $user->name; ?></div>
            <div class="feed-new-input" contenteditable="true"></div>
            <div class="feed-new-photo">
                <img src="<?=$base;?>/assets/images/photo.png" />
                <input type="file" name="photo" class="feed-new-file" accept="image/png, image/jpg, image/jpeg" />
            </div>
            <div class="feed-new-send">
                <img src="<?=$base;?>/assets/images/send.png" />
            </div>
            <form class="feed-new-form" method="POST" action="<?= $base; ?>/post/new">
                <input type="hidden" name="body" />
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">

    // LET é para declarar uma variavel
    let feedInput = document.querySelector('.feed-new-input');
    let feedSubmit = document.querySelector('.feed-new-send');
    let feedForm = document.querySelector('.feed-new-form');
    let feedPhoto = document.querySelector('.feed-new-photo');    
    let feedFile = document.querySelector('.feed-new-file');

    feedPhoto.addEventListener('click', function() {
        feedFile.click();
    });

    feedFile.addEventListener('change', async function() {
        /* ASYNC - São métodos que podem executar assincronamente, ou seja, quem 
        chamou não precisa esperar por sua execução e ela pode continuar 
        normalmente sem bloquear a aplicação
        let photo = feedFile.files[0]; */

        let formData = new FormData();
        // APPEND - Adiciona um novo valor dentro de uma chave existente dentro do objeto FormData
        formData.append('photo', photo);

        let req = await fetch(BASE+'/ajax/upload', {
            method: 'POST',
            body: formData
        });
        let json = await req.json();

        if(json.error != '') {
            alert(json.error);
        }

        //window.localStorage.href = window.localStorage.href; 

    });

    // Se clicar ele executa uma função
    feedSubmit.addEventListener('click', function(obj) {
        let value = feedInput.innerText.trim();

        if (value != '') {
            feedForm.querySelector('input[name="body"]').value = value;
            feedForm.submit();
        }
    })
</script>