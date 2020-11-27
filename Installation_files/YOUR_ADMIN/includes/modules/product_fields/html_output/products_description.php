<?php
if (zen_not_null($_POST)) {
  $products_description = (isset($_POST['products_description']) ? $_POST['products_description'] : '');
}
?>
<p class="col-sm-3 control-label"><?php echo TEXT_PRODUCTS_DESCRIPTION; ?></p>
<div class="col-sm-9 col-md-6">
  <?php for ($i = 0, $n = sizeof($languages); $i < $n; $i++) { ?>
    <div class="input-group">
      <span class="input-group-addon" style="vertical-align: top;">
        <?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>
      </span>
      <?php echo zen_draw_textarea_field('products_description[' . $languages[$i]['id'] . ']', 'soft', '100', '30', htmlspecialchars((isset($products_description[$languages[$i]['id']])) ? stripslashes($products_description[$languages[$i]['id']]) : zen_get_products_description($productInformation->products_id['value'], $languages[$i]['id']), ENT_COMPAT, CHARSET, TRUE), 'class="editorHook form-control"'); ?>
    </div>
    <br>
  <?php } ?>
</div>