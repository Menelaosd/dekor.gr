//version: 1.0.0
//author: opencartmart.com
;(function(window) {
    function OCMCheckoutModule(callback_fn) {
       var modules = {}; 
       var interface = {
           shipping_input_selector: 'input[name="shipping_method"]', 
           shipping_container_selector: 'div',
           shipping_key: 'shipping_method',
           shippings_key: 'shipping_methods',
           shipping_validate_route: /shipping_method\/save/,
           total_refresh_route: /payment_method\/save/i,
           xoffer_selector: '#collapse-checkout-confirm:prepend',
           order_data_keys: false,
           defer_update: false,
           setShippingMethod: function() {},
           setTotals: function() {},
           getOrderData: function() {},
           setOrderData: function() {},
           hideLoader: function() {},
           showLoader: function() {},
       };
       this.detectModule = function detectModule() {
            var name;
            if (window.qc && window.qc.d_quickcheckout_store) {
               name = 'qc_d'
            } 
            else if (window.qc && (window.qc.PaymentMethod || window.qc.ShippingMethod)) {
               name = 'qc_d_latency';
            }
            else if (window._QuickCheckout || window._QuickCheckoutData) {
               name = 'journal3';
            }
            else if ($('.journal-checkout').length) {
               name = 'journal2';
            }
            else if (window.validateShippingMethod || window.validatePaymentMethod) {
              name = 'qc_msg';
            }
            else if (window.xcart) {
              name = 'best_checkout';
            }
            else if ($('#onepagecheckout').length) {
               name = 'onepagecheckout';
            }
            else if ($('#input-order-status').length && $('#input-store').length) {
               name = 'oc_admin';
            }
            else {
              name = 'default_oc';
            }
            return name;
       }
       this.getDetail = function getDetail() {
          var name = this.detectModule(),
              _return = modules[name]();
          _return['name'] = name;
          return _return;
       }

       /* Checkout Module Definitions */
       /* qc by d  */
       modules['qc_d'] = function qc_d() {
          /* Overwrite hidespinner method of qc */
          var _hideSpinner = qc.hideSpinner;
          qc.hideSpinner = function() {
            _hideSpinner();
            callback_fn.call(null);
          }
          function getOrderData() {
             return qc.d_quickcheckout_store.getState();
          }
          function setOrderData(original_data, modified_data, xshippingpro_methods) {
            /* QC module has bug, they did not update state properly when shipping get change.
              So it may not need in future when they fix bug.
            */
             if (xshippingpro_methods && Object.keys(xshippingpro_methods).length) {
                qc.state = qc.state.setIn(['session', 'shipping_methods', 'xshippingpro', 'quote'], xshippingpro_methods);
             }
          }
          function setTotals() {
             /* Should not jquery trigger */
             $('.qc-product-quantity')[0].dispatchEvent(new Event('change'));
          }
          function hideLoader() {
             setTimeout(function() {
                qc.hideLoader();
             }, 10);
          }
          function showLoader() {
             qc.showLoader();
          }
          return $.extend({}, interface, {
             order_data_keys: ['session'],
             getOrderData: getOrderData,
             setOrderData: setOrderData,
             setTotals: setTotals,
             hideLoader: hideLoader,
             showLoader: showLoader,
             defer_update: true,
             xoffer_selector: 'div[data-name="cart"] .step:prepend',
             shipping_validate_route: /d_quickcheckout\/confirm/i,
             total_refresh_route: /payment_method|shipping_method|cart/i,
          });
       };

       /* qc by d  */
       modules['qc_d_latency'] = function qc_d_latency() { //  Not yet tested
          function setTotals() {
             qc.shippingMethod.update($("#shipping_method_form").serializeArray());
          }
          return $.extend({}, interface, {
             setTotals: setTotals,
             xoffer_selector: '#cart_view:prepend',
             total_refresh_route: /payment_method|shipping_method|cart/i,
          });
       }

       /* jounral3  */
       modules['journal3'] = function journal3() {
          function getOrderData() {
             return _QuickCheckout._data;
          }
          function setTotals() {
             _QuickCheckout.save();
          }
          function setShippingMethod() {
             _QuickCheckout.save();
          }
          return $.extend({}, interface, {
             shipping_key: 'shipping_code',
             order_data_keys: ['order_data', 'response', 'response.order_data'],
             getOrderData: getOrderData,
             setTotals: setTotals,
             xoffer_selector: '.cart-section:before',
             setShippingMethod: setShippingMethod,
             shipping_validate_route: /checkout\/save\&confirm=true/,
             total_refresh_route: /checkout\/save|cart_/i,
          });
       }

       /* jounral2  */
       modules['journal2'] = function journal2() { // TODO - Not verify yet
          function setTotals() {
             $(document).trigger('journal_checkout_reload_cart', true);
          }
          function setShippingMethod() {
            $(document).trigger('journal_checkout_shipping_changed', $(interface.shipping_input_selector+':checked').val());
          }
          function hideLoader() {
            triggerLoadingOff();
          }
          function showLoader() {
            triggerLoadingOn();
          }
          return $.extend({}, interface, {
             setTotals: setTotals,
             setShippingMethod: setShippingMethod,
             hideLoader: hideLoader,
             xoffer_selector: '.checkout-cart:before',
             showLoader: showLoader,
             total_refresh_route: /cart/i,
          });
       }
       
       /* QC by MSG */
       modules['qc_msg'] = function qc_msg() {
          function setTotals() {
             $.get("index.php?route=extension/quickcheckout/cart", function(html) { 
                $("#cart1 .quickcheckout-content").html(html);
             });
          }
          function hideLoader() {
             $('#button-payment-method').prop('disabled', false);
             $('#button-payment-method').button('reset');
             $('.fa-spinner').remove();
          }
          function showLoader() {
             $('#button-payment-method').prop('disabled', true);
             $('#button-payment-method').button('loading');
             $('#button-payment-method').after('<i class="fa fa-spinner fa-spin"></i>');
          }
          return $.extend({}, interface, {
             shipping_container_selector: 'tr',
             setTotals: setTotals,
             hideLoader: hideLoader,
             showLoader: showLoader,
             xoffer_selector: '#cart1:before',
             shipping_validate_route: /terms\/validate/i,
             total_refresh_route: /cart/i,
          });
       }
       /* Best Checkout */
       modules['best_checkout'] = function best_checkout() { /* TODO  verification pending */
          function setTotals() {
             $('input[name^="quantity"').first().trigger('change');
          }
          function hideLoader() {
             hideBar();
          }
          function showLoader() {
             showBar();
          }
          return $.extend({}, interface, {
             shipping_container_selector: 'tr',
             setTotals: setTotals,
             hideLoader: hideLoader,
             showLoader: showLoader,
             shipping_validate_route: /validate&showpayment/i,
             xoffer_selector: '#totals:before',
             total_refresh_route: /payment_method|shipping_method|cart/i,
          });
       }
       /* One page Checkout */
       modules['onepagecheckout'] = function onepagecheckout() { /* TODO  verification pending */
          function setTotals() {
             LoadCart();
          }
          function hideLoader() {
             $('#onepagecheckout #button-register').button('reset');
          }
          function showLoader() {
             $('#onepagecheckout #button-register').button('loading');
          }
          return $.extend({}, interface, {
             setTotals: setTotals,
             hideLoader: hideLoader,
             showLoader: showLoader,
             shipping_validate_route: /validate\/validateForm/i,
             xoffer_selector: '.content-shopping-cart:before',
             total_refresh_route: /cart/i,
          });
       }
       /* Default */
       modules['default_oc'] = function default_oc() {
          function setTotals() {
             $.get("index.php?route=checkout/confirm", function(html) { 
                 $("#collapse-checkout-confirm .panel-body").html(html);
             });
          }
          return $.extend({}, interface, {
             setTotals: setTotals,
          });
       }
        /* Default */
       modules['oc_admin'] = function oc_admin() {
          function setTotals() {
             $('#button-refresh').trigger('click');
          }
          return $.extend({}, interface, {
             setTotals: setTotals,
             xoffer_selector: '#tab-total:prepend',
             total_refresh_route: /cart\/products/i,
          });
       }
       /* Next module after this here --> */
    }
    window.OCMCheckoutModule = OCMCheckoutModule;
})(window);