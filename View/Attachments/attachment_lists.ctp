<?php //pr($attachmentData); ?>
<div class="col-sm-12 twelve column table-responsive">
    <div>
        <h3 style="width: 90%;display: inline-block;"><i class="pe-7s-photo-gallery" style="font-weight: 600;"></i>
            Received Files</h3>
        <span style="float: right;padding-top: 20px;" title="Refresh" class="icn">
            <i style="font-weight: 600;" class="pe-7s-refresh action-refresh"></i>
        </span>
    </div>
    <?php if (isset($attachmentData) && !empty($attachmentData)) { ?>

        <table class="table table-striped default">
            <tbody>
            <tr>
                <th style="width: 10px">#</th>
                <th>Name</th>
                <th>Type</th>
                <th>Attachment Type</th>
                <th>Order</th>
                <th></th>
            </tr>
            <?php $i = 1;
            foreach ($attachmentData as $attachData) { ?>
                <tr id="attach_list_<?php echo $i; ?>" fileid="<?php echo $attachData['Attachment']['id']; ?>">
                    <td><?php echo $i; ?>.</td>
                    <td>
                        <?php
                        if(in_array($attachData['Attachment']['file_type'],array('image/jpeg','image/png','image/gif','image/jpg'))){
                            echo '<a class="group" href="' . SITE_URL . $attachData['Attachment']['path'] . $attachData['Attachment']['filename'] . '?attaction=download"><div class="thumbnail" style="background-image:url(' . SITE_URL . $attachData['Attachment']['path'] . $attachData['Attachment']['filename'] . ')"></div></a>';
                        }elseif(in_array($attachData['Attachment']['file_type'],array('application/pdf'))){
                            echo '<div class="thumbnail file-box "><i class="fa fa-file-pdf-o"></i></div>';
                        }elseif(in_array($attachData['Attachment']['file_type'],array('application/vnd.ms-excel'))){
                            echo '<div class="thumbnail file-box"><i class="fa fa-file-excel-o"></i></div>';
                        }elseif(in_array($attachData['Attachment']['file_type'],array('audio/mp3'))){
                            echo '<div class="thumbnail file-box"><i class="pe-7s-music"></i></div>';
                        }elseif(strpos($attachData['Attachment']['file_type'],'ideo')==1){
                            echo '<div class="thumbnail file-box"><i class="pe-7s-video"></i></div>';
                        }else{
                            echo '<div class="thumbnail file-box"><i class="fa fa-file"></i></div>';
                        }

                        echo '<span class="title" title="'.$attachData['Attachment']['original_name'].'">'.$attachData['Attachment']['original_name'].'</span>';
                        ?>
                    </td>
                    <td><?php echo $attachData['Attachment']['file_type']; ?></td>
                    <td>
                        <?php
                        if(isset($this->request->query['stype']))
                        echo $this->Form->select('attachment_type',$attachTypes, array('empty' => 'Type', 'class' => 'form-control input-sm action-change', 'default' => $attachData['Attachment']['attachment_type'], 'style' => 'width:100px')); ?>
                    </td>
                    <td>
		                <?php echo $this->Form->input('order', array('placeholder' => 'Order', 'class' => 'form-control input-sm action-change', 'value' => $attachData['Attachment']['order'], 'style' => 'width:50px','label'=>false)); ?>
                    </td>
                    <td>
                        <?php
                        echo '<span title="download" class="icn"><a download href="' . SITE_URL . $attachData['Attachment']['path'] . $attachData['Attachment']['filename'] . '?attaction=download"><i class="pe-7s-download"></i></a></span>';
                        echo '<span title="delete" class="icn"><i class="pe-7s-trash action-delete" fileid="' . $attachData['Attachment']['id'] . '"></i></span>';
                        ?>
                    </td>
                </tr>
                <?php $i++;
            } ?>
            </tbody>
        </table>
    <?php } ?>
</div>

<style>
    .icn i {
        cursor: pointer;
        font-size: 20px;
        position: relative;
        top: 2px;
        margin: 0px 10px;
    }

    .icn i.pe-7s-download {
        color: #000;
    }

    .icn i.pe-7s-trash {
        color: #ff0000;
    }

    div.thumbnail {
        width: 100px;
        height: 100px;
        border-radius: 8px 8px 8px 8px;
        -moz-border-radius: 8px 8px 8px 8px;
        -webkit-border-radius: 8px 8px 8px 8px;
        background-size: cover;
        margin: 0 auto;
        border: 1px solid #e1e1e1;
        float: left;
    }
    span.title {
        width: 100px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
        font-size: 14px;
        color: #666;
        text-align: center;
        float: left;
        clear: both;
    }
    .file-box{text-align: center;}
    .file-box i{font-size: 60px;color: #9B9B9B;margin-top: 21%;}
    #attachment-list-box tbody tr td{vertical-align: middle;}
</style>
<script type="application/javascript">
    (function ($) {
        jQuery.fn.attach_action = function (options) {
            options = $.extend({
                // Localization
                edit_success_message: 'Successfully Updated',
                save_success_message: 'Successfully Saved',
                // CSS
                file_icon: '<i class="fa"></i>',
                method: 'normal', // normal/ajax
                // AJAX URL
                url: 'ajax_upload.php',
                delete_url: 'ajax_delete.php',
                list_url: 'ajax_view.php',
                update_url: 'update.php',
                list_box_id: 'attachment-list-box',
                uploader_id: '',
                stype:'<?php echo @$stype;?>',
            }, options);

            // Show added files
            $('[id^=attach_list_]').click(function (e) {
                var k = this;
                //console.log(e);
                //console.log(e);return false;
                var className = e.target.className;
                if ($(k).attr('id')) {
                    options.uploader_id = $(k).attr('id');
                    //actions(k,options.uploader_id);
                }
                // Add delete buton listener
                if (className == 'pe-7s-trash action-delete') {
                    var fileid = $(e.target).attr("fileid");
                    if (fileid > 0) {
                        $.ajax({
                            url: options.delete_url,
                            data: "fileid=" + fileid,
                        }).done(function () {
                            $('#' + options.uploader_id).delay(500).fadeOut("slow");
                        });
                    }
                }
                if (className == 'pe-7s-refresh action-refresh') {
                    listData();
                }
            });
            $('.action-change').change(function (e) {
                var k = this;
                var val = $(k).val();
                var name = $(k).attr('name');
                var fileid = $(k).parents('tr').attr("fileid");
                //return false;
                if (val && fileid) {
                    $.ajax({
                        url: options.update_url,
                        data: {name: name, val: val, fileid: fileid},
                        method: 'POST',
                    }).done(function () {
                        toastr.success(options.edit_success_message);
                        listData();
                    });
                }
            })
            $('.action-refresh').click(function (e) {
                listData();
            })

            // Show all uploaded files
            function listData() {
                $.ajax({
                    url: options.list_url,
                    data: {stype:options.stype},
                }).done(function (data) {
                    console.log('kul');
                    $('#' + options.list_box_id).html(data);
                });
            }
        };
    })(jQuery);
    $(document).ready(function () {
        $('#attachment-list-box').attach_action({
            delete_url: '?attaction=delete',
            list_url: '?attaction=viewlist',
            update_url: '?attaction=update',
        });
        //$(".group").colorbox({rel:'group1'});
        $(".group").colorbox({rel: 'group2', slideshow: true, transition: "none", width: "75%", height: "75%"});
    });
</script>