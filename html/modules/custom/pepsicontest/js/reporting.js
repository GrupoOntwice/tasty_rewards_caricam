(function ($, Drupal, window) {


    var request_running = false;
    var data = new Array();
    var data1 = new Array();
    var data_final = new Array();
    var data_final2 = new Array();
    var contestid = '';
    var startdate = '';
    var enddate = '';
    var langs_arr = ['en', 'fr'];
    var tmp_data = '';
    var titles_arr = {'gender': 'Grouping by gender', 'province': 'Grouping by province'};

    var url = "/" + language + "/contests/reportingtool/datachart";

    google.load("visualization", "1", {packages: ["corechart", "table"]});

    $(".btnreporting").on('click', function () {
        $('.reports').hide();
        $('[id^="qcontainer_"]').hide();
        if ($('#contestname').val() == '') {
            alert('please select a contest');
            return false;
        }
        var report_type = 1;
        if ($('#raw_data').is(':checked')) {
            report_type = 2;
        }
        $('.qcontainer').html('');
        contestid = $('#contestname').val();
        $('[id^="qcontainer_"]').hide();
        switch (report_type) {
            case 1:
                var keys_arr = ['gender',
                    'province',
                    'date2',
                    'date',
                    'PollAnswersGroupedByDay',
                    'ReportPollChoicesParticipationCount',
                    'ReportContestsUniqueParticipationByDate',
                    'winners'
                ];
                $('.reports').css('display', 'block');
                setReportType1(keys_arr);
                break;
            case 2:
                var keys_arr = ['getReportContestParticipationData'];
                $('.reports.report' + report_type).css('display', 'block');
                setReportType2(keys_arr);
                break;
        }

    });

    function setReportType1(keys_arr)
    {
        data_final['PollAnswersGroupedByDay'] = new Array({'report': 'getReportPollAnswersGroupedByDay'});
        data_final['ReportPollChoicesParticipationCount'] = new Array({'report': 'getReportPollChoicesParticipationCount'});
        data_final['ReportContestsUniqueParticipationByDate'] = new Array({'report': 'getReportContestsUniqueParticipationByDate'});
        data_final['winners'] = new Array({'report': 'pickwinner'});
        $('[id^="qcontainer_' + contestid + '"]').show();
        startdate = $('#startdate').val();
        enddate = $('#enddate').val();
        data1['gender'] = new Array({'report': 'gender'}, {'contestid': contestid});
        data1['province'] = new Array({'report': 'province'}, {'contestid': contestid});
        data1['date'] = new Array({'report': 'date'}, {'contestid': contestid});
        data1['date2'] = new Array({'report': 'entry_count_by_day'}, {'contestid': contestid});

        for (var key in data1) {
            for (var i = 0; i < 2; i++) {
                tmp_data = data1[key];
                tmp_data = tmp_data.concat(new Array({'startdate': startdate}, {'enddate': enddate}, {'language': langs_arr[i]}));
                if ('date' == key) {
                    drawChartLine(tmp_data, url, key + 'container' + langs_arr[i], 'Grouping by dates', 'Line', 'Date', true, langs_arr[i]);
                } else if ('date2' == key) {
                    drawChartLine(tmp_data, url, key + 'container'+ langs_arr[i], 'Grouping entries count by dates', 'Line', 'Date', true, langs_arr[i]);
                } else {
                    drawChart(tmp_data, url, key + 'container'+ langs_arr[i], titles_arr[key] + ', language :' + langs_arr[i], 'Pie', capitalizeFirstLetter(key));
                }
            }
        }
        $('#' + contestid + ' input').each(function () {
            var questionid = $(this).val();
            var key = 'question';
            var the_question = new Array({'report': 'question'}, {'contestid': contestid}, {'questionid': questionid});
            var csv_url = "/" + language + "/contests/reportingtool/datachart/?contestid=" + contestid + '&startdate=' + startdate + '&enddate=' + enddate;
            data[key] = the_question;
            for (var i = 0; i < 2; i++) {
                tmp_data = data[key];
                var the_array = new Array({'startdate': startdate}, {'enddate': enddate}, {'language': langs_arr[i]});
                var merged = tmp_data.concat(the_array);
                drawBar(merged, url, 'qcontainer_' + contestid + '_' + questionid + '_' + langs_arr[i], langs_arr[i]);
                $('#qcontainer_' + contestid + '_' + questionid + '_' + langs_arr[i] + '_link').on('click', function (e) {
                    e.preventDefault();
                    if (request_running) {
                        return;
                    }
                    console.log('container='+$(this).attr('id'));
                    request_running = true;
                    getCsvFile(csv_url + '&language=' + langs_arr[i] + '&questionid=' + questionid + '&report=' + tmp_data[0]['report']);
                });
            }
        });
        getCsvReports(keys_arr);
        $(".btnwinnerspicking").on('click', function () {
            //drawChart();
            if ($('#contestname').val() == '') {
                alert('please select a contest');
                return false;
            }
            if ($('#startdate').val() == '') {
                alert('please select start date');
                return false;
            }
            if ($('#enddate').val() == '') {
                alert('please select end date');
                return false;
            }

            $('.qcontainer').html('');

            var contestid = $('#contestname').val();
            var startdate = $('#startdate').val();
            var enddate = $('#enddate').val();
            var url = "/" + language + "/contests/reportingtool/datachart";
        });
    }

    function setReportType2(keys_arr)
    {
        data_final['getReportContestParticipationData'] = new Array({'report': 'getReportContestParticipationData'});
        getCsvReports(keys_arr);
    }
    function getCsvReports(keys_arr)
    {
        var cnt_keys = keys_arr.length;
        $('#' + keys_arr.join('_link,#') + '_link').on('click', function (e) {
            e.preventDefault();
            var csv_url = "/" + language + "/contests/reportingtool/datachart/?contestid=" + contestid + '&startdate=' + startdate + '&enddate=' + enddate;
            for (var k = 0; k < cnt_keys; k++) {
                if (request_running) {
                    return;
                }
                if ($(this).attr('id').indexOf(keys_arr[k]) !== -1) {
                    if (typeof data_final[keys_arr[k]] != 'undefined') {
                        tmp_data = data_final[keys_arr[k]];
                    } else if (typeof data1[keys_arr[k]] != 'undefined') {
                        tmp_data = data1[keys_arr[k]];
                    }
                    if (typeof startdate != 'undefined') {
                        tmp_data = tmp_data.concat(new Array({'startdate': startdate}, {'enddate': enddate}));
                    }
                    request_running = true;
                    getCsvFile(csv_url + '&report=' + tmp_data[0]['report']);
                }
            }
        });
    }
    function capitalizeFirstLetter(string)
    {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    function drawBar(data , url, container, lang)
    {
        // Create and populate the data table.

        tmp_data = transformObjData(data);
        var dataArray;
        var question = '';
        var jsonData = $.ajax({
            url: url,
            dataType: "json",
            data: tmp_data,
            method: 'POST',
            async: false,
            success: function (datajson) {
                question = datajson.question;
                dataArray = datajson.rows;
            }
        });
        // Column names
        dataArray.unshift(['Answer', 'Entries']);
        if ("" !== question) {
            var data = google.visualization.arrayToDataTable(dataArray);
            // Create and draw the visualization.
            var chart = new google.visualization.ColumnChart(document.getElementById(container));
            chart.draw(data,
                {title: question + ', Language: ' + lang,
                    width:600, height:400,
                    vAxis: {title: "Answers entries"}, isStacked: true,
                    hAxis: {title: "Answer"}}
            );
        } else {
            $('#' + container).html('No data');
        }

    }

    function transformObjData(data)
    {
        var tmp_data = {};
        for(var key in data) {
            if (!data.hasOwnProperty(key)) continue;

            var obj = data[key];
            for (var prop in obj) {
                if (!obj.hasOwnProperty(prop)) continue;
                tmp_data[prop]  = obj[prop];
            }
        }
        return tmp_data;
    }

    function drawChart(data, url, container, title, chartype, fieldname)
    {
        var tmp_data = transformObjData(data);
        var dataArray;
        var jsonData = $.ajax({
            url: url,
            dataType: "json",
            data: tmp_data,
            method: 'POST',
            async: false,
            error: function(returnval) {
                console.log(returnval + " failure");
            },
            success: function (datajson) {
                dataArray = datajson;
            }
        });
        var total = getTotal(dataArray);

        // Adding tooltip column
        for (var i = 0; i < dataArray.length; i++) {
            dataArray[i].push(customTooltip(dataArray[i][0], dataArray[i][1], total));
            // Changing legend
            dataArray[i][0] = dataArray[i][0] + " " +
                dataArray[i][1] + " entries, " + ((dataArray[i][1] / total) * 100).toFixed(1) + "%";
        }

        // Column names
        dataArray.unshift([fieldname, 'Entries', 'Tooltip', 'Language']);
        var data = google.visualization.arrayToDataTable(dataArray);

        // Setting role tooltip
        data.setColumnProperty(2, 'role', 'tooltip');
        data.setColumnProperty(2, 'html', true);
        var options = {
            title: title + ' (Grand total : ' + total +' entries)',
            width: 900,
            height: 400,
            tooltip: { isHtml: true }
        };
        var chart = new google.visualization.PieChart(document.getElementById(container));
        chart.draw(data, options);
    }



    function drawChartLine(data, url, container, title, chartype, fieldname, monthly, language)
    {
        var tmp_data = transformObjData(data);
        var dataArray;
        var jsonData = $.ajax({
            url: url,
            dataType: "json",
            data: tmp_data ,
            method: 'POST',
            async: false,
            success: function (datajson) {
                dataArray = datajson
            }
        });
        var total = getTotal(dataArray);

        // Column names
        dataArray.unshift([fieldname, 'Entries']);

        var data = google.visualization.arrayToDataTable(dataArray);

        var options = {
            title: title + ', Language : ' + language + ' (Grand total : ' + total +' entries)',
            width: typeof(monthly) != 'undefined' ? 1200: 600,
            height: 400,
            hAxis: {
                title: 'Date'
            },
            isStacked: true,
            vAxis: {
                title: 'Entries'
            }
        };

        var chart = new google.visualization.LineChart(document.getElementById(container));
        chart.draw(data, options);
    }



    function customTooltip(name, value, total)
    {
        return name + '<br/><b>' + value + ' (' + ((value/total) * 100).toFixed(1) + '%)</b>';
    }

    function getTotal(dataArray)
    {
        var total = 0;
        for (var i = 0; i < dataArray.length; i++) {
            total += dataArray[i][1];
        }
        return total;
    }

    function getCsvFile(url)
    {
        console.log('url='+url);
        var jsonData = $.ajax({
            url: url + '&format=csv',
            dataType: "json",
            method: 'GET',
            async: false,
            success: function (datajson) {
                if ('' !== datajson) {
                    window.location.href = datajson;
                } else {
                    alert('No data available');
                }
            },
            complete: function() {
                request_running = false;
            }
        });
    }

    /* //contest entry count by age and gender
     data_entry_age_and_gender = {'contestid': contestid,'startdate':startdate,'enddate':enddate,'report':'age_and_gender'};
     drawChart(data_entry_age_and_gender, url, 'entry_age_and_gendercontainer', 'Grouping entries count by age and gender','Pie','Age and gender');

    //contest participant count by age and gender
     data_participant_age_and_gender = {'contestid': contestid,'startdate':startdate,'enddate':enddate,'report':'participant_count_by_age_and_gender'};
     drawChart(data_participant_age_and_gender, url, 'participant_age_and_gendercontainer', 'Grouping participant entries count by age and gender','Pie','Age and gender');


     //contest entry count by location
     data_location = {'contestid': contestid,'startdate':startdate,'enddate':enddate,'report':'entries_count_by_location'};
     drawChart(data_location, url, 'locationcontainer', 'Grouping entries count by location','Pie','Location');
    //reporting by question answers
    $('#entry_age_and_gender_link').on('click', function(e) {
     e.preventDefault();
     if (request_running) {
     return;
     }
     request_running = true;
     getCsvFile(csv_url + '&report=' + data_entry_age_and_gender.report);
     });
     $('#participant_age_and_gender_link').on('click', function(e) {
     e.preventDefault();
     if (request_running) {
     return;
     }
     request_running = true;
     getCsvFile(csv_url + '&report=' + data_participant_age_and_gender.report);
     });
     $('#location_link').on('click', function(e) {
     e.preventDefault();
     if (request_running) {
     return;
     }
     request_running = true;
     getCsvFile(csv_url + '&report=' + data_location.report);
     });*/
}(jQuery, Drupal, window));