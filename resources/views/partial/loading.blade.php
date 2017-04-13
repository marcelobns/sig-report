<style media="screen">
.loading {
    display: none;
}
.loading .background {
    position: fixed;
    width: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    top: 0;
    bottom: 0;
    z-index: 999998;
}
.loading .indicator {
    color: #fff;
    width: 100%;
    position: absolute;
    top: 9%;
    text-align: center;
    z-index: 999999;
}
</style>
<div class="loading">
    <div class="background">
    </div>
    <div class="indicator">
        <i class="fa fa-circle-o-notch fa-spin fa-2x"></i>
        <div class="detail">
            Paciência, aguarde...
        </div>
    </div>
</div>
<script type="text/javascript">
    $('.btn-waiting').click(function(){
        $('.loading').show();
    });
</script>
