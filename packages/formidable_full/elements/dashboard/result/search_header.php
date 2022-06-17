<?php 
    defined('C5_EXECUTE') or die("Access Denied."); 

    $session = Core::make('app')->make('session');
    $f = \Concrete\Package\FormidableFull\Src\Formidable\Form::getByID($session->get('formidableFormID'));
    $available = \Concrete\Package\FormidableFull\Src\Formidable::getAllForms();

    $form = Core::make('helper/form');
    $preview_token = Core::make('token')->generate('formidable_preview');
    $result_token = Core::make('token')->generate('formidable_result');
?>

<div class="ccm-header-search-form ccm-ui" data-header="file-manager">

    <form method="get" action="<?php echo URL::to('/formidable/dialog/dashboard/results/search/basic')?>" data-search-form=""> 

        <div class="input-group">
            <?php if (is_array($available) && count($available)) { ?>
                <div class="ccm-header-search-form-select">
                    <?php echo $form->select('formID', $available, $f?$f->getFormID():'', array('style' => 'float: right')); ?>
                </div>
            <?php } ?>
            <?php if ($f && $f->hasPermissions('results')) { ?>
                <div class="ccm-header-search-form-input">
                    <a class="ccm-header-reset-search" href="#" data-button-action-url="<?php echo URL::to('/formidable/dialog/dashboard/results/search/clear')?>" data-button-action="clear-search"><?php echo t('Reset Search')?></a>
                    <a class="ccm-header-launch-advanced-search" href="<?php echo URL::to('/formidable/dialog/dashboard/results/search/advanced_search')?>" data-launch-dialog="advanced-search"><?php echo t('Advanced')?></a>
                    <input type="text" class="form-control" autocomplete="off" name="fKeywords" placeholder="<?php echo t('Search')?>">                
                </div>   
                <span class="input-group-btn">
                    <button class="btn btn-info" type="submit"><i class="fa fa-search"></i></button>
                </span>
            <?php } ?>
        </div>
        <ul class="ccm-header-search-navigation">            
            <?php if ($f) { ?>
                <li><a href="<?php echo URL::to('/formidable/dialog/dashboard/forms/preview'); ?>?formID=<?php echo $f->getFormID(); ?>&amp;ccm_token=<?php echo $preview_token; ?>" class="link-primary dialog-launch" dialog-title="<?php echo t('Preview Form'); ?>" dialog-width="900" dialog-height="600" dialog-modal="true"><i class="fa fa-eye"></i> <?php echo t('Preview') ?></a></li>   
                <?php if ($f->hasPermissions('form')) { ?>              
                    <li><a href="<?php echo URL::to('/dashboard/formidable/forms/edit/', $f->getFormID()); ?>" class=""><i class="fa fa-pencil"></i> <?php echo t('Edit form')?></a></li> 
                <?php } ?>
                <?php if ($f->hasPermissions('results')) { ?>
                    <li>
                        <div class="dropdown">
                            <a href="javascript:;" class="link-export" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-download"></i> <?php echo t('Export')?></a>
                            <ul class="dropdown-menu">
                                <li><a href="<?php echo URL::to('/formidable/dialog/dashboard/results/csv').'?formID='.$f->formID.'&ccm_token='.$result_token;?>"><?php echo t('Export as CSV') ?></a></li>
                                <li><a href="<?php echo URL::to('/formidable/dialog/dashboard/results/xls').'?formID='.$f->formID.'&ccm_token='.$result_token;?>"><?php echo t('Export as XLS') ?> <span class="label label-info"><?php echo t('dirty'); ?></span></a></li>                                
                            </ul>
                        </div>
                    </li> 
                    <li><a href="<?php echo URL::to('/formidable/dialog/dashboard/results/delete_all').'?formID='.$f->getFormID().'&ccm_token='.$result_token; ?>" dialog-width="520" dialog-height="100" dialog-modal="true" class="text-danger dialog-launch link-delete"><i class="fa fa-trash"></i> <?php echo t('Delete all')?></a></li> 
                <?php } ?>
            <?php } else { ?>
                <li><a href="<?php echo URL::to('/dashboard/formidable/forms/add'); ?>" class="btn btn-success"><i class="fa fa-plus"></i> <?php echo t('Add form')?></a></li> 
            <?php } ?>        
        </ul>
    </form>
</div>

<div class="clearfix"></div>