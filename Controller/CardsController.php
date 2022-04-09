<?php 
namespace MauticPlugin\PowerticPipesBundle\Controller;

use Mautic\CoreBundle\Controller\AbstractStandardFormController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Mautic\CoreBundle\Controller\AbstractFormController;
use Mautic\CoreBundle\Factory\PageHelperFactoryInterface;

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
        return $this->editStandard($objectId, $ignorePost);
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

        return new JsonResponse(['id' => $entity->getId()]);
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
            $entity->setList($listEntity);
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