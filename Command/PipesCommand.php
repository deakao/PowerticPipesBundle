<?php 
namespace MauticPlugin\PowerticPipesBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use MauticPlugin\PowerticPipesBundle\Model\CardsModel;
use MauticPlugin\PowerticPipesBundle\Model\ListsModel;
use MauticPlugin\PowerticPipesBundle\Model\PipesModel;

class PipesCommand extends ContainerAwareCommand
{
  protected function configure()
  {
    $this->setName('pipes:import:leads')
         ->setDescription('Importar Leads para pipes')
         ->addOption(
           '--pipe',
           '-p',
           InputOption::VALUE_REQUIRED,
           'Pipe Id',
           null
         );
      
    parent::configure();
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $pipe_id = $input->getOption('pipe');
    $container = $this->getContainer();
    $em = $container->get('doctrine.orm.entity_manager');
    $modelFactory = $container->get('mautic.model.factory');
    
    $pipeModel = $modelFactory->getModel('powerticpipes.pipes');
    $entity = $pipeModel->getEntity($pipe_id);

    $listModel = $modelFactory->getModel('powerticpipes.lists');
    $stageModel = $modelFactory->getModel('stage');
    $leadModel = $modelFactory->getModel('lead');
    $cardModel = $modelFactory->getModel('powerticpipes.cards');

    $stagesPublished = $stageModel->getEntities(
        [
            'filter' => [
                'force' => [
                    [
                        'column' => 's.isPublished',
                        'expr'   => 'eq',
                        'value'  => true,
                    ],
                ],
            ],
        ]
    );
    foreach($stagesPublished as $k => $stage){
        $listEntity = $listModel->getEntity();
        $listEntity->setName($stage->getName());
        $listEntity->setSort($k);
        $listEntity->setPipe($entity);
        $listEntity->setStage($stage);
        $listModel->saveEntity($listEntity);
        $list_id = $listEntity->getId();
        $leads = $leadModel->getEntities(
            [
                'filter' => [
                    'force' => [
                        [
                            'column' => 'l.stage_id',
                            'expr'   => 'eq',
                            'value'  => $stage->getId(),
                        ],
                    ],
                ],
            ]
        );
        foreach($leads as $lead){
            $cardEntity = $cardModel->getEntity();
            $cardEntity->setList($listEntity);
            $cardEntity->setLead($lead);
            $cardEntity->setName($lead->getFirstname().' '.$lead->getLastname());
            $cardModel->saveEntity($cardEntity);
        }
        
    }
    $entity->setIsCompleted(true);
    $pipeModel->saveEntity($entity);

    //$output->writeLn('id: '.$pipe_id);
    return 0;
  }
}
