<div class="box">

    <div class="box-body">
        <div class="row">
            <div id="attachment-list-box"></div>
        </div>
    </div>
</div>
<?php
echo $this->Html->css(array('Attach.pe-icon-7-stroke'));
?>
<script>
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
