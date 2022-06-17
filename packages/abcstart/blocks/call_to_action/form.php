<?php  defined("C5_EXECUTE") or die("Access Denied."); ?>
<?php  $form = Loader::helper('form'); ?>
<style>
  .ccm-call-to-action-block-container .hide-button-link {
    display: none;
  }
</style>
<script>
  $(function() {  // activate redactors

      $('.redactor-content2').redactor({
        'concrete5': {},
          "plugins": ["undoredo","underline","specialcharacters","fontsize"],
           buttonsHide: ['image', 'file', 'html', 'format', 'ol', 'link', 'ul', 'indent', 'outdent', 'deleted', 'formatting'],
          minHeight: 100
      });


      $('div[data-field=entry-link-page-selector-select]').concretePageSelector({
          'inputName': 'internallink',
          'cID': <?php if ($linktype == 1) { ?><?php echo intval($internallink); ?><?php } else { ?>false<?php } ?>
      });

      $('.ccm-call-to-action-block-container #icon').on('change', function() {
        var icon = $(this).val();
        if(icon == '') { icon = 'fa fa-question'; }
        $('.ccm-call-to-action-block-container .icon-preview i').attr('class', 'fa ' + icon);
      });

      $('.ccm-call-to-action-block-container .entry-link-select').on('change', function() {
        var type = $(this).val();
        $('div[data-field=entry-link-page-selector]').hide();
        $('div[data-field=entry-link-url]').hide();
        if(type == 1) {
          $('div[data-field=entry-link-page-selector]').show();
        }
        if(type == 2) {
          $('div[data-field=entry-link-url]').show();
        }
      });

      <?php if($linktype == 1) { ?>
        $('div[data-field=entry-link-page-selector]').show();
      <?php } elseif ($linktype == 2) { ?>
        $('div[data-field=entry-link-url]').show();
      <?php } ?>
  });
  //
</script>
<div class="ccm-call-to-action-block-container">
  <div class="row">
    <div class="col-sm-12">
      <div class="form-group">
        <?php  echo $form->label('name', t("Tekst")); ?>
        <?php  echo isset($btFieldsRequired) && in_array('style', $btFieldsRequired) ? '<small class="required">' . t('Required') . '</small>' : null; ?>
        <textarea style="display: none" class="redactor-content2" name="name"><?php echo $name ?></textarea>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-6">
      <div class="form-group">
        <?php  echo $form->label('class', t("Stijl")); ?>
        <?php  echo isset($btFieldsRequired) && in_array('class', $btFieldsRequired) ? '<small class="required">' . t('Required') . '</small>' : null; ?>
        <?php  echo $form->select('class', $class_options, $class); ?>
      </div>
    </div>
    <div class="col-sm-6">
      <div class="form-group">
        <?php  echo $form->label('align', t("Uitlijning")); ?>
        <?php  echo isset($btFieldsRequired) && in_array('align', $btFieldsRequired) ? '<small class="required">' . t('Required') . '</small>' : null; ?>
        <?php  echo $form->select('align', $align_options, $align); ?>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-6">
      <div class="form-group">
        <?php  echo $form->label('icon', t("Icoon")); ?>
        <?php  echo isset($btFieldsRequired) && in_array('icon', $btFieldsRequired) ? '<small class="required">' . t('Required') . '</small>' : null; ?>
        <div class="input-group">
          <span class="input-group-addon icon-preview"><i class="fa <?php echo ($icon ? $icon : 'fa-question'); ?>"></i></span>
          <?php  echo $form->select('icon', $icons, $icon); ?>
        </div>
      </div>
    </div>
    <div class="col-sm-6">
      <div class="form-group">
        <?php  echo $form->label('iconlocation', t("Icoon Locatie")); ?>
        <?php  echo isset($btFieldsRequired) && in_array('iconlocation', $btFieldsRequired) ? '<small class="required">' . t('Required') . '</small>' : null; ?>
        <?php  echo $form->select('iconlocation', $icon_locations, $iconlocation); ?>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-12">
      <div class="form-group" >
         <label><?php echo t('Link'); ?></label>
          <select name="linktype" class="entry-link-select form-control">
              <option value="0" <?php if (!$linktype) { ?>selected<?php } ?>><?php echo t('None'); ?></option>
              <option value="1" <?php if ($linktype == 1) { ?>selected<?php } ?>><?php echo t('Another Page'); ?></option>
              <option value="2" <?php if ($linktype == 2) { ?>selected<?php } ?>><?php echo t('External URL'); ?></option>
          </select>
      </div>
      <div data-field="entry-link-url" class="form-group hide-button-link">
         <label><?php echo t('URL'); ?></label>
         <?php  echo $form->text('linkurl', $linkurl); ?>
      </div>
      <div data-field="entry-link-page-selector" class="form-group hide-button-link">
         <label><?php echo t('Pagina'); ?></label>
          <div data-field="entry-link-page-selector-select"></div>
      </div>
    </div>
  </div>
</div>
