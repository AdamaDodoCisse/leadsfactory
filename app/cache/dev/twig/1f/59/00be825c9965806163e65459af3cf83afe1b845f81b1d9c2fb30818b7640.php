<?php

/* TellawLeadsFactoryBundle:Default:test.html.twig */
class __TwigTemplate_1f5900be825c9965806163e65459af3cf83afe1b845f81b1d9c2fb30818b7640 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = $this->env->loadTemplate("TellawLeadsFactoryBundle::base.html.twig");

        $this->blocks = array(
            'title' => array($this, 'block_title'),
            'body' => array($this, 'block_body'),
        );
    }

    protected function doGetParent(array $context)
    {
        return "TellawLeadsFactoryBundle::base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_title($context, array $blocks = array())
    {
        echo "My cool testing page";
    }

    // line 4
    public function block_body($context, array $blocks = array())
    {
        // line 5
        echo "This is my testing content !!!
";
    }

    public function getTemplateName()
    {
        return "TellawLeadsFactoryBundle:Default:test.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  38 => 5,  35 => 4,  29 => 3,);
    }
}
