<?php include "WURFL.php"?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>RESS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=yes">
    <meta name="description" content="Getting started with RESS. A demo site for using Responsive Web design together with server side techniques.">
    <meta name="author" content="Anders M. Andersen">

    <!-- Styles -->
    <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="bootstrap/css/bootstrap-responsive.css" rel="stylesheet">
    <link href="css/swipe.css" rel="stylesheet">
    <link href="css/site.css" rel="stylesheet">
    <link href="css/header.css" rel="stylesheet">

    <!-- Just neccecary scripts, rest goes in footer -->
    <script src="js/modernizr-2.5.3-custom.js"></script>

    <!-- Include touch and favicons -->
    <?php include "fragments/touchicon.php"?>

    <!-- Check screensize and write to cookie (need to do this after the meta viewport tag)

        We populate a PHP array called RESS.
    -->
    <?php include "RESS.php"?>
    <?php include "fragments/google-analytics.php" ?>
</head>

<body>
<div class="container">
    <div id="header">

        <div class="row">
            <?php
            if ($RESS["width"] >= 320 && $RESS["width"] <= 640) {
                ?>
                <div class="mobile-ad max-320">
                    <?php include "fragments/ads/320.php"?>
                </div>
                <?php
            }?>
            <div id="site-logo">
                <a href="/ress/">RESS</a>
            </div>
            <div class="ad">
                <?php
                if ($RESS["width"] >= 980) {
                    ?>
                    <div class="max-980">
                        <?php include "fragments/ads/728.php"?>
                    </div>
                    <?php
                } else if ($RESS["width"] >= 768) {
                    ?>
                    <div class="max-768">
                        <?php include "fragments/ads/468.php"?>
                    </div>
                    <?php
                }

                ?>
            </div>
        </div>
    </div>

    <div class="navbar">
        <div class="navbar-inner">
            <div class="container">
                <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>
                <a class="brand" href="#">Home</a>

                <div class="nav-collapse">
                    <ul class="nav">
                        <li class="active"><a href="#">Put</a></li>
                        <li><a href="#">Your</a></li>
                        <li><a href="#">Navigation</a></li>
                        <li><a href="#">Links</a></li>
                        <li><a href="#">Here</a></li>
                    </ul>
                </div>
                <!--/.nav-collapse -->
            </div>
        </div>
    </div>
</div>