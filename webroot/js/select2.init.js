(function ($) {
    $('.select2').select2({
        theme: 'bootstrap',
        width: '100%',
        placeholder: '-- Please choose --',
        allowClear: true,
        escapeMarkup: function (text) {
            return text;
        }
    });

    // fix for select2 search not working within bootstrap modals
    // @link https://github.com/select2/select2/issues/1436#issuecomment-21028474
    $.fn.modal.Constructor.prototype.enforceFocus = function () {};
})(jQuery);
