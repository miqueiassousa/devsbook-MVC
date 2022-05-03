<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Login - DevsBook</title>
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1"/>

    <!-- Pra carregar o css de forma correta precisa chamar a variavel $base -->
    <link rel="stylesheet" href="<?= $base;?>/assets/css/login.css" />
</head>
<body>
    <header>
        <div class="container">
            <a href=""><img src="<?=$base;?>/assets/images/devsbook_logo.png" /></a>
        </div>
    </header>
    <section class="container main">

        <!-- Preferivel setar a action mesmo trendo o router -->
        <form method="POST" action="<?=$base;?>/cadastro">

        <?php if(!empty($flash)): ?>
            <div class="flash"> <?php echo $flash; ?> </div>
        <?php endif; ?>

        <input placeholder="Digite nome completo" class="input" type="name" name="name" />

            <input placeholder="Digite seu e-mail" class="input" type="email" name="email" />

            <input placeholder="Digite sua data de nascimento" class="input" type="text" name="birthdate" id="birthdate" />

            <input placeholder="Digite sua senha" class="input" type="password" name="password" />

            <input class="button" type="submit" value="Fazer cadastro" />

            <a href="<?=$base;?>/login">Já tem cadastro? Faça login</a>
        </form>
    </section>

<script src="https://unpkg.com/imask"></script>
<script>
IMask(
    document.getElementById('birthdate'),
    {
        mask:'00/00/0000'
    }
);
</script>
</body>
</html>