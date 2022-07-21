<?php

namespace MauticPlugin\PowerticPipesBundle\Controller;

use Mautic\CoreBundle\Controller\AjaxController as CommonAjaxController;
use Mautic\CoreBundle\Controller\AjaxLookupControllerTrait;
use Mautic\CoreBundle\Helper\InputHelper;
use Symfony\Component\HttpFoundation\Request;
use MauticPlugin\PowerticPipesBundle\Helper\CardHelper;

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
        
        $helper = new CardHelper($this->get('translator'));
        $now = date('Y-m-d H:i:s');

        foreach($cards as $item) {
            $lead = [];
            if($item['lead']){
                $lead = [
                    'id' => $item['lead']['id'],
                    'name' => substr($item['lead']['firstname'].' '.$item['lead']['lastname'], 0, 100),
                    'email' => substr($item['lead']['email'], 0, 100),
                    'company' => substr($item['lead']['company'], 0, 100),
                    'points' => substr($item['lead']['points'], 0, 100),
                    'position' => substr($item['lead']['position'], 0, 100),
                    'phone' => substr($item['lead']['phone'], 0, 100),
                    'mobile' => substr($item['lead']['mobile'], 0, 100),
                    'address' => substr($item['lead']['address1'].' '.$item['lead']['address2'] .' '.$item['lead']['city'].' '.$item['lead']['state'].' '.$item['lead']['zipcode'], 0, 100),
                    'country' => substr($item['lead']['country'], 0, 100),
                ];
            }
            $card_date = ($item['dateModified'] ? $item['dateModified'] : $item['dateAdded']);

            $output[] = [
                'creator' => $item['createdByUser'],
                'date' => $card_date->format('d/m/Y H:i:s'), 
                'stucked' => $helper->getStuckSince($card_date->format('Y-m-d H:i:s'), $now),
                'date_added' => $item['dateAdded']->format('d/m/Y H:i:s'), 
                'title' => $item['name'], 
                'value' => $item['value'],
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
        $status_import = $pipe->getImportStatus();

        return $this->sendJsonResponse(['is_completed' => $pipe->getIsCompleted(), 'status_import' => $status_import]);
    }
}