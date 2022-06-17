<?php 
defined('C5_EXECUTE') or die("Access Denied.");
 
$form = Core::make('helper/form');
$editor = Core::make('editor');
$date_time = new Concrete\Core\Form\Service\Widget\DateTime();
$form_page_selector = new Concrete\Core\Form\Service\Widget\PageSelector();

?>
<form method="post" action="<?php echo View::getInstance()->action('save')?>" id="ccm-form-record" name="formidable_form_edit">
    <?php echo is_object($f)?$form->hidden('formID', intval($f->getFormID())):''; ?>
    <p>&nbsp;</p>

    <fieldset class="form-horizontal">        
        <div class="form-group">
            <?php echo $form->label('label', t('Form name'), array('class' => 'col-sm-3')) ?>
            <div class="col-sm-9">
                <div class="row">
                    <div class="col-sm-6">
                        <?php echo $form->text('label', is_object($f)?$f->getLabel():'', array('placeholder' => t('My Formidable Form'), 'class' => 'createHandle', 'data-target' => 'handle'))?>
                    </div>
                    <div class="col-sm-6">
                        <?php echo $form->text('handle', is_object($f)?$f->getHandle():'', array('placeholder' => t('my_formidable_form')))?>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <?php echo $form->label('submission_redirect', t('On succesfull submission'), array('class' => 'col-sm-3'))?>
            <div class="col-sm-9">
                <div class="message-or-page">
                    <?php echo $form->select('submission_redirect', array(t('Show message'), t('Redirect to page')), is_object($f)?intval($f->getAttribute('submission_redirect')):''); ?>

                    <div id="submission_redirect_content">
                        <div class="input-group">
                            <div class="input-group-addon"><?php echo $form->label('submission_redirect_content', t('Message'))?></div>
                            <div class="form-control-editor">
                                <?php print $editor->outputStandardEditor('submission_redirect_content', is_object($f)?$f->getAttribute('submission_redirect_content'):''); ?>
                            </div>
                        </div>
                    </div>
                    <div id="submission_redirect_page">
                        <div class="input-group">
                            <div class="input-group-addon"><?php echo $form->label('submission_redirect_page', t('Select page'))?></div>
                            <?php echo $form_page_selector->selectPage('submission_redirect_page', is_object($f)?(intval($f->getAttribute('submission_redirect_page'))!=0?intval($f->getAttribute('submission_redirect_page')):''):''); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <?php echo $form->label('limits', t('Enable limits'), array('class' => 'col-sm-3'))?>
            <div class="col-sm-9">                
                <?php echo $form->select('limits', array(0 => t('No limits'), 1 => t('Enable limits')), is_object($f)?intval($f->getAttribute('limits')):''); ?>
                <div id="limits_div">
                    <?php echo $form->label('limits_value', t('Set limits')) ?>
                    <div class="input-group">
                        <?php echo $form->text('limits_value', is_object($f)?$f->getAttribute('limits_value'):'', array('style' => 'width:40%', 'placeholder' => t('Value')))?>
                        <?php echo $form->select('limits_type', array('total' => t('Total submissions'), 'ip' => t('Per IP-address'), 'user' => t('Per user (guest-visitors excluded)')), is_object($f)?$f->getAttribute('limits_type'):'', array('style' => 'width:60%')); ?>
                    </div>
                    <div class="message-or-page">
                        <?php echo $form->select('limits_redirect', array(t('Show message'), t('Redirect to page')), is_object($f)?intval($f->getAttribute('limits_redirect')):''); ?>
                        <div id="limits_redirect_content">
                            <div class="input-group">
                                <div class="input-group-addon"><?php echo $form->label('limits_redirect_content', t('Message'))?></div>
                                <div class="form-control-editor">
                                    <?php print $editor->outputStandardEditor('limits_redirect_content', is_object($f)?$f->getAttribute('limits_redirect_content'):''); ?>
                                </div>
                            </div>
                        </div>
                        <div id="limits_redirect_page">
                            <div class="input-group">
                                <div class="input-group-addon"><?php echo $form->label('limits_redirect_page', t('Select page'))?></div>
                                <?php echo $form_page_selector->selectPage('limits_redirect_page', is_object($f)?(intval($f->getAttribute('limits_redirect_page'))!=0?intval($f->getAttribute('limits_redirect_page')):''):''); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <?php echo $form->label('schedule', t('Enable scheduling'), array('class' => 'col-sm-3'))?>
            <div class="col-sm-9">                
                <?php echo $form->select('schedule', array(0 => t('No scheduling'), 1 => t('Enable scheduling')), is_object($f)?intval($f->getAttribute('schedule')):''); ?>
                <div id="schedule_div">
                    <div>
                        <?php echo $form->label('schedule_start', t('From')) ?>
                        <?php echo $date_time->datetime('schedule_start', is_object($f)?$f->getAttribute('schedule_start'):date("Y-m-d"), false, true); ?>
                        <?php echo $form->label('schedule_end', t('To')) ?>
                        <?php echo $date_time->datetime('schedule_end', is_object($f)?$f->getAttribute('schedule_end'):date("Y-m-d"), false, true); ?>
                    </div>                    
                    <?php echo $form->label('schedule_label', t('When outside schedule')) ?>                    
                    <div class="message-or-page">
                        <?php echo $form->select('schedule_redirect', array(t('Show message'), t('Redirect to page')), is_object($f)?intval($f->getAttribute('schedule_redirect')):''); ?>
                        <div id="schedule_redirect_content">
                            <div class="input-group">
                                <div class="input-group-addon"><?php echo $form->label('schedule_redirect_content', t('Message'))?></div>
                                <div class="form-control-editor">
                                    <?php print $editor->outputStandardEditor('schedule_redirect_content', is_object($f)?$f->getAttribute('schedule_redirect_content'):''); ?>
                                </div>
                            </div>
                        </div>
                        <div id="schedule_redirect_page">
                            <div class="input-group">
                                <div class="input-group-addon"><?php echo $form->label('schedule_redirect_page', t('Select page'))?></div>
                                <?php echo $form_page_selector->selectPage('schedule_redirect_page', is_object($f)?(intval($f->getAttribute('schedule_redirect_page'))!=0?intval($f->getAttribute('schedule_redirect_page')):''):''); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <?php echo $form->label('gdpr', t('GDPR Compliance'), array('class' => 'col-sm-3'))?>
            <div class="col-sm-9">                
                <?php echo $form->select('gdpr', array(0 => t('Disable'), 1 => t('Enable GDPR Compliance')), is_object($f)?intval($f->getAttribute('gdpr')):''); ?>
                <div id="gdpr_div">                    
                    <div>
                        <?php echo $form->label('gdpr_value', t('GDPR Compliance settings')) ?>
                        <p><?php echo t('Remove form submissions automatically after a certain period.'); ?></p>
                        <div class="input-group" style="width: 100%;">
                            <?php echo $form->text('gdpr_value', is_object($f)?$f->getAttribute('gdpr_value'):'', array('style' => 'width:40%', 'placeholder' => t('Value')))?>
                            <?php echo $form->select('gdpr_type', array('days' => t('Days'), 'months' => t('Months')), is_object($f)?$f->getAttribute('gdpr_type'):'', array('style' => 'width:60%')); ?>
                        </div> 
                    </div> 
                    <div class="help-block"><?php echo t('Note: Uploaded files and logs are NOT removed! Please check these manually.'); ?></div>
                    <div>
                        <?php echo $form->label('gdpr_ip', t('Disable registering IP'))?>
                        <div class="input-group">
                            <div class="checkbox">
                                <label>
                                    <?php echo $form->checkbox('gdpr_ip', 1, is_object($f)?intval($f->getAttribute('gdpr_ip')):'')?>
                                    <?php echo t('When enabled, the IP-address of the submitter will not be saved. (Note: This could effect the limit-settings of your form!)'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div>
                        <?php echo $form->label('gdpr_browser', t('Disable registering browser information'))?>
                        <div class="input-group">
                            <div class="checkbox">
                                <label>
                                    <?php echo $form->checkbox('gdpr_browser', 1, is_object($f)?intval($f->getAttribute('gdpr_browser')):'')?>
                                    <?php echo t('When enabled, the browser information of the submitter will not be saved.'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <?php echo $form->label('permission', t('Permissions'), array('class' => 'col-sm-3'))?>
            <div class="col-sm-9">                
                <?php echo $form->select('permission', array(0 => t('Disable'), 1 => t('Enable Permissions')), is_object($f)?intval($f->getAttribute('permission')):''); ?>
                <div id="permission_div">  
                    <?php if (is_array($groups) && count($groups)) { ?>
                        <div class="row">
                            <div class="col-sm-6">
                                <?php echo $form->label('permission_form', t('Form Permissions')) ?>
                                <p><?php echo t('Which user group may edit this form'); ?></p>
                                <div class="input-group">                                
                                    <?php foreach ($groups as $g) { ?>
                                        <div class="checkbox">
                                            <label>
                                                <?php echo $form->checkbox('permission_form[]', $g->getGroupID(), is_object($f)&&is_array($f->getAttribute('permission_form'))&&in_array($g->getGroupID(), (array)$f->getAttribute('permission_form')))?>
                                                <?php echo $g->getGroupDisplayName(false)?>
                                            </label>
                                        </div>
                                    <?php } ?>                            
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <?php echo $form->label('permission_results', t('Result Permissions')) ?>
                                <p><?php echo t('Which user group may view, edit or export the results of this form'); ?></p>
                                <div class="input-group">
                                    <?php if (is_array($groups) && count($groups)) { ?>
                                        <?php foreach ($groups as $g) { ?>
                                            <div class="checkbox">
                                                <label>
                                                    <?php echo $form->checkbox('permission_results[]', $g->getGroupID(), is_object($f)&&is_array($f->getAttribute('permission_results'))&&in_array($g->getGroupID(), (array)$f->getAttribute('permission_results')))?>
                                                    <?php echo $g->getGroupDisplayName(false)?>
                                                </label>
                                            </div>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <div id="groups_note" class="note help-block"><?php echo t('No groups found, please create one.'); ?></div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="input-group">
                            <div id="groups_note" class="note help-block"><?php echo t('No groups found, please create one.'); ?></div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <?php echo $form->label('css', t('CSS Classes'), array('class' => 'col-sm-3'))?>
            <div class="col-sm-9">
                <div class="input-group">
                    <div class="input-group-addon"><?php echo $form->checkbox('css', 1, is_object($f)?intval($f->getAttribute('css')) != 0:'')?></div>
                    <?php echo $form->text('css_value', is_object($f)?$f->getAttribute('css_value'):''); ?>
                </div>
                <div id="css_content_note" class="note help-block"><?php echo t('Add classname(s) to customize your form. Example: myform'); ?></div>
            </div>
        </div>

    </fieldset>

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <a href="<?php echo URL::to(Page::getByPath('/dashboard/formidable/forms'))?>" class="btn pull-left btn-default"><?php echo t('Back')?></a>
            <?php echo $form->submit('submit', t('Save').' '.t('Form Properties'), '', 'btn-primary pull-right'); ?>
        </div>
    </div>
</form> 