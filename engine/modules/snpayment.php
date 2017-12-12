<?php

@session_start();

defined("DATALIFEENGINE") || exit;

require_once ROOT_DIR . '/language/' . $config['langs'] . '/snpayment.lng';
$sqlconf['sn_api'] = "Sk_a8abceecebabc"; 
$sqlconf['sn_webservice'] = 1; 
if (filter_input(INPUT_GET, "action", FILTER_DEFAULT) === "verify") {
					// Security
				$sec=$_GET['sec'];
				$mdback = md5($sec.'vm');
				$mdurl=$_GET['md'];
				// Security
					if(isset($_GET['sec']) AND isset($_GET['md']) AND $mdback == $mdurl )
				{
	
					$transData = $_SESSION[$sec];
					$trans_id = $transData['au'];
					$order_id = $_GET["orderid"];

    $transaction = $db->super_query("SELECT * FROM " . PREFIX . "_snpayments WHERE id = '$order_id' AND verified = '0' LIMIT 1");
     if (is_array($transaction) && count($transaction)) {
   $transid=$transaction['transid'] ;
            
       
						$bank_return = $_POST + $_GET ;
						$data_string = json_encode(array (
						'pin' => $sqlconf['sn_api'],
						'price' => $transaction['snpay_price'],
						'order_id' => $order_id,
						'au' => $transid,
						'bank_return' =>$bank_return,
						));
						
						$ch = curl_init('https://developerapi.net/api/v1/verify');
						curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_HTTPHEADER, array(
						'Content-Type: application/json',
						'Content-Length: ' . strlen($data_string))
						);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
						curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
						$result = curl_exec($ch);
						curl_close($ch);
						$json = json_decode($result,true);


					$res=$json['result'];
	                 switch ($res) {
						    case -1:
						    $msg = "پارامترهای ارسالی برای متد مورد نظر ناقص یا خالی هستند . پارمترهای اجباری باید ارسال گردد";
						    break;
						     case -2:
						    $msg = "دسترسی api برای شما مسدود است";
						    break;
						     case -6:
						    $msg = "عدم توانایی اتصال به گیت وی بانک از سمت وبسرویس";
						    break;
						     case -9:
						    $msg = "خطای ناشناخته";
						    break;
						     case -20:
						    $msg = "پین نامعتبر";
						    break;
						     case -21:
						    $msg = "ip نامعتبر";
						    break;
						     case -22:
						    $msg = "مبلغ وارد شده کمتر از حداقل مجاز میباشد";
						    break;
						    case -23:
						    $msg = "مبلغ وارد شده بیشتر از حداکثر مبلغ مجاز هست";
						    break;
						      case -24:
						    $msg = "مبلغ وارد شده نامعتبر";
						    break;
						      case -26:
						    $msg = "درگاه غیرفعال است";
						    break;
						      case -27:
						    $msg = "آی پی مسدود شده است";
						    break;
						      case -28:
						    $msg = "آدرس کال بک نامعتبر است ، احتمال مغایرت با آدرس ثبت شده";
						    break;
						      case -29:
						    $msg = "آدرس کال بک خالی یا نامعتبر است";
						    break;
						      case -30:
						    $msg = "چنین تراکنشی یافت نشد";
						    break;
						      case -31:
						    $msg = "تراکنش ناموفق است";
						    break;
						      case -32:
						    $msg = "مغایرت مبالغ اعلام شده با مبلغ تراکنش";
						    break;
						      case -35:
						    $msg = "شناسه فاکتور اعلامی order_id نامعتبر است";
						    break;
						    case -36:
						    $msg = "پارامترهای برگشتی بانک bank_return نامعتبر است";
						    break;
						    case -38:
						    $msg = "تراکنش برای چندمین بار وریفای شده است";
						    break;
						    case -39:
						    $msg = "تراکنش در حال انجام است";
						    break;
                            case 1:
						    $msg = "پرداخت با موفقیت انجام گردید.";
						    break;
						    default:
						       $msg = "خطایی در اتصال رخ داده است : ".$josn['result'];
						}
						
                    if($json['result'] == 1)
					{
            $db->query("UPDATE " . PREFIX . "_snpayments SET verified = '1', transid = '$transid' WHERE id = '$order_id'");
            msgbox($lang['verified_msg'], $lang['verified_complete'] . $transid );
        } else msgbox($lang['verification_error'], $msg);
    }
	} else msgbox($lang['verification_error'], $msg);
}
else {
    if (getenv("REQUEST_METHOD") === "POST") {
        $data_pack = filter_input(INPUT_POST, "datapack", FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

        if (is_array($data_pack) && count($data_pack)) {
            $data = array();
            $msgerror = "";

            foreach (array('snpay_name', 'snpay_email',  'snpay_mobile','snpay_price', 'snpay_info') as $fieldname) {
                if ($fieldname === 'snpay_price') $data_pack[$fieldname] = intval($data_pack[$fieldname]);
                else $data_pack[$fieldname] = trim($db->safesql($data_pack[$fieldname]));

                if ($fieldname === "snpay_email" && !filter_var($data_pack[$fieldname], FILTER_VALIDATE_EMAIL)) {
                    $msgerror = $lang['address_required'];
                    break;
                }
                if ($fieldname === "snpay_price" && $data_pack[$fieldname] === 0) {
                    $msgerror = $lang['amount_required'];
                    break;
                }
                if ($fieldname === "snpay_name" && $data_pack[$fieldname] === "") {
                    $msgerror = $lang['name_required'];
                    break;
                }

                $data['index'][$fieldname] = "`" . $fieldname . "`";
                $data['value'][$fieldname] = "'" . $data_pack[$fieldname] . "'";
            }

            if ($msgerror === "") {
                $data['index']['date'] = "`date`";
                $data['value']['date'] = "'$_TIME'";
                $data['index']['gateway'] = "`gateway`";
                $data['value']['gateway'] = "'sn'";

                $db->query("INSERT INTO " . PREFIX . "_snpayments (" . implode(", ", $data['index']) . ") VALUES (" . implode(", ", $data['value']) . ")");
                $insert_id = $db->insert_id();

					 // Security
					$sec = uniqid();
					$md = md5($sec.'vm');
					// Security
					
					$amount= $data_pack['snpay_price'];
					
					$callback= $config['http_home_url'] . 'index.php?do=snpayment&action=verify&orderid='.$insert_id.'&md='.$md.'&sec='.$sec ;
                   $tr = $db->super_query("SELECT * FROM " . PREFIX . "_snpayments WHERE id = '$insert_id' AND verified = '0' LIMIT 1");
					if ($sqlconf['sn_webservice'] == 1){
					 $Email = $tr['snpay_email'];
					 $Paymenter = $tr['snpay_name'];
					 $Mobile =$tr['snpay_mobile'];
					 $Description = $tr['snpay_info'];
				    if($Email==''){$Email='0'; }
				     if($Paymenter==''){$Paymenter='0';}
				      if($Mobile==''){$Mobile='0';}
				       if($Description==''){$Description='0';}
				       
					   	$data_string = json_encode(array(
					'pin'=> $sqlconf['sn_api'],
					'price'=> $amount,
					'callback'=>$callback,
					'order_id'=> $insert_id,
					'email'=> $Email,
					'description'=> $Description,
					'name'=> $Paymenter,
					'mobile'=> $Mobile,
					'ip'=> $_SERVER['REMOTE_ADDR'],
					'callback_type'=>2
					));
				    
			        }
					else
					{
					   	$data_string = json_encode(array(
					'pin'=> $sqlconf['sn_api'],
					'price'=> $amount,
					'callback'=>$callback,
					'order_id'=> $insert_id,
					'email'=> '0',
					'description'=> $Description,
					'name'=> '0',
					'mobile'=> '0',
					'ip'=> $_SERVER['REMOTE_ADDR'],
					'callback_type'=>2
					));
					    
					}

					$ch = curl_init('https://developerapi.net/api/v1/request');
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json',
					'Content-Length: ' . strlen($data_string))
					);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
					curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 20);
					$result = curl_exec($ch);
					curl_close($ch);
					$json = json_decode($result,true);
					
					$res=$json['result'];
	                 switch ($res) {
						    case -1:
						    $msg = "پارامترهای ارسالی برای متد مورد نظر ناقص یا خالی هستند . پارمترهای اجباری باید ارسال گردد";
						    break;
						     case -2:
						    $msg = "دسترسی api برای شما مسدود است";
						    break;
						     case -6:
						    $msg = "عدم توانایی اتصال به گیت وی بانک از سمت وبسرویس";
						    break;
						     case -9:
						    $msg = "خطای ناشناخته";
						    break;
						     case -20:
						    $msg = "پین نامعتبر";
						    break;
						     case -21:
						    $msg = "ip نامعتبر";
						    break;
						     case -22:
						    $msg = "مبلغ وارد شده کمتر از حداقل مجاز میباشد";
						    break;
						    case -23:
						    $msg = "مبلغ وارد شده بیشتر از حداکثر مبلغ مجاز هست";
						    break;
						      case -24:
						    $msg = "مبلغ وارد شده نامعتبر";
						    break;
						      case -26:
						    $msg = "درگاه غیرفعال است";
						    break;
						      case -27:
						    $msg = "آی پی مسدود شده است";
						    break;
						      case -28:
						    $msg = "آدرس کال بک نامعتبر است ، احتمال مغایرت با آدرس ثبت شده";
						    break;
						      case -29:
						    $msg = "آدرس کال بک خالی یا نامعتبر است";
						    break;
						      case -30:
						    $msg = "چنین تراکنشی یافت نشد";
						    break;
						      case -31:
						    $msg = "تراکنش ناموفق است";
						    break;
						      case -32:
						    $msg = "مغایرت مبالغ اعلام شده با مبلغ تراکنش";
						    break;
						      case -35:
						    $msg = "شناسه فاکتور اعلامی order_id نامعتبر است";
						    break;
						      case -36:
						    $msg = "پارامترهای برگشتی بانک bank_return نامعتبر است";
						    break;
						        case -38:
						    $msg = "تراکنش برای چندمین بار وریفای شده است";
						    break;
						      case -39:
						    $msg = "تراکنش در حال انجام است";
						    break;
                            case 1:
						    $msg = "پرداخت با موفقیت انجام گردید.";
						    break;
						    default:
						       $msg = "خطایی در اتصال رخ داده است : ".$josn['result'];
						}
					
					if(!empty($json['result']) AND $json['result'] == 1)
					{
					
					// Set Session
					$_SESSION[$sec] = [
						'price'=>$data_pack['snpay_price'] ,
						'order_id'=>$invoice_id ,
						'au'=>$json['au'] ,
					];
					
                    $db->query("UPDATE " . PREFIX . "_snpayments SET transid = '" . $json['au']. "' WHERE id = '" . $insert_id . "' AND verified = '0'");
                    echo ('<div style="display:none">'.$json['form'].'</div>Please wait ... <script language="javascript">document.payment.submit(); </script>');
                } else {
                    $db->query("DELETE FROM " . PREFIX . "_snpayments WHERE id = '".$insert_id."' AND verified = '0'");
              
                    $msgerror =$json['msg'];
                }
            }
        } else $msgerror = $lang['fields_required'];
        msgbox($lang['payment_error'], $msg);
    }
    if (file_exists(ROOT_DIR . '/templates/' . $config['skin'] . '/snpayment.tpl')) {
        $tpl->load_template("snpayment.tpl");
        $tpl->compile('content');
        $tpl->clear();
    }
}
