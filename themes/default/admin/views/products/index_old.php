<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<style type="text/css" media="screen">
    #PRData td:nth-child(7) {
        text-align: right;
    }
    <?php if($Owner || $Admin || $this->session->userdata('show_cost')) { ?>
    #PRData td:nth-child(9) {
        text-align: right;
    }
    <?php } if($Owner || $Admin || $this->session->userdata('show_price')) { ?>
    #PRData td:nth-child(8) {
        text-align: right;
    }
    <?php } ?>
</style>

<script>
    var oTable;
    const url = '<?= admin_url('products/getProducts'.($warehouse_id ? '/'.$warehouse_id : '').($supplier ? '?supplier='.$supplier->id : '')) ?>';
    $(document).ready(function () {
        oTable = $('#PRData').dataTable({
            "sScrollX": "100%",
            "bScrollCollapse": true,
            "aaSorting": [[2, "asc"], [3, "asc"]],
            "aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "<?= lang('all') ?>"]],
            "iDisplayLength": <?= $Settings->rows_per_page ?>,
            'bProcessing': true, 'bServerSide': true,
            'sAjaxSource': '<?= admin_url('products/getProducts'.($warehouse_id ? '/'.$warehouse_id : '').($supplier ? '?supplier='.$supplier->id : '')) ?>',
            'fnServerData': function (sSource, aoData, fnCallback) {
                aoData.push({
                    "name": "<?= $this->security->get_csrf_token_name() ?>",
                    "value": "<?= $this->security->get_csrf_hash() ?>"
                });
                $.ajax({'dataType': 'json', 'type': 'POST', 'url': sSource, 'data': aoData, timeout: 0, 'success': fnCallback});
            },
            'fnRowCallback': function (nRow, aData, iDisplayIndex) {
                var oSettings = oTable.fnSettings();
                nRow.id = aData[0];
                nRow.className = "product_link";
                //if(aData[7] > aData[9]){ nRow.className = "product_link warning"; } else { nRow.className = "product_link"; }
                return nRow;
            },
            "aoColumns": [
                {"bSortable": false, "mRender": checkbox}, {"bSortable": false,"mRender": img_hl}, null, null, null, null, null, null, <?php if($Owner || $Admin) { echo '{"mRender": currencyFormat}, {"mRender": currencyFormat},{"mRender": currencyFormat},'; } else { if($this->session->userdata('show_cost')) { echo '{"mRender": currencyFormat},';  } if($this->session->userdata('show_price')) { echo '{"mRender": currencyFormat},{"mRender": currencyFormat},';  } } ?> {"mRender": formatQuantity}, {"bSortable": false}
            ]
        }).fnSetFilteringDelay().dtFilter([
            {column_number: 2, filter_default_label: "[<?=lang('code');?>]", filter_type: "text", data: []},
            //{column_number: 3, filter_default_label: "[<?=lang('motor');?>]", filter_type: "text", data: []},
            {column_number: 3, filter_default_label: "[<?=lang('product_name');?>]", filter_type: "text", data: []},
            {column_number: 4, filter_default_label: "[<?=lang('product_name');?>]", filter_type: "text", data: []},
            {column_number: 5, filter_default_label: "[<?=lang('car_brand');?>]", filter_type: "text", data: []},
            {column_number: 6, filter_default_label: "[<?=lang('category');?>]", filter_type: "text", data: []},
            {column_number: 7, filter_default_label: "[<?=lang('piece_brand');?>]", filter_type: "text", data: []},
            <?php $col = 7;
            if($Owner || $Admin) {
                echo '{column_number : 8, filter_default_label: "['.lang('cost').']", filter_type: "text", data: [] },';
                echo '{column_number : 9, filter_default_label: "['.lang('price').']", filter_type: "text", data: [] },';
                echo '{column_number : 10, filter_default_label: "['.lang('web_price').']", filter_type: "text", data: [] },';
                $col += 3;
            } else {
                if($this->session->userdata('show_cost')) { $col++; echo '{column_number : '.$col.', filter_default_label: "['.lang('cost').']", filter_type: "text", data: [] },'; }
                if($this->session->userdata('show_price')) { $col++; echo '{column_number : '.$col.', filter_default_label: "['.lang('price').']", filter_type: "text, data: []" },';
                    $col++; echo '{column_number : '.$col.', filter_default_label: "['.lang('web_price').']", filter_type: "text, data: []" },'; }

            }
            ?>
            {column_number: <?php $col++; echo $col; ?>, filter_default_label: "[<?=lang('quantity');?>]", filter_type: "text", data: []}
        ], "footer");

        /* ------------ added by Rafa --------------- */

        initSelects();
/*
        $('#category_filter_select').on('change', function(e){
            console.log("category changed");
            console.log(e.target.value);
            const id = e.target.value;
            console.log(id);

            $.ajax({
                type: "get",
                //async: false,
                url: "<?= admin_url('products/getSubCategories') ?>/" + id,
                dataType: "json",
                beforeSend: function(){
                    $('#modal-loading').show();
                },
                success: function(scdata){
                    console.log("success");
                    console.log(scdata);

                    if (scdata != null) {
                        $('#subcategory_filter_div').show();
                        scdata.push({id: '', text: '<?= lang('select_subcategory') ?>'});
                        $("#subcategory_filter_select").select2("destroy").empty().attr("placeholder", "<?= lang('select_subcategory') ?>").select2({
                            placeholder: "<?= lang('select_category_to_load') ?>",
                            minimumResultsForSearch: 7,
                            data: scdata
                        });
                    } else {
                        $('#subcategory_filter_div').hide();
                        $("#subcategory_filter_div").select2("destroy").empty().attr("placeholder", "<?= lang('no_subcategory') ?>").select2({
                            placeholder: "<?= lang('no_subcategory') ?>",
                            minimumResultsForSearch: 7,
                            data: [{id: '', text: '<?= lang('no_subcategory') ?>'}]
                        });
                    }
                },
                error: function (data) {
                    console.log("error");
                    console.log(data);
                    bootbox.alert('<?= lang('ajax_error') ?>');
                }
            }).always(() => {
                $('#modal-loading').hide();
                $('#modalUpdatePriceBulk').modal('hide');
                if(oTable !== undefined && oTable !== null)
                    //oTable.fnReloadAjax();
                console.log("always");
            });
        });
*/
        $('#brand_filter_select').on('change', function(e){
            console.log("brand changed");
            console.log(e.target.value);
            const id = e.target.value;
            console.log(id);

            $.ajax({
                type: "get",
                //async: false,
                url: "<?= admin_url('products/getAllMotorsByBrand') ?>/" + id,
                dataType: "json",
                beforeSend: function(){
                    $('#modal-loading').show();
                },
                success: function(scdata){
                    console.log("success");
                    console.log(scdata);
                    scdata.push({id: '', text: '<?= lang('None') ?>'});
                    if (scdata != null) {                        
                        $("#motor_filter_select").select2("destroy").empty().attr("placeholder", "<?= lang('select_a_motor') ?>").select2({
                            placeholder: "<?= lang('select_a_motor') ?>",
                            minimumResultsForSearch: 7,
                            data: scdata
                        });
                    } else {
                        $("#motor_filter_select").select2("destroy").empty().attr("placeholder", "<?= lang('no_motor') ?>").select2({
                            placeholder: "<?= lang('no_motor') ?>",
                            minimumResultsForSearch: 7,
                            data: [{id: '', text: '<?= lang('no_motor') ?>'}]
                        });
                    }
                },
                error: function (data) {
                    console.log("error");
                    console.log(data);
                    bootbox.alert('<?= lang('ajax_error') ?>');
                }
            }).always(() => {
                $('#modal-loading').hide();
                $('#modalUpdatePriceBulk').modal('hide');
                console.log("always");
            });
        });
        /* ------------ end Rafa edits -------------- */
    });
</script>
<?php if ($Owner || $GP['bulk_actions']) {
    echo admin_form_open('products/product_actions'.($warehouse_id ? '/'.$warehouse_id : ''), 'id="action-form"');
} ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i
                class="fa-fw fa fa-barcode"></i><?= lang('products') . ' (' . ($warehouse_id ? $warehouse->name : lang('all_warehouses')) . ')'.($supplier ? ' ('.lang('supplier').': '.($supplier->company && $supplier->company != '-' ? $supplier->company : $supplier->name).')' : ''); ?>
        </h2>

        <div class="box-icon">
            <ul class="btn-tasks">
                <li class="dropdown">
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <i class="icon fa fa-tasks tip" data-placement="left" title="<?= lang("actions") ?>"></i>
                    </a>
                    <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                        <li>
                            <a href="<?= admin_url('products/add') ?>">
                                <i class="fa fa-plus-circle"></i> <?= lang('add_product') ?>
                            </a>
                        </li>
                        <?php if(!$warehouse_id) { ?>
                        <li>
                            <a href="<?= admin_url('products/update_price') ?>" data-toggle="modal" data-target="#modalUpdatePrice">
                                <i class="fa fa-file-excel-o"></i> <?= lang('update_price') ?>
                            </a>
                        </li>
                        <?php } ?>
                        <li>
                            <a href="#" id="labelProducts" data-action="labels">
                                <i class="fa fa-print"></i> <?= lang('print_barcode_label') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="sync_quantity" data-action="sync_quantity">
                                <i class="fa fa-arrows-v"></i> <?= lang('sync_quantity') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" id="excel" data-action="export_excel">
                                <i class="fa fa-file-excel-o"></i> <?= lang('export_to_excel') ?>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#" class="bpo" title="<b><?= $this->lang->line("delete_products") ?></b>"
                                data-content="<p><?= lang('r_u_sure') ?></p><button type='button' class='btn btn-danger' id='delete' data-action='delete'><?= lang('i_m_sure') ?></a> <button class='btn bpo-close'><?= lang('no') ?></button>"
                                data-html="true" data-placement="left">
                            <i class="fa fa-trash-o"></i> <?= lang('delete_products') ?>
                             </a>
                         </li>
                    </ul>
                </li>
                <?php if (!empty($warehouses)) { ?>
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#"><i class="icon fa fa-building-o tip" data-placement="left" title="<?= lang("warehouses") ?>"></i></a>
                        <ul class="dropdown-menu pull-right tasks-menus" role="menu" aria-labelledby="dLabel">
                            <li><a href="<?= admin_url('products') ?>"><i class="fa fa-building-o"></i> <?= lang('all_warehouses') ?></a></li>
                            <li class="divider"></li>
                            <?php
                                foreach ($warehouses as $warehouse) {
                                    echo '<li><a href="' . admin_url('products/' . $warehouse->id) . '"><i class="fa fa-building"></i>' . $warehouse->name . '</a></li>';
                                }
                            ?>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="col-lg-12">
                <p class="introtext"><?= lang('list_results'); ?></p>
                <h2><?= lang('search_filters'); ?></h2>
                <div class="row">
                    <form class="bv-form">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('Brand'); ?></label>
                                <input type="text" id="brand_filter_select" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('Motor'); ?></label>
                                <input type="text" id="motor_filter_select" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label><?= lang('Category'); ?></label>
                                <input type="text" id="category_filter_select" class="form-control">
                            </div>
                        </div>
                        <!--div class="col-md-4" id="subcategory_filter_div" style="display: none;">
                            <div class="form-group">
                                <label><?= lang('Subcategory'); ?></label>
                                <input type="text" id="subcategory_filter_select" class="form-control">
                            </div>
                        </div-->
                        <div class="col-md-12">
                            <a class="btn btn-primary" onclick="execFilter()"><?= lang('Filter'); ?></a>
                            <a class="btn btn-default" onclick="resetFilters()"><?= lang('reset_filters'); ?></a>
                        </div>
                    </form>
                </div>
                <hr>
                <h2><?= lang('selected_rows_actions'); ?></h2>
                <div class="row">
                    <form class="bv-form">
                        <div class="col-md-4">
                            <select id="selected_items_action" class="form-control">
                                <option value="showUpdatePriceModal" selected><?= lang('update_discounts_and_recharges'); ?></option>
                                <option value="showUpdateCostModal" selected><?= lang('update_costs'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <a class="btn btn-primary" onclick="execMultiSelectAction()"><?= lang('Go'); ?></a>
                        </div>
                    </form>
                </div>
                <div class="table-responsive">
                    <table id="PRData" class="table table-bordered table-condensed table-hover table-striped responsive">
                        <thead>
                        <tr class="primary">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkth" type="checkbox" name="check"/>
                            </th>
                            <th style="min-width:40px; width: 40px; text-align: center;"><?php echo $this->lang->line("image"); ?></th>
                            <th><?= lang("bar_code_header") ?></th>
                            <!--th><?= lang("motor") ?></th-->
                            <th><?= lang("product_name") ?></th>
                            <th><?= lang("description") ?></th>
                            <th><?= lang("car_brand") ?></th>
                            <th><?= lang("category") ?></th>
                            <th><?= lang("piece_brand") ?></th>
                            <th><?= lang("cost") ?></th>
                            <th><?= lang("price") ?></th>
                            <th><?= lang("web_price") ?></th>
                            <th><?= lang("quantity") ?></th>
                            <th style="min-width:65px; text-align:center;"><?= lang("actions") ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td colspan="11" class="dataTables_empty"><?= lang('loading_data_from_server'); ?></td>
                        </tr>
                        </tbody>

                        <tfoot class="dtFilter">
                        <tr class="active">
                            <th style="min-width:30px; width: 30px; text-align: center;">
                                <input class="checkbox checkft" type="checkbox" name="check" />
                            </th>
                            <th style="min-width:40px; width: 40px; text-align: center;"><?php echo $this->lang->line("image"); ?></th>                            
                            <th></th>
                            <!--th></th-->
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th style="width:65px; text-align:center;"><?= lang("actions") ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div>
                    <div class="form-group">
                        <label>
                            <?= lang('selected_rows_actions'); ?>
                        </label>
                        <select id="selected_items_action2">
                            <option value="showUpdatePriceModal" selected><?= lang('update_discounts_and_recharges'); ?></option>
                            <option value="showUpdateCostModal" selected><?= lang('update_costs'); ?></option>
                        </select>
                        <a class="btn btn-primary" onclick="execMultiSelectAction()"><?= lang('Go'); ?></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($Owner || $GP['bulk_actions']) { ?>
    <div style="display: none;">
        <input type="hidden" name="form_action" value="" id="form_action"/>
        <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
    </div>
    <?= form_close() ?>
<?php } ?>
<!----------------------------------------- added by rafa ----------------------------------------------->
<!--------------------- update discounts and recharges modal ---------------------------->
<div class="modal" tabindex="-1" role="dialog" id="modalUpdatePriceBulk">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= lang('update_discounts_and_recharges') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p><strong><?= lang('Atention') ?>:</strong> <?= lang('update_dr_desc') ?></p>
        <form id="myForm">
            <div class="form-group">
                <label>
                    <?= lang('cf2') ?> (<?= lang('betwen') ?> 0 <?= lang('and') ?> 100)
                </label>
                <input type="number" id="cf2" name="cf2" class="form-control" max="100" min="0" maxlength="3">
            </div>
            <div class="form-group">
                <label>
                    <?= lang('cf3') ?> (<?= lang('betwen') ?> 0 <?= lang('and') ?> 100)
                </label>
                <input type="number" id="cf3" name="cf3" class="form-control" max="100" min="0" maxlength="3">
            </div>
            <div class="form-group">
                <label>
                    <?= lang('cf4') ?> (<?= lang('betwen') ?> 0 <?= lang('and') ?> 100)
                </label>
                <input type="number" id="cf4" name="cf4" class="form-control" max="100" min="0" maxlength="3">
            </div>
            <div class="form-group">
                <label>
                    <?= lang('cf5') ?> (<?= lang('betwen') ?> 0 <?= lang('and') ?> 1000)
                </label>
                <input type="number" id="cf5" name="cf5" class="form-control" max="1000" min="0" maxlength="4">
            </div>
            <div class="form-group">
                <label>
                    <?= lang('cf6') ?> (<?= lang('betwen') ?> 0 <?= lang('and') ?> 1000)
                </label>
                <input type="number" id="cf6" name="cf6" class="form-control" max="1000" min="0" maxlength="4">
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" onclick="saveChanges()">Guardar</button>
      </div>
    </div>
  </div>
</div>
<!--------------------- end update discounts and recharges modal ------------------------>
<!------------------------------- update costs modal ------------------------------------>
<div class="modal" tabindex="-1" role="dialog" id="modalUpdateCostBulk">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?= lang('update_costs') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p><strong><?= lang('Atention') ?>:</strong> <?= lang('update_costs_desc') ?></p>
        <form id="myForm2">
            <div class="form-group">
                <label>
                    <?= lang('cost') ?> (<?= lang('major_than') ?> 0)
                </label>
                <input type="number" id="cost-n" name="cost" class="form-control" max="10000000000" min="0" maxlength="3">
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" onclick="saveChanges2()">Guardar</button>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
    function initSelects(){
        let categories = <?= json_encode($categories) ?>;
        categories.push({id: '', text: '<?= lang('None') ?>'});
        let brands = <?= json_encode($brands) ?>;
        brands.push({id: '', text: '<?= lang('None') ?>'});
        let motors = <?= json_encode($motors) ?>;
        motors.push({id: '', text: '<?= lang('None') ?>'});
        $("#category_filter_select").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
            placeholder: "<?= lang('select_a_category') ?>", data: categories
        });
/*
        $("#subcategory_filter_select").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
            placeholder: "<?= lang('select_category_to_load') ?>", data: [
                {id: '', text: '<?= lang('select_category_to_load') ?>'}
            ]
        });
*/
        $("#brand_filter_select").select2("destroy").empty().attr("placeholder", "<?= lang('select_category_to_load') ?>").select2({
            placeholder: "<?= lang('select_a_brand') ?>", data: brands
        });

        $("#motor_filter_select").select2("destroy").empty().attr("placeholder", "<?= lang('select_a_motor') ?>").select2({
            placeholder: "<?= lang('select_a_motor') ?>", data: motors
        });
    }
    function execMultiSelectAction(){
        const option = $('#selected_items_action').val();
        console.log(option);
        switch(option){
            case "showUpdatePriceModal": showUpdatePriceModal(); break;
            case "showUpdateCostModal": showUpdateCostModal(); break;
            default: break;
        }
    }
    function execMultiSelectAction2(){
        const option = $('#selected_items_action2').val();
        console.log(option);
        switch(option){
            case "showUpdatePriceModal": showUpdatePriceModal(); break;
            case "showUpdateCostModal": showUpdateCostModal(); break;
            default: break;
        }
    }
    function execFilter(){
        console.log("execFilter");
        const category = $("#category_filter_select").val();
        //const subcategory = $("#subcategory_filter_select").val();
        const brand = $("#brand_filter_select").val();
        const motor = $("#motor_filter_select").val();
        let newUrl = url;
        console.log(category);
        //console.log(subcategory);
        console.log(brand);

        if(category || /*subcategory ||*/ brand)
            newUrl += '?';

        if(category)
            newUrl += 'category=' + category;
/*
        if(subcategory)
            newUrl += (newUrl.includes('category') ? '&' : '') + 'subcategory=' + subcategory;
*/
        if(motor)
            newUrl += (newUrl.includes('category') || newUrl.includes('subcategory') ? '&' : '') + 'motor=' + motor;

        if(brand)
            newUrl += ((newUrl.includes('motor') || newUrl.includes('subcategory') || newUrl.includes('category')) ? '&' : '') + 'brand=' + brand;

        oTable.fnReloadAjax(newUrl);
    }
    function resetFilters(){
        console.log("resetFilters");
        $("#category_filter_select").select2("val","");
        //$("#subcategory_filter_select").select2("val","");
        $("#brand_filter_select").select2("val","");
        $("#motor_filter_select").select2("val","");
        //$("#subcategory_filter_select").hide();
        oTable.fnReloadAjax(url);
    }
    function showUpdatePriceModal(){
        $('#modalUpdatePriceBulk').modal();
    }
    function showUpdateCostModal(){
        $('#modalUpdateCostBulk').modal();
    }
    function saveChanges(){
        const formValues = $('#myForm').serializeArray();
        const formData = new FormData(document.getElementById('myForm'));
        const selectedItems = $('input[name="val[]"]').serializeArray();
        console.log(formValues);
        if($('#cf2').val() == "" || Number($('#cf2').val()) < 0 || Number($('#cf2').val()) > 100){
            bootbox.alert('<?= lang('invalid_field') ?>:' + ' <?= lang('cf2') ?>');
            $('#cf2').focus();
            return;
        }
        if($('#cf3').val() == "" || Number($('#cf3').val()) < 0 || Number($('#cf3').val()) > 100){
            bootbox.alert('<?= lang('invalid_field') ?>:' + ' <?= lang('cf3') ?>');
            $('#cf3').focus();
            return;
        }
        if($('#cf4').val() == "" || Number($('#cf4').val()) < 0 || Number($('#cf4').val()) > 100){
            bootbox.alert('<?= lang('invalid_field') ?>:' + ' <?= lang('cf4') ?>');
            $('#cf4').focus();
            return;   
        }
        if($('#cf5').val() == "" || Number($('#cf5').val()) < 0 || Number($('#cf5').val()) > 1000){
            bootbox.alert('<?= lang('invalid_field') ?>:' + ' <?= lang('cf5') ?>');
            $('#cf5').focus();
            return;   
        }
        if($('#cf6').val() == "" || Number($('#cf6').val()) < 0 || Number($('#cf6').val()) > 1000){
            bootbox.alert('<?= lang('invalid_field') ?>:' + ' <?= lang('cf6') ?>');
            $('#cf6').focus();
            return;   
        }
        if(selectedItems.length == 0){
            bootbox.alert('<?= lang('no_items_selected') ?>');
            return;
        }
        if(formValues.length == 0){
            bootbox.alert('<?= lang('no_input_data') ?>');
            return;
        }
        for(let value of selectedItems){
            formData.append("items[]", value.value);
        }
        
        formData.append("<?= $this->security->get_csrf_token_name() ?>","<?= $this->security->get_csrf_hash() ?>");
        $.ajax({
            type: "post",
            data: formData,
            url: "<?= admin_url('products/update_price_bulk') ?>",
            dataType: "json",
            processData: false,
            contentType: false,
            cache: false,
            timeout: 0,
            beforeSend: function(){
                $('#modal-loading').show();
            },
            success: function (resp) {
                console.log("success");
                console.log(resp);
                
                bootbox.alert(resp.msg);
            },
            error: function (data) {
                console.log("error");
                console.log(data);
                bootbox.alert('<?= lang('ajax_error') ?>');
            }
        }).always(() => {
            $('#modal-loading').hide();
            $('#modalUpdatePriceBulk').modal('hide');
            if(oTable !== undefined && oTable !== null)
                oTable.fnReloadAjax();
            console.log("always");
        });
    }
    function saveChanges2(){
        const formValues = $('#myForm2').serializeArray();
        const formData = new FormData(document.getElementById('myForm2'));
        const selectedItems = $('input[name="val[]"]').serializeArray();
        console.log(formValues);
        if($('#cost-n').val() == "" || Number($('#cost-n').val()) < 0 || Number($('#cost-n').val()) > 100000000){
            bootbox.alert('<?= lang('invalid_field') ?>:' + ' <?= lang('cost') ?>');
            $('#cost-n').focus();
            return;
        }
        
        if(selectedItems.length == 0){
            bootbox.alert('<?= lang('no_items_selected') ?>');
            return;
        }
        if(formValues.length == 0){
            bootbox.alert('<?= lang('no_input_data') ?>');
            return;
        }
        for(let value of selectedItems){
            formData.append("items[]", value.value);
        }
        
        formData.append("<?= $this->security->get_csrf_token_name() ?>","<?= $this->security->get_csrf_hash() ?>");
        $.ajax({
            type: "post",
            data: formData,
            url: "<?= admin_url('products/update_cost_bulk') ?>",
            dataType: "json",
            processData: false,
            contentType: false,
            cache: false,
            timeout: 0,
            beforeSend: function(){
                $('#modal-loading').show();
            },
            success: function (resp) {
                console.log("success");
                console.log(resp);
                
                bootbox.alert(resp.msg);
            },
            error: function (data) {
                console.log("error");
                console.log(data);
                bootbox.alert('<?= lang('ajax_error') ?>');
            }
        }).always(() => {
            $('#modal-loading').hide();
            $('#modalUpdateCostBulk').modal('hide');
            if(oTable !== undefined && oTable !== null)
                oTable.fnReloadAjax();
            console.log("always");
        });
    }
</script>
<!-- end rafa code -->