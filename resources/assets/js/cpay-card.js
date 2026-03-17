jQuery(document).ready(function($) {
    'use strict';
    
    if (window.location.search.includes('cpay_card_payment=1')) {
        $('body').addClass('cpay-card-processing');
    }
});