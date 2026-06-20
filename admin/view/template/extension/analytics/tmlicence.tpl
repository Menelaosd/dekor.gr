<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div id="content">
        <div class="page-header">
            <div class="container-fluid">
                <div class="pull-right">
                  </div>
                <h1><?php echo $heading_title; ?></h1>
            </div>
        </div>
       
        <div class="container-fluid">
            <?php 
            if ($error) {?>
             <div class="alert alert-danger">
                    <i class="fa fa-exclamation-circle"></i> <?php echo $error; ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php }?>
            <?php 
            if ($error_tm) {?>
             <div class="alert alert-danger">
                    <i class="fa fa-exclamation-circle"></i> <?php echo $error_tm; ?>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            <?php }?>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">Tagmanager Licence </h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <form method="post" action="">
                            <div class="col-sm-8">
                                <div class="panel-group" style="margin-top:20px;">
                                    <label class="col-sm-3 control-label">Enter Licence Key:</label>
                                    <div class="col-sm-4">
                                        <input type="text" name="datas" placeholder="licence key" class="form-control" value="" />
                                    </div>
                                    <div class="col-sm-5">
                                        <div class="col-sm-3"><button type="submit" class="btn btn-info">Submit</button></div>
                                    </div>
                                </div>
                                <div class="row"><div class="content" style="margin-top:20px;padding:30px"><a href="https://licence.aits.xyz/" target="new"><h4>Do not have a licence key Generate one here</h4></a></div></div>
                                <p>Your Domain Name to use: <?php echo (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : false);?></p>
                                <p><?php echo $curl;?></p>
                        </form>
                    <div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $footer; ?>