function next3rdTuesday(diff) {
    var next = new Date.today().moveToNthOccurrence(2, 3);
    var time = new Date.today().setTimeToNow();
    if (time.isAfter(next.addHours(18).addMinutes(59)))
    {
            next = Date.today().addMonths(1).moveToNthOccurrence(2, 3);
    }
    if (diff != "1")
    {
        var dateText;
        if (time.same().day(next))
        {
            dateText = 'Today';
        }
        else if (time.addDays(1).same().day(next))
        {
            dateText = 'Tomorrow';
        }
        else
        {
            dateText = next.toString('dddd dS MMMM');
        }
        return dateText;
    } else {
        var future  = next.getTime();
        var present = Date.today().setTimeToNow().getTime();
        var diff    = Date.today().setTimeToNow().getTime() + Math.floor((future - present)/1000);

        return next;
    }
}

document.getElementById("next3rdTuesday").innerHTML = next3rdTuesday();

jQ = jQuery.noConflict();
jQ(document).ready(function() {
   console.log('next: ' + next3rdTuesday());
    jQ('#countDownWrapper').countdown({
        until: next3rdTuesday(1),
        expiryText: "Oh yes, we're live!"
    });
});