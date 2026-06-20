<?php
/*
OpenCart Greece (ocgr)
Version: 2.0.0
Viva Payments for OpenCart 2.x
*/
// Tags
$_['failure_heading_title'] = 'Payment proccess has failed';

// Text
$_['text_title']           = 'Credit or Debit Card (Viva Payments)';
$_['text_credit_card']     = 'Credit Card Details';
$_['text_start_date']      = '(if available)';
$_['text_issue']           = '(for Maestro and Solo cards only)';
$_['text_wait']            = 'Please wait...';
$_['text_credit_card_table_header'] = 'Credit Card details';
$_['credit_card_expiration_date'] = 'Expiration (MM/YYYY):';
$_['text_credit_card_number'] = 'Credit card Number:';
$_['text_credit_card_holder_name'] = 'Holder Name:';
$_['text_credit_card_verification_code'] = 'CVV/CVV2 Code:';
$_['text_credit_card_installments'] = 'Installments';
$_['text_payment_failure_message']    = 'The payment proccess is failed please contact your credit card issuer Bank.';

// Entry
$_['entry_cc_type']        = 'Card Type:';
$_['entry_cc_number']      = 'Card Number:';
$_['entry_cc_start_date']  = 'Card Valid From Date:';
$_['entry_cc_expire_date'] = 'Card Expiry Date:';
$_['entry_cc_cvv2']        = 'Card Security Code (CVV2):';
$_['entry_cc_issue']       = 'Card Issue Number:';
// Button
$_['button_confirm']       = 'Confirm Order';
$_['button_failure_continue'] = 'Continue';
// Info
$_['info_redirect_message'] = 'This Site will redirect you to the secure site of Vivapayments';

//Transactions statusIds
$_['statusid_E']            = 'The transaction was not completed because of an error';
$_['statusid_A']            = 'The transaction is in progress';
$_['statusid_M']            = 'The cardholder has disputed the transaction with the issuing Bank';
$_['statusid_MA']           = 'Dispute Awaiting Response';
$_['statusid_MI']           = 'Dispute in Progress';
$_['statusid_ML']           = 'A disputed transaction has been refunded (Dispute Lost)';
$_['statusid_MW']           = 'Dispute Won';
$_['statusid_MS']           = 'Suspected Dispute';
$_['statusid_X']            = 'The transaction was cancelled by the merchant';
$_['statusid_R']            = 'The transaction has been fully or partially refunded';
$_['statusid_F']            = 'The transaction has been completed successfully';

?>