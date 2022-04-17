<?php 
namespace MauticPlugin\PowerticPipesBundle\Controller;

use Mautic\CoreBundle\Controller\AbstractStandardFormController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Mautic\CoreBundle\Controller\AbstractFormController;
use Mautic\CoreBundle\Factory\PageHelperFactoryInterface;
use Mautic\CoreBundle\Helper\DateTimeHelper;

/**
* Class CardsController.
*/
class CardsController extends AbstractStandardFormController
{
    public function deleteAction($objectId)
    {
        $model     = $this->getModel($this->getModelName());
        $entity    = $model->getEntity($objectId);
        $model->deleteEntity($entity);

        return new JsonResponse(
            [
                'success' => 1,
            ]
        );
    }
  
    public function editAction($objectId, $ignorePost = false)
    {
        $model = $this->getModel($this->getModelName());
        $entity = $model->getEntity($objectId);
        $action = $this->generateUrl('mautic_powerticpipes.cards_action', ['objectAction' => 'edit', 'objectId' => $objectId]);
        $editForm = $model->createForm($entity, $this->get('form.factory'), $action);

        if (!$ignorePost && 'POST' == $this->request->getMethod()) {
            $post = $this->request->get('cards');
            $entity->setName($post['name']);
            $entity->setDescription($post['description']);
            if($post['lead']){
                $lead = $this->getModel('lead')->getEntity($post['lead']);
                $entity->setLead($lead);
            } else {
                $entity->setLead(null);
            }
            $model->saveEntity($entity);
            return new JsonResponse(
                [
                    'closeModal' => true,
                    'notifyChange' => true,
                    'name' => $entity->getName(),
                    'id' => $entity->getId(),
                    'date' => date('d/m/Y H:i:s'),
                ]
            );
        }
        
        return $this->delegateView(
            [
                'viewParameters' => [
                    'editForm' => $editForm->createView(),
                ],
                'contentTemplate' => 'PowerticPipesBundle:Pipes:edit_card.html.php',
                'passthroughVars' => [
                    'mauticContent' => 'powerticpipes',
                    'route'         => false,
                ],
            ]
        );
    }

    public function newAction($entity = null)
    {
        $model = $this->getModel($this->getModelName());

        if (!($entity instanceof Cards)) {
            $entity = $model->getEntity();
        }

        if (!$this->get('mautic.security')->isGranted('powerticpipes:cards:create')) {
            return $this->accessDenied();
        }
        $entity->setName($this->request->get('card'));
        $list = $this->getModel('powerticpipes.lists')->getEntity(str_replace('id_', '', $this->request->get('list_id')));
        $entity->setList($list);
        $entity->setSort($this->request->get('order'));
        $entity->setName($this->request->get('name'));

        $model->saveEntity($entity);

        return new JsonResponse([
            'id' => $entity->getId(),
            'date' => date('d/m/Y H:i:s'),
        ]);
    }

    public function updateSortAction()
    {
        $model = $this->getModel($this->getModelName());
        $listModel = $this->getModel('powerticpipes.lists');
        $listEntity = $listModel->getEntity($this->request->get('list_id'));
        $cards = $this->request->get('card_id');
        $orders = $this->request->get('order');
        foreach($cards as $k => $card) {
            $entity = $model->getEntity($card);
            $entity->setSort($orders[$k]);
            if($entity->getList()->getId() != $this->request->get('list_id')) {
                $entity->setList($listEntity);
                $dateModified = new DateTimeHelper();
                $entity->setDateModified($dateModified->getUtcDateTime());
            }
            $model->saveEntity($entity);
        }
        return new JsonResponse(['status' => 1]);
    }

    protected function getModelName()
    {
        return 'powerticpipes.cards';
    }

    protected function getPermissionBase()
    {
        return 'powerticpipes:cards';
    }

    protected function getActionRoute()
    {
        return 'mautic_powerticpipes.cards_action';
    }

    /**
     * @return string
     */
    protected function getControllerBase()
    {
        return 'PowerticPipesBundle:Cards';
    }

    protected function getTemplateName($file)
    {
        return $this->getTemplateBase() . ':' . $file;
    }
}