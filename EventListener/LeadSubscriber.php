<?php 
namespace MauticPlugin\PowerticPipesBundle\EventListener;
use Mautic\LeadBundle\Model\LeadModel;
use MauticPlugin\PowerticPipesBundle\Model\CardsModel;
use MauticPlugin\PowerticPipesBundle\Model\ListsModel;
use Mautic\LeadBundle\LeadEvents;
use Mautic\LeadBundle\Event\LeadEvent;
use Mautic\StageBundle\Model\StageModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Routing\RouterInterface;


class LeadSubscriber implements EventSubscriberInterface
{
  private $leadModel;
  private $cardsModel;
  private $stageModel;

  public function __construct(
      LeadModel $leadModel,
      CardsModel $cardsModel,
      StageModel $stageModel,
      ListsModel $listsModel
  ) {
      $this->leadModel = $leadModel;
      $this->cardsModel = $cardsModel;
      $this->stageModel = $stageModel;
      $this->listsModel = $listsModel;
  }

  /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            LeadEvents::LEAD_POST_SAVE => ['onLeadSave', 0],
        ];
    }

    public function onLeadSave(LeadEvent $event)
    {
      $lead = $event->getLead();
      if ($details = $event->getChanges()) {
        if(array_key_exists('stage', $details)){
          $stages = $details['stage'];
          $stageEntityTo = $this->stageModel->getEntity($stages[1]);
          $listsTo = $this->listsModel->getEntitiesFromStage($stageEntityTo);
          $cardRepository = $this->cardsModel->getRepository();
          $cards = $cardRepository->getEntities(['lead_id' => $lead->getId()]);
          foreach ($cards as $card) {
            $currentList = $card->getList();
            $listPipeStage = $this->listsModel->getRepository()->findBy(['pipe' => $currentList->getPipe(), 'stage' => $stageEntityTo]);
            if(!empty($listPipeStage)){
              $card->setList($listPipeStage[0]);
              $this->cardsModel->saveEntity($card);
            }
          }
        }
      }
    }
}