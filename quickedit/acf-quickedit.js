(function($) {

    window.acf_qe = {
      
        data: acf_qe_data,
      
        ready: function() {
            if (typeof inlineEditPost === 'undefined') { return; }
            
            $('#bulk-edit .acf-inline-edit-field').appendTo($('#bulk-edit .inline-edit-col-right'));

            //Prepopulating our quick-edit post info
            var $inline_editor = inlineEditPost.edit;
            
            inlineEditPost.edit = function(id) {
        
                //call old copy 
                $inline_editor.apply( this, arguments);
        
                //our custom functionality below
                var post_id = 0;
                if( typeof(id) == 'object'){
                    post_id = parseInt(this.getId(id));
                }
        
                //if we have our post
                if(post_id !== 0) {
        
                    //find our row
                    $row = $('#edit-' + post_id);
                    $row.find('.acf-inline-edit-field').appendTo($row.find('.inline-edit-col-right'));     
               
                    let $fields = $row.find('.acf-inline-edit-field');

                    $fields.each(function() {
                        let $name = $(this).attr('data-name'),
                        $input = $(this).find('input, select');

                        $input.val(acf_qe.data[post_id][$name]);
                    });
                    
                }
            }

        }
        
    };

    $(document).ready(window.acf_qe.ready);
})(jQuery);