<?php
return [
    'name' => 'Powertic Pipes',
    'description' => 'Pipes',
    'version' => '0.4',
    'author' => 'Denis Akao',
    'menu' => [
        'main' => [
            'plugin.powerticpipes.index' => [
                'iconClass' => 'fa-chevron-circle-right',
                'route'     => 'mautic_powerticpipes.pipes_index',
                'priority'  => 99
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
                'arguments' => 'mautic.factory',
                'alias'     => 'pipes',
            ],

            'mautic.lists.type.form' => [
                'class'     => 'MauticPlugin\PowerticPipesBundle\Form\Type\ListsType',
                'arguments' => 'mautic.factory',
                'alias'     => 'lists',
            ],
        ]
    ]
];