function getTuesdays(month, year) {
    var d = new Date(year, month, 1),
        tuesdays = [];

    d.setDate(d.getDate() + (9 - d.getDay()) % 7)
    while (d.getMonth() === month) {
        tuesdays.push(new Date(d.getTime()));
        d.setDate(d.getDate() + 7);
    }

    return tuesdays;
}

var today = new Date(),
    theseTuesdays = getTuesdays(today.getMonth(), today.getFullYear()),
    next;

theseTuesdays.some(function (tuesday, index) {
    if (index % 2 === 1 && tuesday > today) {
        next = tuesday;
        return true;
    }
    return false;
});

//alert(moment(next).format("Do MMMM YYYY"));
