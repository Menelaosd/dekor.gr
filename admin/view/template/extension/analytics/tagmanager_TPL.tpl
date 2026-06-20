<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
<style>
.chkbox {
padding-right:20px;
padding-top:7px;
}
.chklabel {
padding-left:10px;
}
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}
.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}
.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}
.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}
input:checked + .slider {
  background-color: #2196F3;
}

input:focus + .slider {
  box-shadow: 0 0 1px #2196F3;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-tagmanager" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-tagmanager" class="form-horizontal">
	  <ul class="nav nav-tabs">
            <li class="active"><a href="#tab-tab1" data-toggle="tab"><?php echo $tab_tab1; ?></a></li>
            <li><a href="#tab-tab2" data-toggle="tab"><?php echo $tab_tab2; ?></a></li>
            <li><a href="#tab-tab3" data-toggle="tab"><?php echo $tab_tab3; ?></a></li>
            <li><a href="#tab-tab4" data-toggle="tab"><?php echo $tab_tab4; ?></a></li>
            <li><a href="#tab-tab5" data-toggle="tab"><?php echo $tab_tab5; ?></a></li>
            <li><a href="#tab-tab6" data-toggle="tab"><?php echo $tab_tab6; ?></a></li>
	    <li><a href="#tab-tab7" data-toggle="tab"><?php echo $tab_tab7; ?></a></li>
          </ul>
          <div class="tab-content">
<!---- Tab General -->
            <div class="tab-pane active" id="tab-tab1">
		<div class="col-sm-8">
			<div class="form-group">
			    <label class="col-sm-4 control-label" for="input-status"><?php echo $entry_status; ?></label>
			    <div class="col-sm-8">
				<label class="switch"><input type="checkbox" name="<?php echo $PREFIX;?>tagmanager_status" <?php if ($tagmanager_status) { echo 'checked'; }?>><span class="slider round"></span></label>
			    </div>
			</div>
			<div class="form-group required">
			    <label class="col-sm-4 control-label" for="input-code"><?php echo $entry_code; ?></label>
			    <div class="col-sm-8">
			      <input type="text" name="<?php echo $PREFIX;?>tagmanager_code" placeholder="<?php echo $entry_code; ?>" class="form-control" value="<?php echo $tagmanager_code;?>"/>
			      <?php if ($error_code) { ?>
			      <div class="text-danger"><?php echo $error_code; ?></div>
			      <?php } ?>
			    </div>
			  </div>
			   <div class="form-group required">
			    <label class="col-sm-4 control-label" for="input-gid"><span data-toggle="tooltip" title="<?php echo $help_gid;?>"><?php echo $entry_gid; ?></span></label>
			    <div class="col-sm-8">
			      <input type="text" name="<?php echo $PREFIX;?>tagmanager_gid" placeholder="<?php echo $entry_gid; ?>" class="form-control" value="<?php echo $tagmanager_gid;?>"/>
			      <?php if ($error_analytics) { ?>
			      <div class="text-danger"><?php echo $error_analytics; ?></div>
			      <?php } ?>
			    </div>
			  </div>
			  <div class="form-group">
			    <label class="col-sm-4 control-label" for="input-admin"><span data-toggle="tooltip" title="<?php echo $help_admin;?>"><?php echo $entry_admin; ?></span></label>
			    <div class="col-sm-8">
				<label class="switch"><input type="checkbox" name="<?php echo $PREFIX;?>tagmanager_admin" <?php if ($tagmanager_admin) { echo 'checked'; }?>><span class="slider round"></span></label>
			    </div>
			 </div>
			  <div class="form-group">
			    <label class="col-sm-4 control-label" for="input-userid"><span data-toggle="tooltip" title="<?php echo $help_userid;?>"><?php echo $entry_userid_status; ?></span></label>
			    <div class="col-sm-8">
				<label class="switch"><input type="checkbox" name="<?php echo $PREFIX;?>tagmanager_userid_status" <?php if ($tagmanager_userid_status) { echo 'checked'; }?>><span class="slider round"></span></label>
			    </div>
			  </div>  
			  <div class="form-group">
			    <label class="col-sm-4 control-label" for="input-cache"><span data-toggle="tooltip" title="<?php echo $help_cache;?>"><?php echo $entry_cache; ?></span></label>
			    <div class="col-sm-8">
				<label class="switch"><input type="checkbox" name="<?php echo $PREFIX;?>tagmanager_cache" <?php if ($tagmanager_cache) { echo 'checked'; }?>><span class="slider round"></span></label>
			    </div>
			  </div>
			  <div class="form-group">
			    <label class="col-sm-4 control-label" for="input-ptitle"><span data-toggle="tooltip" title="<?php echo $help_ptitle;?>"><?php echo $entry_ptitle; ?></span></label>
			    <div class="col-sm-8">
			      <select name="<?php echo $PREFIX;?>tagmanager_ptitle" id="input-ptitle" class="form-control">
				    <?php 
				      foreach ($product_title as $ptitle) { ?>
				    <?php if ($ptitle == $tagmanager_ptitle) { ?>
				    <option value="<?php echo $ptitle; ?>" selected="selected"><?php echo $ptitle; ?></option>
				    <?php } else { ?>
				  <option value="<?php echo $ptitle; ?>"><?php echo $ptitle; ?></option>
				    <?php } ?>
				    <?php } ?>
			      </select>
			    </div>
			  </div> 

			<div class="form-group">
				<label class="col-sm-4 control-label" for="input-product"><span data-toggle="tooltip" title="<?php echo $help_product;?>"><?php echo $entry_product; ?></span></label>
				<div class="col-sm-8">
					<select name="<?php echo $PREFIX;?>tagmanager_product" id="input-adword" class="form-control">
					   <?php 
					      foreach ($product_map as $pmap) { ?>
					    <?php if ($pmap == $tagmanager_product) { ?>
					    <option value="<?php echo $pmap; ?>" selected="selected"><?php echo $pmap; ?></option>
					    <?php } else { ?>
					  <option value="<?php echo $pmap; ?>"><?php echo $pmap; ?></option>
					    <?php } ?>
					    <?php } ?>
					</select>
				</div>
			</div> 
			<div class="form-group">
				<label class="col-sm-4 control-label" for="input-route-checkout"><span data-toggle="tooltip" title="<?php echo $help_route;?>"><?php echo $entry_route_checkout;?></span></label>
				<div class="col-sm-5">
				      <textarea rows="4" cols="50" name="<?php echo $PREFIX;?>tagmanager_route_checkout"><?php if (!empty($tagmanager_route_checkout)) { echo $tagmanager_route_checkout; }?></textarea>
				</div>
				<div class="col-sm-3">
				 <?php echo $help_route_checkout;?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label" for="input-route-success"><span data-toggle="tooltip" title="<?php echo $help_route;?>"><?php echo $entry_route_success;?></span></label>
				<div class="col-sm-5">
				      <textarea rows="4" cols="50" name="<?php echo $PREFIX;?>tagmanager_route_success"><?php if (!empty($tagmanager_route_success)) { echo $tagmanager_route_success; }?></textarea>
				</div>
				<div class="col-sm-3">
				 <?php echo $help_route_success;?>
				</div>
			</div>
		</div>
		<div class="col-sm-4">
			<h3>Tag Manager Version <?php echo $text_version;?></h3>
				<?php echo $text_about;?>
				<h3><?php echo $heading_container;?></h3>
				<?php echo $text_container;?>
		</div>
	    </div>
<!---- Tab Marketing -->
	    <div class="tab-pane" id="tab-tab2">
		<div class="col-sm-8">
			<div class="form-group">
			    <label class="col-sm-4 control-label" for="input-adword"><?php echo $entry_adword; ?></label>
			     <div class="col-sm-8">
				<label class="switch"><input id="tagmanager_adword" type="checkbox" name="<?php echo $PREFIX;?>tagmanager_adword" <?php if ($tagmanager_adword) { echo 'checked'; }?>><span class="slider round"></span></label>
			    </div>
			</div>  
			<div id="conversion" style="display:<?php if (!$tagmanager_adword) { echo 'none'; }?>">
				  <div id="conversion_id" class="form-group">
				    <label class="col-sm-4 control-label" for="input-cid"><span data-toggle="tooltip" title="<?php echo $help_conversion_id;?>"><?php echo $entry_conversion_id; ?></span></label>
				    <div class="col-sm-8">
				      <input type="text" name="<?php echo $PREFIX;?>tagmanager_conversion_id" placeholder="<?php echo $entry_conversion_id; ?>" class="form-control" value="<?php echo $tagmanager_conversion_id;?>"/>
				    </div>
				  </div>   
				  <div class="form-group">
				    <label class="col-sm-4 control-label" for="input-clabel"><span data-toggle="tooltip" title="<?php echo $help_conversion_label;?>"><?php echo $entry_conversion_label; ?></span></label>
				    <div class="col-sm-8">
				      <input type="text" name="<?php echo $PREFIX;?>tagmanager_conversion_label" placeholder="<?php echo $entry_conversion_label; ?>" class="form-control" value="<?php echo $tagmanager_conversion_label;?>"/>
				    </div>
				  </div>   
				  <div class="form-group">
					<label class="col-sm-4 control-label" for="input-adword"><span data-toggle="tooltip" title="<?php echo $help_remarketing;?>"><?php echo $entry_remarketing; ?></span></label>
					<div class="col-sm-8">
						<label class="switch"><input id="tagmanager_remarketing" type="checkbox" name="<?php echo $PREFIX;?>tagmanager_remarketing" <?php if ($tagmanager_remarketing) { echo 'checked'; }?>><span class="slider round"></span></label>
					</div>
				  </div>
				  <div id="remarketing" style="display:<?php if (!$tagmanager_remarketing) { echo 'none'; }?>">
					<div class="form-group">
						<label class="col-sm-4 control-label" for="input-adscustom"><span data-toggle="tooltip" title="<?php echo $help_custom;?>"><?php echo $entry_custom; ?></span></label>
						<div class="col-sm-8">
							<label class="switch"><input id="tagmanager_custom" type="checkbox" name="<?php echo $PREFIX;?>tagmanager_custom" <?php if ($tagmanager_custom) { echo 'checked'; }?>><span class="slider round"></span></label>
						</div>
					</div>
					<div id="customfields" style="display:<?php if (!$tagmanager_custom) { echo 'none'; }?>">
						<div class="form-group">
							<label class="col-sm-4 control-label" for="input-dynx_itemid2">Selcet the required custom parameters</label>	
							<div class="col-sm-8">
								<label class="chkbox"><input type="checkbox" name="<?php echo $PREFIX;?>tagmanager_dynx_itemid" <?php if ($tagmanager_dynx_itemid) { echo 'checked'; }?>><span class="chklabel"><?php echo $entry_dynx_itemid; ?></span></label>
								<label class="chkbox"><input type="checkbox" name="<?php echo $PREFIX;?>tagmanager_dynx_itemid2" <?php if ($tagmanager_dynx_itemid2) { echo 'checked'; }?>><span class="chklabel"><?php echo $entry_dynx_itemid2; ?></span></label>
								<label class="chkbox"><input type="checkbox" name="<?php echo $PREFIX;?>tagmanager_dynx_pagetype" <?php if ($tagmanager_dynx_pagetype) { echo 'checked'; }?>><span class="chklabel"><?php echo $entry_dynx_pagetype; ?></span></label>
								<label class="chkbox"><input type="checkbox" name="<?php echo $PREFIX;?>tagmanager_dynx_totalvalue" <?php if ($tagmanager_dynx_totalvalue) { echo 'checked'; }?>><span class="chklabel"><?php echo $entry_dynx_totalvalue; ?></span></label>
								<br/>
								<label class="chkbox"><input type="checkbox" name="<?php echo $PREFIX;?>tagmanager_ecomm_pagetype" <?php if ($tagmanager_ecomm_pagetype) { echo 'checked'; }?>><span class="chklabel"><?php echo $entry_ecomm_pagetype; ?></span></label>
								<label class="chkbox"><input type="checkbox" name="<?php echo $PREFIX;?>tagmanager_ecomm_totalvalue" <?php if ($tagmanager_ecomm_totalvalue) { echo 'checked'; }?>><span class="chklabel"><?php echo $entry_ecomm_totalvalue; ?></span></label>
								<label class="chkbox"><input type="checkbox" name="<?php echo $PREFIX;?>tagmanager_ecomm_prodid" <?php if ($tagmanager_ecomm_prodid) { echo 'checked'; }?>><span class="chklabel"><?php echo $entry_ecomm_prodid; ?></span></label>
							</div>
						</div>
					</div>
				  </div>
				  
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label" for="input-goptimizestatus"><?php echo $entry_google_optimize_status; ?></span></label>
				<div class="col-sm-8">
					<label class="switch"><input id="optimize" type="checkbox" name="<?php echo $PREFIX;?>tagmanager_google_optimize_status" <?php if ($tagmanager_google_optimize_status) { echo 'checked'; }?>><span class="slider round"></span></label>
				</div>
			</div> 
			<div id="optimizeid" style="display:<?php if (!$tagmanager_google_optimize) { echo 'none'; }?>">
				<div class="form-group">
					<label class="col-sm-4 control-label" for="input-goptimize"><?php echo $entry_google_optimize; ?></label>
					<div class="col-sm-8">
						<input type="text" name="<?php echo $PREFIX;?>tagmanager_google_optimize" placeholder="<?php echo $entry_google_optimize; ?>" class="form-control" value="<?php echo $tagmanager_google_optimize;?>"/>
					</div>
				</div>   
			</div>
		</div>
		<div class="col-sm-4">

		</div>
	    </div>
<!---- Tab Pixel -->
	    <div class="tab-pane" id="tab-tab3">
		<div class="col-sm-8">
			<div class="form-group">
				<label class="col-sm-4 control-label" for="input-pixel"><?php echo $entry_pixel; ?></label>
				<div class="col-sm-8">
					<label class="switch"><input id="tagmanager_pixel" type="checkbox" name="<?php echo $PREFIX;?>tagmanager_pixel" <?php if ($tagmanager_pixel) { echo 'checked'; }?>><span class="slider round"></span></label>
				</div>
			</div>   
			<div id="pixel" style="display:<?php if (!$tagmanager_pixel) { echo 'none'; }?>">

				<div class="form-group">
				    <label class="col-sm-4 control-label" for="input-pixelcode"><?php echo $entry_pixelcode; ?></label>
				    <div class="col-sm-8">
				      <input type="text" name="<?php echo $PREFIX;?>tagmanager_pixelcode" placeholder="<?php echo $entry_pixelcode; ?>" class="form-control" value="<?php echo $tagmanager_pixelcode;?>"/>
				   </div>
				</div>
				<div class="form-group">
				    <label class="col-sm-4 control-label" for="input-fb_catalog_id"><?php echo $entry_fb_catalog_id; ?></label>
				    <div class="col-sm-8">
				      <input type="text" name="<?php echo $PREFIX;?>tagmanager_fb_catalog_id" placeholder="<?php echo $entry_fb_catalog_id; ?>" class="form-control" value="<?php echo $tagmanager_fb_catalog_id;?>"/>
				    </div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 control-label" for="input-alt-curr-statys"><?php echo $entry_alt_currency_status; ?></label>
				    	<div class="col-sm-8">
						<label class="switch"><input id="tagmanager_alt_currency" type="checkbox" name="<?php echo $PREFIX;?>tagmanager_alt_currency_status" <?php if ($tagmanager_alt_currency_status) { echo 'checked'; }?>><span class="slider round"></span></label>
					</div>
				</div>    
				<div id="altcurrency" style="display:<?php if (!$tagmanager_alt_currency_status) { echo 'none'; }?>">
					<div class="form-group">
						<label class="col-sm-4 control-label" for="input-alt-curr"><span data-toggle="tooltip" title="<?php echo $help_ac;?>"><?php echo $entry_alt_currency; ?></span></label>
						<div class="col-sm-8">
							<select name="<?php echo $PREFIX;?>tagmanager_alt_currency" id="input-alt-curr" class="form-control">
							<?php foreach ($currencies as $curr) { ?>
							<option value="<?php echo $curr['code']; ?>" <?php echo ($tagmanager_alt_currency == $curr['code'] ? 'selected="selected"' : '');?>><?php echo $curr['title'];?></option>
							<?php } ?>
							</select>
						</div>
					</div>
				</div> 
			</div>
	      </div>
	      <div class="col-sm-4">
	      </div>
	    </div>
<!---- Tab Pixel -->
	    <div class="tab-pane" id="tab-tab4">
		<div class="col-sm-8">
			<div class="form-group">
				<label class="col-sm-4 control-label" for="input-hotjar_status"><?php echo $entry_hotjar_status; ?></label>
				<div class="col-sm-8">
					<label class="switch"><input id="tagmanager_hotjar_status" type="checkbox" name="<?php echo $PREFIX;?>tagmanager_hotjar_status" <?php if ($tagmanager_hotjar_status) { echo 'checked'; }?>><span class="slider round"></span></label>
				</div>
			</div>    
			<div id="hotjar" style="display:<?php if (!$tagmanager_hotjar_status) { echo 'none'; }?>">
				  <div class="form-group">
				    <label class="col-sm-4 control-label" for="input-hotjar_siteid"><?php echo $entry_hotjar_siteid; ?></label>
				    <div class="col-sm-8">
				      <input type="text" name="<?php echo $PREFIX;?>tagmanager_hotjar_siteid" placeholder="<?php echo $entry_hotjar_siteid; ?>" class="form-control" value="<?php echo $tagmanager_hotjar_siteid;?>"/>
				    </div>
				</div> 
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label" for="input-skroutz_status"><?php echo $entry_skroutz_status; ?></label>
				<div class="col-sm-8">
					<label class="switch"><input id="tagmanager_skroutz_status" type="checkbox" name="<?php echo $PREFIX;?>tagmanager_skroutz_status" <?php if ($tagmanager_skroutz_status) { echo 'checked'; }?>><span class="slider round"></span></label>
				</div>
			</div>    
			<div id="skroutz" style="display:<?php if (!$tagmanager_skroutz_status) { echo 'none'; }?>">
				  <div class="form-group">
				    <label class="col-sm-4 control-label" for="input-skroutz_siteid"><?php echo $entry_skroutz_siteid; ?></label>
				    <div class="col-sm-8">
				      <input type="text" name="<?php echo $PREFIX;?>tagmanager_skroutz_siteid" placeholder="<?php echo $entry_skroutz_siteid; ?>" class="form-control" value="<?php echo $tagmanager_skroutz_siteid;?>"/>
				    </div>
				</div> 
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label" for="input-yandex_status"><?php echo $entry_yandex_status; ?></label>
				<div class="col-sm-8">
					<label class="switch"><input id="tagmanager_yandex_status" type="checkbox" name="<?php echo $PREFIX;?>tagmanager_yandex_status" <?php if ($tagmanager_yandex_status) { echo 'checked'; }?>><span class="slider round"></span></label>
				</div>
			</div>    
			<div id="yandex" style="display:<?php if (!$tagmanager_yandex_status) { echo 'none'; }?>">
				  <div class="form-group">
				    <label class="col-sm-4 control-label" for="input-yandex_code"><?php echo $entry_yandex_code; ?></label>
				    <div class="col-sm-8">
				      <input type="text" name="<?php echo $PREFIX;?>tagmanager_yandex_code" placeholder="<?php echo $entry_yandex_code; ?>" class="form-control" value="<?php echo $tagmanager_yandex_code;?>"/>
				    </div>
				</div> 
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label" for="input-bing_status"><?php echo $entry_bing_status; ?></label>
			    	<div class="col-sm-8">
					<label class="switch"><input id="tagmanager_bing_status" type="checkbox" name="<?php echo $PREFIX;?>tagmanager_bing_status" <?php if ($tagmanager_bing_status) { echo 'checked'; }?>><span class="slider round"></span></label>
				</div>
			</div>  
			<div id="bing" style="display:<?php if (!$tagmanager_bing_status) { echo 'none'; }?>">
				<div class="form-group">
				    <label class="col-sm-4 control-label" for="input-bing_uetid"><?php echo $entry_bing_uetid; ?></label>
				    <div class="col-sm-8">
				      <input type="text" name="<?php echo $PREFIX;?>tagmanager_bing_uetid" placeholder="<?php echo $entry_bing_uetid; ?>" class="form-control" value="<?php echo $tagmanager_bing_uetid;?>"/>
				    </div>
				</div> 		
			</div>
		</div>
		<div class="col-sm-4">
		</div>
	    </div>
<!---- Tab cookie -->
	    <div class="tab-pane" id="tab-tab5">
	    	<div class="col-sm-8">

			<div class="form-group">
				<label class="col-sm-4 control-label" for="input-eu"><?php echo $entry_eu_cookie; ?></label>
				<div class="col-sm-8">
					<label class="switch"><input id="tagmanager_cookie" type="checkbox" name="<?php echo $PREFIX;?>tagmanager_eu_cookie" <?php if ($tagmanager_eu_cookie) { echo 'checked'; }?>><span class="slider round"></span></label>
				</div>
			</div>

			<div id="cookie" style="display:<?php if (!$tagmanager_eu_cookie) { echo 'none'; }?>"> 
				<div class="form-group">
					<label class="col-sm-4 control-label" for="input-eucookie_enforce"><?php echo $entry_eu_cookie_enforce; ?></label>
					<div class="col-sm-8">
						<label class="switch"><input id="tagmanager_eu_cookie_enforce" type="checkbox" name="<?php echo $PREFIX;?>tagmanager_eu_cookie_enforce" <?php if ($tagmanager_eu_cookie_enforce) { echo 'checked'; }?>><span class="slider round"></span></label>
					</div>
				</div>

				<div class="form-group">
					<label class="col-sm-4 control-label" for="input-cookieposition"><?php echo $entry_cookie_position; ?></label>
					<div class="col-sm-8">
						<select name="<?php echo $PREFIX;?>tagmanager_cookie_position" id="input-cookie_position" class="form-control">
						   <?php 
						      foreach ($cookie_positions as $cposition) { ?>
						    <?php if ($cposition == $tagmanager_cookie_position) { ?>
						    <option value="<?php echo $cposition; ?>" selected="selected"><?php echo $cposition; ?></option>
						    <?php } else { ?>
						  <option value="<?php echo $cposition; ?>"><?php echo $cposition; ?></option>
						    <?php } ?>
						    <?php } ?>
						</select>
					</div>
				</div>
				<div class="form-group">
				    <label class="col-sm-4 control-label" for="input-cookie_title"><?php echo $entry_cookie_title; ?></label>
				    <div class="col-sm-8">
				      <input type="text" name="<?php echo $PREFIX;?>tagmanager_cookie_title" placeholder="<?php echo $entry_cookie_title; ?>" class="form-control" value="<?php echo $tagmanager_cookie_title;?>"/>
				    </div>
				  </div> 
				  <div class="form-group">
				    <label class="col-sm-4 control-label" for="input-cookie_text"><?php echo $entry_cookie_text; ?></label>
				    <div class="col-sm-8">
				      <input type="text" name="<?php echo $PREFIX;?>tagmanager_cookie_text" placeholder="<?php echo $entry_cookie_text; ?>" class="form-control" value="<?php echo $tagmanager_cookie_text;?>"/>
				    </div>
				  </div> 
				  <div class="form-group">
				    <label class="col-sm-4 control-label" for="input-cookie_text2"><?php echo $entry_cookie_text2; ?></label>
				    <div class="col-sm-8">
				      <input type="text" name="<?php echo $PREFIX;?>tagmanager_cookie_text2" placeholder="<?php echo $entry_cookie_text2; ?>" class="form-control" value="<?php echo $tagmanager_cookie_text2;?>"/>
				    </div>
				  </div> 
				  <div class="form-group">
				    <label class="col-sm-4 control-label" for="input-cid"><?php echo $entry_cookie_link; ?></label>
				    <div class="col-sm-8">
				      <input type="text" name="<?php echo $PREFIX;?>tagmanager_cookie_link" placeholder="<?php echo $entry_cookie_link; ?>" class="form-control" value="<?php echo $tagmanager_cookie_link;?>"/>
				    </div>
				  </div> 
				  <div class="form-group">
				    <label class="col-sm-4 control-label" for="input-cid"><?php echo $entry_cookie_button1; ?></label>
				    <div class="col-sm-8">
				      <input type="text" name="<?php echo $PREFIX;?>tagmanager_cookie_button1" placeholder="<?php echo $entry_cookie_button1; ?>" class="form-control" value="<?php echo $tagmanager_cookie_button1;?>"/>
				    </div>
				  </div> 
				  <div class="form-group">
				    <label class="col-sm-4 control-label" for="input-cid"><?php echo $entry_cookie_button2; ?></label>
				    <div class="col-sm-8">
				      <input type="text" name="<?php echo $PREFIX;?>tagmanager_cookie_button2" placeholder="<?php echo $entry_cookie_button2; ?>" class="form-control" value="<?php echo $tagmanager_cookie_button2;?>"/>
				    </div>
				  </div> 
				  <div class="form-group">
				    <label class="col-sm-4 control-label" for="input-cookie_button3"><?php echo $entry_cookie_button3; ?></label>
				    <div class="col-sm-8">
				      <input type="text" name="<?php echo $PREFIX;?>tagmanager_cookie_button3" placeholder="<?php echo $entry_cookie_button3; ?>" class="form-control" value="<?php echo $tagmanager_cookie_button3;?>"/>
				    </div>
				  </div> 
				  <div class="form-group">
				    <label class="col-sm-4 control-label" for="input-bg_popup"><?php echo $entry_cookie_bg_popup; ?></label>
				    <div class="col-sm-8">
				      <input type="text" name="<?php echo $PREFIX;?>tagmanager_cookie_bg_popup" placeholder="<?php echo $entry_cookie_bg_popup; ?>" class="form-control" value="<?php echo $tagmanager_cookie_bg_popup;?>"/>
				    </div>
				  </div> 
				  <div class="form-group">
				    <label class="col-sm-4 control-label" for="input-text_popup"><?php echo $entry_cookie_text_popup; ?></label>
				    <div class="col-sm-8">
				      <input type="text" name="<?php echo $PREFIX;?>tagmanager_cookie_text_popup" placeholder="<?php echo $entry_cookie_text_popup; ?>" class="form-control" value="<?php echo $tagmanager_cookie_text_popup;?>"/>
				    </div>
				  </div> 
				  <div class="form-group">
				    <label class="col-sm-4 control-label" for="input-bg_button"><?php echo $entry_cookie_bg_button; ?></label>
				    <div class="col-sm-8">
				      <input type="text" name="<?php echo $PREFIX;?>tagmanager_cookie_bg_button" placeholder="<?php echo $entry_cookie_bg_button; ?>" class="form-control" value="<?php echo $tagmanager_cookie_bg_button;?>"/>
				    </div>
				  </div> 
				  <div class="form-group">
				    <label class="col-sm-4 control-label" for="input-text_button"><?php echo $entry_cookie_text_button; ?></label>
				    <div class="col-sm-8">
				      <input type="text" name="<?php echo $PREFIX;?>tagmanager_cookie_text_button" placeholder="<?php echo $entry_cookie_text_button; ?>" class="form-control" value="<?php echo $tagmanager_cookie_text_button;?>"/>
				    </div>
				  </div> 
				  <div class="form-group">
				    <label class="col-sm-4 control-label" for="input-cookie_heading_color"><?php echo $entry_cookie_heading_color; ?></label>
				    <div class="col-sm-8">
				      <input type="text" name="<?php echo $PREFIX;?>tagmanager_cookie_heading_color" placeholder="<?php echo $entry_cookie_heading_color; ?>" class="form-control" value="<?php echo $tagmanager_cookie_heading_color;?>"/>
				    </div>
				  </div> 

				  <div class="form-group">
					<label class="col-sm-4 control-label" for="input-cookie_badge"><?php echo $entry_cookie_badge; ?></label>
					<div class="col-sm-8">
						<label class="switch"><input id="tagmanager_badge" type="checkbox" name="<?php echo $PREFIX;?>tagmanager_cookie_badge" <?php if ($tagmanager_cookie_badge) { echo 'checked'; }?>><span class="slider round"></span></label>
					</div>
				</div>

				<div id="badge" style="display:<?php if (!$tagmanager_cookie_badge) { echo 'none'; }?>"> 
					<div class="form-group">
						<label class="col-sm-4 control-label" for="input-cookie_badge_position"><?php echo $entry_cookie_badge_position; ?></label>
						<div class="col-sm-8">
							<select name="<?php echo $PREFIX;?>tagmanager_cookie_badge_position" id="input-cookie_badge_position" class="form-control">
							   <?php 
							      foreach ($badge_positions as $cposition) { ?>
							    <?php if ($cposition == $tagmanager_cookie_badge_position) { ?>
							    <option value="<?php echo $cposition; ?>" selected="selected"><?php echo $cposition; ?></option>
							    <?php } else { ?>
							  <option value="<?php echo $cposition; ?>"><?php echo $cposition; ?></option>
							    <?php } ?>
							    <?php } ?>
							</select>
						</div>
					</div>
					 <div class="form-group">
					    <label class="col-sm-4 control-label" for="input-cookie_badge_color"><?php echo $entry_cookie_badge_color; ?></label>
					    <div class="col-sm-8">
					      <input type="text" name="<?php echo $PREFIX;?>tagmanager_cookie_badge_color" placeholder="<?php echo $entry_cookie_badge_color; ?>" class="form-control" value="<?php echo $tagmanager_cookie_badge_color;?>"/>
					    </div>
					  </div>
				</div>
			</div>
		</div>
		<div class="col-sm-4">
			<h3>EU / GDPR Cookie Consent</h3>
			<?php echo $text_about_cookie; ?>
		</div>
	    </div>
<!---- Tab amp -->
	      <div class="tab-pane" id="tab-tab6">
	      	<div class="col-sm-8">
			<div class="form-group">
				<label class="col-sm-4 control-label" for="input-zenchat_status"><?php echo $entry_zenchat_status; ?></label>
				<div class="col-sm-8">
					<label class="switch"><input id="tagmanager_zenchat_status" type="checkbox" name="<?php echo $PREFIX;?>tagmanager_zenchat_status" <?php if ($tagmanager_zenchat_status) { echo 'checked'; }?>><span class="slider round"></span></label>
				</div>
			</div>    
			<div id="zenchat" style="display:<?php if (!$tagmanager_zenchat_status) { echo 'none'; }?>">
				  <div class="form-group">
				    <label class="col-sm-4 control-label" for="input-zenchat_code"><?php echo $entry_zenchat_code; ?></label>
				    <div class="col-sm-8">
				      <input type="text" name="<?php echo $PREFIX;?>tagmanager_zenchat_code" placeholder="<?php echo $entry_zenchat_code; ?>" class="form-control" value="<?php echo $tagmanager_zenchat_code;?>"/>
				    </div>
				</div> 
			</div>
			<div class="form-group">
				<label class="col-sm-4 control-label" for="input-ampstatus"><?php echo $entry_ampstatus; ?></label>
				<div class="col-sm-8">
					<label class="switch"><input id="tagmanager_amp" type="checkbox" name="<?php echo $PREFIX;?>tagmanager_ampstatus" <?php if ($tagmanager_ampstatus) { echo 'checked'; }?>><span class="slider round"></span></label>
				</div>
			</div>
			<div id="amp" style="display:<?php if (!$tagmanager_ampstatus) { echo 'none'; }?>"> 
				<div class="form-group required">
				    <label class="col-sm-4 control-label" for="input-ampcode"><?php echo $entry_ampcode; ?></label>
				    <div class="col-sm-8">
				      <input type="text" name="<?php echo $PREFIX;?>tagmanager_ampcode" placeholder="<?php echo $entry_ampcode; ?>" class="form-control" value="<?php echo $tagmanager_ampcode;?>"/>
				    </div>
				</div>
			</div>
		</div>
		<div class="col-sm-4">
			<p>Google Analytics tracking for AMP Web pages. If you are using Webkul Accelerated Mobile Pages extesion you can setup Google Analytics tracking using the Tag Manager AMP container.</p>
			<p>Please note AMP tracking is in beta, and only Google Analytics is currently supported</p> 
		</div>
	   </div>
<!---- Tab About -->
	    <div class="tab-pane" id="tab-tab7">
		<div class="table-responsive col-sm-6">
		    <table class="table table-bordered table-hover">
		      <thead>
			<tr>
			  <td class="text-left"><?php echo $column_oid;?></td>
			  <td class="text-left"><?php echo $column_status;?></td>
			  <td class="text-center"><?php echo $column_action;?></td>
			</tr>
		      </thead>
		      <tbody>
			<?php if (isset($transactions)){?>
			<?php foreach ($transactions as $trans){?>
			<tr>
			  <td class="text-left"><?php echo $trans['order_id'];?></td>
			  <td class="text-left">
			  <?php if ($trans['hit'] == "0"){ ?>Not Sent to Analytics<?php }?>
			  <?php if ($trans['hit'] == "1"){ ?>Sent to Analytics<?php }?>
			  <?php if ($trans['hit'] == "2"){ ?>Refund Sent to Analytics<?php }?>
			  </td>
			  <td class="text-center">
				<div id="div-send-<?php echo $trans['id'];?>" data-loading-text="loading" onclick="hitorder(<?php echo $trans['order_id'];?>,<?php echo $trans['id'];?>);" class="btn btn-primary" <?php echo ($trans['hit'] != '0' ? 'disabled' : '');?>><i class="fa fa-plus-circle"></i> <?php echo $button_send;?></div>
				<div id="div-refund-<?php echo $trans['id'];?>" data-loading-text="loading" onclick="refundorder(<?php echo $trans['order_id'];?>,<?php echo $trans['id'];?>);"  class="btn btn-primary" <?php echo ($trans['hit'] != '1' ? 'disabled' : '');?>><i class="fa fa-plus-circle"></i><?php echo $button_refund;?></div>
			</td>
			</tr>
			<?php }?>
			<tr>
			  <td class="text-center" colspan="6">No Results</td>
			</tr>
			<?php }?>
		      </tbody>
		    </table>
		</div>
		<div class="col-sm-6">
			<?php echo $text_order;?>
		</div>
	    </div>

	  </div>
	  <input type="hidden" name="<?php echo $PREFIX;?>tagmanager_mp" value="1">
	  <input type="hidden" name="<?php echo $PREFIX;?>tagmanager_route_confirm" value="<?php echo $tagmanager_route_confirm;?>">
       </form>
       <script>
       $('form').submit(function () {
    $(this).find('input[type="checkbox"]').each( function () {
        var checkbox = $(this);
        if( checkbox.is(':checked')) {
            checkbox.attr('value','1');
        } else {
            checkbox.after().append(checkbox.clone().attr({type:'hidden', value:0}));
            checkbox.prop('disabled', true);
        }
    })
});
    $(function () {
        $("#tagmanager_adword").click(function () {
            if ($(this).is(":checked")) {
                $("#conversion").show();
            } else {
                $("#conversion").hide();
            }
        });
	$("#optimize").click(function () {
            if ($(this).is(":checked")) {
                $("#optimizeid").show();
            } else {
                $("#optimizeid").hide();
            }
        });
	$("#tagmanager_remarketing").click(function () {
            if ($(this).is(":checked")) {
                $("#remarketing").show();
            } else {
                $("#remarketing").hide();
            }
        });
	$("#tagmanager_custom").click(function () {
            if ($(this).is(":checked")) {
                $("#customfields").show();
            } else {
                $("#customfields").hide();
            }
        });
	$("#tagmanager_pixel").click(function () {
            if ($(this).is(":checked")) {
                $("#pixel").show();
            } else {
                $("#pixel").hide();
            }
        });
	$("#tagmanager_alt_currency").click(function () {
            if ($(this).is(":checked")) {
                $("#altcurrency").show();
            } else {
                $("#altcurrency").hide();
            }
        });
	$("#tagmanager_hotjar_status").click(function () {
            if ($(this).is(":checked")) {
                $("#hotjar").show();
            } else {
                $("#hotjar").hide();
            }
        });
	$("#tagmanager_skroutz_status").click(function () {
            if ($(this).is(":checked")) {
                $("#skroutz").show();
            } else {
                $("#skroutz").hide();
            }
        });
	$("#tagmanager_yandex_status").click(function () {
            if ($(this).is(":checked")) {
                $("#yandex").show();
            } else {
                $("#yandex").hide();
            }
        });
	$("#tagmanager_zenchat_status").click(function () {
            if ($(this).is(":checked")) {
                $("#zenchat").show();
            } else {
                $("#zenchat").hide();
            }
        });
	$("#tagmanager_bing_status").click(function () {
            if ($(this).is(":checked")) {
                $("#bing").show();
            } else {
                $("#bing").hide();
            }
        });
	$("#tagmanager_cookie").click(function () {
            if ($(this).is(":checked")) {
                $("#cookie").show();
            } else {
                $("#cookie").hide();
            }
        });
	$("#tagmanager_amp").click(function () {
            if ($(this).is(":checked")) {
                $("#amp").show();
            } else {
                $("#amp").hide();
            }
        });
	$("#tagmanager_badge").click(function () {
            if ($(this).is(":checked")) {
                $("#badge").show();
            } else {
                $("#badge").hide();
            }
        });
    });
    function hitorder(orderid,id) {
    		$.ajax({
			url: '<?php echo $catalog;?>index.php?route=extension/module/tagmanager/hitorder&oid=' + orderid + '&v=<?php echo $tagmanager['vs'];?>',
			type: 'get',
			dataType: 'json',
			beforeSend: function() {
				$("#div-send-" + id).prop('disabled',true);
			},
			complete: function() {
				$('#content > .container-fluid').prepend('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> Order Sent to Analytics</div>');
				location.reload();
				
			},
			success: function(json) {
			
			},
			
		});
	}
	function refundorder(orderid,id) {
		$.ajax({
			url: '<?php echo $catalog;?>index.php?route=extension/module/tagmanager/refund&oid=' + orderid + '&v=<?php echo $tagmanager['vs'];?>&order_status_id=7',
			type: 'get',
			dataType: 'json',
			
			beforeSend: function() {
				$("#div-refund-" + id).prop('disabled',true);
			},
			complete: function() {
				$('#content > .container-fluid').prepend('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> Refund Sent to Analytics</div>');
				location.reload();
				
			},
			success: function(json) {
			
			},
		});
	}
</script>
      </div>
    </div>
  </div>
</div>
</div>
<?php echo $footer; ?> 