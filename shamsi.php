<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

// shamsi-date.php

function gregorian_to_jalali( $g_year, $g_month, $g_day ) {
    $g_days_in_month = [ 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ];
    $j_days_in_month = [ 31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29 ];

    $gy = $g_year - 1600;
    $gm = $g_month - 1;
    $gd = $g_day - 1;

    $g_day_no = 365 * $gy + intval( ( $gy + 3 ) / 4 ) - intval( ( $gy + 99 ) / 100 ) + intval( ( $gy + 399 ) / 400 );

    for ( $i = 0; $i < $gm; ++$i ) {
        $g_day_no += $g_days_in_month[ $i ];
    }

    if ( $gm > 1 && ( ( $gy % 4 == 0 && $gy % 100 != 0 ) || ( $gy % 400 == 0 ) ) ) {
        $g_day_no++;
    }

    $g_day_no += $gd;

    $j_day_no = $g_day_no - 79;
    $j_np     = intval( $j_day_no / 12053 );
    $j_day_no %= 12053;

    $jy = 979 + 33 * $j_np + 4 * intval( $j_day_no / 1461 );
    $j_day_no %= 1461;

    if ( $j_day_no >= 366 ) {
        $jy += intval( ( $j_day_no - 1 ) / 365 );
        $j_day_no = ( $j_day_no - 1 ) % 365;
    }

    for ( $j_month = 0; $j_month < 11 && $j_day_no >= $j_days_in_month[ $j_month ]; ++$j_month ) {
        $j_day_no -= $j_days_in_month[ $j_month ];
    }

    $j_day   = $j_day_no + 1;
    $j_month = $j_month + 1;

    return [ $jy, $j_month, $j_day ];
}

function shamsi_time_difference_from_now( $shamsi_date ) {
    // Requires WordPress function current_time()
    if ( ! function_exists( 'current_time' ) ) {
        return $shamsi_date;
    }

    $current_gregorian_date = current_time( 'mysql' );
    list( $g_year, $g_month, $g_day ) = explode( '-', date( 'Y-m-d', strtotime( $current_gregorian_date ) ) );
    list( $j_year_now, $j_month_now, $j_day_now ) = gregorian_to_jalali( $g_year, $g_month, $g_day );

    $current_time     = date( 'H:i:s', strtotime( $current_gregorian_date ) );
    $current_date_shamsi = sprintf( '%04d-%02d-%02d %s', $j_year_now, $j_month_now, $j_day_now, $current_time );

    $now         = new DateTime( $current_date_shamsi );
    $ticket_date = new DateTime( $shamsi_date );
    $interval    = $now->diff( $ticket_date );

    if ( $interval->m + ( $interval->y * 12 ) > 6 ) {
        return $shamsi_date;
    }

    if ( $interval->y > 0 ) {
        return $interval->y . ' years ago';
    } elseif ( $interval->m > 0 ) {
        return $interval->m . ' months ago';
    } elseif ( $interval->d > 0 ) {
        return $interval->d . ' days ago';
    } elseif ( $interval->h > 0 ) {
        return $interval->h . ' hours ago';
    } elseif ( $interval->i > 0 ) {
        return $interval->i . ' minutes ago';
    } else {
        return 'Just now';
    }
}

function convert_to_shamsi_or_original( $date_time, $options = [] ) {
    if ( ! $date_time || $date_time === '-' ) {
        return $date_time;
    }

    if ( preg_match( '/^(\d{4})[\/\-]/', $date_time, $matches ) ) {
        $year = intval( $matches[1] );
        if ( $year < 1500 ) {
            return $date_time;
        }
    }

    if ( preg_match( '/^(\d{4})\/(\d{2})\/(\d{2})(?:\s+(\d{2}):(\d{2}))?/', $date_time, $matches ) ) {
        $input_y = intval( $matches[1] );
        $input_m = intval( $matches[2] );
        $input_d = intval( $matches[3] );
        $h       = isset( $matches[4] ) ? intval( $matches[4] ) : 0;
        $i       = isset( $matches[5] ) ? intval( $matches[5] ) : 0;

        if ( $input_y >= 1300 && $input_y <= 1500 && $input_m >= 1 && $input_m <= 12 && $input_d >= 1 && $input_d <= 31 ) {
            $month_names = [
                1  => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر', 5 => 'مرداد', 6 => 'شهریور',
                7  => 'مهر',     8 => 'آبان',     9 => 'آذر',   10 => 'دی', 11 => 'بهمن',  12 => 'اسفند'
            ];

            $show_month_text = $options['month_as_text'] ?? false;
            $month_part      = $show_month_text ? $month_names[ $input_m ] : sprintf( '%02d', $input_m );
            $has_time        = ( $h != 0 || $i != 0 || str_contains( $date_time, ':' ) );

            if ( $show_month_text ) {
                return $has_time
                    ? sprintf( '%d %s %04d - %02d:%02d', $input_d, $month_part, $input_y, $h, $i )
                    : sprintf( '%d %s %04d', $input_d, $month_part, $input_y );
            } else {
                return $has_time
                    ? sprintf( '%02d:%02d - %04d/%s/%02d', $h, $i, $input_y, $month_part, $input_d )
                    : sprintf( '%04d/%s/%02d', $input_y, $month_part, $input_d );
            }
        }
    }

    $ts = strtotime( $date_time );
    if ( ! $ts ) {
        return $date_time;
    }

    $g_y = date( 'Y', $ts );
    $g_m = date( 'm', $ts );
    $g_d = date( 'd', $ts );

    list( $j_y, $j_m, $j_d ) = gregorian_to_jalali( $g_y, $g_m, $g_d );

    $month_names = [
        1  => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر', 5 => 'مرداد', 6 => 'شهریور',
        7  => 'مهر',     8 => 'آبان',     9 => 'آذر',   10 => 'دی', 11 => 'بهمن',  12 => 'اسفند'
    ];

    $show_month_text = $options['month_as_text'] ?? false;
    $month_part      = $show_month_text ? $month_names[ intval( $j_m ) ] : sprintf( '%02d', $j_m );

    $h = date( 'H', $ts );
    $i = date( 'i', $ts );

    if ( $show_month_text ) {
        return ( $h == '00' && $i == '00' && ! str_contains( $date_time, ':' ) )
            ? sprintf( '%02d %s %04d', $j_d, $month_part, $j_y )
            : sprintf( '%02d %s %04d - %02d:%02d', $j_d, $month_part, $j_y, $h, $i );
    } else {
        return ( $h == '00' && $i == '00' && ! str_contains( $date_time, ':' ) )
            ? sprintf( '%04d/%s/%02d', $j_y, $month_part, $j_d )
            : sprintf( '%02d:%02d - %04d/%s/%02d', $h, $i, $j_y, $month_part, $j_d );
    }
}
