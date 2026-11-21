<?php
/**
 * Reference ProcessMaker 3.5.8 trigger.
 * Attach to the process start event / before the request Dynaform is rendered.
 * REQUEST_NUMBER is generated on the server and must be read-only in the form.
 *
 * Uniqueness is ultimately enforced by uq_purchase_request_number in SQL.
 * If a collision occurs, stop the case and regenerate rather than overwrite.
 */

$caseUid = trim((string) @@APPLICATION);
if ($caseUid === '') {
    throw new Exception('شناسه پرونده برای تولید شماره درخواست در دسترس نیست.');
}

$caseToken = strtoupper(substr(preg_replace('/[^a-fA-F0-9]/', '', $caseUid), 0, 10));
if ($caseToken === '') {
    throw new Exception('شناسه پرونده معتبر نیست؛ شماره درخواست تولید نشد.');
}

@@REQUEST_NUMBER = 'PR-' . date('Ymd') . '-' . $caseToken;
