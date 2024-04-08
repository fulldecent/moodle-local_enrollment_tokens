define(['jquery'], function($) {
    return {
        init: function() {
            function addToken(button) {
                // Add to #tokens
                $(button).before('<div class="input-group"><input type="text" class="form-control form-control-lg" name="token_code"><div class="input-group-append"><button class="btn btn-outline-danger" type="button" onclick="removeToken(this)">&minus;</button></div></div>');
            }

            function removeToken(button) {
                $(button).parent().parent().remove();
            }

            $(document).ready(function() {
                // Attach your addToken and removeToken functions to buttons
            });
        }
    };
});