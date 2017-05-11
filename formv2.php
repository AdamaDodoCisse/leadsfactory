<?php
/**
 * Created by PhpStorm.
 * User: seth
 * Date: 01/10/15
 * Time: 10:05
 */
?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="http://local.dev/weka-leadsfactory/web/bundles/tellawleadsfactory/js/libs/formValidator/developr.validationEngine.css">
    <script src="http://local.dev/weka-leadsfactory/web/bundles/tellawleadsfactory/js/libs/jquery-1.11.3.min.js"></script>
</head>
<body>

<div id="formulaireleads" class="loading-leads">
    <p class="loading-leads-txt">Merci de patienter ...</p>
</div>
<script type="text/javascript">
baseUrl = 'http://local.dev/weka-leadsfactory/'; // Se termine par un slash
    codeAction = '/CD/AC/DEMO01';
    codeFormulaire = '<?= $_GET['id'] ?>';
    data = [];
    jQuery.when(
        jQuery.getScript(baseUrl+'web/bundles/tellawleadsfactory/js/lf.js'),
        jQuery.getScript(baseUrl+'web/bundles/tellawleadsfactory/js/libs/formValidator/jquery.validationEngine.js'),
        jQuery.getScript(baseUrl+'web/bundles/tellawleadsfactory/js/libs/formValidator/languages/jquery.validationEngine-fr.js'),
        jQuery.getScript(baseUrl+'web/app_dev.php/client/form/twig/'+ codeFormulaire),
        jQuery.getScript(baseUrl+'web/bundles/tellawleadsfactory/js/libs/phoneformat.js'),
        jQuery(document).data("readyDeferred")
    ).done(function() {
        leadsfactory.setCodeAction (codeAction);
        jQuery('#formulaireleads').html(leadsfactory.html);
        jQuery('#formulaireleads').removeClass('loading-leads');
        var params = {};
                var options = {};
                leadsfactory.init(codeFormulaire, data, options);
            });
</script>


</body>
</html>
