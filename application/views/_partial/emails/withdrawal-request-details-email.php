<?php defined('SYSTEM_INIT') or die('Invalid Usage');
$paymentMethods = User::getAffiliatePaymentMethodArr($siteLangId);
$payoutPlugins = Plugin::getNamesByType(Plugin::TYPE_PAYOUTS, $siteLangId);
// include_once EmailHandler::getTemplatePath(__FILE__);
$str = '<table cellspacing="0" cellpadding="0" border="0" width="100%" style="border:1px solid #ddd; border-collapse:collapse;">
    <tr>
    <td width="30%" style="padding:10px;background:#eee;font-size:13px;border:1px solid #ddd; color:#333; font-weight:bold;">' . Labels::getLabel('LBL_Withdrawal_Mode', $siteLangId) . '</td>
    <td width="70%" style="padding:10px;background:#eee;font-size:13px; border:1px solid #ddd;color:#333; font-weight:bold;">' . Labels::getLabel('LBL_Account_Details', $siteLangId) . '</td>    
    </tr>';
$pluginKeyName = '';
if ($data['withdrawal_payment_method'] == 0) {
    $data['withdrawal_payment_method'] = User::AFFILIATE_PAYMENT_METHOD_BANK;
}
$methodType = $paymentMethods + $payoutPlugins;
$methodName = (isset($data['withdrawal_payment_method']) && isset($methodType[$data['withdrawal_payment_method']]) ? $methodType[$data['withdrawal_payment_method']] : Labels::getLabel('LBL_N/A', $siteLangId));

if (!in_array($data['withdrawal_payment_method'], array_keys($paymentMethods)) && in_array($data['withdrawal_payment_method'], array_keys($payoutPlugins))) {
    $pluginKeyName =  Plugin::getAttributesById($data['withdrawal_payment_method'], 'plugin_code');
} else {
    $pluginKeyName = $methodName;
}


$txt = '';
switch ($data['withdrawal_payment_method']) {
    case User::AFFILIATE_PAYMENT_METHOD_CHEQUE:
        $txt .= '<strong>' . Labels::getLabel('LBL_Cheque_Payee_Name', $siteLangId) . ': </strong>' . $data['withdrawal_cheque_payee_name'];
        break;

    case User::AFFILIATE_PAYMENT_METHOD_BANK:
        $txt = '<strong>' . Labels::getLabel('LBL_Bank_Name', $siteLangId) . ': </strong>' . $data["withdrawal_bank"] . '<br>';
        $txt .= '<strong>' . Labels::getLabel('LBL_A/C_Name', $siteLangId) . ': </strong>' . $data["withdrawal_account_holder_name"] . '<br>';
        $txt .= '<strong>' . Labels::getLabel('LBL_A/C_Number', $siteLangId) . ': </strong>' . $data["withdrawal_account_number"] . '<br>';
        $txt .= '<strong>' . Labels::getLabel('LBL_IFSC_Code/Swift_Code', $siteLangId) . ': </strong>' . $data["withdrawal_ifc_swift_code"] . '<br>';
        $txt .= '<strong>' . Labels::getLabel('LBL_Bank_Address', $siteLangId) . ': </strong>' . $data["withdrawal_bank_address"] . '<br>';
        break;

    case User::AFFILIATE_PAYMENT_METHOD_PAYPAL:
        $txt .= '<strong>' . Labels::getLabel('LBL_Paypal_Email_Account', $siteLangId) . ': </strong>' . $data['withdrawal_paypal_email_id'];
        break;

    default:
        if (isset($data['payout_detail']) && !empty($data['payout_detail'])) {
            foreach (explode(',', $data["payout_detail"]) as $detail) {
                $field = explode(':', $detail);
                if (!empty($field) && 'amount' != $field[0] && isset($field[1]) && !empty($field[1])) {
                    $txt .= '<strong>' . ucwords(str_replace('_', ' ', $field[0])) . ': </strong>' . $field[1] . '<br>';
                }
            }
        }
        break;
}

if (!empty($data["withdrawal_instructions"])) {
    $txt .= '<br><strong>' . Labels::getLabel('LBL_Instructions', $siteLangId) . ': </strong>' . $data["withdrawal_instructions"];
}

$str .= '<tr>
    <td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">
        ' . $pluginKeyName . '</td>
    <td style="padding:10px;font-size:13px; color:#333;border:1px solid #ddd;">' . $txt . '</td>            
    </tr>';
echo $str;
