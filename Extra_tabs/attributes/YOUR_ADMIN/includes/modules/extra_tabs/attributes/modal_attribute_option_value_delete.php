<!-- Delete Option Value modal-->
<div id="deleteOptionValueModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">
          <i class="fa fa-times" aria-hidden="true"></i>
          <span class="sr-only"><?php echo TEXT_CLOSE; ?></span>
        </button>
        <h4 class="modal-title" id="deleteOptionValueModalLabel"><?php echo TITLE_CONFIRM_DELETE; ?></h4>
      </div>
      <form name="delete_option_value" method="post" enctype="multipart/form-data" id="deleteOptionValueConfirm">
        <div class="modal-body bg-danger" id="deleteOptionValueText">
          <p class="danger"><?php echo TEXT_DELETE_ATTRIBUTES_VALUE; ?></p>
          <div class="form-group">
            <p class="form-control"><?php echo TEXT_INFO_PRODUCT_NAME . zen_get_products_name($_GET['pID'], $_SESSION['languages_id']); ?></p>
            <p class="form-control" id="deleteOptionValueName"></p>
          </div>
        </div>
        <?php echo zen_draw_hidden_field('attributes_id', ''); ?>
        <div class="modal-footer">
          <button type="submit" class="btn btn-danger" onclick="deleteOptionValue()">
            <i class="fa fa-trash"></i>
          </button>
          <button type="button" class="btn btn-default" data-dismiss="modal">
            <i class="fa fa-close"></i> <?php echo TEXT_CLOSE; ?>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>