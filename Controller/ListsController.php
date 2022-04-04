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
    $entity->setName('Nova Lista');
    $entity->setPipe($this->request->get('pipe'));

    $model->saveEntity($entity);

    return $this->ajaxAction(
        [
            'contentTemplate' => 'PowerticPipesBundle:Pipes:form.html.php',
            'passthroughVars' => [
                'activeLink'    => '#mautic_powerticpipes_index',
                'mauticContent' => 'pipes',
                'route'         => $this->generateUrl(
                    $this->getActionRoute(),
                    [
                        'objectAction' => 'new',
                    ]
                ),
            ],
        ]
    );
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