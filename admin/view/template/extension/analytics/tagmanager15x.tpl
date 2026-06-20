<?php echo $header; ?>
<div id="">
<style>
    .form-horizontal .form-group {padding-top:7px;}
    #tab-tab8, #tab-tab7 {margin-top:10px;}
</style>
    <div id="content">
        <div class="">
            <div class="container-fluid">
                <h1><img style="vertical-align:top;padding-right:4px;" src="view/javascript/tagmanager/img/icon.png"/><?php echo $heading_title; ?></h1>
                <ul class="breadcrumb" style="background-color: white;">
                    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                    <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                    <?php } ?>
                </ul>
            
                 <div class="buttons pull-right" style="margin-bottom:5px;">
                    <?php if (count($stores) > 0) { ?>
                			<div class="stores" style="display:inline-block">
                				<?php echo $text_store; ?>
                				<select name="store_id" id="store_id" onchange="location='<?php echo str_replace("'", "\\'",$change_store); ?>'+'&store_id='+$(this).val()">
                					<?php foreach ($stores as $key => $value) { ?>
                						<option value="<?php echo $value['store_id'] ?>" <?php echo $store_id == $value['store_id'] ? 'selected="selected"' : '' ?>><?php echo $value['name'] ?></option>
                					<?php } ?>
                				</select>
                			</div>
                		<?php } ?>
                    <button type="submit" form="form-tagmanager" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i>Save</button>
                    <button type="submit" name ="apply" value="1" form="form-tagmanager" data-toggle="tooltip" title="<?php echo $button_apply; ?>" class="btn btn-success"><i class="fa fa-check"></i>Apply</button>
                    <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i>Cancel</a>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <?php if ($error_warning) { ?>
            <div class="alert alert-danger">
                <i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
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
                        <li id="tab1" <?php if (!$show_order){ ?> class="active"<?php }?>><a href="#tab-tab1" data-toggle="tab"><?php echo $tab_tab1; ?></a></li>
						<li><a href="#tab-tab2" data-toggle="tab"><?php echo $tab_tab2; ?></a></li>
                        <li><a href="#tab-tab3" data-toggle="tab"><?php echo $tab_tab3; ?></a></li>
                        <li><a href="#tab-tab4" data-toggle="tab"><?php echo $tab_tab4; ?></a></li>
                        <li><a href="#tab-tab5" data-toggle="tab"><?php echo $tab_tab5; ?></a></li>
                        <li><a href="#tab-tab6" data-toggle="tab"><?php echo $tab_tab6; ?></a></li>
                        <li id="tab7" <?php if ($show_order){ ?> class="active"<?php }?>><a href="#tab-tab7" data-toggle="tab"><?php echo $tab_tab7; ?></a></li>
                        <li><a href="#tab-tab8" data-toggle="tab"><?php echo $tab_tab8; ?></a></li>
                    </ul>
                    <!-- TAB START -->                    
                    <div class="tab-content">
<!-- TAB GENERAL -->                    
                        <div class="tab-pane <?php if (!$show_order){ ?>active<?php }?>" id="tab-tab1">
                            <div class="col-sm-8">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label" for="input-status"><?php echo $entry_status; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input type="checkbox" name="<?php echo $PREFIX;?>tagmanager_status" <?php if ($tagmanager_status) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                </div>
                             	<div class="form-group required">
                                    <label class="col-sm-4 control-label" for="input-code"><?php echo $entry_primary; ?></label>
                                    <div class="col-sm-8">
                                        <input type="text" name="code" placeholder="<?php echo $entry_primary; ?>" class="form-control" value="<?php echo $tagmanager['code'];?>" />
                                        <?php if ($error_primary) { ?>
                                        <div class="text-danger">
                                            <?php echo $error_primary; ?>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label" for="input-server"><span data-toggle="tooltip" title="<?php echo $help_server;?>"><?php echo $entry_server; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input type="checkbox" id="tagmanager_server" name="server" <?php if ($tagmanager['server']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                	<div id="server" style="display:<?php if (! $tagmanager['server']) { echo 'none'; }?>">
										<div class="panel-group">
											<label class="col-sm-4 control-label" for="input-server-url"><span data-toggle="tooltip" title="<?php echo $help_server_url;?>"><?php echo $entry_server_url;?></span></label>
											<div class="col-sm-8">
												<input type="text" name="server_url" placeholder="<?php echo $entry_server_url;?>" class="form-control" value="<?php echo $tagmanager['server_url'];?>"/>
											</div>
										</div>
									</div>
                                </div>
                                
                                <div class="form-group">
                                    <label class="col-sm-4 control-label" for="input-admin"><span data-toggle="tooltip" title="<?php echo $help_admin;?>"><?php echo $entry_admin; ?></span></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input type="checkbox" name="admin" <?php if ($tagmanager['admin']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label" for="input-pagecache"><span data-toggle="tooltip" title="<?php echo $help_pagecache;?>"><?php echo $entry_pagecache; ?></span></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input type="checkbox" name="pagecache" <?php if (isset($tagmanager['pagecache']) && $tagmanager['pagecache']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label" for="input-ajax"><span data-toggle="tooltip" title="<?php echo $help_ajax;?>"><?php echo $entry_ajax; ?></span></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input type="checkbox" name="ajax" <?php if (isset($tagmanager['ajax']) && $tagmanager['ajax']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label" for="input-cache"><span data-toggle="tooltip" title="<?php echo $help_cache;?>"><?php echo $entry_cache; ?></span></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input type="checkbox" name="cache" <?php if ($tagmanager['cache']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label" for="input-debug"><span data-toggle="tooltip" title="<?php echo $help_debug;?>"><?php echo $entry_debug; ?></span></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input type="checkbox" name="debug" <?php if ($tagmanager['debug']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                </div>    
                                <div class="form-group">
                                    <label class="col-sm-4 control-label" for="input-debugapi"><span data-toggle="tooltip" title="<?php echo $help_debug_api;?>"><?php echo $entry_debug_api; ?></span></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input type="checkbox" name="debug_api" <?php if ($tagmanager['debug_api']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="col-sm-4 control-label" for="input-userdata"><span data-toggle="tooltip" title="<?php echo $help_customer_data;?>"><?php echo $entry_customer_data; ?></span></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input type="checkbox" name="customer_data" <?php if ($tagmanager['customer_data']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                </div> 
                                <div class="form-group">
                                    <label class="col-sm-4 control-label" for="input-ptitle"><span data-toggle="tooltip" title="<?php echo $help_ptitle;?>"><?php echo $entry_ptitle; ?></span></label>
                                    <div class="col-sm-8">
                                        <select name="ptitle" id="input-ptitle" class="form-control">
                                            <?php
                                            foreach ($product_title as $ptitle) { ?>
                                            <?php if ($ptitle == $tagmanager['ptitle']) { ?>
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
                                        <select name="pmap" id="input-adword" class="form-control">
                                            <?php
                                            foreach ($product_map as $value) { ?>
                                            <?php if ($product_map == $tagmanager['pmap']) { ?>
                                            <option value="<?php echo $value; ?>" selected="selected"><?php echo $value; ?></option>
                                            <?php } else { ?>
                                            <option value="<?php echo $value; ?>"><?php echo $value; ?></option>
                                            <?php } ?>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label" for="input-idprefix"><span data-toggle="tooltip" title="<?php echo $help_id_prefix;?>"><?php echo $entry_id_prefix; ?></span></label>
                                    <div class="col-sm-8">
                                        <input type="text" name="id_prefix" placeholder="<?php echo $entry_id_prefix; ?>" class="form-control" value="<?php echo $tagmanager['id_prefix'];?>" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label" for="input-idsuffix"><span data-toggle="tooltip" title="<?php echo $help_id_suffix;?>"><?php echo $entry_id_suffix; ?></span></label>
                                    <div class="col-sm-8">
                                        <input type="text" name="id_suffix" placeholder="<?php echo $entry_id_suffix; ?>" class="form-control" value="<?php echo $tagmanager['id_suffix'];?>" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label" for="input-route-checkout"><span data-toggle="tooltip" title="<?php echo $help_route;?>"><?php echo $entry_route_checkout;?></span></label>
                                    <div class="col-sm-5">
                                        <textarea rows="4" cols="50" name="route_checkout"><?php if (!empty($tagmanager['route_checkout'])) { echo $tagmanager['route_checkout']; }?></textarea>
                                    </div>
                                    <div class="col-sm-3">
                                        <?php echo $help_route_checkout;?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label" for="input-route-success"><span data-toggle="tooltip" title="<?php echo $help_route;?>"><?php echo $entry_route_success;?></span></label>
                                    <div class="col-sm-5">
                                        <textarea rows="4" cols="50" name="route_success"><?php if (!empty($tagmanager['route_success'])) { echo $tagmanager['route_success']; }?></textarea>
                                    </div>
                                    <div class="col-sm-3">
                                        <?php echo $help_route_success;?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label" for="input-customj"><span data-toggle="tooltip" title="<?php echo $help_customcode;?>"><?php echo $entry_customcode;?></span></label>
                                    <div class="col-sm-8">
                                        <textarea rows="10" cols="70%" style="width:100%" name="customcode"><?php if (!empty($tagmanager['customcode'])) { echo $tagmanager['customcode']; }?></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <h3 class="tm-heading">Tag Manager Version <?php echo $text_version;?></h3>
                                <?php echo $text_about;?>
                                <h3 class="tm-heading"><?php echo $heading_container;?></h3>
                                <?php echo $text_container;?>
                                <div class="row">
                                    <table width="100%" class="tmtable">
                                        <tbody>
                                            <tr>
                                                <td style="width:35%">Developed by</td>
                                                <td>AITS</td>
                                            </tr>
                                            <tr>
                                                <td>Website</td>
                                                <td>https://aits.pk</td>
                                            </tr>
                                            <tr>
                                                <td>Author</td>
                                                <td>Muhammad Akram</td>
                                            </tr>
                                            <tr>
                                                <td>Licenced Order</td>
                                                <td><?php echo (isset($order_id) ? $order_id : 'pirated');?></td>
                                            </tr>
                                            <tr>
                                                <td>Licenced email</td>
                                                <td><?php echo (isset($email) ? $email : ' ');?></td>
                                            </tr>
                                            <tr>
                                                <td>Licenced domain</td>
                                                <td><?php echo (isset($domain) ? $domain : ' ');?></td>
                                            </tr>
                                        </tbody>
                                    </table>    
                                </div>
                            </div>
                        </div>
<!-- TAB GOOGLE -->
                            <div class="tab-pane" id="tab-tab2">
                                <div class="col-sm-8">
                                    <div id="tm_UA" class="form-group">
				    					<label class="col-sm-4 control-label" for="input-ua_status"><span data-toggle="tooltip" title="<?php echo $help_ua;?>"><?php echo $entry_ua_status;?></span></label>
										<div class="col-sm-8">
											<label class="switch">
												<input type="checkbox" id="tagmanager_ua_status" name="ua_status" <?php if($tagmanager['ua_status']){ echo 'checked'; } ?>>
												<span class="slider round"></span>
											</label>
										</div>
										<div id="ua" style="display:<?php if (! $tagmanager['ua_status']) { echo 'none'; }?>">
    										<div class="panel-group">
    											<label class="col-sm-4 control-label" for="input-gid"><span data-toggle="tooltip" title="<?php echo $help_gid;?>"><?php echo $entry_gid;?></span></label>
    											<div class="col-sm-8">
    												<input type="text" name="gid" placeholder="<?php echo $entry_gid;?>" class="form-control" value="<?php echo $tagmanager['gid'];?>"/>
    											</div>
    										</div>
											<div class="panel-group">
                                                <label class="col-sm-4 control-label" for="input-userid"><span data-toggle="tooltip" title="<?php echo $help_userid;?>"><?php echo $entry_userid_status; ?></span></label>
                                                <div class="col-sm-8">
                                                    <label class="switch"><input type="checkbox" name="userid_status" <?php if ($tagmanager['userid_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                                </div>
                                            </div>
                                            <div id="tm_DIMENSIONS" class="panel-group">
                                                <label class="col-sm-4 control-label" for="input-dimensions"><span data-toggle="tooltip" title="<?php echo $help_custom_dim;?>"><?php echo $entry_custom_dimension; ?></span></label>
                                                <div class="col-sm-8">
                                                    <label class="switch"><input id="tagmanager_dimensions" type="checkbox" name="custom_dimension" <?php if ($tagmanager['custom_dimension']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                                </div>
                                                <div id="dimensions" style="display:<?php if (! $tagmanager['custom_dimension']) { echo 'none'; }?>"> 
                                                    <div class="panel-group">
                                                    	<label class="col-sm-4 control-label" for="input-custom_dimension1"><span data-toggle="tooltip" title="<?php echo $entry_custom_dimension1;?>"><?php echo $entry_custom_dimension1; ?></span></label>
                                                    	<div class="col-sm-4">
                                                    	    <select name="custom_dimension1" id="input-custom_dimension1" class="form-control">
                                                                <?php
                                                                foreach ($dimensions_index as $dindex) { ?>
                                                                <?php if ($dindex == $tagmanager['custom_dimension1']) { ?>
                                                                <option value="<?php echo $dindex; ?>" selected="selected"><?php echo $dindex; ?></option>
                                                                <?php } else { ?>
                                                                <option value="<?php echo $dindex; ?>"><?php echo $dindex; ?></option>
                                                                <?php } ?>
                                                                <?php } ?>
                                                            </select>
                                                    	</div>
                                                    	 <div class="col-sm-4">
                                                    	    <select name="custom_dimension1_text" id="input-custom_dimensiontext1" class="form-control">
                                                                <?php
                                                                foreach ($dimensions_text as $dtext) { ?>
                                                                <?php if ($dtext == $tagmanager['custom_dimension1_text']) { ?>
                                                                <option value="<?php echo $dtext; ?>" selected="selected"><?php echo $dtext; ?></option>
                                                                <?php } else { ?>
                                                                <option value="<?php echo $dtext; ?>"><?php echo $dtext; ?></option>
                                                                <?php } ?>
                                                                <?php } ?>
                                                            </select>
                                                    	</div>
                                                	</div>
                                                	<div class="panel-group">
                                                    	<label class="col-sm-4 control-label" for="input-custom_dimension2"><span data-toggle="tooltip" title="<?php echo $entry_custom_dimension2;?>"><?php echo $entry_custom_dimension2; ?></span></label>
                                                    	<div class="col-sm-4">
                                                    	    <select name="custom_dimension2" id="input-custom_dimension2" class="form-control">
                                                                <?php
                                                                foreach ($dimensions_index as $dindex) { ?>
                                                                <?php if ($dindex==$tagmanager['custom_dimension2']) { ?>
                                                                <option value="<?php echo $dindex; ?>" selected="selected"><?php echo $dindex; ?></option>
                                                                <?php } else { ?>
                                                                <option value="<?php echo $dindex; ?>"><?php echo $dindex; ?></option>
                                                                <?php } ?>
                                                                <?php } ?>
                                                            </select>
                                                    	</div>
                                                    	 <div class="col-sm-4">
                                                    	    <select name="custom_dimension2_text" id="input-custom_dimensiontext2" class="form-control">
                                                                <?php
                                                                foreach ($dimensions_text as $dtext) { ?>
                                                                <?php if ($dtext==$tagmanager['custom_dimension2_text']) { ?>
                                                                <option value="<?php echo $dtext; ?>" selected="selected"><?php echo $dtext; ?></option>
                                                                <?php } else { ?>
                                                                <option value="<?php echo $dtext; ?>"><?php echo $dtext; ?></option>
                                                                <?php } ?>
                                                                <?php } ?>
                                                            </select>
                                                    	</div>
                                                	</div>
                                                	<div class="panel-group">
                                                    	<label class="col-sm-4 control-label" for="input-custom_dimension3"><span data-toggle="tooltip" title="<?php echo $entry_custom_dimension3;?>"><?php echo $entry_custom_dimension3; ?></span></label>
                                                    	<div class="col-sm-4">
                                                    	    <select name="custom_dimension3" id="input-custom_dimension3" class="form-control">
                                                                <?php
                                                                foreach ($dimensions_index as $dindex) { ?>
                                                                <?php if ($dindex==$tagmanager['custom_dimension3']) { ?>
                                                                <option value="<?php echo $dindex; ?>" selected="selected"><?php echo $dindex; ?></option>
                                                                <?php } else { ?>
                                                                <option value="<?php echo $dindex; ?>"><?php echo $dindex; ?></option>
                                                                <?php } ?>
                                                                <?php } ?>
                                                            </select>
                                                    	</div>
                                                    	 <div class="col-sm-4">
                                                    	    <select name="custom_dimension3_text" id="input-custom_dimensiontext3" class="form-control">
                                                                <?php
                                                                foreach ($dimensions_text as $dtext) { ?>
                                                                <?php if ($dtext==$tagmanager['custom_dimension3_text']) { ?>
                                                                <option value="<?php echo $dtext; ?>" selected="selected"><?php echo $dtext; ?></option>
                                                                <?php } else { ?>
                                                                <option value="<?php echo $dtext; ?>"><?php echo $dtext; ?></option>
                                                                <?php } ?>
                                                                <?php } ?>
                                                            </select>
                                                    	</div>
                                                	</div>
                                                	<div class="panel-group">
                                                    	<label class="col-sm-4 control-label" for="input-custom_dimension4"><span data-toggle="tooltip" title="<?php echo $entry_custom_dimension4;?>"><?php echo $entry_custom_dimension4; ?></span></label>
                                                    	<div class="col-sm-4">
                                                    	    <select name="custom_dimension4" id="input-custom_dimension4" class="form-control">
                                                                <?php
                                                                foreach ($dimensions_index as $dindex) { ?>
                                                                <?php if ($dindex==$tagmanager['custom_dimension4']) { ?>
                                                                <option value="<?php echo $dindex; ?>" selected="selected"><?php echo $dindex; ?></option>
                                                                <?php } else { ?>
                                                                <option value="<?php echo $dindex; ?>"><?php echo $dindex; ?></option>
                                                                <?php } ?>
                                                                <?php } ?>
                                                            </select>
                                                    	</div>
                                                    	 <div class="col-sm-4">
                                                    	    <select name="custom_dimension4_text" id="input-custom_dimensiontext4" class="form-control">
                                                                <?php
                                                                foreach ($dimensions_text as $dtext) { ?>
                                                                <?php if ($dtext==$tagmanager['custom_dimension4_text']) { ?>
                                                                <option value="<?php echo $dtext; ?>" selected="selected"><?php echo $dtext; ?></option>
                                                                <?php } else { ?>
                                                                <option value="<?php echo $dtext; ?>"><?php echo $dtext; ?></option>
                                                                <?php } ?>
                                                                <?php } ?>
                                                            </select>
                                                    	</div>
                                                	</div>
                                                	<div class="panel-group">
                                                    	<label class="col-sm-4 control-label" for="input-custom_dimension5"><span data-toggle="tooltip" title="<?php echo $entry_custom_dimension5;?>"><?php echo $entry_custom_dimension5; ?></span></label>
                                                    	<div class="col-sm-4">
                                                    	    <select name="custom_dimension5" id="input-custom_dimension5" class="form-control">
                                                                <?php
                                                                foreach ($dimensions_index as $dindex) { ?>
                                                                <?php if ($dindex==$tagmanager['custom_dimension5']) { ?>
                                                                <option value="<?php echo $dindex; ?>" selected="selected"><?php echo $dindex; ?></option>
                                                                <?php } else { ?>
                                                                <option value="<?php echo $dindex; ?>"><?php echo $dindex; ?></option>
                                                                <?php } ?>
                                                                <?php } ?>
                                                            </select>
                                                    	</div>
                                                    	 <div class="col-sm-4">
                                                    	    <select name="custom_dimension5_text" id="input-custom_dimensiontext5" class="form-control">
                                                                <?php
                                                                foreach ($dimensions_text as $dtext) { ?>
                                                                <?php if ($dtext==$tagmanager['custom_dimension5_text']) { ?>
                                                                <option value="<?php echo $dtext; ?>" selected="selected"><?php echo $dtext; ?></option>
                                                                <?php } else { ?>
                                                                <option value="<?php echo $dtext; ?>"><?php echo $dtext; ?></option>
                                                                <?php } ?>
                                                                <?php } ?>
                                                            </select>
                                                    	</div>
                                                	</div>
                                                	<div class="panel-group">
                                                    	<label class="col-sm-4 control-label" for="input-custom_dimension6"><span data-toggle="tooltip" title="<?php echo $entry_custom_dimension6;?>"><?php echo $entry_custom_dimension6; ?></span></label>
                                                    	<div class="col-sm-4">
                                                    	    <select name="custom_dimension6" id="input-custom_dimension6" class="form-control">
                                                                <?php
                                                                foreach ($dimensions_index as $dindex) { ?>
                                                                <?php if ($dindex==$tagmanager['custom_dimension6']) { ?>
                                                                <option value="<?php echo $dindex; ?>" selected="selected"><?php echo $dindex; ?></option>
                                                                <?php } else { ?>
                                                                <option value="<?php echo $dindex; ?>"><?php echo $dindex; ?></option>
                                                                <?php } ?>
                                                                <?php } ?>
                                                            </select>
                                                    	</div>
                                                    	 <div class="col-sm-4">
                                                    	    <select name="custom_dimension6_text" id="input-custom_dimensiontext6" class="form-control">
                                                                <?php
                                                                foreach ($dimensions_text as $dtext) { ?>
                                                                <?php if ($dtext==$tagmanager['custom_dimension6_text']) { ?>
                                                                <option value="<?php echo $dtext; ?>" selected="selected"><?php echo $dtext; ?></option>
                                                                <?php } else { ?>
                                                                <option value="<?php echo $dtext; ?>"><?php echo $dtext; ?></option>
                                                                <?php } ?>
                                                                <?php } ?>
                                                            </select>
                                                    	</div>
                                                	</div>
                                                	<div class="panel-group">
                                                    	<label class="col-sm-4 control-label" for="input-custom_dimension7"><span data-toggle="tooltip" title="<?php echo $entry_custom_dimension7;?>"><?php echo $entry_custom_dimension7; ?></span></label>
                                                    	<div class="col-sm-4">
                                                    	    <select name="custom_dimension7" id="input-custom_dimension7" class="form-control">
                                                                <?php
                                                                foreach ($dimensions_index as $dindex) { ?>
                                                                <?php if ($dindex==$tagmanager['custom_dimension7']) { ?>
                                                                <option value="<?php echo $dindex; ?>" selected="selected"><?php echo $dindex; ?></option>
                                                                <?php } else { ?>
                                                                <option value="<?php echo $dindex; ?>"><?php echo $dindex; ?></option>
                                                                <?php } ?>
                                                                <?php } ?>
                                                            </select>
                                                    	</div>
                                                    	 <div class="col-sm-4">
                                                    	    <select name="custom_dimension7_text" id="input-custom_dimensiontext7" class="form-control">
                                                                <?php
                                                                foreach ($dimensions_text as $dtext) { ?>
                                                                <?php if ($dtext==$tagmanager['custom_dimension7_text']) { ?>
                                                                <option value="<?php echo $dtext; ?>" selected="selected"><?php echo $dtext; ?></option>
                                                                <?php } else { ?>
                                                                <option value="<?php echo $dtext; ?>"><?php echo $dtext; ?></option>
                                                                <?php } ?>
                                                                <?php } ?>
                                                            </select>
                                                    	</div>
                                                	</div>
                                                	<div class="panel-group">
                                                    	<label class="col-sm-4 control-label" for="input-custom_dimension8"><span data-toggle="tooltip" title="<?php echo $entry_custom_dimension8;?>"><?php echo $entry_custom_dimension8; ?></span></label>
                                                    	<div class="col-sm-4">
                                                    	    <select name="custom_dimension8" id="input-custom_dimension8" class="form-control">
                                                                <?php
                                                                foreach ($dimensions_index as $dindex) { ?>
                                                                <?php if ($dindex==$tagmanager['custom_dimension8']) { ?>
                                                                <option value="<?php echo $dindex; ?>" selected="selected"><?php echo $dindex; ?></option>
                                                                <?php } else { ?>
                                                                <option value="<?php echo $dindex; ?>"><?php echo $dindex; ?></option>
                                                                <?php } ?>
                                                                <?php } ?>
                                                            </select>
                                                    	</div>
                                                    	 <div class="col-sm-4">
                                                    	    <select name="custom_dimension8_text" id="input-custom_dimensiontext8" class="form-control">
                                                                <?php
                                                                foreach ($dimensions_text as $dtext) { ?>
                                                                <?php if ($dtext==$tagmanager['custom_dimension8_text']) { ?>
                                                                <option value="<?php echo $dtext; ?>" selected="selected"><?php echo $dtext; ?></option>
                                                                <?php } else { ?>
                                                                <option value="<?php echo $dtext; ?>"><?php echo $dtext; ?></option>
                                                                <?php } ?>
                                                                <?php } ?>
                                                            </select>
                                                    	</div>
                                                	</div>
                                                </div>
                                            </div>
    									</div>
									</div>
									<div id="tm_GA4" class="form-group">
				    					<label class="col-sm-4 control-label" for="input-ga4_status"><span data-toggle="tooltip" title="<?php echo $help_ga4;?>"><?php echo $entry_ga4_status;?></span></label>
										<div class="col-sm-8">
											<label class="switch">
												<input type="checkbox" id="tagmanager_ga4_status" name="ga4_status" <?php if ($tagmanager['ga4_status']) { echo 'checked';} ?>>
												<span class="slider round"></span>
											</label>
										</div>
										<div id="ga4" style="display:<?php if (!$tagmanager['ga4_status']) { echo 'none'; }?>">
    										<div class="panel-group">
    											<label class="col-sm-4 control-label" for="input-mid"><?php echo $entry_ga4_mid;?></label>
    											<div class="col-sm-8">
    												<input type="text" name="ga4_mid" placeholder="<?php echo $entry_ga4_mid;?>" class="form-control" value="<?php echo $tagmanager['ga4_mid'];?>"/>
    												<?php if (isset($error_ga4)) {?>
    												<div class="text-danger"><?php echo $error_ga4;?></div>
    												<?php }?>
    											</div>
    										</div>	
    										<div class="panel-group">
    											<label class="col-sm-4 control-label" for="input-api"><span data-toggle="tooltip" title="<?php echo $help_ga4_api;?>"><?php echo $entry_ga4_api;?></span></label>
    											<div class="col-sm-8">
    												<input type="text" name="ga4_api" placeholder="<?php echo $entry_ga4_api;?>" class="form-control" value="<?php echo $tagmanager['ga4_api'];?>"/>
    											</div>
    										</div>
    									</div>
									</div> 
                                    <div id="tm_ADWORDS" class="form-group">
                                        <label class="col-sm-4 control-label" for="input-adword"><span data-toggle="tooltip" title="<?php echo $help_aw;?>"><?php echo $entry_adword; ?></span></label>
                                        <div class="col-sm-8">
                                            <label class="switch"><input id="tagmanager_adword" type="checkbox" name="adword" <?php if ($tagmanager['adword']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                        </div>
                                        <div id="conversion" style="display:<?php if (!$tagmanager['adword']) { echo 'none'; }?>">
                                            <div id="conversion_id" class="panel-group">
                                                <label class="col-sm-4 control-label" for="input-cid"><span data-toggle="tooltip" title="<?php echo $help_conversion_id;?>"><?php echo $entry_conversion_id; ?></span></label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="conversion_id" placeholder="<?php echo $entry_conversion_id; ?>" class="form-control" value="<?php echo $tagmanager['conversion_id'];?>" />
                                                </div>
                                            </div>
                                            <div class="panel-group">
                                                <label class="col-sm-4 control-label" for="input-clabel"><span data-toggle="tooltip" title="<?php echo $help_conversion_label;?>"><?php echo $entry_conversion_label; ?></span></label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="conversion_label" placeholder="<?php echo $entry_conversion_label; ?>" class="form-control" value="<?php echo $tagmanager['conversion_label'];?>" />
                                                </div>
                                            </div>
                                            <div id="tm_ADWORDADD_EC" class="panel-group">
                    					    	<label class="col-sm-4 control-label" for="input-adwordec"><span data-toggle="tooltip" title="<?php echo $help_aw_ec;?>"><?php echo $entry_adword_ec;?></span></label>
                    							<div class="col-sm-8">
                    								<label class="switch">
                    									<input type="checkbox" id="tagmanager_aw_ec" name="adword_ec" <?php if ($tagmanager['adword_ec']) { echo 'checked'; }?>>
                    									<span class="slider round"></span>
                    								</label>
                    							</div>
                    						</div>
                                            <div id="tm_ADWORDADD" class="panel-group">
                    					    	<label class="col-sm-4 control-label" for="input-adwordopt"><span data-toggle="tooltip" title="<?php echo $help_aw_optional;?>"><?php echo $entry_aw_optional;?></span></label>
                    							<div class="col-sm-8">
                    								<label class="switch">
                    									<input type="checkbox" id="tagmanager_aw_optional" name="aw_optional" <?php if ($tagmanager['aw_optional']) { echo 'checked'; }?>>
                    									<span class="slider round"></span>
                    								</label>
                    							</div>
                    							<div id="aw_optional" style="display:<?php if (!$tagmanager['aw_optional']) { echo 'none'; }?>">
                    								<div class="panel-group">
                    									<label class="col-sm-4 control-label" for="input-aw_merchant"><span data-toggle="tooltip" title="<?php echo $help_aw_merchant;?>"><?php echo $entry_aw_merchant_id;?></span></label>
                    									<div class="col-sm-8">
                    										<input type="text" name="aw_merchant_id" placeholder="<?php echo $entry_aw_merchant_id;?>" class="form-control" value="<?php echo $tagmanager['aw_merchant_id'];?>"/>
                    									</div>
                    								</div>
                    								<div class="panel-group">
                    									<label class="col-sm-4 control-label" for="input-aw_country"><span data-toggle="tooltip" title="<?php echo $help_aw_country;?>"><?php echo $entry_aw_feed_country;?></span></label>
                    									<div class="col-sm-2">
                    										<input type="text" name="aw_feed_country" placeholder="eg: US, UK, GR" class="form-control" value="<?php echo $tagmanager['aw_feed_country'];?>"/>
                    									</div>
                    										<label class="col-sm-3 control-label" for="input-aw_language"><span data-toggle="tooltip" title="<?php echo $help_aw_language;?>"><?php echo $entry_aw_feed_language;?></span></label>
                    									<div class="col-sm-3">
                    										<input type="text" name="aw_feed_language" placeholder="eg: EN, FR, GR" class="form-control" value="<?php echo $tagmanager['aw_feed_language'];?>"/>
                    									</div>
                    								</div>
                    							</div>
                    						</div>
                    						<div id="tm_ADWORD2" class="panel-group">
                    				    	<label class="col-sm-4 control-label" for="input-adword2"><span data-toggle="tooltip" title="<?php echo $help_aw_secondary;?>"><?php echo $entry_adword2; ?></span></label>
                    						<div class="col-sm-8">
                    							<label class="switch">
                    								<input type="checkbox" id="tagmanager_adword2" name="adword2" <?php if ($tagmanager['adword2']) { echo 'checked'; }?>>
                    								<span class="slider round"></span>
                    							</label>
                    						</div>
                    						<div id="conversion2" style="display:<?php if (!$tagmanager['adword2']) { echo 'none'; }?>">
                    							<div class="panel-group">
                    								<label class="col-sm-4 control-label" for="input-cid2"><span data-toggle="tooltip" title="<?php echo $help_conversion_id;?>"><?php echo $entry_conversion_id2; ?></span></label>
                    								<div class="col-sm-8">
                    								      <input type="text" name="conversion_id2" placeholder="<?php echo $entry_conversion_id2; ?>" class="form-control" value="<?php echo $tagmanager['conversion_id2'];?>"/>
                    								</div>
                    							</div>
                    							<div class="panel-group">
                    								<label class="col-sm-4 control-label" for="input-clabel2"><span data-toggle="tooltip" title="<?php echo $help_conversion_label;?>"><?php echo $entry_conversion_label2; ?></span></label>
                    								<div class="col-sm-8">
                    									<input type="text" name="conversion_label2" placeholder="<?php echo $entry_conversion_label2; ?>" class="form-control" value="<?php echo $tagmanager['conversion_label2'];?>"/>
                    								</div>
                    							</div> 
                    							<div class="panel-group">
                    								<label class="col-sm-4 control-label" for="input-route22"><span data-toggle="tooltip" title="<?php echo $help_aw_route;?>"><?php echo $entry_conversion_route2;?></span></label>
                    								<div class="col-sm-3">
                    									<select name="conversion_route2" id="input-tagmanager_conversion_route2" class="form-control">
                    							    	<?php
                                                         foreach ($page_routes as $result) { ?>
                                                         <?php if ($result==$tagmanager['conversion_route2']) { ?>
                                                            <option value="<?php echo $result; ?>" selected="selected"><?php echo $result; ?></option>
                                                            <?php } else { ?>
                                                            <option value="<?php echo $result; ?>"><?php echo $result; ?></option>
                                                            <?php } ?>
                                                            <?php } ?>
                    						      		</select>
                    						      	</div>
                    						      	<div class="col-sm-5">
                    						      		<label class="col-sm-6 control-label" for="input-cvalue2"><span data-toggle="tooltip" title="<?php echo $help_conversion_value2;?>"><?php echo $entry_conversion_value2;?></span></label>
                    									<div class="col-sm-6">
                    										<input type="text" name="conversion_value2" placeholder="<?php echo $entry_conversion_value2;?>" class="form-control" value="<?php echo $tagmanager['conversion_value2'];?>"/>
                    									</div>
                    								</div>
                    					      	</div>
                    						</div>
                    					</div>
                                        </div>
                                                                                
                                    </div>
                                    <div id="tm_REMARKITING" class="form-group">
                                        <label class="col-sm-4 control-label" for="input-adword"><span data-toggle="tooltip" title="<?php echo $help_remarketing;?>"><?php echo $entry_remarketing; ?></span></label>
                                        <div class="col-sm-8">
                                            <label class="switch"><input id="tagmanager_remarketing" type="checkbox" name="remarketing" <?php if ($tagmanager['remarketing']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                        </div>
                                    </div>
                                    <div id="tm_CUSTOMPARA" class="form-group">
                                        <label class="col-sm-4 control-label" for="input-adscustom"><span data-toggle="tooltip" title="<?php echo $help_custom;?>"><?php echo $entry_custom; ?></span></label>
                                        <div class="col-sm-8">
                                            <label class="switch"><input id="tagmanager_custom" type="checkbox" name="custom" <?php if ($tagmanager['custom']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                        </div>
                                        <div id="customfields" style="display:<?php if (!$tagmanager['custom']) { echo 'none'; }?>">
                                            <div class="panel-group">
                                                <label class="col-sm-4 control-label" for="input-dynx_itemid2">Selcet the required custom parameters</label>
                                                <div class="col-sm-8">
                                                    <label class="chkbox"><input type="checkbox" name="dynx_itemid" <?php if ($tagmanager['dynx_itemid']) { echo 'checked'; }?>><span class="chklabel"><?php echo $entry_dynx_itemid; ?></span></label>
                                                    <label class="chkbox"><input type="checkbox" name="dynx_itemid2" <?php if ($tagmanager['dynx_itemid2']) { echo 'checked'; }?>><span class="chklabel"><?php echo $entry_dynx_itemid2; ?></span></label>
                                                    <label class="chkbox"><input type="checkbox" name="dynx_pagetype" <?php if ($tagmanager['dynx_pagetype']) { echo 'checked'; }?>><span class="chklabel"><?php echo $entry_dynx_pagetype; ?></span></label>
                                                    <label class="chkbox"><input type="checkbox" name="dynx_totalvalue" <?php if ($tagmanager['dynx_totalvalue']) { echo 'checked'; }?>><span class="chklabel"><?php echo $entry_dynx_totalvalue; ?></span></label>
                                                    <br />
                                                    <label class="chkbox"><input type="checkbox" name="ecomm_pagetype" <?php if ($tagmanager['ecomm_pagetype']) { echo 'checked'; }?>><span class="chklabel"><?php echo $entry_ecomm_pagetype; ?></span></label>
                                                    <label class="chkbox"><input type="checkbox" name="ecomm_totalvalue" <?php if ($tagmanager['ecomm_totalvalue']) { echo 'checked'; }?>><span class="chklabel"><?php echo $entry_ecomm_totalvalue; ?></span></label>
                                                    <label class="chkbox"><input type="checkbox" name="ecomm_prodid" <?php if ($tagmanager['ecomm_prodid']) { echo 'checked'; }?>><span class="chklabel"><?php echo $entry_ecomm_prodid; ?></span></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="tm_OPTIMIZE" class="form-group">
                                            <label class="col-sm-4 control-label" for="input-goptimizestatus"><?php echo $entry_google_optimize_status; ?></span></label>
                                        <div class="col-sm-8">
                                            <label class="switch"><input id="optimize" type="checkbox" name="google_optimize_status" <?php if ($tagmanager['google_optimize_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                        </div>
                                        <div id="optimizeid" style="display:<?php if (!$tagmanager['google_optimize']) { echo 'none'; }?>">
                                            <div class="panel-group">
                                                <label class="col-sm-4 control-label" for="input-goptimize"><?php echo $entry_google_optimize; ?></label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="google_optimize" placeholder="<?php echo $entry_google_optimize; ?>" class="form-control" value="<?php echo $tagmanager['google_optimize'];?>" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="tm_REVIEW" class="form-group">
                                            <label class="col-sm-4 control-label" for="input-greviewstatus"><?php echo $entry_greview; ?></span></label>
                                        <div class="col-sm-8">
                                            <label class="switch"><input id="greview" type="checkbox" name="greview" <?php if ($tagmanager['greview']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                        </div>
                                        <div id="greviewid" style="display:<?php if (!$tagmanager['greview']) { echo 'none'; }?>">
                                            <div class="panel-group">
                                                <label class="col-sm-4 control-label" for="input-merchant_id"><?php echo $entry_merchant_id; ?></label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="merchant_id" placeholder="<?php echo $entry_merchant_id; ?>" class="form-control" value="<?php echo $tagmanager['merchant_id'];?>" />
                                                </div>
                                            </div>
                                            <div class="panel-group">
                                                <label class="col-sm-4 control-label" for="input-greview_badge"><?php echo $entry_greview_badge; ?></span></label>
                                            <div class="col-sm-8">
                                                <label class="switch"><input id="greview_badge" type="checkbox" name="greview_badge" <?php if ($tagmanager['greview_badge']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                            </div>
                                        </div>
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
<!-- TAB POPULAR -->
                        <div class="tab-pane" id="tab-tab3">
                            <div class="col-sm-8">
                                <div id="tm_PIXEL" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-pixel"><?php echo $entry_pixel; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_pixel" type="checkbox" name="pixel" <?php if ($tagmanager['pixel']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="pixel" style="display:<?php if (!$tagmanager['pixel']) { echo 'none'; }?>">
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-pixelcode"><?php echo $entry_pixelcode; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="pixelcode" placeholder="<?php echo $entry_pixelcode; ?>" class="form-control" value="<?php echo $tagmanager['pixelcode'];?>" />
                                            </div>
                                        </div>
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-fb_api"><?php echo $entry_fb_api; ?></label>
                                            <div class="col-sm-8">
                                                <label class="switch"><input id="tagmanager_fb_api" type="checkbox" name="fb_api" <?php if ($tagmanager['fb_api']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                            </div>
                                        </div>
                                        <div id="fbapi" style="display:<?php if (!$tagmanager['fb_api']) { echo 'none'; }?>">
                                            <div class="panel-group">
                                                <label class="col-sm-4 control-label" for="input-fb_token"><?php echo $entry_fb_token; ?></label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="fb_token" placeholder="<?php echo $entry_fb_token; ?>" class="form-control" value="<?php echo $tagmanager['fb_token'];?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-fb_catalog_id"><?php echo $entry_fb_catalog_id; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="fb_catalog_id" placeholder="<?php echo $entry_fb_catalog_id; ?>" class="form-control" value="<?php echo $tagmanager['fb_catalog_id'];?>" />
                                            </div>
                                        </div>
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-alt-curr-statys"><?php echo $entry_alt_currency_status; ?></label>
                                            <div class="col-sm-8">
                                                <label class="switch"><input id="tagmanager_alt_currency" type="checkbox" name="alt_currency_status" <?php if ($tagmanager['alt_currency_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                            </div>
                                        </div>
                                        <div id="altcurrency" style="display:<?php if (!$tagmanager['alt_currency_status']) { echo 'none'; }?>">
                                            <div class="panel-group">
                                                <label class="col-sm-4 control-label" for="input-alt-curr"><span data-toggle="tooltip" title="<?php echo $help_ac;?>"><?php echo $entry_alt_currency; ?></span></label>
                                                <div class="col-sm-8">
                                                    <select name="alt_currency" id="input-alt-curr" class="form-control">
                                                        <?php foreach ($currencies as $curr) { ?>
                                                        <option value="<?php echo $curr['code']; ?>" <?php echo ($tagmanager['alt_currency']==$curr['code'] ? 'selected="selected"' : '');?>><?php echo $curr['title'];?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="tm_SNAP" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-snap_status"><?php echo $entry_snap_pixel_status; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_snap_pixel_status" type="checkbox" name="snap_pixel_status" <?php if ($tagmanager['snap_pixel_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="snapchat" style="display:<?php if (!$tagmanager['snap_pixel_status']) { echo 'none'; }?>">
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-snap_pixelid"><?php echo $entry_snap_pixel_id; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="snap_pixel_id" placeholder="<?php echo $entry_snap_pixel_id; ?>" class="form-control" value="<?php echo $tagmanager['snap_pixel_id'];?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="tm_TWITER" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-twitter_status"><?php echo $entry_twitter_status; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_twitter_status" type="checkbox" name="twitter_status" <?php if ($tagmanager['twitter_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="twitter" style="display:<?php if (!$tagmanager['twitter_status']) { echo 'none'; }?>">
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-twitterid"><?php echo $entry_twitter_tag; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="twitter_tag" placeholder="<?php echo $entry_twitter_tag; ?>" class="form-control" value="<?php echo $tagmanager['twitter_tag'];?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="tm_PINTEREST" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-pinterest_status"><?php echo $entry_pinterest_status; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_pinterest_status" type="checkbox" name="pinterest_status" <?php if ($tagmanager['pinterest_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="pinterest" style="display:<?php if (!$tagmanager['pinterest_status']) { echo 'none'; }?>">
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-pinterestid"><?php echo $entry_pinterest_tag; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="pinterest_tag" placeholder="<?php echo $entry_pinterest_tag; ?>" class="form-control" value="<?php echo $tagmanager['pinterest_tag'];?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="tm_PAYPAL" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-paypal_status"><?php echo $entry_paypal_status; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_paypal_status" type="checkbox" name="paypal_status" <?php if ($tagmanager['paypal_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="paypal" style="display:<?php if (!$tagmanager['paypal_status']) { echo 'none'; }?>">
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-paypalid"><?php echo $entry_paypal_code; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="paypal_code" placeholder="<?php echo $entry_paypal_code; ?>" class="form-control" value="<?php echo $tagmanager['paypal_code'];?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="tm_HOTJAR" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-hotjar_status"><?php echo $entry_hotjar_status; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_hotjar_status" type="checkbox" name="hotjar_status" <?php if ($tagmanager['hotjar_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="hotjar" style="display:<?php if (!$tagmanager['hotjar_status']) { echo 'none'; }?>">
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-hotjar_siteid"><?php echo $entry_hotjar_siteid; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="hotjar_siteid" placeholder="<?php echo $entry_hotjar_siteid; ?>" class="form-control" value="<?php echo $tagmanager['hotjar_siteid'];?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="tm_LUCKYORANGE" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-luckyorange_status"><?php echo $entry_luckyorange_status; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_luckyorange_status" type="checkbox" name="luckyorange_status" <?php if ($tagmanager['luckyorange_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="luckyorange" style="display:<?php if (!$tagmanager['luckyorange_status']) { echo 'none'; }?>">
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-luckyorange_siteid"><?php echo $entry_luckyorange_siteid; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="luckyorange_siteid" placeholder="<?php echo $entry_luckyorange_siteid; ?>" class="form-control" value="<?php echo $tagmanager['luckyorange_siteid'];?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="tm_SKROUTZ" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-skroutz_status"><?php echo $entry_skroutz_status; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_skroutz_status" type="checkbox" name="skroutz_status" <?php if ($tagmanager['skroutz_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="skroutz" style="display:<?php if (!$tagmanager['skroutz_status']) { echo 'none'; }?>">
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-skroutz_siteid"><?php echo $entry_skroutz_siteid; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="skroutz_siteid" placeholder="<?php echo $entry_skroutz_siteid; ?>" class="form-control" value="<?php echo $tagmanager['skroutz_siteid'];?>" />
                                            </div>
                                        </div>
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-skroutz_manual_tax"><?php echo $entry_skroutz_manual_tax; ?></label>
                                            <div class="col-sm-8">
                                                <label class="switch"><input id="tagmanager_skroutz_manual_tax" type="checkbox" name="skroutz_manual_tax" <?php if ($tagmanager['skroutz_manual_tax']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                            </div>
                                        </div>
                                        <div id="skroutztax" style="display:<?php if (!$tagmanager['skroutz_manual_tax']) { echo 'none'; }?>">
                                            <div class="panel-group">
                                                <label class="col-sm-4 control-label" for="input-skroutz_manual_tax_value"><?php echo $entry_skroutz_manual_tax_value; ?></label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="skroutz_manual_tax_value" placeholder="<?php echo $entry_skroutz_manual_tax_value; ?>" class="form-control" value="<?php echo $tagmanager['skroutz_manual_tax_value'];?>" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="tm_GLAMI" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-glami_status"><?php echo $entry_glami_status; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_glami_status" type="checkbox" name="glami_status" <?php if ($tagmanager['glami_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="glami" style="display:<?php if (!$tagmanager['glami_status']) { echo 'none'; }?>">
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-glami_code"><?php echo $entry_glami_code; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="glami_code" placeholder="<?php echo $entry_glami_code; ?>" class="form-control" value="<?php echo $tagmanager['glami_code'];?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="tm_YANDEX" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-yandex_status"><?php echo $entry_yandex_status; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_yandex_status" type="checkbox" name="yandex_status" <?php if ($tagmanager['yandex_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="yandex" style="display:<?php if (!$tagmanager['yandex_status']) { echo 'none'; }?>">
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-yandex_code"><?php echo $entry_yandex_code; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="yandex_code" placeholder="<?php echo $entry_yandex_code; ?>" class="form-control" value="<?php echo $tagmanager['yandex_code'];?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="tm_CLARITY" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-clarity_status"><?php echo $entry_clarity_status; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_clarity_status" type="checkbox" name="clarity_status" <?php if ($tagmanager['clarity_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="clarity" style="display:<?php if (!$tagmanager['clarity_status']) { echo 'none'; }?>">
                                        <div class="form-group">
                                            <label class="col-sm-4 control-label" for="input-clarity_siteid"><?php echo $entry_clarity_siteid; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="clarity_id" placeholder="<?php echo $entry_clarity_siteid; ?>" class="form-control" value="<?php echo $tagmanager['clarity_id'];?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="tm_BING" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-bing_status"><?php echo $entry_bing_status; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_bing_status" type="checkbox" name="bing_status" <?php if ($tagmanager['bing_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="bing" style="display:<?php if (!$tagmanager['bing_status']) { echo 'none'; }?>">
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-bing_uetid"><?php echo $entry_bing_uetid; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="bing_uetid" placeholder="<?php echo $entry_bing_uetid; ?>" class="form-control" value="<?php echo $tagmanager['bing_uetid'];?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="tm_TIKTOK" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-tiktok_status"><?php echo $entry_tiktok_status; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_tiktok" type="checkbox" name="tiktok_status" <?php if ($tagmanager['tiktok_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="tiktok" style="display:<?php if (!$tagmanager['tiktok_status']) { echo 'none'; }?>">
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-tiktok-code"><?php echo $entry_tiktok_code; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="tiktok_code" placeholder="<?php echo $entry_tiktok_code; ?>" class="form-control" value="<?php echo $tagmanager['tiktok_code'];?>" />
                                            </div>
                                        </div>
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
<!-- TAB AFFILIATE -->
                        <div class="tab-pane" id="tab-tab4">
                            <div class="col-sm-8">
                                <div id="tm_ADMITAD" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-admitad_status"><?php echo $entry_admitad_status; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_admitad" type="checkbox" name="admitad_status" <?php if ($tagmanager['admitad_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="admitad" style="display:<?php if (!$tagmanager['admitad_status']) { echo 'none'; }?>">
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-admitad_code"><?php echo $entry_admitad_code; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="admitad_code" placeholder="<?php echo $entry_admitad_code; ?>" class="form-control" value="<?php echo $tagmanager['admitad_code'];?>" />
                                            </div>
                                        </div>
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-admitad_category"><?php echo $entry_admitad_category; ?></label>
                                            <div class="col-sm-8"><?php if ($tagmanager['admitad_category'] == '') { $tagmanager['admitad_category'] =1; }?>
                                                <input type="text" name="admitad_category" placeholder="<?php echo $entry_admitad_category; ?>" class="form-control" value="<?php echo $tagmanager['admitad_category'];?>" />
                                            </div>
                                        </div>
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-admitad_additional_type"><?php echo $entry_admitad_additional_type; ?></label>
                                            <div class="col-sm-8"><?php if ($tagmanager['admitad_additional_type'] == '') { $tagmanager['admitad_additional_type'] ='sale'; }?>
                                                <input type="text" name="admitad_additional_type" placeholder="<?php echo $entry_admitad_additional_type; ?>" class="form-control" value="<?php echo $tagmanager['admitad_additional_type'];?>"  />
                                            </div>
                                        </div>
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-admitad_invoice_broker"><?php echo $entry_admitad_invoice_broker; ?></label>
                                            <div class="col-sm-8"><?php if ($tagmanager['admitad_invoice_broker'] == '') { $tagmanager['admitad_invoice_broker'] ='adm'; }?>
                                                <input type="text" name="admitad_invoice_broker" placeholder="<?php echo $entry_admitad_invoice_broker; ?>" class="form-control" value="<?php echo $tagmanager['admitad_invoice_broker'];?>"  />
                                            </div>
                                        </div>
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-admitad_invoice_category"><?php echo $entry_admitad_invoice_category; ?></label>
                                            <div class="col-sm-8"><?php if ($tagmanager['admitad_invoice_category'] == '') { $tagmanager['admitad_invoice_category'] =1; }?>
                                                <input type="text" name="admitad_invoice_category" placeholder="<?php echo $entry_admitad_invoice_category; ?>" class="form-control" value="<?php echo $tagmanager['admitad_invoice_category'];?>" />
                                            </div>
                                        </div>
                                        <div class="panel-group">
                                    		<label class="col-sm-4 control-label" for="input-retag__status"><?php echo $entry_admitad_retag_status; ?></label>
                                    		<div class="col-sm-8">
                                        		<label class="switch"><input id="tagmanager_retag" type="checkbox" name="admitad_retag_status" <?php if ($tagmanager['admitad_retag_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    		</div>
                                    	    <div id="retag" style="display:<?php if (!$tagmanager['admitad_retag_status']) { echo 'none'; }?>">
                                        		<div class="panel-group">
                                            		<label class="col-sm-4 control-label" for="input-admitad_retag_code1"><?php echo $entry_admitad_retag_code1; ?></label>
                                            		<div class="col-sm-8">
                                                		<input type="text" name="admitad_retag_code1" placeholder="<?php echo $entry_admitad_retag_code1; ?>" class="form-control" value="<?php echo $tagmanager['admitad_retag_code1'];?>" />
                                            		</div>
                                       	 		</div>
                                       	 		<div class="panel-group">
                                            		<label class="col-sm-4 control-label" for="input-admitad_retag_code2"><?php echo $entry_admitad_retag_code2; ?></label>
                                            		<div class="col-sm-8">
                                                		<input type="text" name="admitad_retag_code2" placeholder="<?php echo $entry_admitad_retag_code2; ?>" class="form-control" value="<?php echo $tagmanager['admitad_retag_code2'];?>" />
                                            		</div>
                                       	 		</div>
                                       	 		<div class="panel-group">
                                            		<label class="col-sm-4 control-label" for="input-admitad_retag_code3"><?php echo $entry_admitad_retag_code3; ?></label>
                                            		<div class="col-sm-8">
                                                		<input type="text" name="admitad_retag_code3" placeholder="<?php echo $entry_admitad_retag_code3; ?>" class="form-control" value="<?php echo $tagmanager['admitad_retag_code3'];?>" />
                                            		</div>
                                       	 		</div>
                                       	 		<div class="panel-group">
                                            		<label class="col-sm-4 control-label" for="input-admitad_retag_code4"><?php echo $entry_admitad_retag_code4; ?></label>
                                            		<div class="col-sm-8">
                                                		<input type="text" name="admitad_retag_code4" placeholder="<?php echo $entry_admitad_retag_code4; ?>" class="form-control" value="<?php echo $tagmanager['admitad_retag_code4'];?>" />
                                            		</div>
                                       	 		</div>
                                       	 		<div class="panel-group">
                                            		<label class="col-sm-4 control-label" for="input-admitad_retag_code5"><?php echo $entry_admitad_retag_code5; ?></label>
                                            		<div class="col-sm-8">
                                                		<input type="text" name="admitad_retag_code5" placeholder="<?php echo $entry_admitad_retag_code5; ?>" class="form-control" value="<?php echo $tagmanager['admitad_retag_code5'];?>" />
                                            		</div>
                                       	 		</div>
                                       		</div>
                                       	</div>
                                    </div>
                                </div>
                                <div id="tm_AFFILIATEGATEWAY" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-affgateway_status"><?php echo $entry_affgateway_status; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_affgateway_status" type="checkbox" name="affgateway_status" <?php if ($tagmanager['affgateway_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="affgateway" style="display:<?php if (!$tagmanager['affgateway_status']) { echo 'none'; }?>">
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-affgateway_code"><?php echo $entry_affgateway_code; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="affgateway_code" placeholder="<?php echo $entry_affgateway_code; ?>" class="form-control" value="<?php echo $tagmanager['affgateway_code'];?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="tm_SENDINBLUE" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-sendinblue_status"><?php echo $entry_sendinblue_status; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_sendinblue_status" type="checkbox" name="sendinblue_status" <?php if ($tagmanager['sendinblue_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="sendinblue" style="display:<?php if (!$tagmanager['sendinblue_status']) { echo 'none'; }?>">
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-sendinblue_code"><?php echo $entry_sendinblue_code; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="sendinblue_code" placeholder="<?php echo $entry_sendinblue_code; ?>" class="form-control" value="<?php echo $tagmanager['sendinblue_code'];?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="tm_LINKWISE" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-linkwise_status"><?php echo $entry_linkwise_status; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_linkwise_status" type="checkbox" name="linkwise_status" <?php if ($tagmanager['linkwise_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="linkwise" style="display:<?php if (!$tagmanager['linkwise_status']) { echo 'none'; }?>">
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-linkwise_code"><?php echo $entry_linkwise_code; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="linkwise_code" placeholder="<?php echo $entry_linkwise_code; ?>" class="form-control" value="<?php echo $tagmanager['linkwise_code'];?>" />
                                            </div>
                                        </div>
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-linkwise_decimal"><?php echo $entry_linkwise_decimal; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="linkwise_decimal" placeholder="<?php echo $entry_linkwise_decimal; ?>" class="form-control" value="<?php echo $tagmanager['linkwise_decimal'];?>" />
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                                <div id="tm_2PERFORMANT" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-performant_status"><?php echo $entry_performant_status; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_performant" type="checkbox" name="performant_status" <?php if ($tagmanager['performant_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="performant" style="display:<?php if (!$tagmanager['performant_status']) { echo 'none'; }?>">
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-performant_code"><?php echo $entry_performant_code; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="performant_code" placeholder="<?php echo $entry_performant_code; ?>" class="form-control" value="<?php echo $tagmanager['performant_code'];?>" />
                                            </div>
                                        </div>
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-performant_confirm"><?php echo $entry_performant_confirm; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="performant_confirm" placeholder="<?php echo $entry_performant_confirm; ?>" class="form-control" value="<?php echo $tagmanager['performant_confirm'];?>" />
                                            </div>
                                        </div>
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-performant_manual_tax"><?php echo $entry_performant_tax; ?></label>
                                            <div class="col-sm-8">
                                                <label class="switch"><input id="tagmanager_performant_tax" type="checkbox" name="performant_tax" <?php if ($tagmanager['performant_tax']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                            </div>
                                        </div>
                                        <div id="performanttax" style="display:<?php if (!$tagmanager['performant_tax']) { echo 'none'; }?>">
                                            <div class="panel-group">
                                                <label class="col-sm-4 control-label" for="input-performant_tax_value"><?php echo $entry_performant_tax_value; ?></label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="performant_tax_value" placeholder="<?php echo $entry_performant_tax_value; ?>" class="form-control" value="<?php echo $tagmanager['performant_tax_value'];?>" />
                                                </div>
                                            </div>
                                        </div>
                                         <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-per-curr-statys"><?php echo $entry_alt_currency_status; ?></label>
                                            <div class="col-sm-8">
                                                <label class="switch"><input id="tagmanager_performant_currency" type="checkbox" name="performant_currency_status" <?php if ($tagmanager['performant_currency_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                            </div>
                                        </div>
                                        <div id="performantcurrency" style="display:<?php if (!$tagmanager['performant_currency_status']) { echo 'none'; }?>">
                                            <div class="panel-group">
                                                <label class="col-sm-4 control-label" for="input-per-curr"><span data-toggle="tooltip" title="<?php echo $help_ac;?>"><?php echo $entry_alt_currency; ?></span></label>
                                                <div class="col-sm-8">
                                                    <select name="performant_currency" id="input-perc-curr" class="form-control">
                                                        <?php foreach ($currencies as $curr) { ?>
                                                        <option value="<?php echo $curr['code']; ?>" <?php echo ($tagmanager['performant_currency']==$curr['code'] ? 'selected="selected"' : '');?>><?php echo $curr['title'];?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
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
<!-- TAB COOKIE -->
                        <div class="tab-pane" id="tab-tab5">
                            <div class="col-sm-8">

                                <div class="form-group">
                                    <label class="col-sm-4 control-label" for="input-eu"><?php echo $entry_eu_cookie; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_cookie" type="checkbox" name="eu_cookie" <?php if ($tagmanager['eu_cookie']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                </div>

                                <div id="cookie" style="display:<?php if (!$tagmanager['eu_cookie']) { echo 'none'; }?>">
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label" for="input-eucookie_enforce"><span data-toggle="tooltip" title="<?php echo $help_cenforce;?>"><?php echo $entry_eu_cookie_enforce; ?></span></label>
                                        <div class="col-sm-8">
                                            <label class="switch"><input id="tagmanager_eu_cookie_enforce" type="checkbox" name="eu_cookie_enforce" <?php if ($tagmanager['eu_cookie_enforce']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-4 control-label" for="input-cookieposition"><?php echo $entry_cookie_position; ?></label>
                                        <div class="col-sm-8">
                                            <select name="cookie_position" id="input-cookie_position" class="form-control">
                                                <?php
                                                foreach ($cookie_positions as $cposition) { ?>
                                                <?php if ($cposition==$tagmanager['cookie_position']) { ?>
                                                <option value="<?php echo $cposition; ?>" selected="selected"><?php echo $cposition; ?></option>
                                                <?php } else { ?>
                                                <option value="<?php echo $cposition; ?>"><?php echo $cposition; ?></option>
                                                <?php } ?>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label" for="input-bg_popup"><?php echo $entry_cookie_bg_popup; ?></label>
                                        <div class="col-sm-8">
                                            <div id="color1" data-format="alias" class="input-group colorpicker-component">
                                                <input type="text" name="cookie_bg_popup" placeholder="<?php echo $entry_cookie_bg_popup; ?>" class="form-control" value="<?php echo $tagmanager['cookie_bg_popup'];?>" />
                                                <span class="input-group-addon"><i></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label" for="input-text_popup"><?php echo $entry_cookie_text_popup; ?></label>
                                        <div class="col-sm-8">
                                            <div id="color2" data-format="alias" class="input-group colorpicker-component">
                                                <input type="text" name="cookie_text_popup" placeholder="<?php echo $entry_cookie_text_popup; ?>" class="form-control" value="<?php echo $tagmanager['cookie_text_popup'];?>" />
                                                <span class="input-group-addon"><i></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label" for="input-bg_button"><?php echo $entry_cookie_bg_button; ?></label>
                                        <div class="col-sm-8">
                                            <div id="color3" data-format="alias" class="input-group colorpicker-component">
                                                <input type="text" name="cookie_bg_button" placeholder="<?php echo $entry_cookie_bg_button; ?>" class="form-control" value="<?php echo $tagmanager['cookie_bg_button'];?>" />
                                                <span class="input-group-addon"><i></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label" for="input-text_button"><?php echo $entry_cookie_text_button; ?></label>
                                        <div class="col-sm-8">
                                            <div id="color4" data-format="alias" class="input-group colorpicker-component">
                                                <input type="text" name="cookie_text_button" placeholder="<?php echo $entry_cookie_text_button; ?>" class="form-control" value="<?php echo $tagmanager['cookie_text_button'];?>" />
                                                <span class="input-group-addon"><i></i></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label" for="input-cookie_heading_color"><?php echo $entry_cookie_heading_color; ?></label>
                                        <div class="col-sm-8">
                                            <div id="color5" data-format="alias" class="input-group colorpicker-component">
                                                <input type="text" name="cookie_heading_color" placeholder="<?php echo $entry_cookie_heading_color; ?>" class="form-control" value="<?php echo $tagmanager['cookie_heading_color'];?>" />
                                                <span class="input-group-addon"><i></i></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-4 control-label" for="input-cookie_badge"><?php echo $entry_cookie_badge; ?></label>
                                        <div class="col-sm-8">
                                            <label class="switch"><input id="tagmanager_badge" type="checkbox" name="cookie_badge" <?php if ($tagmanager['cookie_badge']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                        </div>
                                    </div>
                                    <div id="badge" style="display:<?php if (!$tagmanager['cookie_badge']) { echo 'none'; }?>">
                                        <div class="form-group">
                                            <label class="col-sm-4 control-label" for="input-cookie_badge_position"><?php echo $entry_cookie_badge_position; ?></label>
                                            <div class="col-sm-8">
                                                <select name="cookie_badge_position" id="input-cookie_badge_position" class="form-control">
                                                    <?php
                                                    foreach ($badge_positions as $cposition) { ?>
                                                    <?php if ($cposition==$tagmanager['cookie_badge_position']) { ?>
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
                                                <div id="color6" data-format="alias" class="input-group colorpicker-component">
                                                    <input type="text" id="color6" name="cookie_badge_color" placeholder="<?php echo $entry_cookie_badge_color; ?>" class="form-control" value="<?php echo $tagmanager['cookie_badge_color'];?>" />
                                                    <span class="input-group-addon"><i></i></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <ul class="nav nav-tabs">
						            <?php 
						                $active=true; 
						                $li_text = '';
						                foreach ($languages as $language) { 
						                    if ($active) {
						                        $li_text = ' class="active"';
						                        $active = false;
						                    } else {
						                        $li_text = '';
						                    }
						                ?>
							            <li <?php echo $li_text;?>><a href="#tab-language-<?php echo  $language['code']; ?>" data-toggle="tab"><img src="<?php echo $language['image']; ?>" alt="<?php echo $language['name']; ?>"/> <?php echo $language['name']; ?></a></li>
					                    <?php } ?>
				                	</ul>
				                	<div class="tab-content">
					            	    <?php $active=false; foreach ($languages as $language) { ?>
							            <div id="tab-language-<?php echo $language['code']; ?>" class="tab-pane<?php if(!$active) {echo ' active'; $active=true;} ?>">
    							            <div class="form-group">
                                                <label class="col-sm-4 control-label" for="input-cookie_title"><span data-toggle="tooltip" title="<?php echo $help_ctitle;?>"><?php echo $entry_cookie_title; ?></span></label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="cookie_title_<?php echo $language['language_id']; ?>" placeholder="<?php echo $entry_cookie_title; ?>" class="form-control" value="<?php echo $tagmanager['cookie_title_'.$language['language_id']];?>" />
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label" for="input-cookie_text"><?php echo $entry_cookie_text; ?></label>
                                                <div class="col-sm-8">
                                                    <textarea rows="4" cols="70%" style="width:100%" name="cookie_text_<?php echo $language['language_id']; ?>"><?php if (!empty($tagmanager['cookie_text_'.$language['language_id']])) { echo $tagmanager['cookie_text_'.$language['language_id']]; }?></textarea>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label" for="input-cookie_text2"><span data-toggle="tooltip" title="<?php echo $help_ctext;?>"><?php echo $entry_cookie_text2; ?></span></label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="cookie_text2_<?php echo $language['language_id']; ?>" placeholder="<?php echo $entry_cookie_text2; ?>" class="form-control" value="<?php echo $tagmanager['cookie_text2_'.$language['language_id']];?>" />
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label" for="input-cookie_button3"><?php echo $entry_cookie_button3; ?></label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="cookie_button3_<?php echo $language['language_id']; ?>" placeholder="<?php echo $entry_cookie_button3; ?>" class="form-control" value="<?php echo $tagmanager['cookie_button3_'.$language['language_id']];?>" />
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label" for="input-cid"><span data-toggle="tooltip" title="<?php echo $help_clink;?>"><?php echo $entry_cookie_link; ?></span></label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="cookie_link_<?php echo $language['language_id']; ?>" placeholder="<?php echo $entry_cookie_link; ?>" class="form-control" value="<?php echo $tagmanager['cookie_link_'.$language['language_id']];?>" />
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label" for="input-cid"><?php echo $entry_cookie_button1; ?></label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="cookie_button1_<?php echo $language['language_id']; ?>" placeholder="<?php echo $entry_cookie_button1; ?>" class="form-control" value="<?php echo $tagmanager['cookie_button1_'.$language['language_id']];?>" />
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="col-sm-4 control-label" for="input-cid"><?php echo $entry_cookie_button2; ?></label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="cookie_button2_<?php echo $language['language_id']; ?>" placeholder="<?php echo $entry_cookie_button2; ?>" class="form-control" value="<?php echo $tagmanager['cookie_button2_'.$language['language_id']];?>" />
                                                </div>
                                            </div>
							            </div>
							            <?php }?>
							        </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <h3>EU / GDPR Cookie Consent</h3>
                                <?php echo $text_about_cookie; ?>
                            </div>
                        </div>
<!-- TAB ADD ONS -->
                        <div class="tab-pane" id="tab-tab6">
                            <div class="col-sm-8">
                                <div id="tm_ZENCHAT" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-zenchat_status"><?php echo $entry_zenchat_status; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_zenchat_status" type="checkbox" name="zenchat_status" <?php if ($tagmanager['zenchat_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="zenchat" style="display:<?php if (!$tagmanager['zenchat_status']) { echo 'none'; }?>">
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-zenchat_code"><?php echo $entry_zenchat_code; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="zenchat_code" placeholder="<?php echo $entry_zenchat_code; ?>" class="form-control" value="<?php echo $tagmanager['zenchat_code'];?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="tm_ZOPIMCHAT" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-zopimchat_status"><?php echo $entry_zopimchat_status; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_zopimchat_status" type="checkbox" name="zopimchat_status" <?php if ($tagmanager['zopimchat_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="zopimchat" style="display:<?php if (!$tagmanager['zopimchat_status']) { echo 'none'; }?>">
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-zopimchat_code"><?php echo $entry_zopimchat_code; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="zopimchat_code" placeholder="<?php echo $entry_zopimchat_code; ?>" class="form-control" value="<?php echo $tagmanager['zopimchat_code'];?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="tm_FRESHCHAT" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-freshchat_status"><?php echo $entry_freshchat_status; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_freshchat" type="checkbox" name="freshchat_status" <?php if ($tagmanager['freshchat_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="freshchat" style="display:<?php if (!$tagmanager['freshchat_status']) { echo 'none'; }?>">
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-freshchat_code"><?php echo $entry_freshchat_code; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="freshchat_code" placeholder="<?php echo $entry_freshchat_code; ?>" class="form-control" value="<?php echo $tagmanager['freshchat_code'];?>" />
                                            </div>
                                        </div>
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-freshchat_host"><?php echo $entry_freshchat_host; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="freshchat_host" placeholder="<?php echo $entry_freshchat_host; ?>" class="form-control" value="<?php echo $tagmanager['freshchat_host'];?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="tm_HUBSPOT" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-hubspot_status"><?php echo $entry_hubspot_status; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_hubspot_status" type="checkbox" name="hubspot_status" <?php if ($tagmanager['hubspot_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="hubspot" style="display:<?php if (!$tagmanager['hubspot_status']) { echo 'none'; }?>">
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-hubspot_code"><?php echo $entry_hubspot_code; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="hubspot_code" placeholder="<?php echo $entry_hubspot_code; ?>" class="form-control" value="<?php echo $tagmanager['hubspot_code'];?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="tm_SMARTSUPP" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-smartsupp_status"><?php echo $entry_smartsupp_status; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_smartsupp_status" type="checkbox" name="smartsupp_status" <?php if ($tagmanager['smartsupp_status']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="smartsupp" style="display:<?php if (!$tagmanager['smartsupp_status']) { echo 'none'; }?>">
                                        <div class="panel-group">
                                            <label class="col-sm-4 control-label" for="input-smartsupp_code"><?php echo $entry_smartsupp_code; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="smartsupp_code" placeholder="<?php echo $entry_smartsupp_code; ?>" class="form-control" value="<?php echo $tagmanager['smartsupp_code'];?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="tm_AMP" class="form-group">
                                    <label class="col-sm-4 control-label" for="input-ampstatus"><?php echo $entry_ampstatus; ?></label>
                                    <div class="col-sm-8">
                                        <label class="switch"><input id="tagmanager_amp" type="checkbox" name="ampstatus" <?php if ($tagmanager['ampstatus']) { echo 'checked'; }?>><span class="slider round"></span></label>
                                    </div>
                                    <div id="amp" style="display:<?php if (!$tagmanager['ampstatus']) { echo 'none'; }?>">
                                        <div class="form-group required">
                                            <label class="col-sm-4 control-label" for="input-ampcode"><?php echo $entry_ampcode; ?></label>
                                            <div class="col-sm-8">
                                                <input type="text" name="ampcode" placeholder="<?php echo $entry_ampcode; ?>" class="form-control" value="<?php echo $tagmanager['ampcode'];?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <p>
                                    Google Analytics tracking for AMP Web pages. If you are using Webkul Accelerated Mobile Pages extesion you can setup Google Analytics tracking using the Tag Manager AMP container.
                                </p>
                                <p>
                                    Please note AMP tracking is in beta, and only Google Analytics is currently supported
                                </p>
                            </div>
                        </div>
<!-- TAB ORDERS -->
                        <div class="tab-pane <?php if ($show_order){ ?>active<?php }?>" id="tab-tab7">
                            <div class="table-responsive col-sm-8">
                            	<div class="row" style="margin-bottom:15px">
                                	<div class="pagination"><?php echo $pagination; ?></div>
                                </div>
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
                                                <?php if ($trans['hit']=="0"){ ?>Not Sent to Analytics<?php }?>
                                                <?php if ($trans['hit']=="1"){ ?>Sent to Analytics<?php }?>
                                                <?php if ($trans['hit']=="2"){ ?>Refund Sent to Analytics<?php }?>
                                            </td>
                                            <td class="text-center">
                                                <div id="div-send-<?php echo $trans['id'];?>" data-loading-text="loading" onclick="hitorder(<?php echo $trans['order_id'];?>,<?php echo $trans['id'];?>);" class="btn btn-primary" <?php echo ($trans['hit'] !='0' ? 'disabled' : '');?>
                                                    ><i class="fa fa-plus-circle"></i> <?php echo $button_send;?>
                                                </div>
                                                <div id="div-refund-<?php echo $trans['id'];?>" data-loading-text="loading" onclick="refundorder(<?php echo $trans['order_id'];?>,<?php echo $trans['id'];?>);" class="btn btn-primary" <?php echo ($trans['hit'] !='1' ? 'disabled' : '');?>
                                                    ><i class="fa fa-plus-circle"></i><?php echo $button_refund;?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php }?>
                                        <?php } else {?>
                                        <tr>
                                            <td class="text-center" colspan="6">No Results</td>
                                        </tr>
                                        <?php }?>
                                    </tbody>
                                </table>
                                <div class="row" style="margin-top:15px">
                                	<div class="pagination"><?php echo $pagination; ?></div>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <h3>Tag Manager Version <?php echo $text_version;?></h3>
                                <?php echo $text_about;?>
                                <h3><?php echo $heading_container;?></h3>
                                <?php echo $text_container;?>
                            </div>
                        </div>
                    
<!-- TAB LOGS -->
                        <div class="tab-pane" id="tab-tab8">
                            <div class="table-responsive col-sm-12">
                                <textarea wrap="off" rows="20" readonly class="form-control"><?php echo $log;?></textarea>
                            </div>
                             <div class="pull-right" style="padding-top:10px;padding-right:10px">
                                <a onclick="confirm('This will clear all the log data!') ? location.href='<?php echo $clear;?>' : false;" data-toggle="tooltip" title="Clear Log" class="btn btn-danger"><i class="fa fa-eraser"></i> Clear logs</a>
                            </div>
                        </div>
                    </div>
                    </div>     
<!-- TAB ENDS -->
                 <input type="hidden" name="mp" value="1">
                    <input type="hidden" name="route_confirm" value="<?php echo $tagmanager['route_confirm'];?>">
                    <input type="hidden" name="vs" value="<?php echo (isset($tagmanager['vs']) ? $tagmanager['vs'] : '');?>">  
                  </form>
            </div>
        </div>
    </div>
</div>
</div>
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
</script>
<script>

    	$("#tagmanager_ua_status").click(function () {
    	    console.log('Tagmanager AddToCart Event Sent');
            if ($(this).is(":checked")) {
                $("#ua").show();
            } else {
                $("#ua").hide();
            }
        });
        $("#tagmanager_server").click(function () {
            if ($(this).is(":checked")) {
                $("#server").show();
            } else {
                $("#server").hide();
            }
        });
        $("#tagmanager_ga4_status").click(function () {
            if ($(this).is(":checked")) {
                $("#ga4").show();
            } else {
                $("#ga4").hide();
            }
        });
        $("#tagmanager_dimensions").click(function () {
            if ($(this).is(":checked")) {
                $("#dimensions").show();
            } else {
                $("#dimensions").hide();
            }
        });
        $("#tagmanager_adword").click(function () {
            if ($(this).is(":checked")) {
                $("#conversion").show();
            } else {
                $("#conversion").hide();
            }
        });
        $("#tagmanager_adword2").click(function () {
            if ($(this).is(":checked")) {
                $("#conversion2").show();
            } else {
                $("#conversion2").hide();
            }
        });

        $("#tagmanager_aw_optional").click(function () {
            if ($(this).is(":checked")) {
                $("#aw_optional").show();
            } else {
                $("#aw_optional").hide();
            }
        });
		$("#optimize").click(function () {
	            if ($(this).is(":checked")) {
	                $("#optimizeid").show();
	            } else {
	                $("#optimizeid").hide();
	            }
	        });
		$("#greview").click(function () {
	            if ($(this).is(":checked")) {
	                $("#greviewid").show();
	            } else {
	                $("#greviewid").hide();
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
	        
        $("#tagmanager_fb_api").click(function () {
            if ($(this).is(":checked")) {
                $("#fbapi").show();
            } else {
                $("#fbapi").hide();
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
	     $("#tagmanager_luckyorange_status").click(function () {
	            if ($(this).is(":checked")) {
	                $("#luckyorange").show();
	            } else {
	                $("#luckyorange").hide();
	            }
	        });   
	    $("#tagmanager_clarity_status").click(function () {
	            if ($(this).is(":checked")) {
	                $("#clarity").show();
	            } else {
	                $("#clarity").hide();
	            }
	        });    
	    $("#tagmanager_snap_pixel_status").click(function () {
	            if ($(this).is(":checked")) {
	                $("#snapchat").show();
	            } else {
	                $("#snapchat").hide();
	            }
	      });
	    $("#tagmanager_pinterest_status").click(function () {
	            if ($(this).is(":checked")) {
	                $("#pinterest").show();
	            } else {
	                $("#pinterest").hide();
	            }
	     });
	    $("#tagmanager_paypal_status").click(function () {
	            if ($(this).is(":checked")) {
	                $("#paypal").show();
	            } else {
	                $("#paypal").hide();
	            }
	      });  
	    $("#tagmanager_twitter_status").click(function () {
	            if ($(this).is(":checked")) {
	                $("#twitter").show();
	            } else {
	                $("#twitter").hide();
	            }
	      });      
		$("#tagmanager_skroutz_status").click(function () {
	            if ($(this).is(":checked")) {
	                $("#skroutz").show();
	            } else {
	                $("#skroutz").hide();
	            }
	        });
	   	$("#tagmanager_skroutz_manual_tax").click(function () {
	            if ($(this).is(":checked")) {
	                $("#skroutztax").show();
	            } else {
	                $("#skroutztax").hide();
	            }
	        });    
	    $("#tagmanager_glami_status").click(function () {
	            if ($(this).is(":checked")) {
	                $("#glami").show();
	            } else {
	                $("#glami").hide();
	            }
	        });	
	   	$("#tagmanager_tiktok").click(function () {
	         if ($(this).is(":checked")) {
	              $("#tiktok").show();
	         } else {
	              $("#tiktok").hide();
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
	    $("#tagmanager_hubspot_status").click(function () {
	            if ($(this).is(":checked")) {
	                $("#hubspot").show();
	            } else {
	                $("#hubspot").hide();
	            }
	        });
	    $("#tagmanager_smartsupp_status").click(function () {
	            if ($(this).is(":checked")) {
	                $("#smartsupp").show();
	            } else {
	                $("#smartsupp").hide();
	            }
	        });
		$("#tagmanager_zopimchat_status").click(function () {
	            if ($(this).is(":checked")) {
	                $("#zopimchat").show();
	            } else {
	                $("#zopimchat").hide();
	            }
	    });
	    $("#tagmanager_freshchat").click(function () {
	            if ($(this).is(":checked")) {
	                $("#freshchat").show();
	            } else {
	                $("#freshchat").hide();
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
        $("#tagmanager_admitad").click(function () {
            if ($(this).is(":checked")) {
                $("#admitad").show();
            } else {
                $("#admitad").hide();
            }
        });
         $("#tagmanager_retag").click(function () {
            if ($(this).is(":checked")) {
                $("#retag").show();
            } else {
                $("#retag").hide();
            }
        });
        $("#tagmanager_performant").click(function () {
            if ($(this).is(":checked")) {
                $("#performant").show();
            } else {
                $("#performant").hide();
            }
        });
        $("#tagmanager_performant_tax").click(function () {
            if ($(this).is(":checked")) {
                $("#performanttax").show();
            } else {
                $("#performanttax").hide();
            }
        });  
        $("#tagmanager_performant_currency").click(function () {
	            if ($(this).is(":checked")) {
	                $("#performantcurrency").show();
	            } else {
	                $("#performantcurrency").hide();
	            }
	        });    
        $("#tagmanager_affgateway_status").click(function () {
            if ($(this).is(":checked")) {
                $("#affgateway").show();
            } else {
                $("#affgateway").hide();
            }
        });
        $("#tagmanager_sendinblue_status").click(function () {
            if ($(this).is(":checked")) {
                $("#sendinblue").show();
            } else {
                $("#sendinblue").hide();
            }
        });
        $("#tagmanager_linkwise_status").click(function () {
            if ($(this).is(":checked")) {
                $("#linkwise").show();
            } else {
                $("#linkwise").hide();
            }
        });
		$("#tagmanager_badge").click(function () {
            if ($(this).is(":checked")) {
                $("#badge").show();
            } else {
                $("#badge").hide();
            }
        });
</script> 
<script>    
        $('#color1').colorpicker({color: '<?php echo (!empty(${$PREFIX . 'tagmanager_cookie_bg_popup'}) ? ${$PREFIX . 'tagmanager_cookie_bg_popup'} :'#3B3646');?>',
            colorSelectors: {'black': '#000000','white': '#ffffff','red': '#FF0000','default': '#777777','primary': '#337ab7','success': '#5cb85c','info': '#5bc0de','warning': '#f0ad4e','danger': '#d9534f'            },customClass: 'colorpicker-2x',sliders: {saturation: {maxLeft:200,maxTop: 200},hue: {maxTop: 200},alpha: {maxTop: 200}}
        });
        $('#color2').colorpicker({color: '<?php echo (!empty(${$PREFIX . 'tagmanager_cookie_text_popup'}) ? ${$PREFIX . 'tagmanager_cookie_text_popup'} :'#ffffff');?>',
            colorSelectors: {'black': '#000000','white': '#ffffff','red': '#FF0000','default': '#777777','primary': '#337ab7','success': '#5cb85c','info': '#5bc0de','warning': '#f0ad4e','danger': '#d9534f'            },customClass: 'colorpicker-2x',sliders: {saturation: {maxLeft:200,maxTop: 200},hue: {maxTop: 200},alpha: {maxTop: 200}}
        });
        $('#color3').colorpicker({color: '<?php echo (!empty(${$PREFIX . 'tagmanager_cookie_bg_button'}) ? ${$PREFIX . 'tagmanager_cookie_bg_button'} : '#EE4B5A');?>',
            colorSelectors: {'black': '#000000','white': '#ffffff','red': '#FF0000','default': '#777777','primary': '#337ab7','success': '#5cb85c','info': '#5bc0de','warning': '#f0ad4e','danger': '#d9534f'            },customClass: 'colorpicker-2x',sliders: {saturation: {maxLeft:200,maxTop: 200},hue: {maxTop: 200},alpha: {maxTop: 200}}
        });
        $('#color4').colorpicker({color: '<?php echo (!empty(${$PREFIX . 'tagmanager_cookie_text_button'}) ? ${$PREFIX . 'tagmanager_cookie_text_button'} : '#ffffff');?>',
            colorSelectors: {'black': '#000000','white': '#ffffff','red': '#FF0000','default': '#777777','primary': '#337ab7','success': '#5cb85c','info': '#5bc0de','warning': '#f0ad4e','danger': '#d9534f'            },customClass: 'colorpicker-2x',sliders: {saturation: {maxLeft:200,maxTop: 200},hue: {maxTop: 200},alpha: {maxTop: 200}}
        });
        $('#color5').colorpicker({color: '<?php echo (!empty(${$PREFIX . 'tagmanager_cookie_heading_color'}) ? ${$PREFIX . 'tagmanager_cookie_heading_color'} : '#EE4B5A');?>',
            colorSelectors: {'black': '#000000','white': '#ffffff','red': '#FF0000','default': '#777777','primary': '#337ab7','success': '#5cb85c','info': '#5bc0de','warning': '#f0ad4e','danger': '#d9534f'            },customClass: 'colorpicker-2x',sliders: {saturation: {maxLeft:200,maxTop: 200},hue: {maxTop: 200},alpha: {maxTop: 200}}
        });
        $('#color6').colorpicker({color: '<?php echo (!empty(${$PREFIX . 'tagmanager_cookie_badge_color'}) ? ${$PREFIX . 'tagmanager_cookie_badge_color'} : '#3B3646');?>',
            colorSelectors: {'black': '#000000','white': '#ffffff','red': '#FF0000','default': '#777777','primary': '#337ab7','success': '#5cb85c','info': '#5bc0de','warning': '#f0ad4e','danger': '#d9534f'            },customClass: 'colorpicker-2x',sliders: {saturation: {maxLeft:200,maxTop: 200},hue: {maxTop: 200},alpha: {maxTop: 200}}
        });
</script>
<script>
    function hitorder(orderid,id) {
    		$.ajax({
			url: '<?php echo $catalog;?>index.php?route=extension/analytics/tagmanager/sendorder&oid=' + orderid + '&v=<?php echo $tagmanager['vs'];?>',
			type: 'get',
			dataType: 'json',
			beforeSend: function() {
				$("#div-send-" + id).prop('disabled',true);
			},
			complete: function() {
				
				
			},
			success: function(result) {
				if (!result['error']) {
					$('#content > .container-fluid').prepend('<div id="tmalert" class="alert alert-success alert-dismissible "><i class="fa fa-check-circle"></i><span style="padding-left:10px">' + result['message'] + '</span></div>');
					$("#div-send-"+id).attr('disabled','disabled');
				} else {
					$('#content > .container-fluid').prepend('<div id="tmalert" class="alert alert-warning alert-dismissible "><i class="fa fa-check-circle"></i><span style="padding-left:10px">' + result['message'] + '</span></div>');
				}
				var timer;
				timer = setTimeout(function () {
            		$('#tmalert').hide(500);
        		}, 3000);
			},
			
		});
	}
	function refundorder(orderid,id) {
		$.ajax({
			url: '<?php echo $catalog;?>index.php?route=extension/analytics/tagmanager/refund&oid=' + orderid + '&v=<?php echo $tagmanager['vs'];?>&order_status_id=7',
			type: 'get',
			dataType: 'json',
			
			beforeSend: function() {
				$("#div-refund-" + id).prop('disabled',true);
			},
			success: function(result) {
				if (!result['error']) {
					$('#content > .container-fluid').prepend('<div id="tmalert" class="alert alert-success alert-dismissible "><i class="fa fa-check-circle"></i><span style="padding-left:10px">' + result['message'] + '</span></div>');
					$("#div-refund-"+id).attr('disabled','disabled');
				} else {
					$('#content > .container-fluid').prepend('<div id="tmalert" class="alert alert-warning alert-dismissible "><i class="fa fa-check-circle"></i><span style="padding-left:10px">' + result['message'] + '</span></div>');
				}
				var timer;
				timer = setTimeout(function () {
            		$('#tmalert').hide(500);
        		}, 3000);
			},
			complete: function(result) {

			},
		});
	}
</script>
<?php echo $footer; ?>