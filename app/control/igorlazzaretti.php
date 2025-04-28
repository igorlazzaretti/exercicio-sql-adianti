<?php

use Adianti\Control\TPage;

class igorlazzaretti extends TPage
{
    public function __construct()
    {
        parent::__construct();

        $iframe = new TElement('iframe');
        $iframe->id = "iframe_external";
        $iframe->src = "https://igorlazzaretti.com";
        $iframe->frameborder = "0";
        $iframe->scrolling = "yes";
        $iframe->width = "100%";

        TScript::create('
        $(document).ready(function() {
            $("#iframe_external").css({
                "height": "82vh",
                "width": "100%"
            });
        });
    ');

        parent::add($iframe);
    }
}