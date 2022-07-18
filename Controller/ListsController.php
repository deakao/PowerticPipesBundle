<?php 
namespace MauticPlugin\PowerticPipesBundle\Controller;

use Mautic\CoreBundle\Controller\AbstractStandardFormController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Mautic\CoreBundle\Controller\AbstractFormController;
use Mautic\CoreBundle\Factory\PageHelperFactoryInterface;

/**
* Class ListsController.
*/
class ListsController extends AbstractStandardFormController
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

        if (!($entity instanceof Lists)) {
            $entity = $model->getEntity();
        }

        if (!$this->get('mautic.security')->isGranted('powerticpipes:lists:create')) {
            return $this->accessDenied();
        }
        $entity->setName($this->translator->trans('plugin.powerticpipes.add_list'));
        $pipe = $this->getModel('powerticpipes.pipes')->getEntity($this->request->get('pipe_id'));
        $entity->setPipe($pipe);
        $entity->setSort($this->request->get('order'));

        $model->saveEntity($entity);

        return new JsonResponse([
            'list_id' => $entity->getId(), 
            'name' => $entity->getName(),
            'current_page' => 1,
            'per_page' => 20,
            'total_items' => 0,
            'total_pages' => 1,
            'item' => [],
            'total_value' => 0
        ]);
    }

    public function updateSortAction()
    {
        $model = $this->getModel($this->getModelName());
        $lists = $this->request->get('list_id');
        $orders = $this->request->get('order');
        foreach($lists as $k => $list_id) {
            $list = $model->getEntity($list_id);
            $list->setSort($orders[$k]);
            $model->saveEntity($list);
        }
        return new JsonResponse(['status' => 1]);
    }

    public function updateNameAction()
    {
        $model = $this->getModel($this->getModelName());
        $list = $model->getEntity($this->request->get('list_id'));
        $list->setName($this->request->get('name'));
        $model->saveEntity($list);
        return new JsonResponse(['status' => 1]);
    }

    protected function getModelName()
    {
        return 'powerticpipes.lists';
    }

    protected function getPermissionBase()
    {
        return 'powerticpipes:lists';
    }

    protected function getActionRoute()
    {
        return 'mautic_powerticpipes.lists_action';
    }

    /**
     * @return string
     */
    protected function getControllerBase()
    {
        return 'PowerticPipesBundle:Lists';
    }

    protected function getTemplateBase()
    {
        return 'PowerticPipesBundle:Lists';
    }

    protected function getTemplateName($file)
    {
        return $this->getTemplateBase() . ':' . $file;
    }
}