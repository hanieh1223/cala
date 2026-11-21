<?php
/**
 * Reference payment trigger.
 * Attach before the successful End Event on the approved branch only.
 * Do not attach to the rejected branch.
 */

$caseUid = (string) @@APPLICATION;
$requestNo = trim((string) @@REQUEST_NUMBER);
$total = (int) @@TOTAL_AMOUNT;
$paymentStatus = (string) @@PAYMENT_STATUS;
$method = (string) @@PAYMENT_METHOD;
$reference = trim((string) @@PAYMENT_REFERENCE);
$paymentDate = trim((string) @@PAYMENT_DATE);

if ((string) @@REQUEST_STATUS !== 'APPROVED' || $total > 2000000000) {
    throw new Exception('این درخواست مجاز به ورود به مرحله پرداخت نیست.');
}
if ((int) @@PAYMENT_AMOUNT !== $total) {
    throw new Exception('مبلغ پرداخت با مبلغ تأییدشده مطابقت ندارد.');
}
if (!in_array($paymentStatus, array('PAID', 'UNPAID'), true)) {
    throw new Exception('وضعیت پرداخت نامعتبر است.');
}
if (!in_array($method, array('CARD_TO_CARD', 'BANK_TRANSFER', 'CASH', 'OTHER'), true)) {
    throw new Exception('روش پرداخت نامعتبر است.');
}
if ($paymentStatus !== 'PAID') {
    throw new Exception('برای پایان موفق فرایند، وضعیت پرداخت باید «پرداخت شده» باشد.');
}
if ($paymentDate === '') {
    throw new Exception('تاریخ پرداخت الزامی است.');
}
if (in_array($method, array('CARD_TO_CARD', 'BANK_TRANSFER'), true) && $reference === '') {
    throw new Exception('برای پرداخت بانکی، شماره پیگیری الزامی است.');
}

function pmPaymentSqlString($value) {
    return "'" . addslashes((string) $value) . "'";
}

$sql = "INSERT INTO PURCHASE_PAYMENTS
(REQUEST_NUMBER, PAYMENT_AMOUNT, PAYMENT_STATUS, PAYMENT_METHOD,
 PAYMENT_REFERENCE, PAYMENT_DATE, PAYMENT_DESCRIPTION, CASE_UID)
VALUES (" .
pmPaymentSqlString($requestNo) . ", " . $total . ", 'PAID', " .
pmPaymentSqlString($method) . ", " .
pmPaymentSqlString($reference) . ", " .
pmPaymentSqlString($paymentDate) . ", " .
pmPaymentSqlString(@@PAYMENT_DESCRIPTION) . ", " .
pmPaymentSqlString($caseUid) . ")";

executeQuery($sql);

$sqlUpdate = "UPDATE PURCHASE_REQUESTS
SET REQUEST_STATUS = 'PAID'
WHERE CASE_UID = " . pmPaymentSqlString($caseUid) .
" AND REQUEST_NUMBER = " . pmPaymentSqlString($requestNo) .
" AND REQUEST_STATUS = 'APPROVED'";

executeQuery($sqlUpdate);
