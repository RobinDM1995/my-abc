<?php 
defined('C5_EXECUTE') or die("Access Denied.");

$c = Page::getCurrentPage();

$formID = 0;
$disabled = true;
if(is_object($f) && $f->getFormID()) {
	$disabled = false;
	$formID = $f->getFormID();
}
$token = Core::make('token')->generate('formidable_preview');
?>

<div class="ccm-dashboard-header-buttons btn-group">
	<?php if (!$disabled) { ?>	
		<a href="<?php echo URL::to('/formidable/dialog/dashboard/forms/preview'); ?>?formID=<?php echo $formID; ?>&amp;ccm_token=<?php echo $token; ?>" class="btn btn-default dialog-launch" dialog-title="<?php echo t('Preview Form'); ?>" dialog-width="900" dialog-height="600" dialog-modal="true"><i class="fa fa-eye"></i> <?php echo t('Preview') ?></a>
		<?php if (is_object($f) && $f->hasPermissions('results')) { ?>
			<a href="<?php echo (URL::to('/dashboard/formidable/results/').'?formID='.$formID); ?>" class="btn btn-default"><i class="fa fa-list"></i> <?php echo t('Results') ?></a>
		<?php } ?>
	<?php } ?>
	<a href="<?php echo URL::to('/dashboard/formidable/forms/'); ?>" class="btn btn-default" ><i class="fa fa-undo"></i> <?php echo t('Back to list') ?></a>
</div>

<div class="form-tabs">
	<?php 
	echo Concrete\Core\Application\Service\UserInterface::tabs(array(
		array(
			!$disabled?URL::to('/dashboard/formidable/forms/', 'edit/'.$formID):'#', 
			t('Properties'), 
			($c->getCollectionID()==Page::getByPath('/dashboard/formidable/forms/')->getCollectionID()?true:false)
		),
		array(
			!$disabled?URL::to('/dashboard/formidable/forms/elements/', $formID):'javascript:;', 
			t('Layout and elements'), 
			($c->getCollectionID()==Page::getByPath('/dashboard/formidable/forms/elements/')->getCollectionID()?true:false)
		),
		array(
			!$disabled?URL::to('/dashboard/formidable/forms/mailings/', $formID):'javascript:;', 
			t('Emails'), 
			($c->getCollectionID()==Page::getByPath('/dashboard/formidable/forms/mailings/')->getCollectionID()?true:false)
		),
	), false);
	?>
</div>