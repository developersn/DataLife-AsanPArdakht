<?php
@ob_start ();
@ob_implicit_flush ( 0 );

if( !defined( 'E_DEPRECATED' ) ) {

    @error_reporting ( E_ALL ^ E_WARNING ^ E_NOTICE );
    @ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_NOTICE );

} else {

    @error_reporting ( E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE );
    @ini_set ( 'error_reporting', E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE );

}

@ini_set ( 'display_errors', true );
@ini_set ( 'html_errors', false );

define ( 'DATALIFEENGINE', true );
define ( 'ROOT_DIR', dirname ( __FILE__ ) );
define ( 'ENGINE_DIR', ROOT_DIR . '/engine' );

require_once dirname(__FILE__) . '/engine/api/api.class.php';
$dle_api->install_admin_module("snpayment", "پرداخت آنلاین", "ماژول پرداخت آنلاين", "snpayment.png");

$sql = array();
$sql[] = <<< SQL
CREATE TABLE IF NOT EXISTS `dle_snpayments` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `snpay_name` varchar(255) NOT NULL,
  `snpay_email` varchar(255) NOT NULL,
  `snpay_mobile` varchar(255) NOT NULL,
  `snpay_price` int(10) unsigned NOT NULL DEFAULT '0',
  `snpay_info` text NOT NULL,
  `date` int(11) unsigned NOT NULL DEFAULT '0',
  `transid` varchar(255) NOT NULL,
  `verified` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `gateway` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
SQL;

foreach($sql as $query) $db->query($query);
?>

<!DOCTYPE html>
<html lang="fa">
    <head>
        <meta charset="utf-8">
        <title> نصب ماژول</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style type="text/css">
            * { margin:0;padding:0;outline:0 none; }
            a, a:hover, a:focus, a:active { color:#38d;text-decoration:none; }
        </style>
    </head>
    <body bgcolor="#efefef" style="font-family:Tahoma, Geneva, sans-serif;font-size:11px;line-height:24px;text-align:center;">
        
        <div style="margin:50px auto 0;max-width:300px;width:80%;text-shadow:0 0 3px rgba(0,0,0,0.2);color:#555;">
            <div style="display:block;border-radius:3px;background-color:#fff;padding:7px 15px 11px;border:1px solid #dcdcdc;">
                <p dir="rtl" style="margin:0;padding:0;">تغییرات مورد نیاز اعمال گردید</p>
            </div>
        </div>
       
    </body>
</html>