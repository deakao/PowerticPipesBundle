<?php
return [
    'name' => 'Powertic Pipes',
    'description' => 'Pipes',
    'version' => '0.4',
    'author' => 'Denis Akao',
    'menu' => [
        'main' => [
            'plugin.powerticpipes.index' => [
                'iconClass' => 'fa-trello',
                'access'    => 'powerticpipes:pipes:view',
                'route'     => 'mautic_powerticpipes.pipes_index',
                'priority'  => 25
            ]
        ]
    ],
    'routes'   => [
        'main' => [
            
            'mautic_powerticpipes.pipes_index' => [
                'path'       => '/powerticpipes/pipes/{page}',
                'controller' => 'PowerticPipesBundle:Pipes:index',
            ],
            'mautic_powerticpipes.pipes_action' => [
                'path'       => '/powerticpipes/pipes/{objectAction}/{objectId}',
                'controller' => 'PowerticPipesBundle:Pipes:execute',
            ],
            'mautic_powerticpipes.lists_action' => [
                'path'       => '/powerticpipes/lists/{objectAction}/{objectId}',
                'controller' => 'PowerticPipesBundle:Lists:execute',
            ],
            'mautic_powerticpipes.cards_action' => [
                'path'       => '/powerticpipes/cards/{objectAction}/{objectId}',
                'controller' => 'PowerticPipesBundle:Cards:execute',
            ]
        ]
    ],
    'services' => [
        'models' => [
            'mautic.powerticpipes.model.pipes' => [
                'class'     => 'MauticPlugin\PowerticPipesBundle\Model\PipesModel'
            ],
            'mautic.powerticpipes.model.lists' => [
                'class'     => 'MauticPlugin\PowerticPipesBundle\Model\ListsModel'
            ],
            'mautic.powerticpipes.model.cards' => [
                'class'     => 'MauticPlugin\PowerticPipesBundle\Model\CardsModel'
            ],
        ],
        'forms' => [
            'mautic.pipes.type.form' => [
                'class'     => 'MauticPlugin\PowerticPipesBundle\Form\Type\PipesType',
                'arguments' => [
                    'mautic.security',
                ],
                'alias'     => 'pipes',
            ],

            'mautic.lists.type.form' => [
                'class'     => 'MauticPlugin\PowerticPipesBundle\Form\Type\ListsType',
                'arguments' => 'mautic.factory',
                'alias'     => 'lists',
            ],
        ],
        'integrations' => [
            'mautic.integration.powerticpipes' => [
                'class'     => \MauticPlugin\PowerticPipesBundle\Integration\PowerticPipesIntegration::class,
                'arguments' => [
                    'event_dispatcher',
                    'mautic.helper.cache_storage',
                    'doctrine.orm.entity_manager',
                    'session',
                    'request_stack',
                    'router',
                    'translator',
                    'logger',
                    'mautic.helper.encryption',
                    'mautic.lead.model.lead',
                    'mautic.lead.model.company',
                    'mautic.helper.paths',
                    'mautic.core.model.notification',
                    'mautic.lead.model.field',
                    'mautic.plugin.model.integration_entity',
                    'mautic.lead.model.dnc',
                ],
            ],
        ],
    ]
];