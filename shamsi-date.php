<?php
// shamsi-date.php

function gregorianToJalali($gYear, $gMonth, $gDay) {
    $gDaysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    $jDaysInMonth = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];

    $gy = $gYear - 1600;
    $gm = $gMonth - 1;
    $gd = $gDay - 1;

    $gDayNo = 365 * $gy + intval(($gy + 3) / 4) - intval(($gy + 99) / 100) + intval(($gy + 399) / 400);

    for ($i = 0; $i < $gm; ++$i)
        $gDayNo += $gDaysInMonth[$i];
    if ($gm > 1 && (($gy % 4 == 0 && $gy % 100 != 0) || ($gy % 400 == 0)))
        $gDayNo++;
    $gDayNo += $gd;

    $jDayNo = $gDayNo - 79;

    $jNp = intval($jDayNo / 12053);
    $jDayNo %= 12053;
    $jy = 979 + 33 * $jNp + 4 * intval($jDayNo / 1461);
    $jDayNo %= 1461;

    if ($jDayNo >= 366) {
        $jy += intval(($jDayNo - 1) / 365);
        $jDayNo = ($jDayNo - 1) % 365;
    }

    for ($jMonth = 0; $jMonth < 11 && $jDayNo >= $jDaysInMonth[$jMonth]; ++$jMonth)
        $jDayNo -= $jDaysInMonth[$jMonth];
    $jDay = $jDayNo + 1;

    return [$jy, $jMonth + 1, $jDay];
}

function jalaliToGregorian($jYear, $jMonth, $jDay) {
    $gDaysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    $jDaysInMonth = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];

    $jy = $jYear - 979;
    $jm = $jMonth - 1;
    $jd = $jDay - 1;

    $jDayNo = 365 * $jy + intval($jy / 33) * 8 + intval(($jy % 33 + 3) / 4);
    for ($i = 0; $i < $jm; ++$i)
        $jDayNo += $jDaysInMonth[$i];

    $jDayNo += $jd;

    $gDayNo = $jDayNo + 79;

    $gy = 1600 + 400 * intval($gDayNo / 146097);
    $gDayNo %= 146097;

    $leap = true;
    if ($gDayNo >= 36525) {
        $gDayNo--;
        $gy += 100 * intval($gDayNo / 36524);
        $gDayNo %= 36524;

        if ($gDayNo >= 365) $gDayNo++;
        else $leap = false;
    }

    $gy += 4 * intval($gDayNo / 1461);
    $gDayNo %= 1461;

    if ($gDayNo >= 366) {
        $leap = false;
        $gDayNo--;
        $gy += intval($gDayNo / 365);
        $gDayNo %= 365;
    }

    for ($gm = 0; $gDayNo >= $gDaysInMonth[$gm] + ($gm == 1 && $leap); $gm++)
        $gDayNo -= $gDaysInMonth[$gm] + ($gm == 1 && $leap);
    $gd = $gDayNo + 1;

    return [$gy, $gm + 1, $gd];
}

function saveShamsiDate($date) {
    list($gYear, $gMonth, $gDay) = explode('-', date('Y-m-d', strtotime($date)));
    list($jYear, $jMonth, $jDay) = gregorianToJalali($gYear, $gMonth, $gDay);
    return sprintf('%04d-%02d-%02d %s', $jYear, $jMonth, $jDay, date('H:i:s'));
}

global $is_shamsi_loaded;
$is_shamsi_loaded = true;

add_filter('wp_insert_post_data', function($data) {
    global $is_shamsi_loaded;

    if ($is_shamsi_loaded) {
        if (isset($data['post_date'])) {
            $data['post_date'] = saveShamsiDate($data['post_date']);
        }
        if (isset($data['post_modified'])) {
            $data['post_modified'] = saveShamsiDate($data['post_modified']);
        }
    }

    return $data;
});
?>
