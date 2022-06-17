<?php  defined('C5_EXECUTE') or die("Access Denied."); ?>

<?php
$db = Database::connection();
// set locale for Javascript errors (src/Formidable/Validator/Result.php)
$_SESSION['localeJS'] = $f->getLocale();
?>

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
                                                <div class="element  form-group <?php echo $element->getHandle();?>">


                                                    <div class="input input-field <?php echo $element->getPropertyValue('label_hide')?'no_label':'has_label'; ?>">

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

        <!-- SESSION PREFILL -->
        <?php
        if($_SESSION['answers']){
          $prefillFieldIDs = array(59, 549, 535, 55, 69,57,137, 58, 540, 541, 542, 543, 354, 356, 134, 135, 138, 352,143, 272,273,274, 275,276,364,365,366,279,281,280,282, 242,243,245,244,246,249,251,250,252);

          foreach($_SESSION['answers'] as $answer){
            $elementID = $answer['elementID'];
            if(array_search($elementID, $prefillFieldIDs) !== FALSE){
              if($elementID == 76 || $elementID == 261 || $elementID == 291 || $elemntID == 352 || $elementID == 143){
                $value = unserialize($answer['answer_unformated']);
              }else{
                $value = $answer['answer_formated'];
              }

              $result = $db->getRow('SELECT * FROM FormidableFormElements WHERE elementID = ' . $elementID);

              $data['#' . $result['label_import']] = $value;
            }
          }
        ?>
        <script type="text/javascript">
          <?php foreach($data as $key => $val){
            if($key == '#abcklant-352' || $key == '#admin-contact-143'){
              if($val == 'Ik ben al klant bij ABC.' || isset($val['value'][0]) && $val['value'][0] == 'Ja'){
                ?>
                $('<?= $key . '1'?>').prop('checked', true);
                <?php
              }else{
                ?>
                $('<?= $key . '2'?>').prop('checked', true);
                <?php
              }
            }
            ?>
            $('<?= $key?>').val('<?= $val?>');
            <?php
          } ?>
        </script>
      <?php } ?>

        <!-- PREFILL FORM -->
        <?php
          $db = \Database::connection();
          if($_GET['hash']){
            $stmt = $db->prepare('SELECT * FROM prefillrepairlinks WHERE hash = :hash');
            $stmt->execute(array(
              ":hash" => $_GET['hash']
            ));

            $result = $stmt->fetch();

            $prefillData = json_decode($result['jsondata'], true);
            $prefillData['desc'] = strip_tags($prefillData['desc']);
            $prefillData['desc'] = str_replace("\n", '\\n', $prefillData['desc']);

            // echo '<pre>';
            // print_r($prefillData);
            // exit;
        ?>
        <script type="text/javascript">
        $('.newcustomer').css( "display", "none" );
        //nl
        $('#referentie-85').css("display", "none");
        $('#referentie-535').css("display", "block");
        //en
        $('#reference-267').css("display", "none");
        $('#reference-536').css("display", "block");
        //fr
        $('#reference-297').css("display", "none");
        $('#reference-537').css("display", "block");


        if("<?= $prefillData['lang']?>" == 'Dutch'){
          var compName = $('#bedrijfsnaam-59');
          var firstname = $('#voornaam-55');
          var lastname = $('#familienaam-69');
          var email = $('#e-mailadres-58');
          var phone = $('#telefoon-gsm-57');
          var brand = $('#merknaam-62');
          var item = $('#typeartikelnummer-63');
          var desc = $('#foutomschrijving-64');
          var lowPrior = $('#prioriteit-761');
          var medPrior = $('#prioriteit-762');
          var highPrior = $('#prioriteit-763');
          var withOffer = $('#offerte-781');
          var withoutOffer = $('#offerte-782');
          var ref = $('#referentie-85');
          var ref2 = $('#referentie-535');
        }

        if("<?= $prefillData['lang']?>" == 'English'){
          var compName = $('#company-name-242');
          var firstname = $('#first-name-243');
          var lastname = $('#surname-245');
          var email = $('#e-mail-address-244');
          var phone = $('#telefoon-gsm-246');
          var brand = $('#brand-name-254');
          var item = $('#typearticle-number-255');
          var desc = $('#error-description-256');
          var lowPrior = $('#priority-2611');
          var medPrior = $('#priority-2612');
          var highPrior = $('#priority-2613');
          var withOffer = $('#offer-2631');
          var withoutOffer = $('#offer-2632');
          var ref = $('#reference-267');
          var ref2 = $('#reference-536');
        }

        if("<?= $prefillData['lang']?>" == 'French'){
          var compName = $('#nom-de-lentreprise-272');
          var firstname = $('#prenom-273');
          var lastname = $('#nom-de-famille-275');
          var email = $('#e-mail-274');
          var phone = $('#telephonegsm-276');
          var brand = $('#marque-284');
          var item = $('#type-numero-darticle-285');
          var desc = $('#analyse-de-defaut-286');
          var lowPrior = $('#priorite-2911');
          var medPrior = $('#priorite-2912');
          var highPrior = $('#priorite-2913');
          var withOffer = $('#devis-2931');
          var withoutOffer = $('#devis-2932');
          var ref = $('#reference-297');
          var ref2 = $('#reference-537');
        }


          compName.val("<?= addslashes($prefillData['compName'])?>");
          firstname.val("<?= addslashes($prefillData['firstname'])?>");
          lastname.val("<?= addslashes($prefillData['lastname'])?>");
          email.val("<?= addslashes($prefillData['email'])?>");
          phone.val("<?= addslashes($prefillData['phone'])?>");
          brand.val("<?= addslashes($prefillData['brand'])?>");
          item.val("<?= addslashes($prefillData['code'])?>");
          desc.val("<?= $prefillData['desc']?>");
          switch("<?= $prefillData['priority']?>"){
            case 'INL':
            lowPrior.prop("checked", true);
            break;

            case 'INM':
            medPrior.prop("checked", true);
            break;

            case 'INH':
            highPrior.prop("checked", true);
            break;

            default:
            lowPrior.prop("checked", true);
          }
          if("<?= $prefillData['offer']?>" == 1){
            withOffer.prop("checked", true);
          }else{
            withoutOffer.prop("checked", true);
          }
          ref.val("<?= $prefillData['ref']?>");
          ref2.val("<?= $prefillData['ref']?>");
          </script>
      <?php }else{
        ?>
        <script type="text/javascript">
        //nl
        $('#referentie-85').css("display", "block");
        $('#referentie-535').css("display", "none");
        //en
        $('#reference-267').css("display", "block");
        $('#reference-536').css("display", "none");
        //fr
        $('#reference-297').css("display", "block");
        $('#reference-537').css("display", "none");
        </script>
        <?php
      } ?>
        <!-- ADMINISTRATIVE COST -->
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
<?php } ?>
