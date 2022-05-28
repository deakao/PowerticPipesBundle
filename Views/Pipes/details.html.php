<?php

/*
 * @copyright   2014 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
echo $view['assets']->includeStylesheet('plugins/PowerticPipesBundle/Assets/css/jkanban.min.css');

$view->extend('MauticCoreBundle:Default:content.html.php');
$view['slots']->set('headerTitle', $entity->getName());

$view['slots']->set(
    'actions',
    $view->render(
        'MauticCoreBundle:Helper:page_actions.html.php',
        [
            'item'            => $entity,
            'templateButtons' => [
                'edit'   => $permissions['powerticpipes:pipes:edit'],
                'clone'  => $permissions['powerticpipes:pipes:create'],
                'delete' => $permissions['powerticpipes:pipes:delete'],
                'close'  => $permissions['powerticpipes:pipes:view'],
            ],
            'routeBase' => 'powerticpipes.pipes',
        ]
    )
);

?>

<!-- start: box layout -->
<div class="box-layout">
    <!-- left section -->
    <div class="col-md-12 bg-white height-auto">
        <div class="bg-auto">
            <!-- bar detail header -->
            <div class="pr-md pl-md pt-lg pb-lg">
                <div class="box-layout">
                    <div class="col-xs-6 va-m">
                        <div class="text-white dark-sm mb-0"><?php echo $entity->getDescription(); ?></div>
                    </div>
                </div>
            </div>
            <!--/ bar detail header -->

            <!-- bar detail collapseable -->
            <div class="collapse" id="bar-details">
                <div class="pr-md pl-md pb-md">
                    <div class="panel shd-none mb-0">
                        <table class="table table-bordered table-striped mb-0">
                            <tbody>
                            <?php echo $view->render(
                                'MauticCoreBundle:Helper:details.html.php',
                                ['entity' => $entity]
                            ); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!--/ bar detail collapseable -->
        </div>

        <div class="bg-auto bg-dark-xs">
            <!-- bar detail collapseable toggler -->
            <div class="hr-expand nm">
                <span data-toggle="tooltip" title="Detail">
                    <a href="javascript:void(0)" class="arrow text-muted collapsed" data-toggle="collapse"
                       data-target="#bar-details"><span
                            class="caret"></span> <?php echo $view['translator']->trans('mautic.core.details'); ?></a>
                </span>
            </div>
            <!--/ bar detail collapseable toggler -->
        </div>
        <?php if(!$entity->getIsCompleted()) : ?>
            <div class="pr-md pl-md pt-lg pb-lg">
                <p class="text-center"><span class="fa fa-spinner fa-spin fa-3x"></span></p>
                <p class="text-center"><?php echo $view['translator']->trans('plugin.powerticpipes.loadingLeads') ?></p>
            </div>
            <script>
                function getStatusPipe(){
                    setTimeout(function () {
                        mQuery.getJSON(mauticAjaxUrl+'?action=plugin:powerticPipes:pipeCompleted&pipe_id=<?php echo $entity->getId() ?>', function(data){
                            if(data.is_completed){
                                window.location.reload();
                            } else {
                                getStatusPipe()
                            }
                        });
                    }, 5000);
                }
                getStatusPipe();
            </script>
        <?php else: ?>
            <div class="pr-md pl-md pt-lg pb-lg">
                <a href="<?php echo $view['router']->path('mautic_powerticpipes.lists_action', ['pipe_id' => $entity->getId(), 'objectAction' => 'new']); ?>" class="btn btn-success" id="add_list"><i class="fa fa-plus"></i> <?php echo $view['translator']->trans('plugin.powerticpipes.add_list'); ?></a>
            </div>
            
            <script>
                var addCardAction = "<?php echo $addCardAction ?>";
                var updateListSortAction = "<?php echo $updateListSortAction ?>";
                var updateListNameAction = "<?php echo $updateListNameAction ?>";
                var updateCardSortAction = "<?php echo $updateCardSortAction ?>";
                var editCardAction = "<?php echo $editCardAction ?>";
                var removeListAction = "<?php echo $removeListAction ?>";
                var removeCardAction = "<?php echo $removeCardAction ?>";
                var kanban_content = <?php echo json_encode($boards) ?>;
            </script>
            
            <div id="myKanban"></div>
        <?php endif; ?>
    </div>
    <!--/ left section -->
</div>
<!--/ end: box layout -->
<?php 
echo $view['assets']->includeScript('plugins/PowerticPipesBundle/Assets/js/jkanban.min.js');

echo $view['assets']->includeScript('plugins/PowerticPipesBundle/Assets/js/pipes.js', 'composePipeCreate', 'composePipeWatcher');
?>