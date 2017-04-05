$(function () {
    $('#search').popover({
        'content': 'A search is a combination of operator:value, where you can use:<ul>' +
            '<li><kbd>is:available</kbd>, <kbd>is:lent</kbd>, <kbd>is:lost</kbd> to search by device status;</li>' +
            '<li><kbd>imei:12345</kbd>, <kbd>mac:1:2:3:4:5</kbd> to search by IMEI or MAC address;</li>' +
            '<li><kbd>number:12</kbd> to search by device number;</li>' +
            '<li><kbd>fname:John</kbd>, <kbd>lname:Doe</kbd> to search by first or last name;</li>' +
            '<li><kbd>email:*@tld.com</kbd> to search by email address;</li>' +
            '<li><kbd>started:&gt;2014-01-01</kbd>, <kbd>ended:&lt;yesterday</kbd> to search by date of lending;</li>' +
            '<li><kbd>token:d6ff7f4c-c8a</kbd> to search by lending token;</li>' +
            '<li><kbd>segment:privamov</kbd> to search by lending segment.</li>' +
        '</ul> Values quand be quoted is they contain spaces.',
        'title': 'Search syntax',
        'html': true,
        'placement': 'bottom',
        'trigger': 'focus'
    });
});