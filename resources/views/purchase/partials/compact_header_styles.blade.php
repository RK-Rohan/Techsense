<style>
    /* Wrap fields in order instead of letting taller float columns leave gaps. */
    #add_purchase_form .purchase-header-fields {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
    }
    #add_purchase_form .purchase-header-fields::before,
    #add_purchase_form .purchase-header-fields::after {
        display: none;
    }
    #add_purchase_form .purchase-header-fields > [class*="col-"] {
        float: none;
        min-width: 0;
    }
    #add_purchase_form .purchase-header-fields .multi-input::after {
        content: "";
        display: table;
        clear: both;
    }
    @media (max-width: 767px) {
        #add_purchase_form .purchase-header-fields > [class*="col-"] {
            width: 100%;
        }
    }
</style>
