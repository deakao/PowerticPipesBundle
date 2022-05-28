<?php

namespace MauticPlugin\PowerticPipesBundle\Controller;

use Mautic\CoreBundle\Controller\AbstractStandardFormController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Mautic\CoreBundle\Controller\AbstractFormController;
use Mautic\CoreBundle\Factory\PageHelperFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Process\Process;


/**
 * Class PipesController.
 */
class PipesController extends AbstractStandardFormController
{
    /**
     * Deletes the entity.
     *
     * @param   $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($objectId)
    {
        return $this->deleteStandard($objectId);
    }

    /**
     * @param      $objectId
     * @param bool $ignorePost
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editAction($objectId, $ignorePost = false)
    {
        return $this->editStandard($objectId, $ignorePost);
    }

    /**
     * @param int $page
     *
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($page = null)
    {
        return $this->indexStandard($page);
    }

    /**
     * Generates new form and processes post data.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction($entity = null)
    {
        $model = $this->getModel($this->getModelName());

        if (!($entity instanceof Pipes)) {
            $entity = $model->getEntity();
        }

        if (!$this->get('mautic.security')->isGranted('powerticpipes:pipes:create')) {
            return $this->accessDenied();
        }

        $page       = $this->get('session')->get('mautic.powerticpipes.page', 1);
        $method     = $this->request->getMethod();
        $pipes      = $this->request->request->get('pipes', []);
        $actionType = 'POST' === $method ? ($pipes['type'] ?? '') : '';
        $action     = $this->generateUrl($this->getActionRoute(), ['objectAction' => 'new']);

        $form = $model->createForm($entity, $this->get('form.factory'), $action);
        
        
        
        $viewParameters = ['page' => $page];

        if ('POST' === $method) {
            $valid = false;

            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    //form is valid so process the data
                    $post = $this->request->get('pipes');
                    if($post['fromStages']){
                        $entity->setIsCompleted(false);
                    } else {
                        $entity->setIsCompleted(true);
                    }

                    $model->saveEntity($entity);
                    
                    if($post['fromStages']){
                        $cmd = 'php '.$this->get('kernel')->getProjectDir().'/bin/console pipes:import:leads --pipe='.$entity->getId().' > /dev/null&';
                        shell_exec($cmd);
                    }


                    $this->addFlash(
                        'mautic.core.notice.created',
                        [
                            '%name%'      => $entity->getName(),
                            '%menu_link%' => 'mautic_powerticpipes.pipes_index',
                            '%url%'       => $this->generateUrl(
                                $this->getActionRoute(),
                                [
                                    'objectAction' => 'edit',
                                    'objectId'     => $entity->getId(),
                                ]
                            ),
                        ]
                    );

                    if ($form->get('buttons')->get('save')->isClicked()) {
                        return $this->viewAction($entity->getId());
                    } else {
                        return $this->editAction($entity->getId(), true);
                    }
                }
            } else {
                $returnUrl = $this->generateUrl('mautic_powerticpipes.pipes_index', $viewParameters);
                $template  = 'PowerticPipesBundle:Pipes:index';
            }

            if ($cancelled || ($valid && $form->get('buttons')->get('save')->isClicked())) {
                return $this->postActionRedirect(
                    [
                        'returnUrl'       => $returnUrl,
                        'viewParameters'  => $viewParameters,
                        'contentTemplate' => $template,
                        'passthroughVars' => [
                            'activeLink'    => '#mautic_powerticpipes.pipes_index',
                            'mauticContent' => 'pipes',
                        ],
                    ]
                );
            }
        }

        $themes = ['PowerticPipesBundle:FormTheme\Action'];
        if ($actionType && !empty($actions['actions'][$actionType]['formTheme'])) {
            $themes[] = $actions['actions'][$actionType]['formTheme'];
        }

        return $this->delegateView(
            [
                'viewParameters' => [
                    'entity'  => $entity,
                    'newAction' => true,
                    'form'    => $this->setFormTheme($form, 'PowerticPipesBundle:Pipes:form.html.php', $themes),
                ],
                
                'contentTemplate' => 'PowerticPipesBundle:Pipes:form.html.php',
                'passthroughVars' => [
                    'activeLink'    => '#mautic_powerticpipes.pipes_index',
                    'mauticContent' => 'pipes',
                    'route'         => $this->generateUrl(
                        $this->getActionRoute(),
                        [
                            'objectAction' => (!empty($valid) ? 'edit' : 'new'),
                            'objectId'     => ($entity) ? $entity->getId() : 0,
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     * View a specific campaign.
     *
     * @param $objectId
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($objectId)
    {
        $model    = $this->getModel($this->getModelName());
        $entity   = $model->getEntity($objectId);
        $security = $this->get('mautic.security');
        $modelCards = $this->getModel('powerticpipes.cards');

        if (null === $entity) {
            $page = $this->get('session')->get('mautic.'.$this->getSessionBase().'.page', 1);

            return $this->postActionRedirect(
                $this->getPostActionRedirectArguments(
                    [
                        'returnUrl'       => $this->generateUrl($this->getIndexRoute(), ['page' => $page]),
                        'viewParameters'  => ['page' => $page],
                        'contentTemplate' => $this->getControllerBase().':'.$this->getPostActionControllerAction('view'),
                        'passthroughVars' => [
                            'mauticContent' => $this->getJsLoadMethodPrefix(),
                        ],
                        'flashes' => [
                            [
                                'type'    => 'error',
                                'msg'     => $this->getTranslatedString('error.notfound'),
                                'msgVars' => ['%id%' => $objectId],
                            ],
                        ],
                    ],
                    'view'
                )
            );
        } elseif (!$this->checkActionPermission('view', $entity)) {
            return $this->accessDenied();
        }

        $this->setListFilters();


        // Generate route
        $routeVars = [
            'objectAction' => 'view',
            'objectId'     => $entity->getId(),
        ];

        $route = $this->generateUrl($this->getActionRoute(), $routeVars);
        $board = $model->getFull($entity->getId())[0];
        $boards = [];
        foreach ($board['lists'] as $list) {
            $boards[$list['id']]['id'] = 'id_'.$list['id'];
            $boards[$list['id']]['title'] = $list['name'];
            $boards[$list['id']]['item'] = [];

            $boards[$list['id']]['current_page'] = 1;
            $boards[$list['id']]['per_page'] = 5;
            $boards[$list['id']]['total_items'] = (int) $modelCards->getCountFromList($list['id']);
            $boards[$list['id']]['total_pages'] = ceil($boards[$list['id']]['total_items'] / $boards[$list['id']]['per_page']);
            $cards = $modelCards->getFromList($list['id'], $boards[$list['id']]['per_page']);
            
            foreach($cards as $item) {
                $lead = [];
                if($item['lead']){
                    $lead = [
                        'id' => $item['lead']['id'],
                        'name' => $item['lead']['firstname'].' '.$item['lead']['lastname'],
                        'email' => $item['lead']['email'],
                    ];
                }

                $boards[$list['id']]['item'][] = [
                    'creator' => $item['createdByUser'],
                    'date' => ($item['dateModified'] ? $item['dateModified']->format('d/m/Y H:i:s') : $item['dateAdded']->format('d/m/Y H:i:s')), 
                    'title' => $item['name'], 
                    'lead' => $lead,
                    'id' => $item['id']
                ];
            }
        }


        $delegateArgs = [
            'viewParameters' => [
                'entity'     => $entity,
                'boards'     => array_values($boards),
                'addCardAction' => $this->generateUrl('mautic_powerticpipes.cards_action', ['objectAction' => 'new']),
                'updateListSortAction' => $this->generateUrl('mautic_powerticpipes.lists_action', ['objectAction' => 'updateSort']),
                'removeListAction' => $this->generateUrl('mautic_powerticpipes.lists_action', ['objectAction' => 'delete']),
                'removeCardAction' => $this->generateUrl('mautic_powerticpipes.cards_action', ['objectAction' => 'delete']),
                'editCardAction' => $this->generateUrl('mautic_powerticpipes.cards_action', ['objectAction' => 'edit']),
                'updateListNameAction' => $this->generateUrl('mautic_powerticpipes.lists_action', ['objectAction' => 'updateName']),
                'updateCardSortAction' => $this->generateUrl('mautic_powerticpipes.cards_action', ['objectAction' => 'updateSort']),
                'tmpl'        => $this->request->isXmlHttpRequest() ? $this->request->get('tmpl', 'index') : 'index',
                'permissions' => $security->isGranted(
                    [
                        $this->getPermissionBase().':view',
                        $this->getPermissionBase().':viewown',
                        $this->getPermissionBase().':viewother',
                        $this->getPermissionBase().':create',
                        $this->getPermissionBase().':edit',
                        $this->getPermissionBase().':editown',
                        $this->getPermissionBase().':editother',
                        $this->getPermissionBase().':delete',
                        $this->getPermissionBase().':deleteown',
                        $this->getPermissionBase().':deleteother',
                        $this->getPermissionBase().':publish',
                        $this->getPermissionBase().':publishown',
                        $this->getPermissionBase().':publishother',
                    ],
                    'RETURN_ARRAY',
                    null,
                    true
                ),
            ],
            'contentTemplate' => $this->getTemplateName('details.html.php'),
            'passthroughVars' => [
                'mauticContent' => $this->getJsLoadMethodPrefix(),
                'route'         => $route,
            ],
            'objectId' => $objectId,
            'entity'   => $entity,
        ];

        return $this->delegateView(
            $this->getViewArguments($delegateArgs, 'view')
        );
    }

    public function cloneAction($objectId)
    {
        $model  = $this->getModel($this->getModelName());
        $entity = $model->getEntity($objectId);

        if (null != $entity) {
            if (!$this->get('mautic.security')->isGranted($this->getPermissionBase().':create')) {
                return $this->accessDenied();
            }

            $entity = clone $entity;
            $entity->setIsPublished(false);
        }

        return $this->newAction($entity);
    }

    public function batchDeleteAction()
    {
        $page      = $this->get('session')->get('mautic_powerticpipes.pipes_index.page', 1);
        $returnUrl = $this->generateUrl('mautic_powerticpipes.pipes_index', ['page' => $page]);
        $flashes   = [];

        $postActionVars = [
            'returnUrl'       => $returnUrl,
            'viewParameters'  => ['page' => $page],
            'contentTemplate' => 'PowerticPipesBundle:Pipes:index',
            'passthroughVars' => [
                'activeLink'    => '#mautic_powerticpipes.pipes_index',
                'mauticContent' => 'powerticpipes',
            ],
        ];

        if ('POST' == $this->request->getMethod()) {
            $model     = $this->getModel($this->getModelName());
            $ids       = json_decode($this->request->query->get('ids', '{}'));
            $deleteIds = [];

            // Loop over the IDs to perform access checks pre-delete
            foreach ($ids as $objectId) {
                $entity = $model->getEntity($objectId);

                if (null === $entity) {
                    $flashes[] = [
                        'type'    => 'error',
                        'msg'     => $this->getTranslatedString('error.notfound'),
                        'msgVars' => ['%id%' => $objectId],
                    ];
                } elseif (!$this->get('mautic.security')->isGranted($this->getPermissionBase().':delete')) {
                    $flashes[] = $this->accessDenied(true);
                } elseif ($model->isLocked($entity)) {
                    $flashes[] = $this->isLocked($postActionVars, $entity, 'powerticpipes', true);
                } else {
                    $deleteIds[] = $objectId;
                }
            }

            // Delete everything we are able to
            if (!empty($deleteIds)) {
                $entities = $model->deleteEntities($deleteIds);

                $flashes[] = [
                    'type'    => 'notice',
                    'msg'     => $this->getTranslatedString('notice.batch_deleted'),
                    'msgVars' => [
                        '%count%' => count($entities),
                    ],
                ];
            }
        } //else don't do anything

        return $this->postActionRedirect(
            array_merge(
                $postActionVars,
                [
                    'flashes' => $flashes,
                ]
            )
        );
    }

    /**
     * Get this controller's model name.
     */
    protected function getModelName()
    {
        return 'powerticpipes.pipes';
    }

    protected function getPermissionBase()
    {
        return 'powerticpipes:pipes';
    }

    protected function getActionRoute()
    {
        return 'mautic_powerticpipes.pipes_action';
    }

    /**
     * @return string
     */
    protected function getControllerBase()
    {
        return 'PowerticPipesBundle:Pipes';
    }

    protected function getTemplateBase()
    {
        return 'PowerticPipesBundle:Pipes';
    }

    protected function getTemplateName($file)
    {
        return $this->getTemplateBase() . ':' . $file;
    }

}