/* BN Woo Manager AJAX Product Edit
 *
 * @version     1.1
 * @author      BN-KareM
 */
jQuery(document).ready( function( $ ) {

    // Export CSV
    function bn_woo_manager_export_csv( $table, filename ) {
        var $rows = $table.find( '.adata:visible' ),
            // Temporary delimiter characters unlikely to be typed by keyboard
            // This is to avoid accidentally splitting the actual contents
            tmpColDelim = String.fromCharCode(11), // vertical tab character
            tmpRowDelim = String.fromCharCode(0), // null character
            // actual delimiter characters for CSV format
            colDelim = '","',
            rowDelim = '"\r\n"',
            // Grab text from table into CSV formatted string
            csv = '"' + $rows.map( function( i, row ) {
                var $row = $( row ),
                    $cols = $row.find( 'td input, td select' );
                return $cols.map( function( j, col ) {
                    var $col = $( col ),
                        text = $col.val();
                    return text.replace( /"/g, '""' ); // escape double quotes

                }).get().join( tmpColDelim );
            }).get().join( tmpRowDelim )
                .split( tmpRowDelim ).join( rowDelim )
                .split( tmpColDelim ).join( colDelim ) + '"',
            // Data URI
            csvData = 'data:application/csv;charset=utf-8,' + encodeURIComponent(csv);
        $( this )
            .attr({
            'download': filename,
                'href': csvData,
                'target': '_blank'
        });
    }

    // Group Edit
    $( '#bn_group_edit_activate' ).on( 'click', function() {
        if ( $( this ).html() == bn_woo_manager.activate ) {
            $( '#bn_group_edit' ).addClass( 'bn_group_edit_is_active' );
            $( '#bn_group_edit_activate' ).html( bn_woo_manager.deactivate );
            $( '#bn_group_edit_toggle' ).show();
            $( '#bn_group_edit_action' ).show();
            $( '#bn_group_edit_active_fields' ).prop( 'disabled', false);
            $( '#bn_group_edit_operation' ).prop( 'disabled', false);
            $( '#bn_group_edit_value' ).prop( 'readonly', false);
            $( '#bn_group_edit_unit' ).prop( 'disabled', false);
            if ( $( '#bn_group_edit_active_fields' ).val() == 'regular_price' )
                $( 'form#bn_form .adata td input.regular_price:visible:not([readonly])' ).addClass( 'bn_group_edit_active_field' );
                else if ( $( '#bn_group_edit_active_fields' ).val() == 'sale_price' )
                    $( 'form#bn_form .adata td input.sale_price:visible:not([readonly])' ).addClass( 'bn_group_edit_active_field' );
                    else
                    $( 'form#bn_form .adata td input.product_stock:visible:not([readonly])' ).addClass( 'bn_group_edit_active_field' );
            $( '#bn_group_edit_toggle' ).css( 'background', '#9eeded' );
        } else {
            $( '#bn_group_edit_active_fields' ).prop( 'disabled', true);
            $( '#bn_group_edit_operation' ).prop( 'disabled', true);
            $( '#bn_group_edit_value' ).prop( 'readonly', true);
            $( '#bn_group_edit_unit' ).prop( 'disabled', true);
            $( '#bn_group_edit' ).removeClass( 'bn_group_edit_is_active' );
            $( '#bn_group_edit_activate' ).html( bn_woo_manager.activate );
            $( '#bn_group_edit_toggle' ).hide();
            $( '#bn_group_edit_action' ).hide();
            $( 'form#bn_form .adata td input' ).removeClass( 'bn_group_edit_active_field' );
            $( '#bn_group_edit_toggle' ).css( 'background', '' );
        }
    });

    // Group Edit Toggle Fields
    $( '#bn_group_edit_toggle' ).on( 'click', function() {
        if ( $( 'form#bn_form .adata td input.bn_group_edit_active_field' ).length ) {
            $( 'form#bn_form .adata td input' ).removeClass( 'bn_group_edit_active_field' );
            $( '#bn_group_edit_toggle' ).css( 'background', '' );
            }
            else {
            if ( $( '#bn_group_edit_active_fields' ).val() == 'regular_price' )
                $( 'form#bn_form .adata td input.regular_price:visible:not([readonly])' ).addClass( 'bn_group_edit_active_field' );
                else if ( $( '#bn_group_edit_active_fields' ).val() == 'sale_price' )
                    $( 'form#bn_form .adata td input.sale_price:visible:not([readonly])' ).addClass( 'bn_group_edit_active_field' );
                    else
                    $( 'form#bn_form .adata td input.product_stock:visible:not([readonly])' ).addClass( 'bn_group_edit_active_field' );
                $( '#bn_group_edit_toggle' ).css( 'background', '#9eeded' );
            }
    });

    // Group Edit Action
    $( '#bn_group_edit_action' ).on( 'click', function( event ) {
        if ( $( '.bn_group_edit_active_field:visible' ).length == 0 ) return;
        var group_value = parseFloat ( $( '#bn_group_edit_value' ).val().replace( ',', '.' ).replace( /[^0-9\.]/, '' ) );
        if ( isNaN( group_value ) ) group_value = '';
        var attention_group_edit = $( '.bn_group_edit_active_field:visible' ).length + ' ' + bn_woo_manager.attention_group_edit2;
        var operation = $( '#bn_group_edit_operation' ).val();
        var unit = $( '#bn_group_edit_unit' ).val();
        var new_value = field_value = field_value_raw  = '';
        if ( operation == '*' && unit == '%' ) {
            alert( bn_woo_manager.notpossible );
            return;
        }
        if ( !confirm( attention_group_edit ) ) {
            alert( bn_woo_manager.canceled_operation );
            return;
        }
        $( $( 'form#bn_form input.bn_group_edit_active_field:visible' ).get().reverse() ).each( function() {
            field_value_raw = $( this ).val().replace( ',', '.' ).replace( /[^0-9\.]/, '' );
            if ( !$( this ).hasClass( 'product_stock' ) )
                field_value_raw = field_value_raw.slice( 0, -3 ).replace( '.', '' ) + field_value_raw.slice( -3 );
            field_value = parseFloat ( field_value_raw );
            if ( isNaN( field_value ) ) field_value = 0;
            if ( operation == '*' ) {
                if ( group_value != '' ) new_value = group_value.toFixed( 2 ); else new_value = '';
            } else {
                if ( operation == '+' ) {
                    if ( unit == '%' )
                        new_value = ( field_value + ( field_value * ( group_value / 100 ) ) ).toFixed( 2 );
                        else
                        new_value = ( field_value + group_value ).toFixed( 2 );
                } else {
                    if ( unit == '%' )
                        new_value = ( field_value - ( field_value * ( group_value / 100 ) ) ).toFixed( 2 );
                        else
                        new_value = ( field_value - group_value ).toFixed( 2 );
                }
            }
            if ( $( this ).hasClass( 'product_stock' ) )
                new_value = Math.round( parseFloat( new_value ) ).toFixed();
            if ( new_value < 0 ) new_value = 0;
            $( this ).attr( 'value', new_value ).trigger( 'change' );
        });
    });

    // Group Edit Change Active Fields
    $( '#bn_group_edit_active_fields' ).on( 'change', function() {
         $( 'form#bn_form .adata td input' ).removeClass( 'bn_group_edit_active_field' );
         if ( $( '#bn_group_edit_active_fields' ).val() == 'regular_price' )
            $( 'form#bn_form .adata td input.regular_price:visible:not([readonly])' ).addClass( 'bn_group_edit_active_field' );
            else if ( $( '#bn_group_edit_active_fields' ).val() == 'sale_price' )
                $( 'form#bn_form .adata td input.sale_price:visible:not([readonly])' ).addClass( 'bn_group_edit_active_field' );
                else
                $( 'form#bn_form .adata td input.product_stock:visible:not([readonly])' ).addClass( 'bn_group_edit_active_field' );
    });

    // Group Edit Validate Edit field
    $( '#bn_group_edit_value' ).keypress( function( event ) {
        if( event.which == 8 || event.keyCode == 37 || event.keyCode == 39 || event.keyCode == 46 )
            return true;
        else if( ( ( event.which != 44 && event.which != 46 ) || ( $( this ).val().indexOf( ',' ) != -1 || $( this ).val().indexOf( '.' ) != -1 ) ) && ( event.which < 48 || event.which > 57 ) )
            event.preventDefault();
    });

    // CSV export on click
    $( '.bn_export' ).on( 'click', function( event ) {
        bn_woo_manager_export_csv.apply(this, [$( 'form#bn_form' ), 'bn_export.csv']);
    });

    // Delete log on click
    $( '.bn_delete_log' ).on( 'click', function( event ) {
        if ( !confirm( bn_woo_manager.attention_delete_log ) ) {
            alert( bn_woo_manager.canceled_operation );
            return;
        }
        var data2 = {
            'action':           'bn_action_delete_log',
            'nonce':            bn_woo_manager.NONCE,
            'bn_delete_log':    true
        };
        $.ajax({
            beforeSend: function() {
                $( '#bn_refresh_x' ).hide();
                $( '#bn_update_x' ).css( 'display', 'inline-block' );
                $( '.bn_ajax_loader_h #bn_circularG_h' ).show();
            },
            url:    bn_woo_manager.ajax_url,
            type:   'POST',
            data:   data2,
            success: function( response ) {
                    // check response
                    if ( response !== false ) {
                        $( '.bn_ajax_loader_h #bn_circularG_h' ).hide();
                        $( '#bn_update_x' ).hide();
                        $( '#bn_refresh_x' ).css( 'display', 'inline-block' );
                    }
                },
            error: function() {
            }
        }); // ajax
    });

    // Delete transients Cache
    $( '.bn_delete_cache' ).on( 'click', function( event ) {
        if ( !confirm( bn_woo_manager.attention_delete_cache ) ) {
            alert( bn_woo_manager.canceled_operation );
            return;
        }
        var data_cache = {
            'action':           'bn_action_delete_cache',
            'nonce':            bn_woo_manager.NONCE,
            'bn_delete_cache':  true
        };
        $.ajax({
            beforeSend: function() {
                $( '#bn_refresh_x' ).hide();
                $( '#bn_update_x' ).css( 'display', 'inline-block' );
                $( '.bn_ajax_loader_h #bn_circularG_h' ).show();
            },
            url:    bn_woo_manager.ajax_url,
            type:   'POST',
            data:   data_cache,
            success: function( response ) {
                    // check response
                    if ( response !== false ) {
                        $( '.bn_ajax_loader_h #bn_circularG_h' ).hide();
                        $( '#bn_update_x' ).hide();
                        $( '#bn_refresh_x' ).css( 'display', 'inline-block' );
                    }
                },
            error: function() {
            }
        }); // ajax
    });

    // Filter Product List
    $( '#bn_filter, #bn_filter_and, #bn_filter_or, #bn_filter_not' ).on( 'input', function() {
        var dInput = $( '#bn_filter' ).val().toLowerCase();
        var dInput_not = $( '#bn_filter_not' ).val().toLowerCase();
        if ( dInput.length > 1 || dInput_not.length > 1 ) {
            if ( $( '#bn_expand' ).html() == '+' ) {
                $( 'form#bn_form .adata.bn_variable' ).removeClass( 'bn_variation' );
                $( '.bn_parent' ).addClass( 'bn_parent_exp' );
                $( '.bn_parent_2' ).addClass( 'bn_parent_2_exp' );
                $( 'form#bn_form .adata .bn_parent' ).html( '-' );
                $( '#bn_expand' ).html( '-' ).addClass( 'bn_expand_exp' );
            }
            var filter = filter_and = filter_not = true;
            var filter_or = false;
            var filter_active = ( dInput.length > 1 );
            var filter_not_active = ( dInput_not.length > 1 );
            if ( !filter_active ) {
                $( '#bn_filter_or' ).val( '' );
                $( '#bn_filter_or' ).removeClass( 'bn_filter_active' ).prop( 'readonly', true);
                filter_or_active = false;
                $( '#bn_filter_and' ).val( '' );
                $( '#bn_filter_and' ).removeClass( 'bn_filter_active' ).prop( 'readonly', true);
                filter_and_active = false;
            } else {
                var dInput_or = $( '#bn_filter_or' ).val().toLowerCase();
                var dInput_and = $( '#bn_filter_and' ).val().toLowerCase();
                $( '#bn_filter_or' ).prop( 'readonly', false);
                $( '#bn_filter_and' ).prop( 'readonly', false);
                var filter_or_active = ( dInput_or.length > 1 );
                var filter_and_active = ( dInput_and.length > 1 );
                if ( filter_or_active ) $( '#bn_filter_or' ).addClass( 'bn_filter_active' ); else $( '#bn_filter_or' ).removeClass( 'bn_filter_active' );
                if ( filter_and_active ) $( '#bn_filter_and' ).addClass( 'bn_filter_active' ); else  $( '#bn_filter_and' ).removeClass( 'bn_filter_active' );
            }
            var sus = '';
            $( 'form#bn_form .adata' ).each( function() {
                sus = $( this ).find( '.bn_search_string' ).text();
                if ( filter_active ) filter = ( sus.match( new RegExp( dInput ) ) );
                if ( filter_or_active ) filter_or = ( sus.match( new RegExp( dInput_or ) ) );
                if ( filter_and_active ) filter_and = ( sus.match( new RegExp( dInput_and ) ) );
                if ( filter_not_active ) filter_not = ( !sus.match( new RegExp( dInput_not ) ) );
                if ( ( ( filter || filter_or ) && filter_and ) && filter_not )
                    $( this ).removeClass( 'bn_filter' );
                    else {
                    $( this ).addClass( 'bn_filter' );
                    $( this ).find( 'td input' ).removeClass( 'bn_group_edit_active_field' );
                }
            });
        } else {
            if ( !filter ) {
                $( '#bn_filter_or' ).val( '' );
                $( '#bn_filter_or' ).removeClass( 'bn_filter_active' ).prop( 'readonly', true);
                $( '#bn_filter_and' ).val( '' );
                $( '#bn_filter_and' ).removeClass( 'bn_filter_active' ).prop( 'readonly', true);
            }
            if ( dInput.length == 1 || dInput_not.length == 1 ) {
                $( 'form#bn_form .adata' ).removeClass( 'bn_filter' );
            }
            if ( dInput.length < 1 && dInput_not.length < 1 ) {
                $( 'form#bn_form .adata' ).removeClass( 'bn_filter' );
            }
        }
        if ( dInput.length > 1 ) $( 'input#bn_filter' ).addClass( 'bn_filter_active' ); else $( '#bn_filter' ).removeClass(  'bn_filter_active' );
        if ( dInput_not.length > 1 ) $('#bn_filter_not' ).addClass( 'bn_filter_active' ); else $( '#bn_filter_not' ).removeClass( 'bn_filter_active' );
    }).triggerHandler( 'input' );

    // BN filter reset
    $( '#bn_filter_reset' ).on( 'click', function() {
        $( '#bn_filter' ).val( '' );
        $( '#bn_filter_not' ).val( '' );
        $( '#bn_filter_not' ).css( 'background', '' );
        $( '#bn_filter' ).trigger( 'input' );
    });

    // Hide parent not filter
    $( '#bn_hide_parent' ).on( 'click', function() {
        $( '#bn_filter_not' ).attr( 'value', '#P' ).trigger( 'input' );
    });

    // Cat show all
    $( '#bn_cat_all' ).on( 'click', function() {
        if (  $( '#bn_cat' ).val() != '0' )
            $( '#bn_cat' ).attr( 'value', '0' ).trigger( 'change' );
    });

    // BN Special off
    $( '#bn_special_off' ).on( 'click', function() {
        if (  $( '#bn_special' ).val() != 'off' )
            $( '#bn_special' ).attr( 'value', 'off' ).trigger( 'change' );
    });

    // BN Refresh
    $( '#bn_refresh_all' ).on( 'click', function() {
        $( '#bn_cat' ).trigger( 'change' );
    });

    // Query search field on keypress
    $( '#bn_query_search_string' ).on( 'keyup change', function( event ) {
        if ( $( this ).val().length == 0 || event.type == 'change' || event.which == 13 )
            $( '#bn_cat' ).trigger( 'change' );
    });

    // BN query key & compare on change
        $( '#bn_query_key, #bn_query_compare' ).on( 'change', function() {
            $( '#bn_query_search_string' ).attr( 'value', $.trim( $( '#bn_query_search_string' ).val() ) );
            if ( $( '#bn_query_search_string' ).val() != '' )
               $( '#bn_cat' ).trigger( 'change' );
               else
               $( '#bn_query_search_string' ).css( 'background', '' );
        });

    // BN query clear / reset
        $( '#bn_query_reset' ).on( 'click', function() {
            if ( $( '#bn_query_search_string' ).val() != '' ) {
                $( '#bn_query_search_string' ).attr( 'value', '' );
                $( '#bn_cat' ).trigger( 'change' );
            }
        });

    // Product Cat, Sort, Special on change
    $( '#bn_cat, #bn_sort_by, #bn_special' ).on( 'change', function() {
        // Activate / deactivate Status Selections
        if ( $( '#bn_special' ).val() != 'off' )
            $( '#bn_special' ).css( 'background', 'lightblue' );
            else
            $( '#bn_special' ).css( 'background', '' );
        if ( $( '#bn_cat' ).val() != '0' )
            $( '#bn_cat' ).css( 'background', 'lightblue' );
            else
            $( '#bn_cat' ).css( 'background', '' );
        if ( $( '#bn_query_search_string' ).val() != '' )
            $( '#bn_query_search_string' ).css( 'background', 'lightblue' );
            else
            $( '#bn_query_search_string' ).css( 'background', '' );
        bn_woo_manager_AJAX_show( 1, $( '.bn_products_per_page' ).val() );
    }).triggerHandler( 'change' );

    function bn_woo_manager_AJAX_show( bn_page, bn_count ) {
        bn_page = bn_page || 1;
        bn_count = bn_count || '*';
        var prod_cat                = $( '#bn_cat' ).val();
        var bn_sort                 = $( '#bn_sort_by' ).val();
        var bn_special              = $( '#bn_special' ).val();
        var bn_query_key            = $( '#bn_query_key' ).val();
        var bn_query_search_string  = $.trim( $( '#bn_query_search_string' ).val() );
        var bn_query_compare        = $( '#bn_query_compare' ).val();
        var prod_expand             = $( '#bn_expand' ).text();
        if ( prod_expand != '+' && prod_expand != '-' ) prod_expand = '+';
        var data1 = {
            'action':                   'bn_action_show',
            'nonce':                    bn_woo_manager.NONCE,
            'bn_count':                 bn_count,
            'bn_page':                  bn_page,
            'bn_cat':                   prod_cat,
            'bn_sort':                  bn_sort,
            'bn_special':               bn_special,
            'bn_query_key':             bn_query_key,
            'bn_query_search_string':   bn_query_search_string,
            'bn_query_compare':         bn_query_compare,
            'bn_expand':                prod_expand
        };
        // AJAX Show
        $.ajax({
            beforeSend: function() {
                // Show animation
                $( '#bn_refresh_x' ).hide();
                $( '#bn_update_x' ).css( 'display', 'inline-block' );
                $( '.bn_ajax_loader_h #bn_circularG_h' ).show();
                $('#bn_product_list').css( { opacity: 0.5 } );
            },
            url: bn_woo_manager.ajax_url,
            type: 'POST',
            data: data1,
            success: function( response ) {
                // Check response
                if ( response !== false ) {
                    // Hide animation
                    $( '.bn_ajax_loader_h #bn_circularG_h' ).hide();
                    $( '#bn_update_x' ).hide();
                    $( '#bn_refresh_x' ).css( 'display', 'inline-block' );
                    // Show new product list
                    $( '#bn_product_list' ).html( response ).css( { opacity: 1 } );

                    // Trigger BN Filter
                    $( '#bn_filter' ).trigger( 'input' );
                    $( '#bn_group_edit_activate' ).html( bn_woo_manager.deactivate ).trigger( 'click' );
                    // Expand Status
                    if ( prod_expand == '+' ) {
                        $( 'form#bn_form .adata.bn_variable' ).addClass( 'bn_variation' );
                        $( '.bn_parent' ).removeClass( 'bn_parent_exp' );
                        $( '.bn_parent_2' ).removeClass( 'bn_parent_2_exp' );
                        $( '.bn_parent' ).html( '+' );
                    } else {
                        $( 'form#bn_form .adata.bn_variable' ).removeClass( 'bn_variation' );
                        $( '.bn_parent' ).addClass( 'bn_parent_exp' );
                        $( '.bn_parent_2' ).addClass( 'bn_parent_2_exp' );
                        $( '.bn_parent' ).html( '-' );
                    }
                    if ( $( '#bn_special' ).val() != 'off' || bn_query_search_string != '' ) {
                         if ( $('#bn_expand' ).html() == '+' ) {
                            $( 'form#bn_form .adata.bn_variable' ).removeClass( 'bn_variation' );
                            $( '.bn_parent' ).addClass( 'bn_parent_exp' );
                            $( '.bn_parent_2' ).addClass( 'bn_parent_2_exp' );
                            $( 'form#bn_form .adata .bn_parent' ).html( '-' );
                            $( '#bn_expand' ).html( '-' ).addClass( 'bn_expand_exp' );
                        }
                    }
                    // Pagination
                    if ( $( '#bn_special' ).val() == 'off' && bn_query_search_string == '' ) {
                        $( '#bn_page_navigation' ).show();
                        var current_page = parseInt( $( '#bn_datastring' ).text().split( '|' )[0] );
                        var number_of_pages = parseInt( $( '#bn_datastring' ).text().split( '|' )[1] );
                        var products_per_page = parseInt( $( '#bn_datastring' ).text().split( '|' )[2] );
                        var prod_per_page_option = [ -1, 1, 5, 10, 20, 50, 100, 200, 500 ];
                        var bn_navigation_html = '<select class="bn_products_per_page" name="products-per-page">';
                        var selected = '';
                        $.each( prod_per_page_option, function( key, value ) {
                            if ( products_per_page == value ) selected = 'selected'; else selected = '';
                            if ( value != -1 ) value2 = value + ' ' + bn_woo_manager.per_page; else value2 = bn_woo_manager.show_all;
                            bn_navigation_html += '<option value="' + value + '" ' + selected + '>' + value2 + '</option>';
                        });
                        bn_navigation_html += '</select>';
                        if ( number_of_pages > 1 ) {
                            bn_navigation_html += '<span class="bn_previous_link">' + bn_woo_manager.bn_prev + '</span>';
                            var current_link = 1;
                            while( number_of_pages >= current_link ) {
                                bn_navigation_html += '<span class="bn_page_link" data-page="' + current_link + '">' + current_link +'</span>';
                                current_link++;
                            }
                            bn_navigation_html += '<span class="bn_next_link">' + bn_woo_manager.bn_next + '</span>';
                        }
                        $( '#bn_page_navigation' ).html( bn_navigation_html );
                        $( '.bn_page_link[data-page=' + current_page +']' ).addClass( 'bn_active_page' );
                    } else {
                        $( '#bn_page_navigation' ).hide();
                    }
                    // AJAX Edit
                    bn_woo_manager_AJAX_edit();
                }
            },
            error: function() {
            }
        }); // ajax show
    } // fuction ajax show

    function bn_woo_manager_AJAX_edit() {

        // Pagination prev
        $( '.bn_previous_link' ).on( 'click', function() {
            var page_num = parseInt( $( '#bn_datastring' ).text().split( '|' )[0] ) - 1;
            if( $( '.bn_active_page' ).prev( '.bn_page_link' ).length==true ) {
                // AJAX show page
                bn_woo_manager_AJAX_show( page_num, $( '.bn_products_per_page' ).val() );
            }
        });
        // Pagination next
        $('.bn_next_link').on('click', function() {
            var page_num = parseInt( $( '#bn_datastring' ).text().split( '|' )[0] ) + 1;
            if( $( '.bn_active_page' ).next( '.bn_page_link' ).length == true ) {
                // AJAX show page
                bn_woo_manager_AJAX_show( page_num, $( '.bn_products_per_page' ).val() );
            }
        });
        // Pagination goto page
        $( '.bn_page_link' ).on( 'click', function() {
            var page_num = parseInt( $( this ).attr( 'data-page' ) ) ;
            // AJAX show page
            bn_woo_manager_AJAX_show( page_num, $( '.bn_products_per_page' ).val() );
        });

        // Pagination products per page
        $( '.bn_products_per_page' ).on( 'change', function() {
            // AJAX show page
            bn_woo_manager_AJAX_show( 1, $( this ).val() );
            $( '#bn_filter_reset' ).trigger( 'click' );
        });

        // NUMBERS ONLY CLASS
        $( '.numbersOnly' ).keyup( function() {
            this.value = this.value.replace(/[^0-9\.]/g,'');
        });

        // UNDO
        $( '.bn_options_undo' ).on( 'click', function() {
            var product_id = $( this ).parents( '.adata' ).attr( 'id' );
            var selrow = 'form#bn_form .adata[id="' + product_id + '"]';
            $( 'form#bn_form .adata' ).blur();
            setTimeout(function() {
                $( $( selrow + ' .bn_updated:visible' ).get().reverse() ).each( function() {
                    var field_name = $( this ).children( 'input, select' ).attr( 'class' ).split( ' ' )[0];
                    bn_woo_manager_undo( selrow, field_name );
                });
            }, 250);
        });

        // UNDO All
        $( '#bn_undo_all' ).on( 'click', function() {
            if ( !$( 'form#bn_form .adata .bn_updated:visible' ).length ) return;
            if ( !confirm( bn_woo_manager.attentiom_all_undo ) ) {
                alert( bn_woo_manager.canceled_operation );
                return;
            }
            $( $( 'form#bn_form .adata .bn_updated:visible' ).get().reverse() ).each( function() {
                var product_id = $( this ).parents( '.adata' ).attr( 'id' );
                var selrow = 'form#bn_form .adata[id="' + product_id + '"]';
                var field_name = $( this ).children( 'input, select' ).attr( 'class' ).split( ' ' )[0];
                bn_woo_manager_undo( selrow, field_name );
            });
        });

        // Group Edit Select / Deselect fields
        $( 'form#bn_form .adata td input.regular_price, form#bn_form .adata td input.sale_price, form#bn_form .adata td input.product_stock' ).on( 'click', function( ev ) {
            if ( $( '#bn_group_edit_activate' ).html() == bn_woo_manager.deactivate && $( ev.target ).attr( 'class' ).split( ' ' )[0] == $( '#bn_group_edit_active_fields' ).val() ) {
                $( this ).toggleClass( 'bn_group_edit_active_field', '' );
                if ( $( 'form#bn_form .adata td input.bn_group_edit_active_field' ).length )
                    $( '#bn_group_edit_toggle' ).css( 'background', '#9eeded' );
                    else
                    $( '#bn_group_edit_toggle' ).css( 'background', '' );
            }
        });

        // Variation Parent expand on click
        $( '.bn_parent_2' ).on( 'click', function() {
            $( this ).prev( '.bn_parent' ).trigger( 'click' );
        });
        $( '.bn_parent' ).on( 'click', function() {
            if ( $( this ).text() == '+' ) {
                $( '.adata[data-bn_parent_id="V' + $( this ).parent().attr( 'id' ) + '"]' ).hide().removeClass( 'bn_variation' ).fadeIn( 'slow' );
                $( this ).parent().find( '.bn_parent' ).addClass( 'bn_parent_exp' );
                $( this ).parent().find('.bn_parent_2' ).addClass( 'bn_parent_2_exp' );
                $( this ).html( '-' );
                if ( $( '.adata .bn_parent:visible:not(.bn_parent_exp)' ).length == 0 ) $( '#bn_expand' ).text( '-' );
            } else {
                $( '.adata[data-bn_parent_id="V' + $( this ).parent().attr( 'id' ) + '"]' ).addClass( 'bn_variation' );
                $( this ).parent().find( '.bn_parent' ).removeClass( 'bn_parent_exp' );
                $( this ).parent().find( '.bn_parent_2' ).removeClass( 'bn_parent_2_exp' );
                $( this ).parent().removeClass( 'bn_variation' );
                $( '.adata[data-bn_parent_id="V' + $( this ).parent().attr( 'id' ) + '"]' ).find( 'td input' ).removeClass( 'bn_group_edit_active_field' );
                $( this ).html( '+' );
                if ( $( '.adata .bn_parent_exp:visible' ).length == 0 ) $( '#bn_expand' ).text( '+' );
            }
        });

        // Variation Parent hover
        $( '.bn_parent, .bn_parent_2' ).on( 'mouseenter', function() {
                $( this ).parent().find( '.bn_parent' ).addClass( 'bn_parent_exp' );
                $( this ).parent().find('.bn_parent_2' ).addClass( 'bn_parent_2_exp' );
        });
        $( '.bn_parent, .bn_parent_2' ).on( 'mouseleave', function() {
            if ( $( this ).parent().find( '.bn_parent' ).text() == '+' ) {
                $( this ).parent().find( '.bn_parent' ).removeClass( 'bn_parent_exp' );
                $( this ).parent().find( '.bn_parent_2' ).removeClass( 'bn_parent_2_exp' );
            }
        });

        // Variation Expand All on click
        $( '#bn_expand' ).on( 'click', function() {
            if ( $(this).text() == '+' ) {
                $( 'form#bn_form .adata.bn_variable' ).removeClass( 'bn_variation' );
                $( '.bn_parent' ).addClass( 'bn_parent_exp' );
                $( '.bn_parent_2' ).addClass( 'bn_parent_2_exp' );
                $( '.bn_parent' ).html('-');
                $( this ).html( '-' ).addClass( 'bn_expand_exp' );
            } else {
                $( 'form#bn_form .adata.bn_variable' ).addClass( 'bn_variation' );
                $( '.bn_parent' ).removeClass( 'bn_parent_exp' );
                $( '.bn_parent_2' ).removeClass( 'bn_parent_2_exp' );
                $( '.bn_parent' ).html( '+' );
                $( 'form#bn_form .adata td input').removeClass( 'bn_group_edit_active_field' );
                $( this ).html( '+' ).removeClass( 'bn_expand_exp' );
            }
        });

        // Enter, key up, key down next field
        $( 'form#bn_form' ).on( 'keypress', 'input', function( event ) {
            if ( event.which == 13 || event.keyCode == 40 ) {
                event.preventDefault();
                var $this = $( event.target );
                $( 'form#bn_form .adata td input' ).attr( 'data-index', '' );
                $this.attr( 'data-index', '8001' );
                $this.parents( '.adata' ).nextAll( '.adata:visible' ).find( '.' + $this.attr( 'class' ).split( ' ' )[0] + ':not([readonly])' ).first().attr( 'data-index', '8002' );
                $this.blur();
                var index = parseFloat( $this.attr( 'data-index' ) );
                $( '[data-index="' + (index + 1).toString() + '"]' ).select();
            }
            if ( event.keyCode == 38 ) {
                event.preventDefault();
                var $this = $( event.target );
                $( 'form#bn_form .adata td input' ).attr( 'data-index', '' );
                $this.parents( '.adata' ).prevAll( '.adata:visible' ).find('.' + $this.attr( 'class' ).split( ' ' )[0] + ':not([readonly])' ).last().attr( 'data-index', '8000' );
                $this.attr( 'data-index', '8001' );
                $this.blur();
                var index = parseFloat( $this.attr( 'data-index' ) );
                $( '[data-index="' + (index - 1).toString() + '"]' ).select();
            }
        });

        // Input field on focus select
        $( 'form#bn_form' ).on( 'focus', 'input', function() {
            $( this ).select();
        });

        // Row on focusin
        $( 'form#bn_form .adata' ).on( 'focusin', function() {
            $( this ).css( 'background', $( this ).find( '.bn_pp_id' ).css( 'backgroundColor' ) );
            if ( $( this ).hasClass( 'bn_back_parent' ) && !$( this ).find( '.bn_parent' ).hasClass( 'bn_parent_exp' ) ) {
                $( '.adata[data-bn_parent_id="V' + $( this ).attr( 'id' ) + '"]' ).hide().removeClass( 'bn_variation' ).fadeIn( 'slow' );
                $( this ).find( '.bn_parent' ).addClass( 'bn_parent_exp' );
                $( this ).find('.bn_parent_2' ).addClass( 'bn_parent_2_exp' );
                $( this ).find( '.bn_parent' ).html( '-' );
                if ( $( '.adata .bn_parent:visible:not(.bn_parent_exp)' ).length == 0 ) $( '#bn_expand' ).text( '-' );
            }
        });

        // Row on focusout
        $( 'form#bn_form .adata' ).on( 'focusout', function() {
            $( this ).css( 'background', '' );
        });

        // On Change / Edit
        $( 'form#bn_form .adata' ).on( 'change', function( ev ) {
            var product_id = $( this ).attr( 'id' );
            var selrow = 'form#bn_form .adata[id="' + product_id + '"] .';
            var field_name = $( ev.target ).attr( 'class' ).split( ' ' )[0];
            var field_val = $( selrow + field_name ).val();
            var product_type = $( selrow + 'bn_pp_id' ).html().slice( 0, 2 );
            var old_val = $(selrow + field_name).attr( 'data-bn_old' );
            if ( field_name == 'post_status' ) {
                if ( field_val != 'publish' )
                    field_val = "*";
                else {
                    if ( $( selrow + field_name + '.bn_password' ).length ) var inactive_status = 'pending'; else var inactive_status = 'private';
                    field_val = ( old_val != 'publish' ) ? old_val : inactive_status;
                }
            }
            var pparentid = $( 'form#bn_form .adata[id="' + product_id + '"]' ).attr( 'data-bn_parent_id' ).slice( 1 );
            var data = {
                'action':           'bn_action_update',
                'nonce':            bn_woo_manager.NONCE,
                'bn_product_id':    product_id,
                'bn_field_name':    field_name,
                'bn_field':         field_val,
                'bn_product_type':  product_type
            };
            // AJAX update
            $.ajax({
                beforeSend: function() {
                    // show animation
                    $( selrow + 'bn_options_undo' ).hide();
                    $( selrow + 'bn_ajax_loader #bn_circularG' ).show();
                },
                url: bn_woo_manager.ajax_url,
                type:   'POST',
                data:   data,
                datatype: 'json',
                success: function( response ) {
                    // hide animation
                    $( selrow + 'bn_ajax_loader #bn_circularG' ).hide();
                    // check response
                    if ( response !== false ) {
                        // refresh data
                        bn_woo_manager_refresh_data( response.resp0, response.resp1, selrow, field_name, old_val, pparentid, product_type, product_id );
                    } else {
                        // restore old data
                        if ( field_name != 'product_shipping_class' && field_name != 'bn_woo_manager_unit' ) {
                            $( selrow + field_name ).attr( 'value', old_val );
                        } else {
                            $( selrow + field_name + ' option' ).removeAttr( 'selected' );
                            $( selrow + field_name + ' option[value="' + old_val + '"]' ).attr( 'selected', true );
                        }
                        switch ( field_name ) {
                            case 'manage_stock':
                                if ( old_val == 'no' ) $( selrow + field_name ).prop( 'checked', false );
                                    else $( selrow + field_name ).prop( 'checked', true );
                                break;
                            case 'post_status':
                                if ( old_val != 'publish' ) $( selrow + field_name ).prop( 'checked', false );
                                    else $( selrow + field_name ).prop( 'checked', true );
                                break;
                            default:
                        }
                    }
                },
                error: function() {
                }
            }); // ajax update
        }); // on Change edit
    } // functon ajax edit

    // UNDO function
    function bn_woo_manager_undo( selrow, field_name ) {
        var old_val = $( selrow + ' .' + field_name ).attr( 'data-bn_old' );
        switch ( field_name ) {
            case 'manage_stock':
                if ( old_val == 'yes' ) {
                    old_val = 'no';
                    $( selrow + ' .' + field_name ).prop( 'checked', true );
                } else {
                    old_val = 'yes';
                    $( selrow + ' .' + field_name ).prop( 'checked', false );
                }
                break;
            case 'post_status':
                if ( old_val == 'publish' ) {
                    old_val = '';
                    $( selrow + ' .' + field_name ).prop( 'checked', true );
                } else {
                    old_val = 'publish';
                    $( selrow + ' .' + field_name ).prop( 'checked', false );
                }
                break;
            default:
        }
        if ( field_name != 'product_shipping_class' && field_name != 'bn_woo_manager_unit' ) {
            $( selrow + ' .' + field_name ).attr( 'value', old_val ).trigger( 'change' );
        } else {
            $( selrow + ' .' + field_name + ' option' ).removeAttr( 'selected' );
            $( selrow + ' .' + field_name + ' option[value="' + old_val + '"]' ).attr( 'selected', true ).parent( 'select' ).trigger( 'change' );
        }
        if ( field_name == 'manage_stock' && old_val == 'no' ) {
            setTimeout(function() {
                $( selrow + ' .product_stock' ).attr( 'value', $( selrow + ' .product_stock' ).attr( 'data-bn_old' ) ).trigger( 'change' );
            }, 5000 );
        }
    }

    // refresh data
    function bn_woo_manager_refresh_data( resp0, resp1, selrow, field_name, old_val, pparentid, product_type, product_id ) {
        // refresh field
        if ( field_name != 'product_shipping_class' && field_name != 'bn_woo_manager_unit' ) {
            $( selrow + field_name ).attr( 'value', resp0 );
        } else {
            $( selrow + field_name + ' option' ).removeAttr( 'selected' );
            $( selrow + field_name + ' option[value="' + resp0 + '"]' ).attr( 'selected', true );
        }
        // mark updated/undo
        if ( $( selrow + field_name ).val() != old_val ) {
            $( selrow + field_name ).parent().addClass( 'bn_updated' );
            $( selrow + field_name ).parents( '.adata' ).find( '.bn_options_undo' ).show();
            $( '#bn_undo_all' ).css( 'background', '#046' );
        } else {
            $( selrow + field_name ).parent().removeClass( 'bn_updated' );
            if ( !$( selrow + field_name ).parents( '.adata' ).find( '.bn_updated' ).length ) {
                $( selrow + field_name ).parents( '.adata' ).find( '.bn_options_undo' ).hide();
                if ( !$( 'form#bn_form .adata .bn_updated' ).length )
                    $( '#bn_undo_all' ).css( 'background', '' );
            } else {
                $( selrow + field_name ).parents( '.adata' ).find( '.bn_options_undo' ).show();
            }
        }
        // additional operations
        var selek = 'form#bn_form .adata[data-bn_parent_id = "V' + pparentid + '"] .';
        var selek_parent = $( 'form#bn_form .adata[id="' + pparentid + '"] .product_stock' );
        // change Variations (inherited from parent)
        if ( product_type == '#P' ) {
            $( '.adata[data-bn_parent_id="P' + pparentid + '"] .bn_parent' ).text( '+' ).trigger( 'click' );
            if ( field_name != 'product_shipping_class' ) {
                $( selek + field_name ).each( function() {
                    if ( $( this ).val() == '' )
                        $( this ).trigger( 'change' ).parent().addClass( 'bn_updated_inherited' );
                    if ( !$( '.adata[data-bn_parent_id="P' + pparentid + '"] .bn_updated' ).length )
                        $( this ).parent().removeClass( 'bn_updated_inherited' );
                });
            } else {
                $( '.adata[data-bn_parent_id="V' + pparentid + '"]' ).each( function() {
                    var v_selrow_each = $( this );
                    var undo_visible = v_selrow_each.find( '.bn_options_undo:visible' ).length;
                    var ship_product_id = v_selrow_each.attr( 'id' );
                    var ship_old = v_selrow_each.find( '.product_shipping_class' ).val();
                    var data_ship = {
                        'action':               'bn_action_get_shipping_class',
                        'nonce':                bn_woo_manager.NONCE,
                        'bn_ship_product_id':   ship_product_id,
                        'bn_old_ship_class':    ship_old
                    };
                    // AJAX get Shipping Class
                    $.ajax({
                        url: bn_woo_manager.ajax_url,
                        type:   'POST',
                        data:   data_ship,
                        beforeSend: function() {
                            // show animation
                            v_selrow_each.find( '.bn_options_undo' ).hide();
                            v_selrow_each.find( '.bn_ajax_loader #bn_circularG' ).show();
                        },
                        success: function( shipping_class ) {
                            v_selrow_each.find( '.bn_ajax_loader #bn_circularG' ).hide();
                            if ( undo_visible > 0 ) v_selrow_each.find( '.bn_options_undo' ).show();
                            // check response
                            if ( shipping_class !== false ) {
                                $( '.adata[id="' + ship_product_id + '"] .' + field_name + ' option' ).removeAttr( 'selected' );
                                $( '.adata[id="' + ship_product_id + '"] .' + field_name + ' option[value="' + shipping_class + '"]' ).attr( 'selected', true );
                                // mark updated
                                if ( shipping_class != ship_old )
                                    $( '.adata[id="' + ship_product_id + '"] .' + field_name ).parent().addClass( 'bn_updated_inherited' );
                                if ( !$( '.adata[data-bn_parent_id="P' + pparentid + '"] .bn_updated' ).length )
                                    $( '.adata[id="' + ship_product_id + '"] .' + field_name ).parent().removeClass( 'bn_updated_inherited' );
                            }
                        },
                        error: function() {
                        }
                    });
                });
            }
        } // end change Variations (inherited from parent)
        switch ( field_name ) {
            case 'regular_price':
            case 'sale_price':
            case 'bn_woo_manager_total_units':
            case 'bn_woo_manager_base_units':
            case 'bn_woo_manager_unit':
                $( selrow + 'price_x' ).html( resp1 );
                if ( $( selrow + 'bn_woo_manager_total_units' ).val() != '' && $( selrow + 'bn_woo_manager_base_units' ).val() != '' && $( selrow + 'bn_woo_manager_unit' ).val() != '_none' ) {
                    bn_woo_manager_set_price_html( selrow, product_id, product_type );
                }
                if ( product_type == '#V' && ( field_name == 'regular_price' || field_name == 'sale_price' ) )
                    bn_woo_manager_set_price_html( 'form#bn_form .adata[id="' + pparentid + '"] .', pparentid, '#P' );
                break;
            case 'post_status':
                if ( resp0 == 'publish' ) $( selrow + field_name ).parent().removeClass( 'bn_prod_active' );
                    else $( selrow + field_name ).parent().addClass( 'bn_prod_active' );
                break;
            case 'manage_stock':
                if ( resp0 == 'yes' ) {
                    $( selrow + 'product_stock' ).attr( 'value', '0' ).prop( 'readonly', false ).next( 'div' ).attr( 'class', 'bn_outofstock' );
                    if ( product_type == '#P' ) {
                        $( selek + 'manage_stock:checkbox:not(:checked)' ).parents( '.adata' ).find( '.product_stock' ).next( 'div' ).attr('class', 'bn_outofstock' );
                    }
                     if ( product_type == '#P' || product_type == '#V' ) {
                        if ( $( selek + 'bn_instock' ).length == 0 )
                            selek_parent.next( 'div' ).attr( 'class', 'bn_outofstock' );
                            else
                            selek_parent.next( 'div' ).attr( 'class', 'bn_instock' );
                    }
                } else {
                    $( selrow + 'product_stock' ).attr( 'value', '' ).prop( 'readonly', true );
                    if ( $( selrow + 'product_stock' ).attr( 'data-bn_old') != '' )
                        $( selrow + 'product_stock' ).parent().addClass( 'bn_updated' );
                    if ( product_type == '#V' && $( 'form#bn_form .adata[id="' + pparentid + '"] .manage_stock' ).val() == 'yes' ) {
                        if ( selek_parent.val() > 0 ) {
                            $( selrow + 'product_stock' ).next( 'div' ).attr( 'class', 'bn_instock' );
                            selek_parent.next( 'div' ).attr( 'class', 'bn_instock' );
                        } else
                            $( selrow + 'product_stock' ).next( 'div' ).attr( 'class', 'bn_outofstock' );
                            if ( $( selek + 'bn_instock' ).length == 0 )
                                selek_parent.next( 'div' ).attr( 'class', 'bn_outofstock' );
                    }
                }
                if ( product_type == '#V' && $( selrow + 'manage_stock' ).val() == 'no' && $( 'form#bn_form .adata[id="' + pparentid + '"] .manage_stock' ).val() == 'yes' )
                        $( selrow + 'product_stock' ).attr( 'placeholder', parseInt( selek_parent.val() ) );
                if ( product_type == '#P' ) {
                    $( selek + 'manage_stock' ).each( function() {
                        var placeh = parseInt( selek_parent.val() );
                        placeh = ( isNaN( placeh ) ) ? '' : placeh;
                        if ( $( this ).val() == 'no' ) $( this ).parent().next( 'td' ).children( '.product_stock' ).attr( 'placeholder', placeh );
                    });
                }
                break;
            case 'bn_woo_manager_package_qty':
            case 'sku':
                $( selrow + field_name ).attr( 'placeholder', resp1 );
                break;
            case 'product_stock':
                $( selrow + field_name ).attr( 'placeholder', resp1 );
                if ( $( selrow + field_name ).val() < 1 && resp1 < 1 ) var st_status = 'bn_outofstock'; else var st_status = 'bn_instock';
                $( selrow + field_name ).next( 'div' ).attr( 'class', st_status );
                if ( product_type == '#P' ) {
                    $( selek + 'manage_stock:checkbox:not(:checked)' ).parents( '.adata' ).find( '.product_stock' ).next( 'div' ).attr('class', st_status );
                }
                if ( product_type == '#P' || product_type == '#V' ) {
                    if ( $( selek + 'bn_instock' ).length == 0 )
                        selek_parent.next( 'div' ).attr( 'class', 'bn_outofstock' );
                        else
                        selek_parent.next( 'div' ).attr( 'class', 'bn_instock' );
                }
                break;
            default:
        }
    } // refresh data
    // set price html
    function bn_woo_manager_set_price_html( selrow, product_id, product_type ) {
        var undo_visible = $( selrow + 'bn_options_undo:visible' ).length;
        var data_price = {
            'action':                   'bn_action_get_price_html',
            'nonce':                    bn_woo_manager.NONCE,
            'bn_price_product_id':      product_id,
            'bn_price_product_type':    product_type
        };
        // AJAX get Price HTML
        $.ajax({
            url: bn_woo_manager.ajax_url,
            type:   'POST',
            data:   data_price,
            beforeSend: function() {
                // show animation
                $( selrow + 'bn_options_undo' ).hide();
                $( selrow + 'bn_ajax_loader #bn_circularG' ).show();
            },
            success: function( price_html ) {
                $( selrow + 'bn_ajax_loader #bn_circularG' ).hide();
                if ( undo_visible > 0 ) $( selrow + 'bn_options_undo' ).show();
                // check response
                if ( price_html !== false ) {
                    $( selrow + 'price_x' ).html( price_html );
                }
            },
            error: function() {
            }
        });
    }
}); // document.readey
