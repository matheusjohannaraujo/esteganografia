<!--span> https://youtu.be/vUOVw2ufdWg </span-->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esteganografia</title>
    <?= tag_favicon("index.jpeg", "jpeg"); ?>
    <link rel="canonical" href="index, follow"/>
    <link rel="next" href="index, follow">
    <meta name="description" content="Esteganografia - Matheus Johann Araújo">
    <meta name="keywords" content="Esteganografia - Matheus Johann Araújo. Esteganografia. Matheus Johann Araújo. Matheus. Johann. Araújo. Matheus Johann. Matheus Araújo">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Matheus Johann Araújo"/>
    <meta property="og:title" content="Esteganografia - Matheus Johann Araújo">
    <meta property="og:description" content="Esteganografia - Matheus Johann Araújo">
    <meta property="og:image" content="public/img/profile.jpg"/>
    <meta property="og:url" content="index, follow">
    <meta property="og:site_name" content="Esteganografia - Matheus Johann Araújo">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:type" content="article">
    <?= tag_js("jquery-3.5.1.min.js"); ?>
    <?= tag_js("popper.min.js"); ?>
    <?= tag_css("bootstrap.min.css"); ?>
    <?= tag_js("bootstrap.min.js"); ?>
    <?= tag_js("bootstrap.bundle.min.js"); ?>
</head>
<body class="bg-dark p-3">
    <div class="container bg-white pt-3 pb-3 rounded">
        <a href="">
            <div class="jumbotron jumbotron-fluid bg-dark text-white p-2 pt-3 rounded mb-3 overflow-auto">
                <div class="container">
                    <h1 class="text-white" style="font-weight: 300; font-size: 2.4em;">Esteganografia</h1>
                </div>
            </div>
        </a>
        <hr>
        <div class="alert alert-info" role="alert">
            <a href="https://github.com/matheusjohannaraujo/esteganografia" target="_blank" class="alert-link">Link do projeto no GitHub</a>
        </div>
        <hr>        
        <h5 class="mb-3">Esconde um texto dentro de uma imagem</h5>
        <form class="bg-dark text-white p-3 rounded" action="<?= action("main.hide_message_in_image"); ?>" method="post" enctype="multipart/form-data">
            <?= tag_csrf(); ?>            
            <div class="form-group">
                <label for="image">Imagem:</label>
                <input class="form-control-file" type="file" name="image" id="image" accept="image/*" required>
            </div>
            <div class="form-group">
                <label for="message">Mensagem:</label>
                <?= tag_message("error_hide_message_in_image", ["class" => "alert alert-warning font-weight-bold", "role" => "alert"], "div"); ?>
                <textarea class="form-control" name="message" id="message" cols="10" rows="3" placeholder="Digite a mensagem" required></textarea>
            </div>
            <input class="btn btn-primary mb-2" type="submit" value="Esconder">
        </form>
        <hr>
        <h5 class="mb-3">Mostra o texto contido dentro de uma imagem</h5>
        <form class="bg-dark text-light p-3 rounded" action="<?= action("main.show_message_in_image"); ?>" method="post" enctype="multipart/form-data">
            <?= tag_csrf(); ?>
            <div class="form-group">
                <label for="image">Imagem:</label>
                <input class="form-control-file" type="file" name="image" id="image" accept="image/png" required>
            </div>
            <div class="form-group">
                <label>Mensagem:</label>
                <?= tag_message("error_show_message_in_image", ["class" => "alert alert-warning font-weight-bold", "role" => "alert"], "div"); ?>
                <textarea class="form-control" cols="10" rows="3" disabled placeholder="A mensagem aparecerá aqui."><?= message("message"); ?></textarea>
            </div>
            <input class="btn btn-primary" type="submit" value="Mostrar">
        </form>
    </div>    
</body>
</html>
