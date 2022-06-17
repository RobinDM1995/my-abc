<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<?php if (is_array($forms) && count($forms)) { ?>  

    <style type="text/css">
        .html-value { width: 100%;  border: 1px solid #eee; height: 245px;  }
    </style>


    <?=Loader::helper('concrete/ui')->tabs(array(
        array('formidable-form', t('Form'), true),
        array('formidable-display', t('Displaying')),
        array('formidable-steps', t('Steps')),
        array('formidable-callback', t('Callback'))
    ));?>

    <div class="ccm-tab-content" id="ccm-tab-content-formidable-form">
        <div class="formidable-form">  

            <div class="form-group">
                <label for="formID" class="control-label"><?= t('Form') ?> <span class="ccm-required">*</span></label>
                <?php echo $form->select('formID', $forms, $controller->formID, ['class' => 'form-control']);?>
            </div>

        </div>                    
    </div>

    <div class="ccm-tab-content" id="ccm-tab-content-formidable-display">
        <div class="formidable-form">
            
            <p><b><?= t('Displaying errors'); ?></b></p>
            <div class="form-group">
                <label for="options[error_messages_beneath_field]" class="control-label"><?= t('Show validation errors inline') ?> <span class="ccm-required">*</span></label>
                <?php echo $form->select('options[error_messages_beneath_field]', [0 => t('No'), 1 => t('Yes')], isset($controller->options['error_messages_beneath_field'])?intval($controller->options['error_messages_beneath_field']):1, ['class' => 'change']);?>
                <div class="help-block"><?= t('Show inline validation errors, beneath each field'); ?></div>
            </div>

            <div class="form-group error_messages_beneath_field">
                <label for="options[error_messages_beneath_field_class]" class="control-label"><?= t('Class for validation error') ?></label>
                <?php echo $form->text('options[error_messages_beneath_field_class]', $controller->options['error_messages_beneath_field_class']?$controller->options['error_messages_beneath_field_class']:'text-danger error', ['class' => 'form-control']);?>
            </div>

            <div class="form-group">
                <label for="options[error_messages_on_top]" class="control-label"><?= t('Show validation errors on top of form') ?> <span class="ccm-required">*</span></label>
                <?php echo $form->select('options[error_messages_on_top]', [0 => t('No'), 1 => t('Yes')], isset($controller->options['error_messages_on_top'])?intval($controller->options['error_messages_on_top']):0, ['class' => 'change']);?>
                <div class="help-block"><?= t('Do you want to show the validation errors of the form on top of the form?'); ?></div>
            </div>

            <div class="form-group error_messages_on_top">
                <label for="options[error_messages_on_top_class]" class="control-label"><?= t('Class for validation error') ?></label>
                <?php echo $form->text('options[error_messages_on_top_class]', $controller->options['error_messages_on_top_class']?$controller->options['error_messages_on_top_class']:'alert alert-danger', ['class' => 'form-control']);?>
            </div>
            
            <p><b><?= t('Messages'); ?></b></p>

            <div class="form-group">
                <label for="options[warning_messages_class]" class="control-label"><?= t('Warning message (class)') ?></label>
                <?php echo $form->text('options[warning_messages_class]', $controller->options['warning_messages_class']?$controller->options['warning_messages_class']:'alert alert-warning', ['class' => 'form-control']);?>
            </div>

            <div class="form-group">
                <label for="options[success_messages_class]" class="control-label"><?= t('Success message (class)') ?></label>
                <?php echo $form->text('options[success_messages_class]', $controller->options['success_messages_class']?$controller->options['success_messages_class']:'alert alert-success', ['class' => 'form-control']);?>
            </div>


            <p><b><?= t('After submission'); ?></b></p>
        
            <div class="form-group">
                <label for="options[remove_form_on_success]" class="control-label"><?= t('Hide form after a succesfull submission') ?> <span class="ccm-required">*</span></label>
                <?php echo $form->select('options[remove_form_on_success]', [0 => t('No'), 1 => t('Yes')], isset($controller->options['remove_form_on_success'])?intval($controller->options['remove_form_on_success']):0, ['class' => 'form-control']);?>
                <div class="help-block"><?= t('Hide form after a successfully submission?'); ?></div>
            </div>

        </div>
    </div>

    <div class="ccm-tab-content" id="ccm-tab-content-formidable-steps">
        <div class="formidable-form">  

            <p><b><?= t('Steps (only for form with steps)'); ?></b></p>

            <div class="form-group">
                <label for="options[step_progress_bar]" class="control-label"><?= t('Show progressbar') ?> <span class="ccm-required">*</span></label>
                <?php echo $form->select('options[step_progress_bar]', [0 => t('No'), 1 => t('Yes')], isset($controller->options['step_progress_bar'])?intval($controller->options['step_progress_bar']):1, ['class' => 'change']);?>
                <div class="help-block"><?= t('Want to activate a progressbar?'); ?></div>
            </div>

            <div class="form-group step_progress_bar">
                <label for="options[step_progress_bar_selector]" class="control-label"><?= t('Progress bar (jQuery Selector)') ?> <span class="ccm-required">*</span></label>
                <?php echo $form->text('options[step_progress_bar_selector]', $controller->options['step_progress_bar_selector']?$controller->options['step_progress_bar_selector']:'ul#formidable_steps', ['class' => 'form-control']);?>
            </div>

            <div class="form-group">
                <label for="options[animate_step]" class="control-label"><?= t('Animate step') ?> <span class="ccm-required">*</span></label>
                <?php echo $form->select('options[animate_step]', [0 => t('No'), 1 => t('Yes')], isset($controller->options['animate_step'])?intval($controller->options['animate_step']):1, ['class' => 'change']);?>
                <div class="help-block"><?= t('Do you want to animate the transistion between steps'); ?></div>
            </div>

            <div class="form-group animate_step">
                <label for="options[animate_step_easing]" class="control-label"><?= t('Animation') ?> <span class="ccm-required">*</span></label>
                <?php echo $form->select('options[animate_step_easing]', ['easeInSine', 'easeOutSine', 'easeInOutSine', 'easeInQuad', 'easeOutQuad', 'easeInOutQuad', 'easeInCubic', 'easeOutCubic', 'easeInOutCubic', 'easeInQuart', 'easeOutQuart', 'easeInOutQuart', 'easeInQuint', 'easeOutQuint', 'easeInOutQuint', 'easeInExpo', 'easeOutExpo', 'easeInOutExpo', 'easeInCirc', 'easeOutCirc', 'easeInOutCirc', 'easeInBack', 'easeOutBack', 'easeInOutBack', 'easeInElastic', 'easeOutElastic', 'easeInOutElastic', 'easeInBounce', 'easeOutBounce', 'easeInOutBounce'], $controller->options['animate_step_easing']?$controller->options['animate_step_easing']:'easeInSine', ['class' => 'form-control']);?>
            </div>

            <div class="form-group animate_step">
                <label for="options[animate_step_duration]" class="control-label"><?= t('Duration') ?> <span class="ccm-required">*</span></label>
                <?php echo $form->text('options[animate_step_duration]', $controller->options['animate_step_duration']?$controller->options['animate_step_duration']:800, ['class' => 'form-control']);?>
            </div>

            <p><b><?= t('After submission'); ?></b></p>

            <div class="form-group">
                <label for="options[hide_steps_after_submission]" class="control-label"><?= t('Hide steps after a succesfull submission') ?> <span class="ccm-required">*</span></label>
                <?php echo $form->select('options[hide_steps_after_submission]', [0 => t('No'), 1 => t('Yes')], isset($controller->options['hide_steps_after_submission'])?intval($controller->options['hide_steps_after_submission']):1, ['class' => 'form-control']);?>
                <div class="help-block"><?= t('Hide steps after a successfully submission?'); ?></div>
            </div>

        </div>                    
    </div>

    <div class="ccm-tab-content" id="ccm-tab-content-formidable-callback">
        <div class="formidable-form">  

            <p><b><?= t('Callbacks (jquery)'); ?></b></p>
            <div class="form-group">
                <label for="options[errorCallback]" class="control-label"><?= t('Callback on error') ?></label>
                <div id="errorCallback" class="html-value"><?php echo htmlspecialchars($controller->options['errorCallback']?$controller->options['errorCallback']:'function() { }', ENT_QUOTES,APP_CHARSET) ?></div>
                <textarea style="display:none" id="errorCallback-textarea" name="options[errorCallback]"></textarea>
            </div>
            
            <div class="form-group">
                <label for="options[successCallback]" class="control-label"><?= t('Callback on success') ?></label>
                <div id="successCallback" class="html-value"><?php echo htmlspecialchars($controller->options['successCallback']?$controller->options['successCallback']:'function() { }', ENT_QUOTES,APP_CHARSET) ?></div>
                <textarea style="display:none" id="successCallback-textarea" name="options[successCallback]"></textarea>

            </div>
        </div>                    
    </div>

    <script type="text/javascript">
        $(function() {
            var callbackError = ace.edit("errorCallback");
            callbackError.setTheme("ace/theme/eclipse");
            callbackError.getSession().setMode("ace/mode/javascript");
            $('#errorCallback-textarea').val(callbackError.getValue());
            callbackError.getSession().on('change', function() {
                $('#errorCallback-textarea').val(callbackError.getValue());
            });

            var callbackSuccess = ace.edit("successCallback");
            callbackSuccess.setTheme("ace/theme/eclipse");
            callbackSuccess.getSession().setMode("ace/mode/javascript");
            $('#successCallback-textarea').val(callbackSuccess.getValue());
            callbackSuccess.getSession().on('change', function() {
                $('#successCallback-textarea').val(callbackSuccess.getValue());
            });

            $('select.change').on('change', function() {
                var name = $(this).attr('name').replace(/options\[|\]/gi, '');
                var val = $(this).val();
                if ($('div.'+name).length > 0) {
                    if (val == 1) $('div.'+name).show();
                    else $('div.'+name).hide();
                }
            }).trigger('change');
        });
    </script>

<?php } else { ?>

    <div class="ccm-ui">
        <div class="alert alert-warning">
            <p><strong><?php echo t('There are no Formidable Forms!') ?></strong></p>
            <p class="ccm-note"><?php echo t('Go to dashboard and create a Formidable Form') ?></p>
            <p><a href="<?php echo URL::to('/dashboard/formidable/forms/'); ?>" class="btn btn-default"><?php echo t('Create a new Formidable Form') ?></a></p>    
        </div>
    </div>

<?php } ?>