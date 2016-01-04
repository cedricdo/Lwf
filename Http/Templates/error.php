<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">

        <title>Une erreur est survenue</title>
    </head>
    <body>
        <h1>Oups, une erreur est survenue.</h1>
        <h2>Le serveur a retourné [<?= $code; ?>, <?= $text; ?>]</h2>
        <p>
            Merci de prévenir l'administrateur de cette erreur et de lui indiquer ce que
            vous étiez en train de faire quand c'est arrivé. L'erreur sera corrigée aussi
            vite que possible.
            <p style="text-align: center;">
                <a href="<?= $baseUrl ?>">Retour à l'accueil</a>
            </p>
        </p>
    </body>
</html>