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

    protected function cardsListAction(Request $request)
    {
        $modelCards = $this->getModel('powerticpipes.cards');
        $modelLists = $this->getModel('powerticpipes.lists');
        $list_id = $request->query->get('list_id');
        $offset = $request->query->get('offset');
        $limit = $request->query->get('per_page');

        $output = [];
        $cards = $modelCards->getFromList($list_id, $limit, $offset);
        foreach($cards as $item) {
            $lead = [];
            if($item['lead']){
                $lead = [
                    'id' => $item['lead']['id'],
                    'name' => $item['lead']['firstname'].' '.$item['lead']['lastname'],
                    'email' => $item['lead']['email'],
                    'company' => $item['lead']['company'],
                ];
            }

            $output[] = [
                'creator' => $item['createdByUser'],
                'date' => ($item['dateModified'] ? $item['dateModified']->format('d/m/Y H:i:s') : $item['dateAdded']->format('d/m/Y H:i:s')), 
                'date_added' => $item['dateAdded']->format('d/m/Y H:i:s'), 
                'title' => $item['name'], 
                'lead' => $lead,
                'id' => $item['id']
            ];
        }
        return $this->sendJsonResponse(['cards' => $output]);
    }

    protected function pipeCompletedAction(Request $request)
    {
        $pipeModel = $this->getModel('powerticpipes.pipes');
        $pipe_id = $request->query->get('pipe_id');
        $pipe = $pipeModel->getEntity($pipe_id);

        return $this->sendJsonResponse(['is_completed' => $pipe->getIsCompleted()]);
    }
}