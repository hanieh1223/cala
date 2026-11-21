<?php
/**
 * Reference persistence trigger.
 * Attach after server-side calculation, on both decision branches.
 *
 * IMPORTANT: adapt table connection/quoting to your ProcessMaker installation.
 * The code uses ProcessMaker executeQuery() and escaped string literals.
 * Run with a DB account restricted to the three workflow tables.
 */

$caseUid = (string) @@APPLICATION;
$requestNo = trim((string) @@REQUEST_NUMBER);
if ($caseUid === '' || $requestNo === '') {
    throw new Exception('شناسه پرونده یا شماره درخواست موجود نیست.');
}

function pmSqlString($value) {
    return "'" . addslashes((string) $value) . "'";
}

$status = (string) @@REQUEST_STATUS;
if ($status !== 'APPROVED' && $status !== 'REJECTED') {
    throw new Exception('وضعیت بررسی نامعتبر است.');
}

$threshold = 2000000000;
$total = (int) @@TOTAL_AMOUNT;
if (($status === 'APPROVED' && $total > $threshold) ||
    ($status === 'REJECTED' && $total <= $threshold)) {
    throw new Exception('ناسازگاری نتیجه بررسی با مبلغ محاسبه‌شده.');
}

// Unique CASE_UID constraints make this operation idempotent only when
// duplicate-key handling is implemented for the installed DB adapter.
// Before production, wrap the two inserts in a transaction and use
// INSERT ... ON DUPLICATE KEY UPDATE with the installation's DB API.

$sql = "INSERT INTO PURCHASE_REQUESTS
(REQUEST_NUMBER, REQUESTER_NAME, REQUESTER_PHONE, PRODUCT_NAME,
 PRODUCT_DESCRIPTION, QUANTITY, UNIT_PRICE, TOTAL_AMOUNT, REQUEST_DATE,
 REQUEST_STATUS, REJECTION_REASON, CASE_UID)
VALUES (" .
pmSqlString($requestNo) . ", " .
pmSqlString(@@REQUESTER_NAME) . ", " .
pmSqlString(@@REQUESTER_PHONE) . ", " .
pmSqlString(@@PRODUCT_NAME) . ", " .
pmSqlString(@@PRODUCT_DESCRIPTION) . ", " .
(int) @@QUANTITY . ", " . (int) @@UNIT_PRICE . ", " . $total . ", " .
pmSqlString(@@REQUEST_DATE) . ", " .
pmSqlString($status) . ", " .
pmSqlString($status === 'REJECTED' ? @@REVIEW_REASON : '') . ", " .
pmSqlString($caseUid) . ")";

executeQuery($sql);

$sqlReview = "INSERT INTO PURCHASE_REVIEWS
(REQUEST_NUMBER, TOTAL_AMOUNT, THRESHOLD_AMOUNT, REVIEW_RESULT,
 REVIEW_REASON, REVIEW_DATE, CASE_UID)
VALUES (" .
pmSqlString($requestNo) . ", " . $total . ", " . $threshold . ", " .
pmSqlString($status) . ", " .
pmSqlString(@@REVIEW_REASON) . ", " .
pmSqlString(@@REVIEW_DATE) . ", " .
pmSqlString($caseUid) . ")";

executeQuery($sqlReview);
