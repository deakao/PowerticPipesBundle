<?php

$view->extend('MauticCoreBundle:Default:content.html.php');
if (!$view['slots']->get('mauticContent')) {
    if (isset($mauticContent)) {
        $view['slots']->set('mauticContent', $mauticContent);
    }
}

$view['slots']->set('headerTitle', 'Pipes');

$view['slots']->set(
    'actions',
    $view->render(
        'MauticCoreBundle:Helper:page_actions.html.php',
        [
            'templateButtons' => [
                'new' => $permissions[$permissionBase.':create'],
            ],
            'actionRoute'     => $actionRoute,
            'indexRoute'      => $indexRoute,
            'translationBase' => $translationBase,
        ]
    )
);
?>

<div class="panel panel-default bdr-t-wdh-0 mb-0">
    <?php echo $view->render(
        'MauticCoreBundle:Helper:list_toolbar.html.php',
        [
            'searchValue'      => $searchValue,
            'searchHelp'       => isset($searchHelp) ? $searchHelp : '',
            'action'           => $currentRoute,
            'actionRoute'      => $actionRoute,
            'indexRoute'       => $indexRoute,
            'translationBase'  => $translationBase,
            'preCustomButtons' => (isset($toolBarButtons)) ? $toolBarButtons : null,
            'templateButtons'  => [
                'delete' => $permissions[$permissionBase.':delete'],
            ],
            'filters' => (isset($filters)) ? $filters : [],
        ]
    ); ?>

    <div class="page-list">
        <?php echo $view['content']->getCustomContent('content.above', $mauticTemplateVars); ?>
        <?php $view['slots']->output('_content'); ?>
        <?php echo $view['content']->getCustomContent('content.below', $mauticTemplateVars); ?>
    </div>
</div>