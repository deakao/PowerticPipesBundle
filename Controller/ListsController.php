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
        return $this->deleteStandard($objectId);
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

        return new JsonResponse(['list_id' => $entity->getId(), 'name' => $entity->getName()]);
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