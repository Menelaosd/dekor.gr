 //version: 1.0.1
//author: opencartmart.com
 ;(function() {
   function XshippingproEstimator() {
       var tpl = {
          header : '<div class="shipping-header">'+_xshippingpro['lang']['header']+'</div>',
          country : '<div class="shipping-field"><select id="xshippingpro-country" name="_xestimator[country_id]" class="form-control"></select></div>',
          zone : '<div class="shipping-field"><select id="xshippingpro-zone" name="_xestimator[zone_id]" class="form-control"><option value="">'+_xshippingpro['lang']['zone']+'</option></select></div>',
          postal : '<div class="shipping-field"><input type="text" name="_xestimator[postcode]" id="input-postcode" placeholder="'+_xshippingpro['lang']['postal']+'" class="form-control" /></div>',
          btn : '<button type="button" id="estimate-xshipping" class="btn btn-default btn-block"><i style="display:none" class="fa fa-spinner fa-spin loader-icon"></i>&nbsp;'+_xshippingpro['lang']['btn']+'</button>',
          quote: '<div class="xshippingpro-quote"><b>{title}</b> {cost}</div>'
       };

       this.pouplateCountry = function() {
          if (_xshippingpro['country']) {
              var _options ='<option value="">'+_xshippingpro['lang']['country']+'</option>';
              $.each(_xshippingpro['country'], function (i, item) {
                  var selected = _xshippingpro['meta']['country_id'] == item.country_id ? 'selected' : '';
                  _options += '<option '+selected+' value="'+item.country_id+'">'+item.name+'</option>';
              });
              $('#xshippingpro-country').html(_options);
          }
       };

       this.initEvent = function() {
           $('#xshippingpro-country').on('change', _click_on_country);
           $('#estimate-xshipping').on('click', _click_on_button);
       };

       this.getQuoteBox = function() {
          var quote_box = '<div id="xshippingpro-box" class="xshippingpro-box">';
          quote_box += tpl.header;
          quote_box += '<div class="shipping-fields">';
          quote_box += '<input type="hidden" value="'+_xshippingpro['meta']['product_id']+'" name="_xestimator[product_id]" />';
          if (_xshippingpro['meta']['country']) {
             quote_box += tpl.country;
          }
          if (_xshippingpro['meta']['zone']) {
             quote_box += tpl.zone;
          }
          if (_xshippingpro['meta']['postal']) {
             quote_box += tpl.postal;
          }
          quote_box += '</div>';
          quote_box += tpl.btn;
          quote_box += '</div>';
          return quote_box;
       };

       function _click_on_country() {
           $('#xshippingpro-box input[type="text"], #xshippingpro-box select').removeClass('xshippingpro-error');
           var country_id = this.value || _xshippingpro['meta']['country_id'];
           $.ajax({
              url: _xshippingpro['url']['country'] + '&country_id=' + country_id,
              dataType: 'json',
              success: function(json) {
                var _options = '<option value="">'+_xshippingpro['lang']['zone']+'</option>';
                if (json['zone']) {
                   $.each(json['zone'], function (i, item) {
                     _options += '<option value="'+item.zone_id+'">'+item.name+'</option>';
                   });
                }
                $('#xshippingpro-zone').html(_options);
              }
          });
       }

       function _click_on_button() {
          $('.xshippingpro-quotes').remove();
          $('#xshippingpro-box input[type="text"], #xshippingpro-box select').removeClass('xshippingpro-error');
          var is_valid = true;
          $('#xshippingpro-box input[type="text"], #xshippingpro-box select').each(function() {
              if (!$(this).val()) {
                 is_valid = false;
                 $(this).addClass('xshippingpro-error');
              }
          });
          if (!is_valid) {
             return;
          }
          var parent_inputs  = $('#xshippingpro-box').parent().find('input[name="quantity"], input[name^="option"], select[name^="option"]').serializeArray();
          var data = $('#xshippingpro-box :input').serializeArray().concat(parent_inputs);
          $.ajax({
              url: _xshippingpro['url']['estimate'],
              dataType: 'json',
              data: data,
              type:'POST',
              beforeSend: function() {
                $('#estimate-xshipping').attr('disabled', true).find('i').css('display','inline');
              },
              complete: function() {
                $('#estimate-xshipping').attr('disabled', false).find('i').css('display','none');
              },
              success: function(json) {
                 var _shippping_data = '<div class="xshippingpro-quotes">';
                 if (json && json.quote) {
                    $.each(json.quote, function (i, item) {
                       _shippping_data += tpl.quote.replace('{title}', item.title).replace('{cost}', item.text);
                    });
                 } else if (json && json.message) {
                    _shippping_data += '<div class="xshippingpro-'+json.class+'">'+json.message+'</div>';
                 }
                  else {
                    _shippping_data += '<div class="xshippingpro-no-quote">'+_xshippingpro['lang']['no_data']+'</div>';
                 }
                 _shippping_data += '</div>';
                 $('#xshippingpro-box').after(_shippping_data);
              }
          });
       }

       var quote_box = this.getQuoteBox();
       $(_xshippingpro['selectors']['estimator']).after(quote_box);
       this.pouplateCountry();
       this.initEvent();
       if (_xshippingpro['meta']['country_id']) {
          _click_on_country(); // load default zones
       }
    }
    /* End of Estimator */

    function XshippingproExtender() {
        var _error_template = '<div class="alert alert-danger xshippingpro-global-error"><i class="fa fa-exclamation-circle"></i>&nbsp;__MSG__<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
        var _request_cache = {};
        var _request_counter = 1;
        var _current_methods = [];
        var _grand_method_count = 0;
        this.chmod = new OCMCheckoutModule(_onAjaxReqComplete).getDetail();
        this.shipping_key = this.chmod.shipping_key;
        this.shippings_key = this.chmod.shippings_key;
        this.xshippingpro_delimiter = 'xshippingpro.xshippingpro';
        this.option_delimiter = /_\d+/;
        this.option_replace_regex = /xshippingpro\.xshippingpro\d+/;
        this.free_option_delimiter = '!!--';
        this.user_data = {};
        this.sub_options = (_xshippingpro && _xshippingpro['sub_options']) || false;
        this.free_options = [];
        this.desc = (_xshippingpro && _xshippingpro['desc']) || false;
        this.logo = (_xshippingpro && _xshippingpro['logo']) || false;
        this.force_update = false;
        this.scroll_top = false;
        var _self = this;
        this.isXshipping = function isXshipping(code) {
            return code.indexOf(this.xshippingpro_delimiter) !== -1;
        };
        this.getTabIdFromCode = function getTabIdFromCode(code) {
           return code.replace(this.xshippingpro_delimiter,'');
        };
        this.getTabIdFromOptionCode = function getTabIdFromOptionCode(code) {
           code = code.replace(this.option_delimiter,'');
           return this.getTabIdFromCode(code);
        };
        this.getCodeFromOptionCode = function getCodeFromOptionCode(code) {
           return code.replace(this.option_delimiter,'');
        };
        this.isOptionCode = function isOptionCode(code) {
           return code.indexOf('_') !== -1;
        };
        this.getOptionsByCode = function getOptionsByCode(code) {
            var _dd = '';
            var tab_id = this.getTabIdFromCode(code);
            var index = this.free_options.indexOf(tab_id);
            if (this.sub_options[tab_id]) {
                _dd += '<option value="">'+_xshippingpro['lang']['select']+'</option>';
                $.each(this.sub_options[tab_id], function (i, item) {
                    var selected = _self.user_data.option == item.code ? 'selected' : '';
                    var title = index !== -1 ? item.title.replace(/\(.*\d+\)/,'') : item.title;
                    _dd += '<option '+selected+' value="'+item.code+'">'+title+'</option>';
                });
            }
            if (_dd) {
               _dd = '<div class="xshippingpro-options"><select tab_id="'+tab_id+'" id="xshippingpro-options-'+tab_id+'" name="_xshippingpro['+tab_id+'][option]" class="form-control xshippingpro-option">' + _dd + '</select></div>';
            }
            return _dd;
       };
       this.isValidShippingCode = function isValidShippingCode(code) {
          /*Some extension i.e xtension checkout return html in place of code, so validate first*/
          if (!$.isPlainObject(code) && /<(tr|td|div|label|input|span).*\/>/.test(code)) {
             this.force_update = true; 
             return false;
          }
          return true;
       }
       this.validate = function validate() {
           $('.xshippingpro-option-error').remove();
           var is_valid = true,
               selected_node = $(this.chmod.shipping_input_selector + ':checked'),
               code = selected_node.val();
           if (this.isXshipping(code)) {
               var tab_id = this.getTabIdFromCode(code);
               if (this.sub_options[tab_id]) {
                  var option_node = $('select[name="_xshippingpro['+tab_id+'][option]"]');
                  /* have sub-option but no dropdwon someting wrong, so  reapply dropwodn*/
                  if (!option_node.length) {
                      this.setOptionByNode(selected_node);
                      option_node = $('select[name="_xshippingpro['+tab_id+'][option]"]');
                  }
                  if (!this.user_data.option) {
                     is_valid = false;
                     option_node.after('<div class="xshippingpro-option-error">'+_xshippingpro['lang']['error']+'</div>');
                  }
               }
           }
           return is_valid;
       };
       this.setDescByNode = function setDescByNode(node, tab_id) {
           var _desc,
               $this = $(node);
           if (!this.desc) return;
           if (this.desc[tab_id]) {
              _desc = '<div class="xshippingpro-desc">' + this.desc[tab_id] + '</div>';
              $this.closest(this.chmod.shipping_container_selector).after(_desc);
           }
       };
       this.setLogoByNode = function setLogoByNode(node, tab_id) {
           var _logo,
               $this = $(node),
               img_regex = /\.(png|jpg|bmp|jpeg|webp|gif)/i;
           if (!this.logo) return;
           if (this.logo[tab_id]) {
              _logo = img_regex.test(this.logo[tab_id]) ? '<img class="xshippingpro-logo" src="' + this.logo[tab_id] + '"/>' : '<i class="fa fas ' + this.logo[tab_id] + ' xshippingpro-icon"></i>';
              $this.after(_logo);
           }
       };
       this.setOptionByNode = function setOptionByNode(node) {
           var $this = $(node),
               $closest = $this.closest(this.chmod.shipping_container_selector),
               code = $this.val();
           $('.xshippingpro-options').remove();
           if (/xshippingpro/.test(code)) {
              var dd = this.getOptionsByCode(code);
              if (!dd) return;

              if ($closest.next().hasClass('xshippingpro-desc')) {
                 $closest.next().after(dd);
              } else {
                 $closest.after(dd);
              }
           }
       };

       this.parseAndGetData = function parseAndGetData(data, needle_key) {
          var data_keys = this.chmod.order_data_keys,
              _return,
              i,
              keys,
              key;
          if (!needle_key) {
             needle_key = this.shipping_key;
          }
          if (!data || !$.isPlainObject(data) || data[needle_key]) {
             _return = data;
          }
          else if (data_keys && $.isArray(data_keys)) {
             for (i = 0; i < data_keys.length; i++) {
                key = data_keys[i];
                if (key.indexOf('.') == -1) {
                   if (data[key] && data[key][needle_key]) {
                     _return = data[key];
                     break;
                   }
                } else {
                   keys = key.split('.');
                   if (keys.length == 3) {
                      if (data[keys[0]] && data[keys[0]][keys[1]] && data[keys[0]][keys[1]][keys[2]] && data[keys[0]][keys[1]][keys[2]][needle_key] ) {
                          _return = data[keys[0]][keys[1]][keys[2]];
                          break;
                      }
                   }
                   else {
                      if (data[keys[0]] && data[keys[0]][keys[1]] && data[keys[0]][keys[1]][needle_key] ) {
                          _return = data[keys[0]][keys[1]];
                          break;
                      }
                   }
                }
             }
          }
          /* Set Shipping error if found */
          if (_return && _return[needle_key]) {
             this.setShippingError(_return[needle_key]);
          }
          return _return;
       }

       this.isValidationReq = function isValidationReq(url) {
          var current_node = window.document.activeElement,
              node_type = current_node.nodeName.toLowerCase(),
              selected_node;

         if (!this.sub_options) {
            return false;
         }
         if (this.chmod.shipping_validate_route) {
            return this.chmod.shipping_validate_route.test(url);
         }
         if (node_type == 'button'
            || (node_type == 'input' && current_node.type && (current_node.type == 'submit' || current_node.type == 'button'))) {
            return true;
         }
          return false;
       };
       this.updateOptionPriceInfo = function updateOptionPriceInfo(option_text, option_code) {
            var free_token = option_text.indexOf(this.free_option_delimiter);
                tab_id = this.getTabIdFromOptionCode(option_code),
                index = this.free_options.indexOf(tab_id),
                is_changed = false;

            if (free_token !== -1 && index === -1) {
                is_changed = true;
                this.free_options.push(tab_id);
            } if (free_token === -1 && index !== -1) {
                is_changed = true;
                this.free_options.splice(index, 1);
            }
            return is_changed;
       };
       this.removeOptionMethods = function removeOptionMethods(order_data) {
          var _any_diff,
              _xshippping_methods = {},
              _xquote = {},
              _final_quote = {};
          if (order_data[this.shippings_key] && order_data[this.shippings_key]['xshippingpro']) {
             _xquote = order_data[this.shippings_key]['xshippingpro']['quote'];
             for (var key in  _xquote) {
                 var code = _xquote[key]['code'];
                 if (!this.isOptionCode(code)) {
                    _final_quote[key] = _xquote[key];
                    _any_diff = _current_methods.indexOf(code) === -1;
                 } else {
                    var text = _xquote[key].text;
                    if (this.updateOptionPriceInfo(text, code)) {
                       this.force_update = true;
                    }
                 }
             }
             order_data[this.shippings_key]['xshippingpro']['quote'] = _final_quote;
          }
          if (_any_diff) {
            this.force_update = true;
          }
          return _final_quote;
       };
       this.revertToParentCode = function revertToParentCode(order_data, set_user_data) {
            var option_code,
                is_object = $.isPlainObject(order_data[this.shipping_key]);

            /* value could be string or object dependon on checkout module */
            option_code = is_object ?  order_data[this.shipping_key]['code'] : order_data[this.shipping_key];
            if (this.isOptionCode(option_code)) {
               if (is_object) {
                  order_data[this.shipping_key]['code'] = this.getCodeFromOptionCode(order_data[this.shipping_key]['code']);
               } else {
                  order_data[this.shipping_key] = this.getCodeFromOptionCode(order_data[this.shipping_key]);
               }
               if (set_user_data) {
                  this.user_data.option = option_code;
               }
            }
       };

       this.setShippingError = function setShippingError(shipping_data) {
          $('.xshippingpro-global-error').remove();
          if (shipping_data 
              && shipping_data.xshippingpro
              && shipping_data.xshippingpro.error
              && _xshippingpro && 
              _xshippingpro.selectors.shipping_error) {
                var error_html = _error_template.replace('__MSG__', shipping_data.xshippingpro.error);
                $(_xshippingpro.selectors.shipping_error).prepend(error_html);
          }
       }

       this.initEvent = function initEvent() {
           if (this.sub_options) {
              $(document).on('change', '.xshippingpro-option', _onShippingOptionSelect);
              $(document).on('change', this.chmod.shipping_input_selector, _onShippingSelect);
           }
           $.ajaxPrefilter(_onAjaxReq);
           $(document).ajaxComplete(_onAjaxReqComplete);
           $(document).ready(_onDomLoad);
       };

       this.deferUpdateBychmod = function deferUpdateBychmod() {
          if (this.chmod.defer_update) {
             this.force_update = true;
             setTimeout(_onAjaxReqComplete, 50);
          }
       };

       /* DOM events */
       function _onShippingSelect(e, isTriggered) {
          /* Ignore manula trigger event */
          if (!isTriggered) {
             _self.user_data.option = '';
             _self.setOptionByNode(this);
          }
       }
       function _onShippingOptionSelect(e) {
          var tab_id = $(this).attr('tab_id');
          //_self.user_data.option = $(this).val();
          $('.xshippingpro-option-error').remove();
          $('input[value="'+_self.xshippingpro_delimiter + tab_id+'"]').trigger('click');
          $('input[value="'+_self.xshippingpro_delimiter + tab_id+'"]').trigger('change', [true]);
          _self.chmod.setShippingMethod();
       }
       function _onDomLoad() {
          var data,
              order_data,
              xshippingpro_methods;

          _onAjaxReqComplete();
          /* Set initial shipping code */
          data = _self.chmod.getOrderData();
          order_data = _self.parseAndGetData(data);
          if (order_data && order_data[_self.shipping_key]) {
              _self.revertToParentCode(order_data, true);
          }
          /* Set Shipping methods */
          order_data = _self.parseAndGetData(data, _self.shippings_key);
          if (order_data && order_data[_self.shippings_key]) {
             xshippingpro_methods = _self.removeOptionMethods(order_data);
          }
          _self.chmod.setOrderData(data, order_data, xshippingpro_methods);
       }

       /* Need this on ajaxComplete as it perform once dom is ready */
       function _onAjaxReqComplete(event, xhr, settings) {
          var shipping_nodes = $(_self.chmod.shipping_input_selector),
              xshipping_nodes = [],
              parent_code,
              parent_node,
              selected_node,
              tab_id,
              is_changed = _self.force_update,
              new_methods = [],
              i,
              j;

          if (shipping_nodes.length == 0) {
             $('.xshippingpro-options, .xshippingpro-desc').remove();
             return;
          } 

          /*Find a small context around to give a performance boost */
          if (!_self.scroll_top) {
             _self.scroll_top = shipping_nodes.closest(_self.chmod.shipping_container_selector).parent().closest('div');
          }

          if (_grand_method_count !== shipping_nodes.length) {
             is_changed = true;
             _grand_method_count = shipping_nodes.length;
          }

          shipping_nodes.map(function () {
             var $this = $(this),
                 code = $this.val();
             if (!_self.isXshipping(code)) {
                return false;
             }
             xshipping_nodes.push(this);
             /* If option code is found in the list, then must refresh*/
             if (_self.isOptionCode(code)) {
                is_changed = true;
             }
             new_methods.push(code);
          });
          new_methods.sort();

          if (!is_changed) {
              if (new_methods.length !== _current_methods.length) {
                 is_changed = true;
              } else {
                 for (i = 0; i < new_methods.length; i++) {
                    if (new_methods[i] !== _current_methods[i]) {
                       is_changed = true;
                       break;
                    }
                 }
              }
          }
          _current_methods = new_methods;

          if (is_changed) {
              $('.xshippingpro-options, .xshippingpro-desc, .xshippingpro-logo, .xshippingpro-icon').remove();
              $.each(xshipping_nodes, function(i, node) {
                  var code = $(node).val(),
                      text,
                      closest;
                  if (_self.isOptionCode(code)) {
                      closest = $(node).closest(_self.chmod.shipping_container_selector);
                      text = closest.text();
                      closest.remove();
                      _self.updateOptionPriceInfo(text, code);
                      /* Select Parent Shipping if sub-option was selected somehow e.g browser refresh */
                      if ($(node).prop("checked")) {
                         _self.user_data.option = code;
                         parent_code = _self.getCodeFromOptionCode(code);
                         parent_node = $('input[value="'+parent_code+'"]');
                         parent_node.prop('checked', true);
                         _self.setOptionByNode(parent_node);
                      }
                  } else {
                      tab_id = _self.getTabIdFromCode(code);
                      if (_self.sub_options && _self.sub_options[tab_id]) {
                         if ($(node).prop("checked")) {
                            _self.setOptionByNode(node);
                         }
                      }
                      _self.setDescByNode(node, tab_id);
                      _self.setLogoByNode(node, tab_id);
                  }
              });
              _self.deferUpdateBychmod();
              _self.force_update = false;
          } else {
              /* If no changes is found but xshippingpro is selcted then show option again to be sure */
              selected_node = $(_self.chmod.shipping_input_selector + ':checked');
              if (selected_node.length && _self.isXshipping(selected_node.val())) {
                 _self.setOptionByNode(selected_node);
              }
          }
       }

       function _getJSONData(data){
          var _array = data.serializeArray(),
              json = {};
          $.map(_array, function(item){
              json[item['name']] = item['value'];
          });
          return json;
       }
       /*  Convert array of name/value pair (i.e jquery serializeArra) to json
        Warning:- One level array flattening only */
       function _flatten(data) {
           var _return = {};
           $.each(data, function(index, item){
              if (/\[\]$/.test(item.name)) {
                 var name = item.name.replace(/\[\]$/, '');
                 if (!_return[name]) _return[name] = [];
                 _return[name].push(item.value)
              } else {
                 _return[item.name] = item.value;
              }
           }); 
           return _return;
       }

       function _onAjaxSuccess(data, status, jqXhr) {
          var order_data,
              xshippingpro_methods;
          if (_request_cache[jqXhr.xid]) {
               order_data = _self.parseAndGetData(data);
               if (order_data && order_data[_self.shipping_key] && _self.isValidShippingCode(order_data[_self.shipping_key])) {
                  _self.revertToParentCode(order_data, false);
               }
               order_data = _self.parseAndGetData(data, _self.shippings_key);
               if (order_data && order_data[_self.shippings_key]) {
                  xshippingpro_methods = _self.removeOptionMethods(order_data);
                  _self.chmod.setOrderData(data, order_data, xshippingpro_methods);
               }
              _request_cache[jqXhr.xid].call(null, data, status, jqXhr); // pass data now NOT order_data
              _request_cache[jqXhr.xid] = null;
          }
       }

       function _onAjaxReq(options, originalOptions, jqXhr) {
          var option_node;

          if (_self.sub_options) {
             option_node = $('.xshippingpro-option').first();
             if (option_node.length) {
                _self.user_data.option = option_node.val();
             }
          }

          if (options.data && _self.user_data.option) {
              options.data = options.data.replace(_self.option_replace_regex, _self.user_data.option);
          }
          /* Hint future extension: encode new data using jquery $.param(data) func and append to the option data  */

          if (_self.isValidationReq(options.url, option_node) && (options.dataType == 'json' || !options.dataType)) {
              if (!_self.validate()) {
                  jqXhr.abort();
                  _self.chmod.hideLoader();

                  $('html, body').animate({
                    scrollTop: _self.scroll_top.offset().top
                  }, 1000);
                 return false;
              }
          }
          if (options.dataType == 'json' || !options.dataType) {
             jqXhr.xid = _request_counter++;
             _request_cache[jqXhr.xid] = options.success;
             options.success = _onAjaxSuccess;
          }
       }
       if (_xshippingpro['is_checkout']) {
         this.initEvent();
       }
       this.validate = this.validate.bind(this);
    }

    new XshippingproEstimator();
    new XshippingproExtender();
  })();