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
           InputOption::VALUE_OPTIONAL,
           'Pipe Id',
           null
         )
         ->addOption(
           '--create',
           '-c',
           InputOption::VALUE_OPTIONAL,
           'Create lists',
           null
         );
      
    parent::configure();
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $pipe_id = $input->getOption('pipe');
    $create = $input->getOption('create');
    if($create and !$pipe_id){
        $output->writeln('<error>Pipe Id is required</error>');
        return false;
    }
    $container = $this->getContainer();
    $em = $container->get('doctrine.orm.entity_manager');
    $modelFactory = $container->get('mautic.model.factory');
    
    
    $pipeModel = $modelFactory->getModel('powerticpipes.pipes');
    $where = [];
    if($pipe_id){
        $where = [
            'filter' => [
                'force' => [
                    [
                        'column' => 'powertic_pipes.id',
                        'expr'   => 'eq',
                        'value'  => $pipe_id,
                    ],
                ],
            ],
        ];
    }
    $pipeEntities = $pipeModel->getEntities($where);
    $listModel = $modelFactory->getModel('powerticpipes.lists');
    $stageModel = $modelFactory->getModel('stage');        
    $cardModel = $modelFactory->getModel('powerticpipes.cards');
    $leadModel = $modelFactory->getModel('lead');
    $leadsRepository = $leadModel->getRepository();
    
    foreach($pipeEntities as $entity) {
        
        if($create){

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

                $leadsTotal = $leadsRepository->createQueryBuilder('l')
                            ->select('count(l.id)')
                            ->where('l.stage = :stage')
                            ->setParameter('stage', $stage->getId())
                            ->getQuery()
                            ->getSingleScalarResult();
                $pages = ceil($leadsTotal / 100);
                for ($i=0; $i < $pages; $i++) { 
                
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
                            'limit' => 100,
                            'start' => ($i * 100),
                        ]
                    );
                    foreach($leads as $lead){
                        $cardEntity = $cardModel->getEntity();
                        $cardEntity->setList($listEntity);
                        $cardEntity->setLead($lead);
                        $cardEntity->setName($lead->getFirstname().' '.$lead->getLastname());
                        $cardModel->saveEntity($cardEntity);
                    }
                    $import_status = $entity->getImportStatus();
                    $import_status['processed'] += count($leads);
                    $entity->setImportStatus($import_status);
                    $pipeModel->saveEntity($entity);
                }
                
            }
            $entity->setIsCompleted(true);
            $pipeModel->saveEntity($entity);
        } else {
            $listEntities = $listModel->getEntitiesFromPipe($entity->getId(), true);

            foreach($listEntities as $listEntity){
                $leadsTotal = $leadsRepository->createQueryBuilder('l')
                            ->select('count(l.id)')
                            ->where('l.stage = :stage')
                            ->setParameter('stage', $listEntity->getStage()->getId())
                            ->getQuery()
                            ->getSingleScalarResult();
                $pages = ceil($leadsTotal / 100);
                for ($i=0; $i < $pages; $i++) { 
                    $leads = $leadModel->getEntities(
                        [
                            'filter' => [
                                'force' => [
                                    [
                                        'column' => 'l.stage_id',
                                        'expr'   => 'eq',
                                        'value'  => $listEntity->getStage()->getId(),
                                    ],
                                ],
                            ],
                            'limit' => 100,
                            'start' => ($i * 100),
                        ]
                    );
                    foreach($leads as $lead){
                        $checkCardLead = $cardModel->getEntityFromLead($lead->getId(), $listEntity->getId());
                        if(!$checkCardLead){
                            $cardEntity = $cardModel->getEntity();
                            $cardEntity->setList($listEntity);
                            $cardEntity->setLead($lead);
                            $cardEntity->setName($lead->getFirstname().' '.$lead->getLastname());
                            $cardModel->saveEntity($cardEntity);
                        }
                    }
                }
            }
        }
    }

    //$output->writeLn('id: '.$pipe_id);
    return 0;
  }
}
