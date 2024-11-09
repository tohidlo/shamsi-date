Shamsi Date

Shamsi Date is a simple PHP script to convert Gregorian dates to the Jalali (Shamsi) calendar and vice versa. This code is useful for PHP developers, especially those building or extending CMS platforms like WordPress for Persian-speaking audiences.
Features

    Convert Gregorian to Shamsi (Jalali) Date: Using the gregorianToJalali function.
    Convert Shamsi (Jalali) to Gregorian Date: Using the jalaliToGregorian function.
    WordPress Compatible: Includes a function to modify post_date and post_modified dates in WordPress.

Usage

    Add the shamsi.php file to your project.

    Include the file as follows:

    include 'shamsi.php';

Use the following functions to convert dates:

    Convert Gregorian to Jalali:

    list($jy, $jm, $jd) = gregorianToJalali(2023, 10, 31);
    echo "$jy-$jm-$jd"; // Example output: 1402-8-9

Convert Jalali to Gregorian:

    list($gy, $gm, $gd) = jalaliToGregorian(1402, 8, 9);
    echo "$gy-$gm-$gd"; // Example output: 2023-10-31

In WordPress, you can use the wp_insert_post_data filter to store post dates in the Shamsi calendar format.

Changelog v1.1

    Added Time Support: The saveShamsiDate function now stores both the date and time in the Shamsi format (date and time including hours, minutes, and seconds).
    Improved WordPress Integration: The plugin automatically converts and saves post_date and post_modified fields in the Shamsi format, including the time


Notes

    This code uses precise calculations to convert dates without requiring any external libraries.
    In WordPress, ensure that this filter is correctly applied so that changes in post dates don’t conflict with other date fields.

Contributing

If you have suggestions or improvements, feel free to contribute on GitHub.

Author

    Tohidlo
    GitHub: @tohidlo
















