<?php
/*
OpenCart Greece (ocgr)
Version: 2.0.0
Viva Payments for OpenCart 2.x
*/
// Tags
$_['failure_heading_title'] = 'Η διαδικασία πληρωμής απέτυχε';

// Text
$_['text_title']           = 'Πιστωτική ή χρεωστική κάρτα (Viva Payments)';
$_['text_credit_card']     = 'Πληροφορίες κάρτας';
$_['text_start_date']      = '(if available)';
$_['text_issue']           = '(for Maestro and Solo cards only)';
$_['text_wait']            = 'Παρακαλώ περιμένετε!';
$_['text_credit_card_table_header'] = 'Πληροφορίες κάρτας';
$_['credit_card_expiration_date'] = 'Λήξη (MM/YYYY):';
$_['text_credit_card_number'] = 'Αριθμός Κάρτας:';
$_['text_credit_card_holder_name'] = 'Ονοματεπώνυμο κατόχου:';
$_['text_credit_card_verification_code'] = 'CVV/CVV2:';
$_['text_credit_card_installments'] = 'Δόσεις';
$_['text_payment_failure_message']    = 'Η διαδικασία πληρωμής απέτυχε. Παρακαλούμε επικοινωνήστε με την τράπεζα σας.';

// Entry
$_['entry_cc_type']        = 'Card Type:';
$_['entry_cc_number']      = 'Card Number:';
$_['entry_cc_start_date']  = 'Card Valid From Date:';
$_['entry_cc_expire_date'] = 'Card Expiry Date:';
$_['entry_cc_cvv2']        = 'Card Security Code (CVV2):';
$_['entry_cc_issue']       = 'Card Issue Number:';

// Button

$_['button_confirm']       = 'Επιβεβαίωση παραγγελίας';
$_['button_failure_continue'] = 'Συνέχεια';

// Info
$_['info_redirect_message'] = 'Πατώντας "επιβεβαίωση παραγγελίας" θα μεταφερθείτε στην ασφαλή σελίδα της Viva Payments για την πληρωμή σας.';

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