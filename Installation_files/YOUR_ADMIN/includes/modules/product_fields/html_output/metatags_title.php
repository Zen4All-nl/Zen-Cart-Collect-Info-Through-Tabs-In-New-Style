<?php
if (zen_not_null($_POST)) {
  $metatags_title = (isset($_POST['metatags_title']) ? $_POST['metatags_title'] : '');
}
?>
<?php echo zen_draw_label(TEXT_META_TAGS_TITLE, 'metatags_title[' . $languages[$i]['id'] . ']', 'class="col-sm-3 control-label"'); ?>
<div class="col-sm-9 col-md-6">
    <?php for ($i = 0, $n = sizeof($languages); $i < $n; $i++) { ?>
    <div class="input-group">
      <span class="input-group-addon">
        <?php echo zen_image(DIR_WS_CATALOG_LANGUAGES . $languages[$i]['directory'] . '/images/' . $languages[$i]['image'], $languages[$i]['name']); ?>
      </span>
      <?php echo zen_draw_input_field('metatags_title[' . $languages[$i]['id'] . ']', htmlspecialchars(isset($metatags_title[$languages[$i]['id']]) ? stripslashes($metatags_title[$languages[$i]['id']]) : zen_get_metatags_title($productInfo['products_id']['value'], $languages[$i]['id']), ENT_COMPAT, CHARSET, TRUE), zen_set_field_length(TABLE_META_TAGS_PRODUCTS_DESCRIPTION, 'metatags_title', '150', false) . ' class="form-control"'); ?>
    </div>
    <br>
  <?php } ?>
</div>