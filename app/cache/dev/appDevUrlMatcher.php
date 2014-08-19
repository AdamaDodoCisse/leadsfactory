<?php

use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContext;

/**
 * appDevUrlMatcher
 *
 * This class has been auto-generated
 * by the Symfony Routing Component.
 */
class appDevUrlMatcher extends Symfony\Bundle\FrameworkBundle\Routing\RedirectableUrlMatcher
{
    /**
     * Constructor.
     */
    public function __construct(RequestContext $context)
    {
        $this->context = $context;
    }

    public function match($pathinfo)
    {
        $allow = array();
        $pathinfo = rawurldecode($pathinfo);
        $context = $this->context;
        $request = $this->request;

        if (0 === strpos($pathinfo, '/_')) {
            // _wdt
            if (0 === strpos($pathinfo, '/_wdt') && preg_match('#^/_wdt/(?P<token>[^/]++)$#s', $pathinfo, $matches)) {
                return $this->mergeDefaults(array_replace($matches, array('_route' => '_wdt')), array (  '_controller' => 'web_profiler.controller.profiler:toolbarAction',));
            }

            if (0 === strpos($pathinfo, '/_profiler')) {
                // _profiler_home
                if (rtrim($pathinfo, '/') === '/_profiler') {
                    if (substr($pathinfo, -1) !== '/') {
                        return $this->redirect($pathinfo.'/', '_profiler_home');
                    }

                    return array (  '_controller' => 'web_profiler.controller.profiler:homeAction',  '_route' => '_profiler_home',);
                }

                if (0 === strpos($pathinfo, '/_profiler/search')) {
                    // _profiler_search
                    if ($pathinfo === '/_profiler/search') {
                        return array (  '_controller' => 'web_profiler.controller.profiler:searchAction',  '_route' => '_profiler_search',);
                    }

                    // _profiler_search_bar
                    if ($pathinfo === '/_profiler/search_bar') {
                        return array (  '_controller' => 'web_profiler.controller.profiler:searchBarAction',  '_route' => '_profiler_search_bar',);
                    }

                }

                // _profiler_purge
                if ($pathinfo === '/_profiler/purge') {
                    return array (  '_controller' => 'web_profiler.controller.profiler:purgeAction',  '_route' => '_profiler_purge',);
                }

                if (0 === strpos($pathinfo, '/_profiler/i')) {
                    // _profiler_info
                    if (0 === strpos($pathinfo, '/_profiler/info') && preg_match('#^/_profiler/info/(?P<about>[^/]++)$#s', $pathinfo, $matches)) {
                        return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler_info')), array (  '_controller' => 'web_profiler.controller.profiler:infoAction',));
                    }

                    // _profiler_import
                    if ($pathinfo === '/_profiler/import') {
                        return array (  '_controller' => 'web_profiler.controller.profiler:importAction',  '_route' => '_profiler_import',);
                    }

                }

                // _profiler_export
                if (0 === strpos($pathinfo, '/_profiler/export') && preg_match('#^/_profiler/export/(?P<token>[^/\\.]++)\\.txt$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler_export')), array (  '_controller' => 'web_profiler.controller.profiler:exportAction',));
                }

                // _profiler_phpinfo
                if ($pathinfo === '/_profiler/phpinfo') {
                    return array (  '_controller' => 'web_profiler.controller.profiler:phpinfoAction',  '_route' => '_profiler_phpinfo',);
                }

                // _profiler_search_results
                if (preg_match('#^/_profiler/(?P<token>[^/]++)/search/results$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler_search_results')), array (  '_controller' => 'web_profiler.controller.profiler:searchResultsAction',));
                }

                // _profiler
                if (preg_match('#^/_profiler/(?P<token>[^/]++)$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler')), array (  '_controller' => 'web_profiler.controller.profiler:panelAction',));
                }

                // _profiler_router
                if (preg_match('#^/_profiler/(?P<token>[^/]++)/router$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler_router')), array (  '_controller' => 'web_profiler.controller.router:panelAction',));
                }

                // _profiler_exception
                if (preg_match('#^/_profiler/(?P<token>[^/]++)/exception$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler_exception')), array (  '_controller' => 'web_profiler.controller.exception:showAction',));
                }

                // _profiler_exception_css
                if (preg_match('#^/_profiler/(?P<token>[^/]++)/exception\\.css$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_profiler_exception_css')), array (  '_controller' => 'web_profiler.controller.exception:cssAction',));
                }

            }

            if (0 === strpos($pathinfo, '/_configurator')) {
                // _configurator_home
                if (rtrim($pathinfo, '/') === '/_configurator') {
                    if (substr($pathinfo, -1) !== '/') {
                        return $this->redirect($pathinfo.'/', '_configurator_home');
                    }

                    return array (  '_controller' => 'Sensio\\Bundle\\DistributionBundle\\Controller\\ConfiguratorController::checkAction',  '_route' => '_configurator_home',);
                }

                // _configurator_step
                if (0 === strpos($pathinfo, '/_configurator/step') && preg_match('#^/_configurator/step/(?P<index>[^/]++)$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_configurator_step')), array (  '_controller' => 'Sensio\\Bundle\\DistributionBundle\\Controller\\ConfiguratorController::stepAction',));
                }

                // _configurator_final
                if ($pathinfo === '/_configurator/final') {
                    return array (  '_controller' => 'Sensio\\Bundle\\DistributionBundle\\Controller\\ConfiguratorController::finalAction',  '_route' => '_configurator_final',);
                }

            }

        }

        if (0 === strpos($pathinfo, '/entity')) {
            if (0 === strpos($pathinfo, '/entity/form')) {
                // _form_list
                if ($pathinfo === '/entity/form/list') {
                    return array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityFormController::indexAction',  '_route' => '_form_list',);
                }

                // _form_new
                if ($pathinfo === '/entity/form/new') {
                    return array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityFormController::newAction',  '_route' => '_form_new',);
                }

                // _form_edit
                if (0 === strpos($pathinfo, '/entity/form/edit') && preg_match('#^/entity/form/edit/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_form_edit')), array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityFormController::editAction',));
                }

                // _form_delete
                if (0 === strpos($pathinfo, '/entity/form/delete/id') && preg_match('#^/entity/form/delete/id/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not__form_delete;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_form_delete')), array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityFormController::deleteAction',));
                }
                not__form_delete:

                if (0 === strpos($pathinfo, '/entity/formType')) {
                    // _formType_list
                    if ($pathinfo === '/entity/formType/list') {
                        return array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityFormTypeController::indexAction',  '_route' => '_formType_list',);
                    }

                    // _formType_new
                    if ($pathinfo === '/entity/formType/new') {
                        return array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityFormTypeController::newAction',  '_route' => '_formType_new',);
                    }

                    // _formType_edit
                    if (0 === strpos($pathinfo, '/entity/formType/edit') && preg_match('#^/entity/formType/edit/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                        return $this->mergeDefaults(array_replace($matches, array('_route' => '_formType_edit')), array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityFormTypeController::editAction',));
                    }

                    // _formType_delete
                    if (0 === strpos($pathinfo, '/entity/formType/delete/id') && preg_match('#^/entity/formType/delete/id/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not__formType_delete;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => '_formType_delete')), array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityFormTypeController::deleteAction',));
                    }
                    not__formType_delete:

                }

            }

            if (0 === strpos($pathinfo, '/entity/leads')) {
                // _leads_list
                if ($pathinfo === '/entity/leads/list') {
                    return array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityLeadsController::indexAction',  '_route' => '_leads_list',);
                }

                // _leads_edit
                if (0 === strpos($pathinfo, '/entity/leads/edit') && preg_match('#^/entity/leads/edit/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_leads_edit')), array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityLeadsController::editAction',));
                }

            }

            if (0 === strpos($pathinfo, '/entity/referenceList')) {
                // _referenceList_list
                if ($pathinfo === '/entity/referenceList/list') {
                    return array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityReferenceListController::indexAction',  '_route' => '_referenceList_list',);
                }

                // _referenceList_new
                if ($pathinfo === '/entity/referenceList/new') {
                    return array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityReferenceListController::newAction',  '_route' => '_referenceList_new',);
                }

                // _referenceList_edit
                if (0 === strpos($pathinfo, '/entity/referenceList/edit') && preg_match('#^/entity/referenceList/edit/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_referenceList_edit')), array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityReferenceListController::editAction',));
                }

                if (0 === strpos($pathinfo, '/entity/referenceList/delete')) {
                    // _referenceList_delete
                    if (0 === strpos($pathinfo, '/entity/referenceList/delete/id') && preg_match('#^/entity/referenceList/delete/id/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not__referenceList_delete;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => '_referenceList_delete')), array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityReferenceListController::deleteAction',));
                    }
                    not__referenceList_delete:

                    // _referenceList_deleteElement
                    if (0 === strpos($pathinfo, '/entity/referenceList/deleteElement/id') && preg_match('#^/entity/referenceList/deleteElement/id/(?P<id>[^/]++)/(?P<referenceListId>[^/]++)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not__referenceList_deleteElement;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => '_referenceList_deleteElement')), array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityReferenceListController::deleteElementAction',));
                    }
                    not__referenceList_deleteElement:

                }

                // _referenceList_addElement
                if ($pathinfo === '/entity/referenceList/addElement') {
                    return array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityReferenceListController::saveElementAction',  '_route' => '_referenceList_addElement',);
                }

                if (0 === strpos($pathinfo, '/entity/referenceListElement')) {
                    // _referenceListElement_list
                    if (0 === strpos($pathinfo, '/entity/referenceListElement/list') && preg_match('#^/entity/referenceListElement/list/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                        return $this->mergeDefaults(array_replace($matches, array('_route' => '_referenceListElement_list')), array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityReferenceListElementController::indexAction',));
                    }

                    // _referenceListElement_new
                    if ($pathinfo === '/entity/referenceListElement/new') {
                        return array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityReferenceListElementController::newAction',  '_route' => '_referenceListElement_new',);
                    }

                    // _referenceListElement_edit
                    if (0 === strpos($pathinfo, '/entity/referenceListElement/edit') && preg_match('#^/entity/referenceListElement/edit/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                        return $this->mergeDefaults(array_replace($matches, array('_route' => '_referenceListElement_edit')), array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityReferenceListElementController::editAction',));
                    }

                    // _referenceListElement_delete
                    if (0 === strpos($pathinfo, '/entity/referenceListElement/delete/id') && preg_match('#^/entity/referenceListElement/delete/id/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                        if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                            $allow = array_merge($allow, array('GET', 'HEAD'));
                            goto not__referenceListElement_delete;
                        }

                        return $this->mergeDefaults(array_replace($matches, array('_route' => '_referenceListElement_delete')), array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityReferenceListElementController::deleteAction',));
                    }
                    not__referenceListElement_delete:

                }

            }

            if (0 === strpos($pathinfo, '/entity/users')) {
                // _users_list
                if ($pathinfo === '/entity/users/list') {
                    return array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityUsersController::indexAction',  '_route' => '_users_list',);
                }

                // _users_new
                if ($pathinfo === '/entity/users/new') {
                    return array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityUsersController::newAction',  '_route' => '_users_new',);
                }

                // _users_edit
                if (0 === strpos($pathinfo, '/entity/users/edit') && preg_match('#^/entity/users/edit/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_users_edit')), array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityUsersController::editAction',));
                }

                // _users_delete
                if (0 === strpos($pathinfo, '/entity/users/delete/id') && preg_match('#^/entity/users/delete/id/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    if (!in_array($this->context->getMethod(), array('GET', 'HEAD'))) {
                        $allow = array_merge($allow, array('GET', 'HEAD'));
                        goto not__users_delete;
                    }

                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_users_delete')), array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\EntityUsersController::deleteAction',));
                }
                not__users_delete:

            }

        }

        if (0 === strpos($pathinfo, '/client')) {
            if (0 === strpos($pathinfo, '/client/form')) {
                // _client_get_form
                if (preg_match('#^/client/form/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_client_get_form')), array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\FrontController::getFormAction',));
                }

                // _client_get_form_js
                if (0 === strpos($pathinfo, '/client/form/js') && preg_match('#^/client/form/js/(?P<id>[^/]++)$#s', $pathinfo, $matches)) {
                    return $this->mergeDefaults(array_replace($matches, array('_route' => '_client_get_form_js')), array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\FrontController::getFormAsJsAction',));
                }

            }

            // _client_post_form
            if ($pathinfo === '/client/post') {
                return array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\FrontController::postLeadsAction',  '_route' => '_client_post_form',);
            }

        }

        // _utils_navigation
        if (0 === strpos($pathinfo, '/utils/navigation') && preg_match('#^/utils/navigation/(?P<parentRoute>[^/]++)$#s', $pathinfo, $matches)) {
            return $this->mergeDefaults(array_replace($matches, array('_route' => '_utils_navigation')), array (  '_controller' => 'Tellaw\\LeadsFactoryBundle\\Controller\\UtilsController::navigationAction',));
        }

        if (0 === strpos($pathinfo, '/log')) {
            // login_check
            if ($pathinfo === '/login_check') {
                return array('_route' => 'login_check');
            }

            // _security_logout
            if ($pathinfo === '/logout') {
                return array('_route' => '_security_logout');
            }

        }

        throw 0 < count($allow) ? new MethodNotAllowedException(array_unique($allow)) : new ResourceNotFoundException();
    }
}
