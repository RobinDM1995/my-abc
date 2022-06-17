<?php 
	defined('C5_EXECUTE') or die("Access Denied."); 
	$task = $this->controller->getTask();
	$form = Core::make('helper/form');
	$request = \Concrete\Core\Http\Request::getInstance()->request(); 
?>

<div class="ccm-ui">

	<?php if (is_array($errors)) { ?>
		<div class="alert alert-danger">
			<?php echo $errors['message']; ?>
		</div>
	<?php } else { ?>
		<?php if ($task == 'view') { ?> 
			<?php if (!is_object($layout) && !is_array($layout)) { ?>
				<div class="alert alert-danger">
					<?php 
						if (!is_array($errors)) echo t('Can\'t find layout'); 
						else echo $errors['message'];
					?>
				</div>
			<?php } else { ?>
					<form id="layoutForm" method="post" action="">
						<div class="alert alert-danger dialog_message" style="display:none"></div>
							<?php if (is_object($layout) && isset($layout->layoutID)) $request['layoutID'] = $layout->layoutID; ?>  
							<input type="hidden" name="formID" id="formID" value="<?php echo $request['formID']; ?>" />
							<input type="hidden" name="layoutID" id="layoutID" value="<?php echo $request['layoutID']; ?>" />
							<input type="hidden" name="rowID" id="rowID" value="<?php echo $request['rowID']; ?>" />
							<input type="hidden" name="ccm_token" id="ccm_token" value="<?php echo Core::make('token')->generate('formidable_layout'); ?>" />
					        <fieldset class="form-horizontal">
					        	<?php if ($request['layoutID'] < 0 || $layout->appearance == 'step') { ?>									
									<h5><?php echo t('Set the properties of the row'); ?></h5>
								<?php } else { ?>
									<h5><?php echo t('Set the properties of the column'); ?></h5>
								<?php } ?>
								
								<div class="form-group">
									<?php echo $form->label('appearance', t('Appearance'), array('class' => 'col-sm-3')) ?>
									<div class="col-sm-9">
										<?php 
										$params = array();
										if (($request['layoutID'] < 0 && $request['rowID'] >= 0) || $layout->appearance == 'step') { 
											$params = array('disabled' => 'disabled');
											echo $form->hidden('appearance', $layout->appearance);
										} ?>
										<?php echo $form->select('appearance', $appearances, $layout->appearance, $params)?>
									</div>
								</div>
										
								<?php if ($request['layoutID'] < 0 && $layout->appearance != 'step') { ?>
									<div class="form-group columns-holder">
										<?php echo $form->label('cols', t('Number of columns'), array('class' => 'col-sm-3')) ?>
										<div class="col-sm-9">
											<?php echo $form->select('cols', array(1=>1, 2=>2, 3=>3, 4=>4, 5=>5), (is_array($layout) && count($layout))?count($layout):1)?>
											<div class="note help-block">
												<?php echo t('If you want to have less columns, empty them first.'); ?>
											</div>
										</div>
									</div>
								<?php } ?>
								
								<div class="form-group label-holder">
									<?php echo $form->label('label', t('Label / Name'), array('class' => 'col-sm-3')) ?>
									<div class="col-sm-9">
										<?php echo $form->text('label', $layout->label)?>
									</div>
								</div>	

								<div class="form-group">
									<?php echo $form->label('css', t('CSS Classes'), array('class' => 'col-sm-3'))?>
									<div class="col-sm-9">
										<div class="input-group">
											<div class="input-group-addon"><?php echo $form->checkbox('css', 1, intval($layout->css) != 0)?></div>
											<?php echo $form->text('css_value', $layout->css_value); ?>
										</div>
										<div id="css_content_note" class="note help-block">
											<?php echo t('Add classname(s) to customize your form element. Example: myformelement'); ?>
										</div>
									</div>
								</div>

								
								<?php if ($request['layoutID'] < 0 || $layout->appearance == 'step') { ?>	

									<div class="button-holder">
										<div class="form-group">
											<?php echo $form->label('custom_buttons', t('Customize buttons'), array('class' => 'col-sm-3')) ?>
											<div class="col-sm-9">
												<div class="input-group">
													<div class="checkbox">
														<label>
															<?php echo $form->checkbox('custom_buttons', 1, intval($layout->custom_buttons) != 0)?>
															<?php echo t('You can set the name of the buttons and add classes to them.'); ?>
														</label>
													</div>
												</div>
											</div>
										</div>
										
										<div id="custom_buttons_div">
											<div class="form-group">
												<?php echo $form->label('btn_prev', t('Button "previous"'), array('class' => 'col-sm-3')) ?>
												<div class="col-sm-9">
													<?php echo $form->text('btn_prev', !empty($layout->btn_prev)?$layout->btn_prev:t('Previous')); ?>
												</div>
											</div>
											<div class="form-group">
												<?php echo $form->label('btn_prev_css', t('Button CSS Classes'), array('class' => 'col-sm-3'))?>
												<div class="col-sm-9">
													<div class="input-group">
														<div class="input-group-addon"><?php echo $form->checkbox('btn_prev_css', 1, intval($layout->btn_prev_css) != 0)?></div>
														<?php echo $form->text('btn_prev_css_value', $layout->btn_prev_css_value); ?>
													</div>
													<div id="btn_prev_css_content_note" class="note help-block">
														<?php echo t('Add classname(s) to customize your step button. Example: myformstep'); ?>
													</div>
												</div>
											</div>

											<div class="form-group">
												<?php echo $form->label('btn_next', t('Button "Next"'), array('class' => 'col-sm-3')) ?>
												<div class="col-sm-9">
													<?php echo $form->text('btn_next', !empty($layout->btn_next)?$layout->btn_next:t('Next')); ?>
												</div>
											</div>
											<div class="form-group">
												<?php echo $form->label('btn_next_css', t('Button CSS Classes'), array('class' => 'col-sm-3'))?>
												<div class="col-sm-9">
													<div class="input-group">
														<div class="input-group-addon"><?php echo $form->checkbox('btn_next_css', 1, intval($layout->btn_next_css) != 0)?></div>
														<?php echo $form->text('btn_next_css_value', $layout->btn_next_css_value); ?>
													</div>
													<div id="btn_next_css_content_note" class="note help-block">
														<?php echo t('Add classname(s) to customize your step button. Example: myformstep'); ?>
													</div>
												</div>
											</div>

										</div>
									</div>		
								<?php } ?>
				
					        </fieldset>

					    </div>

					    <div class="dialog-buttons">
							<button class="btn btn-default pull-left" data-dialog-action="cancel"><?php echo t('Cancel')?></button>
							<button type="button" onclick="ccmFormidableCheckFormLayoutSubmit();return false;" class="btn btn-primary pull-right"><?php echo t('Save')?></button>
						</div>
					</form>
					
					<script>
						$(function() {
							ccmFormidableFormLayoutCheckSelectors();
							$("input[name=css]").click(function() {
								ccmFormidableFormLayoutCheckSelectors($(this));
							});
							$("select[name=appearance]").change(function() {
								ccmFormidableFormLayoutCheckSelectors($(this));
							});
							$("input[name=custom_buttons]").click(function() {
								ccmFormidableFormLayoutCheckSelectors($(this));
							});
							$("input[name=btn_prev_css]").click(function() {
								ccmFormidableFormLayoutCheckSelectors($(this));
							});
							$("input[name=btn_next_css]").click(function() {
								ccmFormidableFormLayoutCheckSelectors($(this));
							});
						});
					</script> 
			<?php } ?>

		<?php } ?>


		<?php if ($task == 'select') { ?>

			<?php if (is_array($errors)) { ?>
				<div class="alert alert-danger">
					<?php echo $errors['message']; ?>
				</div>
			<?php } else { ?>
				<?php 
		            echo $form->hidden('formID', $request['formID']);
					echo $form->hidden('layoutID', $request['layoutID']);              
		        ?> 

				<div id="ccm-pane-body-left">
					<div class="well-sm well form-inline">
				    	<?php echo $form->label('quick_search_element', t('Search:'), array('style' => 'margin-left:20px; margin-right:5px;')); ?>
				        <?php echo $form->text('quick_search_element', '', array('placeholder' => 'Quick search')); ?>
					</div>
					<?php 
						$elements = $f->getAvailableElements();
						if(is_array($elements) && count($elements)) {
							ksort($elements);
							foreach ($elements as $group => $types) {
								ksort($types);	
								?>  
								<div class="col-sm-4">
									<p class="text-muted"><?php echo t($group); ?></p>
									<ul class="searchable_elements list-group">
										<?php foreach ($types as $element) { 
											$disabled = ''; 
											if ($element->getElementType() == 'captcha' && $f->hasCaptcha()) $disabled = 'disabled';
											?>
											<li label="<?php echo $element->getElementType(); ?>" class="list-group-item <?php echo $disabled; ?>">
												<?php echo t($element->getElementText())?>
												<?php echo (!empty($disabled))?'<span>'.t('disabled').'</span>':''; ?>
											</li>
										<?php } ?>
									</ul>
								</div>
								<?php 
							} 
						} else { ?>
							<div class="message alert alert-danger alert-message error">
								<?php echo t('No available elements found!'); ?>
							</div>
							<?php 
						} 
					?>
				</div>

				<script>
					$(function() {
						$('#quick_search').val('');
						$(".searchable_elements li").show();

						$('input[id="quick_search_element"]').on('keydown, keyup', function() {
							var s = $(this).val();
							$(".searchable_elements li").show();
							if (s.length > 0)
								$(".searchable_elements li:not(:contains('"+s+"'))").hide();	
						});
						$("#ccm-pane-body-left li:not('.disabled')").click(function(){
							ccmFormidableOpenElementDialog($(this).attr('label'), $(this).text(), $('#layoutID').val());
						});

						$('#ccm-pane-body-left li[label]').each(function() {
							$(this).css('background-image', 'url(' + CCM_REL + '/packages/formidable_full/images/icons/' + $(this).attr('label') + '.png)');	
						});
					});
				</script> 
			<?php } ?>
		<?php } ?>

		<?php if ($task == 'delete') { ?>
			<?php if (!is_object($layout) && !is_array($layout)) { ?>
				<div class="alert alert-danger">
					<?php 
						if (!is_array($errors)) echo t('Can\'t find layout'); 
						else echo $errors['message'];
					?>
				</div>
			<?php } else { ?>
				<div class="alert alert-warning">

		            <p><b><?php echo t('Are you sure you want to delete this layout?'); ?></b></p>
		        
					<form class="form-horizontal" data-dialog-form="delete-result" method="post" action="">
						<?php echo $form->hidden('ccm_token', Core::make('token')->generate('formidable_layout')); ?>
						<?php echo $form->hidden('layoutID', $layoutID); ?>
						<?php echo $form->hidden('rowID', $rowID); ?>

						<div class="form-group" style="margin-bottom:0;">
							<div class="col-sm-12">
								<div class="checkbox">
									<label>
										<?php echo $form->checkbox('all', 1, 0)?>
										<?php echo t('Yes, remove all elements in this layout!'); ?>
									</label>
								</div>
								<div class="note help-block">
									<?php echo t('If there are elements in this layout, you need to check this!'); ?>
								</div>
							</div>
						</div>
					</form>

				

		        <div class="dialog-buttons">
		            <button class="btn btn-default pull-left" data-dialog-action="cancel"><?php echo t('Cancel')?></button>
		            <button type="button" class="btn btn-danger pull-right" name="submit"><?php echo t('Delete')?></button>
		        </div>

		        <script>
					$(function() {	                
		                $('button[name=submit]').click(function() {
		                    ccmFormidableDeleteLayout(<?php echo $layoutID ?>, <?php echo $rowID ?>);
		                    jQuery.fn.dialog.closeTop();
		                });
		            });
				</script>
			<?php } ?>
		<?php } ?>

		<?php if ($task == 'duplicate') { ?>
			<?php if (!is_object($layout) && !is_array($layout)) { ?>
				<div class="alert alert-danger">
					<?php 
						if (!is_array($errors)) echo t('Can\'t find layout'); 
						else echo $errors['message'];
					?>
				</div>
			<?php } else { ?>
				<div class="alert alert-warning">

		            <p><b><?php echo t('Are you sure you want to duplicate this layout and his elements?'); ?></b></p>
		        
					<form class="form-horizontal" data-dialog-form="duplicate-result" method="post" action="">
						<?php echo $form->hidden('ccm_token', Core::make('token')->generate('formidable_layout')); ?>
						<?php echo $form->hidden('layoutID', $layoutID); ?>
						<?php echo $form->hidden('rowID', $rowID); ?>						
					</form>				

		        <div class="dialog-buttons">
		            <button class="btn btn-default pull-left" data-dialog-action="cancel"><?php echo t('Cancel')?></button>
		            <button type="button" class="btn btn-success pull-right" name="submit"><?php echo t('Duplicate')?></button>
		        </div>

		        <script>
					$(function() {	                
		                $('button[name=submit]').click(function() {
		                    ccmFormidableDuplicateLayout(<?php echo $layoutID ?>, <?php echo $rowID ?>);
		                    jQuery.fn.dialog.closeTop();
		                });
		            });
				</script>
			<?php } ?>
		<?php } ?>

	<?php } ?>
</div>