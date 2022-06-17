<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<?php if (is_array($forms) && count($forms)) { ?>
    <div class="formidable-form">  
        <div class="form-group">
            <label for="formID" class="control-label"><?= t('Form') ?> <span class="ccm-required">*</span></label>
            <?php echo $form->select('formID', $forms, $controller->formID, ['class' => 'form-control']);?>
        </div>
    </div>    
<?php } else { ?>
    <div class="alert alert-info"><?=t('No forms created');?></div>
<?php } ?>