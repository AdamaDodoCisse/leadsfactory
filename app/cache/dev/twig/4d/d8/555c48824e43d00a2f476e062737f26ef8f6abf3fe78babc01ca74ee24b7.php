<?php

/* TellawLeadsFactoryBundle::base.html.twig */
class __TwigTemplate_4dd8555c48824e43d00a2f476e062737f26ef8f6abf3fe78babc01ca74ee24b7 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'body' => array($this, 'block_body'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 2
        echo "<!DOCTYPE html>
<head>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>";
        // line 6
        $this->displayBlock('title', $context, $blocks);
        echo "</title>


    <link href='http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700,800' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Roboto:400,500,700' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=PT+Sans:700,400' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Pontano+Sans' rel='stylesheet' type='text/css'>
    <link href='http://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600' rel='stylesheet' type='text/css'>


    <!-- Styles -->
    <link type=\"text/css\" rel=\"stylesheet\" href=\"http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css\">
    <link rel=\"stylesheet\" href=\"";
        // line 18
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("css/bootstrap.min.css"), "html", null, true);
        echo "\" type=\"text/css\" /><!-- Bootstrap -->
    <link rel=\"stylesheet\" href=\"";
        // line 19
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("font-awesome-4.0.3/css/font-awesome.css"), "html", null, true);
        echo "\" type=\"text/css\" /><!-- Font Awesome -->
    <link rel=\"stylesheet\" href=\"";
        // line 20
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("css/nv.d3.css"), "html", null, true);
        echo "\" type=\"text/css\" /><!-- VISITOR CHART -->
    <link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"";
        // line 21
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("css/daterangepicker-bs3.css"), "html", null, true);
        echo "\" /><!-- Date Range Picker -->
    <link rel=\"stylesheet\" href=\"";
        // line 22
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("css/style.css"), "html", null, true);
        echo "\" type=\"text/css\" /><!-- Style -->
    <link rel=\"stylesheet\" href=\"";
        // line 23
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("css/responsive.css"), "html", null, true);
        echo "\" type=\"text/css\" /><!-- Responsive -->



    <!-- Script -->
    <script src=\"";
        // line 28
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("js/jquery-1.10.2.js"), "html", null, true);
        echo "\"></script><!-- Jquery -->
    <script type=\"text/javascript\"  src=\"";
        // line 29
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("js/d3.v2.js"), "html", null, true);
        echo "\"></script><!-- VISITOR CHART -->
    <script type=\"text/javascript\"  src=\"";
        // line 30
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("js/nv.d3.js"), "html", null, true);
        echo "\"></script><!-- VISITOR CHART -->
    <script type=\"text/javascript\"  src=\"";
        // line 31
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("js/live-updating-chart.js"), "html", null, true);
        echo "\"></script><!-- VISITOR CHART -->
    <script type=\"text/javascript\"  src=\"";
        // line 32
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("js/bootstrap.min.js"), "html", null, true);
        echo "\"></script><!-- Bootstrap -->
    <script type=\"text/javascript\"  src=\"";
        // line 33
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("js/script.js"), "html", null, true);
        echo "\"></script><!-- Script -->
    <script src=\"";
        // line 34
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("js/jquery.easypiechart.min.js"), "html", null, true);
        echo "\"></script> <!-- Easy Pie Chart -->
    <script src=\"";
        // line 35
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("js/easy-pie-chart.js"), "html", null, true);
        echo "\"></script> <!-- Easy Pie Chart -->
    <script src=\"";
        // line 36
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("js/skycons.js"), "html", null, true);
        echo "\"></script> <!-- Skycons -->
    <script src=\"";
        // line 37
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("js/enscroll-0.5.2.min.js"), "html", null, true);
        echo "\"></script> <!-- Custom Scroll bar -->
    <script src=\"";
        // line 38
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("js/moment.js"), "html", null, true);
        echo "\"></script> <!-- Date Range Picker -->
    <script src=\"";
        // line 39
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("js/daterangepicker.js"), "html", null, true);
        echo "\"></script><!-- Date Range Picker -->
    <script src=\"";
        // line 40
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("js/ticker.js"), "html", null, true);
        echo "\"></script><!-- Ticker -->
    <script src=\"";
        // line 41
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("js/html5lightbox.js"), "html", null, true);
        echo "\"></script><!-- Ticker -->

</head>
<body>
<div class=\"responsive-menu\">
    <div class=\"responsive-menu-dropdown blue\">
        <a title=\"\" class=\"blue\">MENU <i class=\"fa fa-align-justify\" ></i></a>
    </div>
    <ul>
        <li id=\"intro4\"><a href=\"#\" title=\"\" ><i class=\"fa fa-desktop\"></i><span><i>4</i></span>Formulaires</a>
            <ul>
                <li><a href=\"dashboard.html\" title=\"\">Dashboard 1</a></li>
                <li><a href=\"dashboard2.html\" title=\"\">Dashboard 2</a></li>
                <li><a href=\"dashboard3.html\" title=\"\">Dashboard 3</a></li>
                <li><a href=\"dashboard4.html\" title=\"\">Dashboard 4</a></li>
                <li><a href=\"dashboard5.html\" title=\"\">Wide Dashboard</a></li>
            </ul>
        </li>
        <li id=\"intro5\"><a href=\"widget.html\" title=\"\" ><i class=\"fa fa-heart-o\"></i><span><i>20+</i></span>Widget</a></li>
        <li><a href=\"#\" title=\"\" ><i class=\"fa fa-tint\"></i><span><i>12</i></span>Ui Kit</a>
            <ul>
                <li><a href=\"notifications.html\" title=\"\">Notifications</a></li>
                <li><a href=\"grids.html\" title=\"\">Grids</a></li>
                <li><a href=\"buttons.html\" title=\"\">Buttons</a></li>
                <li><a href=\"calendars.html\" title=\"\">Calendars</a></li>
                <li><a href=\"file-manager.html\" title=\"\">File Manager</a></li>
                <li><a href=\"timeline.html\" title=\"\">Liquid Timeline</a></li>
                <li><a href=\"gallery.html\" title=\"\">Simple Gallery</a></li>
                <li><a href=\"gallery2.html\" title=\"\">Gallery Manager</a></li>
                <li><a href=\"slider.html\" title=\"\">Slider</a></li>
                <li><a href=\"page-tour.html\" title=\"\">Page Tour</a></li>
                <li><a href=\"collapse.html\" title=\"\">Collapse</a></li>
                <li><a href=\"range-slider.html\" title=\"\">Range Slider</a></li>
                <li><a href=\"typography.html\" title=\"\">Typography</a></li>
                <li><a href=\"tables.html\" title=\"\">Tables</a></li>
            </ul>
        </li>
        <li><a href=\"form.html\" title=\"\" ><i class=\"fa fa-paperclip\"></i>Form Stuff</a></li>
        <li><a href=\"charts.html\" title=\"\" ><i class=\"fa fa-unlink\"></i><span><i>5+</i></span>Charts</a></li>
        <li><a href=\"#\" title=\"\" ><i class=\"fa fa-rocket\"></i><span><i>8+</i></span>Pages</a>
            <ul>
                <li><a href=\"invoice.html\" title=\"\">Invoice</a></li>
                <li><a href=\"order-recieved.html\" title=\"\">Order Recieved</a></li>
                <li><a href=\"search-result.html\" title=\"\">Search Result</a></li>
                <li><a href=\"price-table.html\" title=\"\">Price Table</a></li>
                <li><a href=\"inbox.html\" title=\"\">Inbox</a></li>
                <li><a href=\"profile.html\" title=\"\">Profile</a></li>
                <li><a href=\"contact.html\" title=\"\">Contact Us</a></li>
                <li><a href=\"css-spinners.html\" title=\"\">Css Spinners</a></li>
            </ul>
        </li>
        <li><a href=\"#\" title=\"\" ><i class=\"fa fa-thumbs-o-up\"></i><span><i>6+</i></span>Bonus</a>
            <ul>

                <li><a href=\"faq.html\" title=\"\">Faq</a></li>
                <li><a href=\"index.html\" title=\"\">Log in</a></li>
                <li><a href=\"blank.html\" title=\"\">blank</a></li>
                <li><a href=\"cart.html\" title=\"\">Cart</a></li>
                <li><a href=\"billing.html\" title=\"\">Billing</a></li>
                <li><a href=\"icons.html\" title=\"\">Icons</a></li>
            </ul>
        </li>
    </ul>
</div>
<header>
    <div class=\"logo\">
        <img src=\"";
        // line 107
        echo twig_escape_filter($this->env, $this->env->getExtension('assets')->getAssetUrl("/images/logo.png"), "html", null, true);
        echo "\" alt=\"\" />
    </div>

</header><!-- Header -->

<div class=\"menu\">
    <div class=\"menu-profile\" id=\"intro3\">
        <img src=\"http://placehold.it/57x57\" alt=\"\" />
        <span><i class=\"fa fa-plus\"></i></span>
        <div class=\"menu-profile-hover\">
            <h1><i>Brian</i> Kelly</h1>
            <p><i class=\"fa fa-map-marker\"></i>LONDON, UNITED KINGDOM</p>
            <a href=\"index.html\" title=\"\"><i class=\"fa fa-power-off\"></i></a>
            <div class=\"menu-profile-btns\">

                <h3>
                    <i class=\"fa fa-user blue\"></i>
                    <a href=\"profile.html\" title=\"\">PROFILE</a>
                </h3>
                <h3>
                    <i class=\"fa fa-inbox pink\"></i>
                    <a href=\"inbox.html\" title=\"\">INBOX</a>
                </h3>


            </div>
        </div>
    </div>
    <ul>
        <li><a href=\"#\" title=\"\" ><i class=\"fa fa-paperclip\"></i>Formulaires</a></li>
        <li><a href=\"#\" title=\"\" ><i class=\"fa fa-rocket\"></i>Exports</a></li>
        <li><a href=\"#\" title=\"\" ><i class=\"fa fa-desktop\"></i>Monitoring</a></li>
        <li><a href=\"#\" title=\"\" ><i class=\"fa fa-random\"></i>Requester</a></li>
        <li><a href=\"#\" title=\"\" ><i class=\"fa fa-unlink\"></i>Paramètres</a></li>

    </ul>
</div><!-- Right Menu -->

<div class=\"wrapper\">

    ";
        // line 147
        $this->displayBlock('body', $context, $blocks);
        // line 148
        echo "
</div><!-- Wrapper -->

<!-- RAIn ANIMATED ICON-->
<script>
    var icons = new Skycons();
    icons.set(\"rain\", Skycons.RAIN);
    icons.play();
</script>


</body>
</html>";
    }

    // line 6
    public function block_title($context, array $blocks = array())
    {
        echo "Leads Factory";
    }

    // line 147
    public function block_body($context, array $blocks = array())
    {
    }

    public function getTemplateName()
    {
        return "TellawLeadsFactoryBundle::base.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  258 => 147,  252 => 6,  236 => 148,  234 => 147,  191 => 107,  122 => 41,  118 => 40,  114 => 39,  110 => 38,  106 => 37,  102 => 36,  98 => 35,  94 => 34,  90 => 33,  86 => 32,  82 => 31,  78 => 30,  74 => 29,  70 => 28,  62 => 23,  58 => 22,  54 => 21,  50 => 20,  46 => 19,  42 => 18,  27 => 6,  21 => 2,);
    }
}
