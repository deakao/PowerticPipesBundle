<?php

namespace MauticPlugin\PowerticPipesBundle\Controller;

use Mautic\CoreBundle\Controller\AjaxController as CommonAjaxController;
use Mautic\CoreBundle\Controller\AjaxLookupControllerTrait;
use Mautic\CoreBundle\Helper\InputHelper;
use Symfony\Component\HttpFoundation\Request;

class AjaxController extends CommonAjaxController
{
    use AjaxLookupControllerTrait;

    protected function searchContactAction(Request $request)
    {
        $filter    = InputHelper::clean($request->query->get('search'));
        $model = $this->getModel('lead');
        
        $results = $model->getRepository()->createQueryBuilder('l')
            ->select('partial l.{id, firstname, lastname, email}')
            ->where('l.firstname LIKE :filter OR l.lastname LIKE :filter OR l.email LIKE :filter')
            ->setParameter('filter', "%{$filter}%")
            ->getQuery()
            ->getArrayResult();
        
        $dataArray = [];
        foreach ($results as $r) {
            $name        = $r['firstname'].' '.$r['lastname']. ' ('.$r['email'].')';
            $dataArray[] = [
                'label' => $name,
                'value' => $r['id'],
            ];
        }

        
        return $this->sendJsonResponse(['leads' => $dataArray]);
    }
}