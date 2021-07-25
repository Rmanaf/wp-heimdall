; (function ($) {

    $(document).ready(() => {
        
        $active_hooks = $('#wp_dcp_heimdall_active_hooks');

        $active_hooks.tagEditor({
            placeholder: $active_hooks.data('placeholder') || ''
        });

    });

})(jQuery);