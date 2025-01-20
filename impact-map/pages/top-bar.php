<?php

function impact_map_css_map_site_css_php(){
    ?>
    <style>
        html {
            --primary-color: #b13634 !important;
            --secondary-color: #8bc34a !important;
            --success-color: #4caf50 !important;
            --warning-color: #ffae00 !important;
            --alert-color: #cc4b37 !important;
            --white: #ffffff !important;
        }
        .top-bar,.top-bar ul {
            background-color: var(--white);
        }
        .logo-mobile {
            height: 30px;
        }
        .logo-img {
            height: 20px;
        }
        .logo-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        .go-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        .button {
            background-color: var(--primary-color);
            color: var(--white);
        }
        body {
            background-color:white;
        }
        .top {}
        .content {}
        .footer {}
        .right {
            text-align: end;
        }
    </style>
    <?php
}

function impact_map_top() {
    ?>
     <div class="top-bar">
            <div class="top-bar-left">
                <div class="show-for-medium">
                    <img class="logo-img" src="<?php echo plugin_dir_url(__DIR__)  ?>images/go-circle-logo.png" alt="Gospel Ambition Logo">
                    <span class="logo-title">Gospel Ambition</span>
                </div>
                <div class="show-for-small-only">
                    <img class="logo-mobile" src="<?php echo plugin_dir_url(__DIR__)  ?>images/go-circle-logo.png" alt="Gospel Ambition Logo">
                </div>
            </div>
            <div class="top-bar-right">
                <button class="button small" style="margin:0;">Donate</button>
            </div>
        </div>
    <?php
}
