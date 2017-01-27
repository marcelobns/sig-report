$(function(){
    $('.select').selectize();
});

$('#modal_frame').on('show.bs.modal', function(e) {
    $(this).find('.modal-content').load($(e.relatedTarget).attr('href'));
});
