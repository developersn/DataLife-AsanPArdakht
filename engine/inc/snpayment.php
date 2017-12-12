<?php

defined("DATALIFEENGINE") || exit;

$modulename = basename(__FILE__, '.php');

require_once ROOT_DIR . '/language/' . $config['langs'] . '/snpayment.lng';

echoheader("<em class=\"icon-credit-card\"></em> " . $lang['snpayment_t'], $lang['snpayment_d']);

echo "<div class=\"box\"><div class=\"box-header\"><div class=\"title\">{$lang['success_transactions']}</div></div><div class=\"box-content\">";

$db->query("DELETE FROM " . PREFIX . "_snpayments WHERE date < " . ($_TIME - 21600) . " AND gateway = 'sn' AND verified = '0'");

$page = (($page = intval(filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT)))) ? $page : 1;
$endpoint = 30;
$startpoint = $page * $endpoint - $endpoint;

echo <<< HTML
<table class="table table-normal table-hover">
<thead><tr><td width="5%">{$lang['order']}</td><td>{$lang['transid']}</td><td>{$lang['name']}</td>
<td>{$lang['email']}</td><td>{$lang['mobile']}</td><td>{$lang['info']}</td><td>{$lang['amount']}</td><td>{$lang['date']}</td>
<td>{$lang['situation']}</td><td>{$lang['actions']}</td></tr></thead><tbody>
HTML;

$db->query("SELECT * FROM ".PREFIX."_snpayments WHERE verified = '1' AND gateway = 'sn' ORDER BY id DESC LIMIT $startpoint, $endpoint");
if ($db->num_rows()) {
    while($row = $db->get_row()) {
        $startpoint++;

        $row['date'] = jdate('Y/m/d H:i', $row['date']);
        $row['verified'] = "<center><em style=\"color:".(($row['verified']) ? "green" : "red")."\" class=\"" . (($row['verified']) ? "icon-ok" : "icon-remove") . "\"></em></center>";
        $records .= <<< HTML
<tr>
    <td>{$startpoint}</td>
    <td>{$row['transid']}</td>
    <td>{$row['snpay_name']}</td>
    <td>{$row['snpay_mobile']}</td>
    <td>{$row['snpay_email']}</td>
    <td style="padding:3px 2px 0 2px"><button onclick="DLEalert('{$row['snpay_info']}', '{$lang['info']}'); return false;" class="btn btn-xs btn-primary" style="width:100%;padding:2px 0 1px;"><em class="icon-pencil"></em></button></td>
    <td>{$row['snpay_price']} {$lang['toman']}</td>
    <td>{$row['date']}</td>
    <td>{$row['verified']}</td>
    <td style="padding:3px 2px 0 2px"><a href="?mod=snpayment&action=delete&id={$row['id']}" style="padding:2px 6px;width:100%;" class="btn btn-danger"><em class="icon-trash"></em></a></td>
</tr>
HTML;
    }
}
else $records = "<tr><td colspan=\"10\"><center><span style=\"color:red\">رکوردی در سیستم ثبت نشده است...</span></center></td></tr>";

echo $records . '</tbody></table>';

$pages = "";
$row = $db->super_query("SELECT COUNT(*) AS counter FROM ".PREFIX."_snpayments WHERE gateway = 'sn' AND verified = '1'");
$row = $row['counter'];

if  ($row > $endpoint) {
    $pages = "<div class=\"pull-left\"><ul class=\"pagination pagination-sm\">";
    $lastpage = ceil($row / $endpoint);
    for ($counter = 1; $counter <= $lastpage; $counter++) {
        if ($counter < 3 || $counter > ($lastpage - 2) || ($counter > ($page - 2) && $counter < ($page + 2))) {
            if ($counter === $page) $pages .= '<li class="active"><span>' . $counter . '</span></li>';
            else $pages .= '<li><a href="?mod='.$modulename.'&page='.$counter.'">' . $counter . '</a></li>';
        }
        elseif ($counter == 3 || $counter == ($lastpage - 2)) $pages .= '<li class="active"><span>...</span></li>';
    }
    $pages .= "</ul></div>";
}

echo ($pages !== "") ? '</div><div class="box-footer" style="padding:10px">' . $pages . '</div></div>' : '</div></div>';
echofooter();