<div class="wrap">
    <h1><?php _e('Makhlas', 'makhlas'); ?></h1>
    <h3><?php _e('Create short url for all posts', 'makhlas'); ?></h3>
    <p><?php _e('Create all published posts short link.', 'makhlas'); ?></p>    
    <style type="text/css">
        .progress_container{width:100%}.progress{overflow:hidden;height:18px;margin-bottom:18px;background-color:#89949B;background-image:-moz-linear-gradient(top,#f5f5f5,#f9f9f9);background-image:-ms-linear-gradient(top,#f5f5f5,#f9f9f9);background-image:-webkit-gradient(linear,0 0,0 100%,from(#f5f5f5),to(#f9f9f9));background-image:-webkit-linear-gradient(top,#f5f5f5,#f9f9f9);background-image:-o-linear-gradient(top,#f5f5f5,#f9f9f9);background-image:linear-gradient(top,#f5f5f5,#f9f9f9);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#f5f5f5',endColorstr='#f9f9f9',GradientType=0);-webkit-box-shadow:inset 0 1px 2px rgba(0,0,0,.1);-moz-box-shadow:inset 0 1px 2px rgba(0,0,0,.1);box-shadow:inset 0 1px 2px rgba(0,0,0,.1);-webkit-border-radius:4px;-moz-border-radius:4px;border-radius:4px}.progress .bar{width:0%;height:18px;color:#fff;font-size:12px;text-align:center;text-shadow:0 -1px 0 rgba(0,0,0,.25);background-color:#0e90d2;background-image:-moz-linear-gradient(top,#149bdf,#0480be);background-image:-ms-linear-gradient(top,#149bdf,#0480be);background-image:-webkit-gradient(linear,0 0,0 100%,from(#149bdf),to(#0480be));background-image:-webkit-linear-gradient(top,#149bdf,#0480be);background-image:-o-linear-gradient(top,#149bdf,#0480be);background-image:linear-gradient(top,#149bdf,#0480be);background-repeat:repeat-x;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#149bdf',endColorstr='#0480be',GradientType=0);-webkit-box-shadow:inset 0 -1px 0 rgba(0,0,0,.15);-moz-box-shadow:inset 0 -1px 0 rgba(0,0,0,.15);box-shadow:inset 0 -1px 0 rgba(0,0,0,.15);-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;-webkit-transition:width 0.6s ease;-moz-transition:width 0.6s ease;-ms-transition:width 0.6s ease;-o-transition:width 0.6s ease;transition:width 0.6s ease}.progress-striped .bar{background-image:-webkit-gradient(linear,0 100%,100% 0,color-stop(.25,rgba(255,255,255,.15)),color-stop(.25,transparent),color-stop(.5,transparent),color-stop(.5,rgba(255,255,255,.15)),color-stop(.75,rgba(255,255,255,.15)),color-stop(.75,transparent),to(transparent));background-image:-webkit-linear-gradient(-45deg,rgba(255,255,255,.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.15) 50%,rgba(255,255,255,.15) 75%,transparent 75%,transparent);background-image:-moz-linear-gradient(-45deg,rgba(255,255,255,.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.15) 50%,rgba(255,255,255,.15) 75%,transparent 75%,transparent);background-image:-ms-linear-gradient(-45deg,rgba(255,255,255,.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.15) 50%,rgba(255,255,255,.15) 75%,transparent 75%,transparent);background-image:-o-linear-gradient(-45deg,rgba(255,255,255,.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.15) 50%,rgba(255,255,255,.15) 75%,transparent 75%,transparent);background-image:linear-gradient(-45deg,rgba(255,255,255,.15) 25%,transparent 25%,transparent 50%,rgba(255,255,255,.15) 50%,rgba(255,255,255,.15) 75%,transparent 75%,transparent);-webkit-background-size:40px 40px;-moz-background-size:40px 40px;-o-background-size:40px 40px;background-size:40px 40px}.progress.active .bar{-webkit-animation:progress-bar-stripes 2s linear infinite;-moz-animation:progress-bar-stripes 2s linear infinite;animation:progress-bar-stripes 2s linear infinite}@-webkit-keyframes progress-bar-stripes{from{background-position:0 0}to{background-position:40px 0}}@-moz-keyframes progress-bar-stripes{from{background-position:0 0}to{background-position:40px 0}}@keyframes progress-bar-stripes{from{background-position:0 0}to{background-position:40px 0}}
    </style> 
    <form class="convert-allposts">
        <div class="progress_container" style="display: none;">
            <div class="progress progress-striped active">
                <div class="bar" style="width: 1%;"></div>
            </div>
        </div>
        <span class="total-posts" data-total="<?php echo $total; ?>"></span>
        <input type="hidden" name="start" value="1"></input>
        <input class="button button-primary" type="submit" value="<?php _e('Update All', 'makhlas'); ?>"></input>
    </form>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        function process_step(step, total, self) {
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'makhlas_convert_all_posts_link',
                    security: '<?php echo wp_create_nonce("makhlas_converter"); ?>',
                    total: total,
                    step: step,
                },
                dataType: "json",
                success: function( response ) {
                    if (!response) {
                        alert("<?php _e('Just admin can do it, Converte Failed!', 'makhlas'); ?>");
                    } else if ('done' == response.step) {
                        var export_form = $('.convert-allposts');
                        export_form.find('.spinner').remove();
                        export_form.find('.convert-notic').remove();
                        export_form.append('<div class="notice notice-success convert-notic"><p><?php _e('All posts short link created successfully.', 'makhlas'); ?></p></div>');
                        export_form.find('.bar').width('100%');
                        export_form.find('.progress').removeClass('active');
                    } else {
                        if (!!response.total) {
                            var total = response.total;
                        }
                        percentage = Math.round(((parseInt(response.converted)) / total) * 100);
                        $('.bar').width(percentage + '%');
                        process_step(parseInt(response.step), parseInt(total), self);
                    }
                }
            }).fail(function (response) {
                alert("<?php _e('Convert Error!', 'makhlas'); ?>");
            });

        }

        $('body').on('submit', '.convert-allposts', function(e) {
            e.preventDefault();
            // var data = $(this).serialize();
            $('.convert-allposts').find('.button').removeClass('button-info').addClass('button-primary').attr("disabled", true);
            $(this).append('<span style="float: none;" class="spinner is-active"></span><div class="notice notice-warning convert-notic"><p><?php _e('Do not Close or refresh this page.', 'makhlas'); ?></p></div>');
            $('.progress_container').show();
            // start the process
            process_step( 1, 0, self);
        });
    });

    </script>
</div>