<div class="box">
    <div class="listing-header clearfix">
        <div class="bulkAction pull-right">
            <p><button onclick=" window.history.back();" class="btn btn-default btn-flat btn-sm "><i class="fa fa-arrow-left" aria-hidden="true"></i>  Go Back</button></p>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-sm-12 twelve column">
                <div style="display: none;">
                    <h4>Multiple File Upload Form</h4>
                    <form method="POST" action="upload.php" enctype="multipart/form-data">
                        <input type="file" name="file[]" multiple>
                        <input class="button-primary" type="submit" value="Submit">
                    </form>
                    <h4>AJAX Upload Form</h4>
                    <form method="POST" action="upload.php" enctype="multipart/form-data">
                        <input type="file" name="file[]" data-method="ajax" multiple>
                        <input class="button-primary" type="submit" value="Submit">
                    </form>
                    <h4>Single File Upload Form</h4>
                    <form method="POST" action="upload.php" enctype="multipart/form-data">
                        <input type="file" name="file">
                        <input class="button-primary" type="submit" value="Submit">
                    </form>
                    <h4>Only Images Upload Form</h4>
                    <form method="POST" action="upload.php" enctype="multipart/form-data">
                        <input type="file" multiple name="file[]" accept="image/*">
                        <input class="button-primary" type="submit" value="Submit">
                    </form>
                    <h4>Only PDF Upload Form</h4>
                    <form method="POST" action="upload.php" enctype="multipart/form-data">
                        <input type="file" multiple name="file[]" accept="application/pdf">
                        <input class="button-primary" type="submit" value="Submit">
                    </form>
                    <h4>Only Files Less than 5 Mb</h4>
                    <form method="POST" action="upload.php" enctype="multipart/form-data">
                        <input type="file" multiple name="file[]" data-maxfilesize="5000000">
                        <input class="button-primary" type="submit" value="Submit">
                    </form>
                    <h4>File List Layout</h4>
                    <form method="POST" action="upload.php" enctype="multipart/form-data">
                        <input type="file" name="file[]" multiple data-layout="list">
                        <input class="button-primary" type="submit" value="Submit">
                    </form>
                    <h4>AJAX Single File Upload with List Layout</h4>
                    <form method="POST" action="upload.php" enctype="multipart/form-data">
                        <input type="file" name="file" data-layout="list" data-method="ajax">
                        <input class="button-primary" type="submit" value="Submit">
                    </form>
                </div>
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="file" name="file" data-layout="list" data-method="ajax" data-maxfilesize="<?php echo $max_file_size;?>">
                    <!--<input class="button-primary" type="submit" value="Submit">-->
                </form>
            </div>
            <div id="attachment-list-box"></div>

        </div>
    </div>
</div>
<?php
echo $this->Html->css(array('Attach.pe-icon-7-stroke','Attach.drop_uploader'));
echo $this->Html->script(array('Attach.drop_uploader'));
?>
<script>
    $(document).ready(function(){
        $('input[type=file]').drop_uploader({
            uploader_text: 'Drop files to upload, or',
            browse_text: 'Browse',
            browse_css_class: 'button-browse',
            browse_css_selector: 'file_browse',
            uploader_icon: '<i class="pe-7s-cloud-upload"></i>',
            file_icon: '<i class="pe-7s-file"></i>',
            time_show_errors: 5,
            layout: 'thumbnails',
            method: 'normal',
            url: '?attaction=upload',
            delete_url: '?attaction=delete',
            list_url: '?attaction=viewlist',
            list_box_id: 'attachment-list-box',
            stype:'<?php echo @$this->request->query['stype'];?>',
        });
    });
    $(window).load(function() {listData();});
    function listData() {
        $.ajax({
            url: '',
            data: {attaction:'viewlist'},
        }).done(function(data) {
            $('#attachment-list-box').html(data);
        });
    }
</script>
<style>
    .button-browse {
        color: #f4f4f4;
        background: #4aaaa5;
        font-weight: bold;
        margin-left: 5px;
        margin-top: 50px;
        font-family: Arial,sans-serif;
        border-radius: 4px;
        border: solid 0 #e3edf4;
        border-bottom: 2px solid #3a9a95;
        -moz-border-radius: 4px;
        -webkit-border-radius: 4px;
        border-radius: 4px;
        padding: 7px 25px 7px 25px;
    }
    .button-browse:hover,.button-browse:focus,.button-browse:active {
        background: #2a8a85;
        color: #f4f4f4;
    }
</style>

