jQuery(document).ready(function($) {

    'use strict';

    let table_data_a = [];
    let comma_separated_numbers_regex = /^(\s*(\d+\s*,\s*)+\d+\s*|\s*\d+\s*)$/;
    let hex_rgb_regex = /^#(?:[0-9a-fA-F]{3}){1,2}$/;

    //Save the data attributes of all the tables in the table_data_a array of objects
    $('.daextletal-table').each(function(index){

        'use strict';

        let table_obj = new Object();

        table_obj.id = $(this).attr("id");

        //sorting options
        table_obj.enable_sorting = $(this).data("enable-sorting");
        table_obj.enable_manual_sorting = $(this).data("enable-manual-sorting");
        table_obj.show_position = $(this).data("show-position");
        table_obj.position_side = $(this).data("position-side");
        table_obj.position_label = $(this).data("position-label");
        table_obj.number_format = $(this).data("number-format");
        table_obj.order_desc_asc = $(this).data("order-desc-asc");
        table_obj.order_by = $(this).data("order-by");
        table_obj.order_data_type = $(this).data("order-data-type");
        table_obj.order_date_format = $(this).data("order-date-format");

        //style options
        table_obj.table_layout = $(this).data("table-layout");
        table_obj.table_width = $(this).data("table-width");
        table_obj.table_width_value = $(this).data("table-width-value");
        table_obj.table_minimum_width = $(this).data("table-minimum-width");
        table_obj.table_margin_top = $(this).data("table-margin-top");
        table_obj.table_margin_bottom = $(this).data("table-margin-bottom");
        table_obj.enable_container = $(this).data("enable-container");
        table_obj.container_width = $(this).data("container-width");
        table_obj.container_height = $(this).data("container-height");
        table_obj.show_header = $(this).data("show-header");
        table_obj.header_font_size = $(this).data("header-font-size");
        table_obj.header_font_family = $(this).data("header-font-family");
        table_obj.header_font_weight = $(this).data("header-font-weight");
        table_obj.header_font_style = $(this).data("header-font-style");
        table_obj.header_background_color = $(this).data("header-background-color");
        table_obj.header_font_color = $(this).data("header-font-color");
        table_obj.header_link_color = $(this).data("header-link-color");
        table_obj.header_border_color = $(this).data("header-border-color");
        table_obj.header_position_alignment = $(this).data("header-position-alignment");
        table_obj.body_font_size = $(this).data("body-font-size");
        table_obj.body_font_family = $(this).data("body-font-family");
        table_obj.body_font_weight = $(this).data("body-font-weight");
        table_obj.body_font_style = $(this).data("body-font-style");
        table_obj.even_rows_background_color = $(this).data("even-rows-background-color");
        table_obj.odd_rows_background_color = $(this).data("odd-rows-background-color");
        table_obj.even_rows_font_color = $(this).data("even-rows-font-color");
        table_obj.even_rows_link_color = $(this).data("even-rows-link-color");
        table_obj.odd_rows_font_color = $(this).data("odd-rows-font-color");
        table_obj.odd_rows_link_color = $(this).data("odd-rows-link-color");
        table_obj.rows_border_color = $(this).data("rows-border-color");

        //autoalignment options
        table_obj.autoalignment_priority = $(this).data("autoalignment-priority");
        table_obj.autoalignment_affected_rows_left = $(this).data("autoalignment-affected-rows-left").toString();
        table_obj.autoalignment_affected_rows_center = $(this).data("autoalignment-affected-rows-center").toString();
        table_obj.autoalignment_affected_rows_right = $(this).data("autoalignment-affected-rows-right").toString();
        table_obj.autoalignment_affected_columns_left = $(this).data("autoalignment-affected-columns-left").toString();
        table_obj.autoalignment_affected_columns_center = $(this).data("autoalignment-affected-columns-center").toString();
        table_obj.autoalignment_affected_columns_right = $(this).data("autoalignment-affected-columns-right").toString();

        //responsive options
        table_obj.tablet_breakpoint = $(this).data("tablet-breakpoint");
        table_obj.hide_tablet_list = $(this).data("hide-tablet-list").toString();
        table_obj.tablet_header_font_size = $(this).data("tablet-header-font-size");
        table_obj.tablet_body_font_size = $(this).data("tablet-body-font-size");
        table_obj.tablet_hide_images = $(this).data("tablet-hide-images");
        table_obj.phone_breakpoint = $(this).data("phone-breakpoint");
        table_obj.hide_phone_list = $(this).data("hide-phone-list").toString();
        table_obj.phone_header_font_size = $(this).data("phone-header-font-size");
        table_obj.phone_body_font_size = $(this).data("phone-body-font-size");
        table_obj.phone_hide_images = $(this).data("phone-hide-images");

        //advanced options
        table_obj.enable_cell_properties = $(this).data("enable-cell-properties");

        table_data_a[index] = table_obj;

    });

    /*
     * Loop trough all the objects available in teh table_data_a array of objects. Each object includes the identifier
     * of the table and all the data attributes.
     */
    $.each(table_data_a, function( index, table_obj ) {

        'use strict';

        let margin;
        let vertical_padding;
        let horizontal_padding;
        let image_height;
        let header_font_size_cells;
        let header_padding_cells;
        let image_height_value;
        let header_image_height;
        let header_image_left_margin;
        let header_image_right_margin;
        let line_height_value;
        let body_font_size_cells;
        let body_padding_cells;
        let body_line_height;
        let body_image_left_margin;
        let body_image_right_margin;
        let header_line_height;

        //sorting options ----------------------------------------------------------------------------------------------

        //initialize tablesorter if the "Enable Sorting" option is true
        if( parseInt(table_obj.enable_sorting, 10) == 1 ){

            let sortList = [];

            //apply the sorting only if the "Order" option is not set to 0 (Disabled)
            if( parseInt(table_obj['order_desc_asc'], 10) == 1 || parseInt(table_obj['order_desc_asc'], 10) == 2 ){

                //generate the value for the sortList option, used to determine the initial sort of the table
                sortList.push([(table_obj['order_by'] - 1), Math.abs(parseInt(table_obj['order_desc_asc'], 10) - 2)]);

                /*
                 * If the "Order Data Type" option is different from "Auto" add to the specific th element the
                 * "data-sorter" attribute to set the parser
                 *
                 * If the "Order Data Type" option is equal to "shortDate" add to the specific th element the
                 * "data-date-format" attribute to set the date format
                 */
                if( table_obj["order_data_type"] != 'auto' ){
                    $("#" + table_obj.id + " th:nth-child(" + table_obj['order_by'] + ")" ).attr("data-sorter", table_obj['order_data_type']);
                    if(table_obj['order_data_type'] == 'shortDate'){
                        $("#" + table_obj.id + " th:nth-child(" + table_obj['order_by'] + ")" ).attr("data-date-format", table_obj['order_date_format']);
                    }
                }

            }

            let configuration = new Object();

            //define the configuration options -------------------------------------------------------------------------

            //set the intial sorting order
            configuration.sortList = sortList;

            //set the number format
            if( parseInt(table_obj.number_format, 10) == 1 ){
                configuration.usNumberFormat = true;
            }else{
                configuration.usNumberFormat = false;
            }

            //init tablesorter
            $("#" + table_obj.id).tablesorter(configuration);

            //enable or disable the manual sorting
            if( parseInt(table_obj.enable_manual_sorting, 10) == 0 ){
                //disable the event used to trigger the manual sorting
                $( "#" + table_obj.id +" > thead th").off();
            }else{
                //set the mouse cursor to pointer
                $( "#" + table_obj.id +" > thead th").addClass('daextletal-cursor-pointer');
            }

        }

        //generate the position column
        if( parseInt(table_obj.show_position, 10) == 1 ){

            if(table_obj.position_side == 'left'){

                //Generate the position column on the left side of the table -------------------------------------------

                //add a "th" element with the "Position Label" as the content in the "table > thead > tr" element
                if( parseInt(table_obj.enable_sorting, 10) == 1 ){
                    //include the <div> to maintain a DOM structure similar to the one of the other header cells
                    $('#' + table_obj.id + ' > thead > tr').prepend('<th><div>' + table_obj.position_label + '</div></th>');
                }else{
                    //do not include the <div> to maintain a DOM structure similar to the one of the other header cells
                    $('#' + table_obj.id + ' > thead > tr').prepend('<th>' + table_obj.position_label + '</th>');
                }

                //add the "td" element as the first child of each "table > tbody > tr" element
                $('#' + table_obj.id + ' > tbody > tr').each(function(index){
                    $(this).prepend('<td>' + (index + 1) + '</td>');
                });

            }else{

                //Generate the position column on the right side of the table ------------------------------------------

                //add a "th" element with the "Position Label" as the content in the "table > thead > tr" element
                if( parseInt(table_obj.enable_sorting, 10) == 1 ){
                    //include the <div> to maintain a DOM structure similar to the one of the other header cells
                    $('#' + table_obj.id + ' > thead > tr').append('<th><div>' + table_obj.position_label + '</div></th>');
                }else{
                    //do not include the <div> to maintain a DOM structure similar to the one of the other header cells
                    $('#' + table_obj.id + ' > thead > tr').append('<th>' + table_obj.position_label + '</th>');
                }

                //add the "td" element as the first child of each "table > tbody > tr" element
                $('#' + table_obj.id + ' > tbody > tr').each(function(index){
                    $(this).append('<td>' + (index + 1) + '</td>');
                });

            }



        }

        //style options ------------------------------------------------------------------------------------------------

        //Table Layout
        switch(parseInt(table_obj.table_layout, 10)){
            case 0:
                $('#' + table_obj.id).css('table-layout', 'auto');
                break;
            case 1:
                $('#' + table_obj.id).css('table-layout', 'fixed');
                break;
        }

        //Table Width
        switch(parseInt(table_obj.table_width, 10)){
            case 0:
                $('#' + table_obj.id).css('width', '100%');
                break;
            case 1:
                $('#' + table_obj.id).css('width', 'auto');
                break;
            case 2:
                $('#' + table_obj.id).css('width', table_obj.table_width_value + 'px');
                break;
        }

        //Table Minimum Width
        $('#' + table_obj.id).css('min-width', table_obj.table_minimum_width + 'px');

        //If the container is enabled apply it with the related styles, otherwise apply only the margin to the table
        if(parseInt(table_obj.enable_container, 10) == 1){

            //If the container is enabled apply the two margins to the container
            $('#' + table_obj.id).parent().css('margin-top', table_obj.table_margin_top + 'px');
            $('#' + table_obj.id).parent().css('margin-bottom', table_obj.table_margin_bottom + 'px');

            //Container Width
            if(parseInt(table_obj.container_width, 10) > 0){
                $('#' + table_obj.id).parent().css('width', table_obj.container_width + 'px');
            }else{
                $('#' + table_obj.id).parent().css('width', 'auto');
            }

            //Container Height
            if(parseInt(table_obj.container_height, 10) > 0){
                $('#' + table_obj.id).parent().css('height', table_obj.container_height + 'px');
            }else{
                $('#' + table_obj.id).parent().css('height', 'auto');
            }

        }else{

            //If the container is not enabled apply the two margins to the table
            $('#' + table_obj.id).css('margin-top', table_obj.table_margin_top + 'px');
            $('#' + table_obj.id).css('margin-bottom', table_obj.table_margin_bottom + 'px');

        }

        //Header Cells Padding based on the Header Font Size
        vertical_padding = Math.round(0.636363 * table_obj.header_font_size) + 'px';
        horizontal_padding = Math.round(0.909090 * table_obj.header_font_size) + 'px';
        $('#' + table_obj.id + ' th').css('padding', vertical_padding + ' ' + horizontal_padding);

        //Image Height - Based on the Header Font size
        image_height = Math.round(1.545454 * table_obj.header_font_size) + 'px';
        $('#' + table_obj.id + ' th img.daextletal-image-left, #' + table_obj.id + ' th img.daextletal-image-right').css('height', image_height);

        //Image Left - Margin based on the Header Font Size
        margin = '0 ' + Math.round(0.454545 * table_obj.header_font_size) + 'px 0 0';
        $('#' + table_obj.id + ' th img.daextletal-image-left').css('margin', margin);

        //Image Right - Margin based on the Header Font Size
        margin = '0 0 0 ' + Math.round(0.454545 * table_obj.header_font_size) + 'px';
        $('#' + table_obj.id + ' th img.daextletal-image-right').css('margin', margin);

        /*
         * If the sorting is enabled Tablesorter adds a <div> as the child of the <th> element, for this reason some CSS
         * rules should be applied to different selectors.
         */
        if( parseInt(table_obj.enable_sorting, 10) == 1 ){

            //Line Height based on the Header Font Size
            let line_height = Math.round(1.454545 * table_obj.header_font_size) + 'px';
            $('#' + table_obj.id + ' th > div').css('line-height', line_height);

            //Header Font Size
            $('#' + table_obj.id + ' th > div').css('font-size', table_obj.header_font_size + 'px');

            //Header Font Family
            $('#' + table_obj.id + ' th > div').css('font-family', table_obj.header_font_family);

            //Header Font Weight
            $('#' + table_obj.id + ' th > div').css('font-weight', table_obj.header_font_weight);

            //Header Font Style
            $('#' + table_obj.id + ' th > div').css('font-style', table_obj.header_font_style);

            //Header Font Color
            $('#' + table_obj.id + ' th > div').css('color', table_obj.header_font_color);

            //Header Link Color
            $('#' + table_obj.id + ' th > div a').css('color', table_obj.header_link_color);

        }else{

            //Line Height based on the Header Font Size
            let line_height = Math.round(1.454545 * table_obj.header_font_size) + 'px';
            $('#' + table_obj.id + ' th').css('line-height', line_height);

            //Header Font Size
            $('#' + table_obj.id + ' th').css('font-size', table_obj.header_font_size + 'px');

            //Header Font Family
            $('#' + table_obj.id + ' th').css('font-family', table_obj.header_font_family);

            //Header Font Weight
            $('#' + table_obj.id + ' th').css('font-weight', table_obj.header_font_weight);

            //Header Font Style
            $('#' + table_obj.id + ' th').css('font-style', table_obj.header_font_style);

            //Header Font Color
            $('#' + table_obj.id + ' th').css('color', table_obj.header_font_color);

            //Header Link Color
            $('#' + table_obj.id + ' th a').css('color', table_obj.header_link_color);

        }

        //Header Background Color
        $('#' + table_obj.id + ' th').css('background-color', table_obj.header_background_color);

        //Header Border Color
        $('#' + table_obj.id + ' th').css('border-color', table_obj.header_border_color);

        //Header Position Alignment
        if(parseInt(table_obj.show_position, 10) == 1){
            if(table_obj.position_side == 'left'){
                $('#' + table_obj.id + ' th').first().css('text-align', table_obj.header_position_alignment);
            }else{
                $('#' + table_obj.id + ' th').last().css('text-align', table_obj.header_position_alignment);
            }
        }

        //Body Font Size
        $('#' + table_obj.id + ' td').css('font-size', table_obj.body_font_size + "px");

        //Body Cells Padding based on the Body Font Size
        vertical_padding = Math.round(0.272727 * table_obj.body_font_size) + 'px';
        horizontal_padding = Math.round(0.909090 * table_obj.body_font_size) + 'px';
        $('#' + table_obj.id + ' td').css('padding', vertical_padding + ' ' + horizontal_padding);

        //Line Height based on the Body Font Size
        let line_height = Math.round(1.545454 * table_obj.body_font_size) + 'px';
        $('#' + table_obj.id + ' td').css('line-height', line_height);

        //Image Height - Based on the Body Font size
        image_height = Math.round(1.545454 * table_obj.body_font_size) + 'px';
        $('#' + table_obj.id + ' td img.daextletal-image-left, #' + table_obj.id + ' td img.daextletal-image-right').css('height', image_height);

        //Image Left - Margin based on the Body Font Size
        margin = '0 ' + Math.round(0.454545 * table_obj.body_font_size) + 'px 0 0';
        $('#' + table_obj.id + ' td img.daextletal-image-left').css('margin', margin);

        //Image Right - Margin based on the Body Font Size
        margin = '0 0 0 ' + Math.round(0.454545 * table_obj.body_font_size) + 'px';
        $('#' + table_obj.id + ' td img.daextletal-image-right').css('margin', margin);

        //Body Font Family
        $('#' + table_obj.id + ' td').css('font-family', table_obj.body_font_family);

        //Body Font Weight
        $('#' + table_obj.id + ' td').css('font-weight', table_obj.body_font_weight);

        //Body Font Style
        $('#' + table_obj.id + ' td').css('font-style', table_obj.body_font_style);

        //Even Rows Background Color
        $('head').append('<style type="text/css">' + '\#' + table_obj.id + ' tr:nth-child(even) td{background-color: ' + table_obj.even_rows_background_color + ';}</style>');

        //Even Rows Font Color
        $('head').append('<style type="text/css">' + '\#' + table_obj.id + ' tr:nth-child(even) td{color: ' + table_obj.even_rows_font_color + ';}</style>');

        //Even Rows Link Color
        $('head').append('<style type="text/css">' + '\#' + table_obj.id + ' tr:nth-child(even) td a{color: ' + table_obj.even_rows_link_color + ';}</style>');

        //Odd Rows Background Color
        $('head').append('<style type="text/css">' + '\#' + table_obj.id + ' tr:nth-child(odd) td{background-color: ' + table_obj.odd_rows_background_color + ';}</style>');

        //Odd Rows Font Color
        $('head').append('<style type="text/css">' + '\#' + table_obj.id + ' tr:nth-child(odd) td{color: ' + table_obj.odd_rows_font_color + ';}</style>');

        //Odd Rows Link Color
        $('head').append('<style type="text/css">' + '\#' + table_obj.id + ' tr:nth-child(odd) td a{color: ' + table_obj.odd_rows_link_color + ';}</style>');

        //Rows Border Color
        $('#' + table_obj.id + ' td').css('border-color', table_obj.rows_border_color);

        //Autoalignment options ----------------------------------------------------------------------------------------
        if(table_obj.autoalignment_priority == 'columns'){
            //Columns has the priority
            apply_autoalignment_on_rows(table_obj);
            apply_autoalignment_on_columns(table_obj);
        }else{
            //Rows has the priority
            apply_autoalignment_on_columns(table_obj);
            apply_autoalignment_on_rows(table_obj);
        }

        //responsive options -------------------------------------------------------------------------------------------

        //Tablet

        //generate an array from the table_obj.hidden_columns_tablet string
        let hide_tablet_list_a = table_obj.hide_tablet_list.split(',');

        //init css rule
        let hidden_cells = '';

        //generate the selectors of the css rule by using the values included in the hidden_columns_tablet_a array
        $.each(hide_tablet_list_a, function( index, value ) {

            'use strict';

            hidden_cells += '#' + table_obj.id + ' tr th:nth-child(' + value + '),';
            hidden_cells += '#' + table_obj.id + ' tr td:nth-child(' + value + ')';
            if(hide_tablet_list_a.length > (index + 1)){hidden_cells += ',';}

        });

        //complete the css rule with the declaration block
        if(hidden_cells.length > 0){hidden_cells += '{display: none;}'}

        //set the font size for table cells in the header
        header_font_size_cells = '#' + table_obj.id + ' tr th, #' + table_obj.id + ' tr th div{font-size: ' + table_obj.tablet_header_font_size+ 'px !important}';

        //set the padding for the table cells in the header
        vertical_padding = Math.round(0.636363 * table_obj.tablet_header_font_size) + 'px';
        horizontal_padding = Math.round(0.909090 * table_obj.tablet_header_font_size) + 'px';
        header_padding_cells = '#' + table_obj.id + ' tr th{padding: ' + vertical_padding + ' ' + horizontal_padding + ' !important}';

        //Image Height - Based on the Header Font size
        image_height_value = Math.round(1.545454 * table_obj.tablet_header_font_size) + 'px';
        header_image_height = '#' + table_obj.id + ' th img.daextletal-image-left, #' + table_obj.id + ' th img.daextletal-image-right{height: ' + image_height_value + ' !important}';

        //Image Left - Margin based on the Header Font Size
        margin = '0 ' + Math.round(0.454545 * table_obj.tablet_header_font_size) + 'px 0 0';
        header_image_left_margin = '#' + table_obj.id + ' th img.daextletal-image-left{margin: ' + margin + ' !important;}';

        //Image Right - Margin based on the Header Font Size
        margin = '0 0 0 ' + Math.round(0.454545 * table_obj.tablet_header_font_size) + 'px';
        header_image_right_margin = '#' + table_obj.id + ' th img.daextletal-image-right{margin: ' + margin + ' !important;}';

        //Line Height based on the Header Font Size
        line_height_value = Math.round(1.454545 * table_obj.tablet_header_font_size) + 'px';
        if( parseInt(table_obj.enable_sorting, 10) == 1 ) {
            let header_line_height = '#' + table_obj.id + ' th > div{line-height: ' + line_height_value + ' !important;}';
        }else{
            let header_line_height = '#' + table_obj.id + ' th{line-height: ' + line_height_value + ' !important;}';
        }

        //set the font size for table cells in the body
        body_font_size_cells = '#' + table_obj.id + ' tr td{font-size: ' + table_obj.tablet_body_font_size+ 'px !important}';

        //set the padding for the table cells in the body
        vertical_padding = Math.round(0.272727 * table_obj.tablet_body_font_size) + 'px';
        horizontal_padding = Math.round(0.909090 * table_obj.tablet_body_font_size) + 'px';
        body_padding_cells = '#' + table_obj.id + ' tr td{padding: ' + vertical_padding + ' ' + horizontal_padding + ' !important}';

        //Line Height based on the Body Font Size
        line_height_value = Math.round(1.545454 * table_obj.tablet_body_font_size) + 'px';
        body_line_height = '#' + table_obj.id + ' td{line-height: ' + line_height_value + ' !important;}';

        //Image Height - Based on the Body Font size
        image_height_value = Math.round(1.545454 * table_obj.tablet_body_font_size) + 'px';
        let body_image_height = '#' + table_obj.id + ' td img.daextletal-image-left, #' + table_obj.id + ' td img.daextletal-image-right{height: ' + image_height_value + ' !important}';

        //Image Left - Margin based on the Body Font Size
        margin = '0 ' + Math.round(0.454545 * table_obj.tablet_body_font_size) + 'px 0 0';
        body_image_left_margin = '#' + table_obj.id + ' td img.daextletal-image-left{margin: ' + margin + ' !important;}';

        //Image Right - Margin based on the Body Font Size
        margin = '0 0 0 ' + Math.round(0.454545 * table_obj.tablet_body_font_size) + 'px';
        body_image_right_margin = '#' + table_obj.id + ' td img.daextletal-image-right{margin: ' + margin + ' !important;}';

        //append the media query with the css rule in the head
        $('head').append('<style type="text/css">@media all and (max-width: ' + table_obj.tablet_breakpoint + 'px){' + hidden_cells + header_font_size_cells + header_padding_cells + header_image_height + header_image_left_margin + header_image_right_margin + header_line_height + body_font_size_cells + body_padding_cells + body_line_height + body_image_height + body_image_left_margin + body_image_right_margin + '} </style>');

        //tablet hide images
        if(parseInt(table_obj.tablet_hide_images, 10) == 1){
            $('head').append('<style type="text/css">@media all and (max-width: ' + table_obj.tablet_breakpoint + 'px){\#' + table_obj.id + ' img{ display: none !important;}}</style>');
        }

        //Phone

        //generate an array from table_obj.hidden_columns_phone string
        let hide_phone_list_a = table_obj.hide_phone_list.split(',');

        //init css rule
        hidden_cells = '';

        //generate the selectors of the css rule by using the values included in the hidden_columns_phone_a array
        $.each(hide_phone_list_a, function( index, value ) {

            'use strict';

            hidden_cells += '#' + table_obj.id + ' tr th:nth-child(' + value + '),';
            hidden_cells += '#' + table_obj.id + ' tr td:nth-child(' + value + ')';
            if(hide_phone_list_a.length > (index + 1)){hidden_cells += ',';}

        });

        //complete the css rule with the declaration block
        if(hidden_cells.length > 0){hidden_cells += '{display: none;}'}

        //set the font size for table cells in the header
        header_font_size_cells = '#' + table_obj.id + ' tr th, #' + table_obj.id + ' tr th div{font-size: ' + table_obj.phone_header_font_size + 'px !important}';

        //set the padding for the table cells in the header
        vertical_padding = Math.round(0.636363 * table_obj.phone_header_font_size) + 'px';
        horizontal_padding = Math.round(0.909090 * table_obj.phone_header_font_size) + 'px';
        header_padding_cells = '#' + table_obj.id + ' tr th{padding: ' + vertical_padding + ' ' + horizontal_padding + ' !important}';

        //Image Height - Based on the Header Font size
        image_height_value = Math.round(1.545454 * table_obj.phone_header_font_size) + 'px';
        header_image_height = '#' + table_obj.id + ' th img.daextletal-image-left, #' + table_obj.id + ' th img.daextletal-image-right{height: ' + image_height_value + ' !important}';

        //Image Left - Margin based on the Header Font Size
        margin = '0 ' + Math.round(0.454545 * table_obj.phone_header_font_size) + 'px 0 0';
        header_image_left_margin = '#' + table_obj.id + ' th img.daextletal-image-left{margin: ' + margin + ' !important;}';

        //Image Right - Margin based on the Header Font Size
        margin = '0 0 0 ' + Math.round(0.454545 * table_obj.phone_header_font_size) + 'px';
        header_image_right_margin = '#' + table_obj.id + ' th img.daextletal-image-right{margin: ' + margin + ' !important;}';

        //Line Height based on the Header Font Size
        line_height_value = Math.round(1.454545 * table_obj.phone_header_font_size) + 'px';
        if( parseInt(table_obj.enable_sorting, 10) == 1 ) {
            header_line_height = '#' + table_obj.id + ' th > div{line-height: ' + line_height_value + ' !important;}';
        }else{
            header_line_height = '#' + table_obj.id + ' th{line-height: ' + line_height_value + ' !important;}';
        }

        //set the font size for table cells in the body
        body_font_size_cells = '#' + table_obj.id + ' tr td{font-size: ' + table_obj.phone_body_font_size + 'px !important}';

        //set the padding for the table cells in the body
        vertical_padding = Math.round(0.272727 * table_obj.phone_body_font_size) + 'px';
        horizontal_padding = Math.round(0.909090 * table_obj.phone_body_font_size) + 'px';
        body_padding_cells = '#' + table_obj.id + ' tr td{padding: ' + vertical_padding + ' ' + horizontal_padding + ' !important}';

        //Line Height based on the Body Font Size
        line_height_value = Math.round(1.545454 * table_obj.phone_body_font_size) + 'px';
        body_line_height = '#' + table_obj.id + ' td{line-height: ' + line_height_value + ' !important;}';

        //Image Height - Based on the Body Font size
        image_height_value = Math.round(1.545454 * table_obj.phone_body_font_size) + 'px';
        body_image_height = '#' + table_obj.id + ' td img.daextletal-image-left, #' + table_obj.id + ' td img.daextletal-image-right{height: ' + image_height_value + ' !important}';

        //Image Left - Margin based on the Body Font Size
        margin = '0 ' + Math.round(0.454545 * table_obj.phone_body_font_size) + 'px 0 0';
        body_image_left_margin = '#' + table_obj.id + ' td img.daextletal-image-left{margin: ' + margin + ' !important;}';

        //Image Right - Margin based on the Body Font Size
        margin = '0 0 0 ' + Math.round(0.454545 * table_obj.phone_body_font_size) + 'px';
        body_image_right_margin = '#' + table_obj.id + ' td img.daextletal-image-right{margin: ' + margin + ' !important;}';

        //append the media query with the css rule in the head
        $('head').append('<style type="text/css">@media all and (max-width: ' + table_obj.phone_breakpoint + 'px){' + hidden_cells + header_font_size_cells + header_padding_cells + header_image_height + header_image_left_margin + header_image_right_margin + header_line_height + body_font_size_cells + body_padding_cells + body_line_height + body_image_height + body_image_left_margin + body_image_right_margin + '} </style>');

        //phone hide images
        if(parseInt(table_obj.phone_hide_images, 10) == 1){
            $('head').append('<style type="text/css">@media all and (max-width: ' + table_obj.phone_breakpoint + 'px){\#' + table_obj.id + ' img{ display: none !important;}}</style>');
        }

        //Hide the table header if "Show Header" is set to "No"
        if( parseInt(table_obj.show_header, 10) === 0 ){

            $('#' + table_obj.id + ' thead').css('display', 'none');
            $('#' + table_obj.id + ' tbody tr:first-of-type td').css('border-top-width', '1px');

        }

        //Make the table visible
        $('#' + table_obj.id).css('visibility', 'visible');

    });

    /*
     * Applies the autoalignment of the rows for the "Left", "Center" and "Right" category.
     */
    function apply_autoalignment_on_rows(table_obj){

        'use strict';

        //Left
        if(comma_separated_numbers_regex.test(table_obj.autoalignment_affected_rows_left)) {
            let autoalignment_affected_rows_left = table_obj.autoalignment_affected_rows_left.split(',');
            $.each(autoalignment_affected_rows_left, function( index, value ) {
                $('#' + table_obj.id + ' tr:nth-child(' + parseInt(value, 10) + ') td').css('text-align', 'left');
            });
        }

        //Center
        if(comma_separated_numbers_regex.test(table_obj.autoalignment_affected_rows_center)) {
            let autoalignment_affected_rows_center = table_obj.autoalignment_affected_rows_center.split(',');
            $.each(autoalignment_affected_rows_center, function( index, value ) {
                $('#' + table_obj.id + ' tr:nth-child(' + parseInt(value, 10) + ') td').css('text-align', 'center');
            });
        }

        //Right
        if(comma_separated_numbers_regex.test(table_obj.autoalignment_affected_rows_right)) {
            let autoalignment_affected_rows_right = table_obj.autoalignment_affected_rows_right.split(',');
            $.each(autoalignment_affected_rows_right, function( index, value ) {
                $('#' + table_obj.id + ' tr:nth-child(' + parseInt(value, 10) + ') td').css('text-align', 'right');
            });
        }

    }

    /*
     * Applies the autoalignment of the columns for the "Left", "Center" and "Right" category.
     */
    function apply_autoalignment_on_columns(table_obj){

        'use strict';

        //Left
        if(comma_separated_numbers_regex.test(table_obj.autoalignment_affected_columns_left)) {
            let autoalignment_affected_columns_left = table_obj.autoalignment_affected_columns_left.split(',');
            $.each(autoalignment_affected_columns_left, function( index, value ) {
                $('#' + table_obj.id + ' tr td:nth-child('+ parseInt(value, 10) +')').css('text-align', 'left');
            });
        }

        //Center
        if(comma_separated_numbers_regex.test(table_obj.autoalignment_affected_columns_center)) {
            let autoalignment_affected_columns_center = table_obj.autoalignment_affected_columns_center.split(',');
            $.each(autoalignment_affected_columns_center, function( index, value ) {
                $('#' + table_obj.id + ' tr td:nth-child('+ parseInt(value, 10) +')').css('text-align', 'center');
            });
        }

        //Right
        if(comma_separated_numbers_regex.test(table_obj.autoalignment_affected_columns_right)) {
            let autoalignment_affected_columns_right = table_obj.autoalignment_affected_columns_right.split(',');
            $.each(autoalignment_affected_columns_right, function( index, value ) {
                $('#' + table_obj.id + ' tr td:nth-child('+ parseInt(value, 10) +')').css('text-align', 'right');
            });
        }

    }

});