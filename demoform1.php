<html>
<head>

    <script src="http://local.dev/leadsfactory/web/bundles/tellawleadsfactory/js/libs/jquery-1.10.2.min.js"></script>
    <script src="http://local.dev/leadsfactory/web/bundles/tellawleadsfactory/js/libs/formValidator/jquery.validationEngine.js"></script>
    <script src="http://local.dev/leadsfactory/web/bundles/tellawleadsfactory/js/libs/formValidator/languages/jquery.validationEngine-fr.js"></script>

    <script type="text/javascript" src="http://local.dev/leadsfactory/web/app_dev.php/client/form/js/adette/ks%47toto" ></script>

    <link rel="stylesheet" href="http://local.dev/leadsfactory/web/bundles/tellawleadsfactory/js/libs/formValidator/developr.validationEngine.css?v=1">

</head>
<body>

<h1>Affichage formulaire</h1>
<script>

    leadsfactory.render();

    leadsfactory.setNom ('Le nom');
    leadsfactory.setPrenom ('Le prénom');

    $(document).ready(function(){
        $('form').validationEngine('attach', {promptPosition : "bottomLeft", scroll: false});
    });

</script>
</body>
</html>