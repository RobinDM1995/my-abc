<?php  defined('C5_EXECUTE') or die("Access Denied."); ?>

<?php
// set locale for Javascript errors (src/Formidable/Validator/Result.php)
$_SESSION['localeJS'] = $f->getLocale(); ?>

<?php if (!$f || !$f->getFormID()) { ?>
    <div class="alert alert-danger">
        <?php echo t('Can\'t find the Formidable Form'); ?>
    </div>
<?php } else { ?>

    <div id="formidable_container_<?php echo $f->getFormID() ?>" class="formidable <?php echo $error?'error':'' ?>">

        <div id="formidable_message_<?php echo $f->getFormID() ?>" class="formidable_message">
            <?php if ($limits) { ?><div class="alert alert-warning"><?php echo $limits; ?></div><?php } ?>
            <?php if ($schedule) { ?><div class="alert alert-info"><?php echo $schedule; ?></div><?php } ?>
            <?php if ($error) { ?>
                <div class="alert alert-danger">
                    <?php foreach ((array)$error as $er) { ?>
                        <div><?php echo $er ?></div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>

        <?php if (!$limits && !$schedule) { ?>
            <?php
                if ($f->hasSteps()) {
                    $steps = $f->getSteps();
                    if (is_array($steps) && count($steps)) { ?>
                        <ul id="formidable_steps" class="steps progressbar">
                        <?php foreach ($steps as $i => $step) {
                          ?>
                            <li class="<?= $i==0?'active':''; ?> <?= $step['css_value']; ?>"><?= $step['label'];?></li>
                        <?php } ?>
                        </ul>
            <?php } } ?>

            <form id="ff_<?php echo $f->getFormID() ?>" name="formidable_form" method="post" class="<?php echo $f->getAttribute('class'); ?>" role="form" action="<?php echo \URL::to('/formidable/dialog/formidable'); ?>">
                <input type="hidden" name="formID" id="formID" value="<?php echo $f->getFormID(); ?>">
                <input type="hidden" name="cID" id="cID" value="<?php echo $f->getCollectionID(); ?>">
                <input type="hidden" name="bID" id="bID" value="<?php echo $f->getBlockID(); ?>">
                <input type="hidden" name="resolution" id="resolution" value="">
                <input type="hidden" name="ccm_token" id="ccm_token" value="<?php echo $f->getToken(); ?>">
                <input type="hidden" name="locale" id="locale" value="<?php echo $f->getLocale(); ?>">
                <input type="hidden" name="step" id="step" value="0">

                <?php
                $layout = $f->getLayout();
                if (is_array($layout) && count($layout)) {
                    $current_step = false;
                    foreach($layout as $rowID => $row) {
                        if (is_object($row)) {
                            // Need to close the step if there is any step active.
                            // Because of the foreach we need to "save" the current step.
                            if (is_object($current_step)) echo $current_step->getContainerEnd();
                            echo $row->getContainerStart();
                            $current_step = $row;
                        }
                        else {
                            echo '<div class="row">';
                            $columns = $row;
                            $i=0;
                            $width = round(12/count($row));
                            foreach($row as $column) { ?>
                                <div class="formidable_column col-sm-<?php echo $width; ?> <?php echo ($i==(count($row)-1)?' last':''); ?>">
                                <?php
                                    echo $column->getContainerStart();
                                    $elements = $column->getElements();
                                    if(is_array($elements) && count($elements)) {
                                        foreach($elements as $element) {
                                            if (in_array($element->getElementType(), array('hidden', 'hr', 'heading', 'line'))) echo $element->getInput();
                                            else { ?>
                                                <div class="element form-group <?php echo $element->getHandle(); ?>">
                                                    <?php if ($column->hasElementsWithLabels()) { ?>
                                                        <?php if (!$element->getPropertyValue('label_hide')) { ?>
                                                            <label for="<?php echo $element->getHandle(); ?>">
                                                                <?php echo $element->getLabel(); ?>
                                                                <?php if ($element->getPropertyValue('required')) { ?>
                                                                    <span class="required">*</span>
                                                                <?php } ?>
                                                            </label>
                                                        <?php } ?>
                                                    <?php } ?>
                                                    <div class="input <?php echo $element->getPropertyValue('label_hide')?'no_label':'has_label'; ?>">

                                                        <?php
                                                            // Changing elements format (for checkboxes and radios)
                                                            //$element->setFormat('<div class="radio {SIZE}"><label for="{ID}">{ELEMENT} {TITLE}</label></div>');
                                                            echo $element->getInput();
                                                        ?>

                                                        <?php if ($element->getPropertyValue('min_max')) { ?>
                                                            <div class="help-block">
                                                                <div id="<?php echo $element->getHandle() ?>_counter" class="counter" type="<?php echo $element->getPropertyValue('min_max_type') ?>" min="<?php echo $element->getPropertyValue('min_value') ?>" max="<?php echo $element->getPropertyValue('max_value') ?>">
                                                                    <?php if ($element->getPropertyValue('max_value') > 0) { ?>
                                                                        <?php  echo t('You have') ?> <span id="<?php echo $element->getHandle() ?>_count"><?php echo $element->getPropertyValue('max_value') ?></span> <?php echo strtolower($element->getProperty('min_max')[$element->getPropertyValue('min_max_type')]); ?> <?php echo t('left')?>.
                                                                    <?php } ?>
                                                                </div>
                                                            </div>
                                                        <?php } ?>
                                                    </div>

                                                    <?php if ($element->getPropertyValue('option_other')) { ?>
                                                        <div class="input option_other <?php echo $element->getPropertyValue('label_hide')?'no_label':'has_label'; ?>">
                                                            <?php echo $element->getOther(); ?>
                                                        </div>
                                                    <?php } ?>

                                                    <?php if ($element->getPropertyValue('confirmation')) { ?>
                                                        <div class="clearfix"></div>
                                                        <?php if ($column->hasElementsWithLabels()) { ?>
                                                            <?php if (!$element->getPropertyValue('label_hide')) { ?>
                                                                <label for="<?php echo $element->getHandle(); ?>">
                                                                    <?php echo t('Confirm %s', $element->getLabel()) ?>
                                                                    <?php if ($element->getPropertyValue('required')) { ?>
                                                                        <span class="required">*</span>
                                                                    <?php } ?>
                                                                </label>
                                                            <?php } ?>
                                                        <?php } ?>
                                                        <div class="input <?php echo $element->getPropertyValue('label_hide')?'no_label':'has_label'; ?>">
                                                            <?php echo $element->getConfirm(); ?>
                                                        </div>
                                                    <?php } ?>

                                                    <?php if ($element->getPropertyValue('tooltip') && !$review) { ?>
                                                        <div class="tooltip" id="<?php echo "tooltip_".$element->getElementID(); ?>">
                                                            <?php echo $element->getPropertyValue('tooltip_value'); ?>
                                                        </div>
                                                    <?php } ?>

                                                </div>
                                            <?php
                                                }
                                            }
                                        }
                                        echo $column->getContainerEnd();
                                        $i++;
                                    ?>
                                </div>
                                <?php
                            }
                            echo '</div>';
                        }
                    }
                    // After looping through all the layout elements, see if there is a step still open..
                    // If so, close the current step.
                    if (is_object($current_step)) echo $current_step->getContainerEnd();
                } ?>

                <?php if (!$f->hasButtons() && !$f->hasSteps()) { ?>
                    <div class="formidable_row">
                        <div class="formidable_column width-12">
                            <div class="element">
                                <div class="label-hidden"></div>
                                <div id="ff_buttons" class="buttons">
                                    <?php echo Core::make('helper/form')->submit('submit', t('Submit'), array(), 'submit btn btn-primary'); ?>
                                    <div class="please_wait_loader"><img src="<?php echo BASE_URL ?>/packages/formidable_full/images/loader.gif" alt="<?php echo t('Please wait...'); ?>"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </form>
        </div>
        <?php
          $db = \Database::connection();
          $serviceCode = 'ANM' . date('Y');

          $stmt = $db->prepare('SELECT * FROM esengoServices WHERE serviceCode = :serviceCode');
          $stmt->execute(array(
            ":serviceCode" => $serviceCode
          ));

          $result = $stmt->fetch();

          if(empty($result)){
            $lastYear = date('Y', strtotime('- 1 year'));
            $serviceCode = 'ANM' . $lastYear;

            $stmt = $db->prepare('SELECT * FROM esengoServices WHERE serviceCode = :serviceCode');
            $stmt->execute(array(
              ":serviceCode" => $serviceCode
            ));

            $result = $stmt->fetch();
          }
          $servicePrice = $result['baseprice'];
        ?>
        <script type="text/javascript">
        var adminCostLabel = $('label[for="opgelet-er-wordt-een-extra-administratieve-kost-aangerekend-bij-het-afkeuren-van-een-offerte-1321"]');

        if(typeof adminCostLabel.html() == "undefined"){
          var adminCostLabel = $('label[for="opgelet-er-wordt-een-extra-administratieve-kost-aangerekend-bij-het-afkeuren-van-een-offerte-2641"]');
        }

        if(typeof adminCostLabel.html() == "undefined"){
          var adminCostLabel = $('label[for="opgelet-er-wordt-een-extra-administratieve-kost-aangerekend-bij-het-afkeuren-van-een-offerte-2941"]');
        }

        var labelText = adminCostLabel.html() + '(€' + <?= $servicePrice?> + ')';
        adminCostLabel.html(labelText);
        </script>

    <?php } ?>
<?php }
