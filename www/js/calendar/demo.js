$(document).ready(function() {


   var $calendar = $('#calendar');
   

   $calendar.weekCalendar({
      calendarId: calendarId,
      data: loadCalendarEventsLink,
      date: new Date(2012, 1, 1),
      buttons: false,
      timeslotsPerHour : 4,
      allowCalEventOverlap : false,
      overlapEventsSeparate: false,
      firstDayOfWeek : 1,
      use24Hour: true,
      useShortDayNames: false,
      businessHours :{start: 7, end: 22, limitDisplay: true },
      daysToShow : 7,
      height : function($calendar) {
         return $(window).height() - $("h1").outerHeight() - 1;
      },
      eventRender : function(calEvent, $event) {
        $event.css("backgroundColor", calEvent.color);
         
      },
      draggable : function(calEvent, $event) {
         return calEvent.readOnly != true;
      },
      resizable : function(calEvent, $event) {
         return calEvent.readOnly != true;
      },
      eventNew : function(calEvent, $event) {
         var $dialogContent = $("#event_edit_container");
         resetForm($dialogContent);
         var startField = $dialogContent.find(".calendar-start").val(calEvent.start);
         var endField = $dialogContent.find(".calendar-end").val(calEvent.end);
         
         var exerciseField = $dialogContent.find(".calendar-exercise_id");
         var trainerField = $dialogContent.find(".calendar-trainer_id");
         //var bodyField = $dialogContent.find("textarea[name='body']");


         $dialogContent.dialog({
            modal: true,
            title: "Přidat položku do rozvrhu",
            close: function() {
               $dialogContent.dialog("destroy");
               $dialogContent.hide();
               $('#calendar').weekCalendar("removeUnsavedEvents");
            },
            buttons: {
               save : function() {
//                  calEvent.id = id;
//                  id++;
                  calEvent.start = new Date(startField.val());
                  calEvent.end = new Date(endField.val());
                  
                  calEvent.trainer_id = trainerField.val();
                  calEvent.exercise_id = exerciseField.val();
                  //calEvent.body = bodyField.val();
                  //calEvent.title = 'Ahoj';
                  //calEvent.body = 'body';
              
                  var $alert = '';
              
                  if (trainerField.val() == '') {
                      $alert = 'Zadejte trenéra';
                  } else if (exerciseField.val() == '') {
                      $alert = 'Zadejte cvičení';
                  }

                  if ($alert == '') {

                      $.get(calendarEventChangedLink, getEventDataForAjaxRequest(calEvent), function(data) {
                            //alert(data);
                            calEvent.id = data.event_id;
                            calEvent.title = data.title;
                            calEvent.body = data.body;
                            calEvent.color = data.color;

                            $calendar.weekCalendar("removeUnsavedEvents");
                            $calendar.weekCalendar("updateEvent", calEvent);
                            $dialogContent.dialog("close");
                      });

                      $calendar.weekCalendar("removeUnsavedEvents");
                      $calendar.weekCalendar("updateEvent", calEvent);
                  
                   } else {
                       alert($alert);
                   }
                  
               },
               cancel : function() {
                  $dialogContent.dialog("close");
               }
            }
         }).show();

         //$dialogContent.find(".date_holder").text($calendar.weekCalendar("formatDate", calEvent.start));
         setupStartAndEndTimeFields(startField, endField, calEvent, $calendar.weekCalendar("getTimeslotTimes", calEvent.start));

      },
      eventDrop : function(calEvent, $event) {
          $.get(calendarEventChangedLink, getEventDataForAjaxRequest(calEvent));
      },
      eventResize : function(calEvent, $event) {          
          $.get(calendarEventChangedLink, getEventDataForAjaxRequest(calEvent));
      },
      eventClick : function(calEvent, $event) {

         if (calEvent.readOnly) {
            return;
         }

         var $dialogContent = $("#event_edit_container");
         resetForm($dialogContent);
         var startField = $dialogContent.find(".calendar-start");
         var endField = $dialogContent.find(".calendar-end");
         
         var exerciseField = $dialogContent.find(".calendar-exercise_id").val(calEvent.exercise_id);
         var trainerField = $dialogContent.find(".calendar-trainer_id").val(calEvent.trainer_id);

         $dialogContent.dialog({
            modal: true,
            title: "Editovat položku kalendáře",
            close: function() {
               $dialogContent.dialog("destroy");
               $dialogContent.hide();
               $('#calendar').weekCalendar("removeUnsavedEvents");
            },
            buttons: {
               save : function() {

                  calEvent.start = new Date(startField.val());
                  calEvent.end = new Date(endField.val());
                  
                  calEvent.trainer_id = trainerField.val();
                  calEvent.exercise_id = exerciseField.val();

                  var $alert = '';
              
                  if (trainerField.val() == '') {
                      $alert = 'Zadejte trenéra';
                  } else if (exerciseField.val() == '') {
                      $alert = 'Zadejte cvičení';
                  }

                  if ($alert == '') {
                        $.get(calendarEventChangedLink, getEventDataForAjaxRequest(calEvent), function(data) {
                            calEvent.id = data.event_id;
                            calEvent.title = data.title;
                            calEvent.body = data.body;
                            calEvent.color = data.color;

                            $calendar.weekCalendar("updateEvent", calEvent);

                            $dialogContent.dialog("close");
                      });
                  } else {
                      alert($alert);
                  }
                  
                  

               },
               "delete" : function() {
                   $.get(calendarEventDeleteLink, {'eventId':calEvent.id}, function(payload) {
                        $calendar.weekCalendar("removeEvent", calEvent.id);
                        $dialogContent.dialog("close");
                  });
                  
               },
               cancel : function() {
                  $dialogContent.dialog("close");
               }
            }
         }).show();

//         startField.val(calEvent.start);
//         endField.val(calEvent.end);
//         exerciseField.val(calEvent.start);
//         trainerField.val(calEvent.end);
         
//         var startField = $dialogContent.find("select[name='start']").val(calEvent.start);
//         var endField = $dialogContent.find("select[name='end']").val(calEvent.end);
         //$dialogContent.find(".date_holder").text($calendar.weekCalendar("formatDate", calEvent.start));
         setupStartAndEndTimeFields(startField, endField, calEvent, $calendar.weekCalendar("getTimeslotTimes", calEvent.start));
         //$(window).resize().resize(); //fixes a bug in modal overlay size ??

      },
      eventMouseover : function(calEvent, $event) {
      },
      eventMouseout : function(calEvent, $event) {
      },
      noEvents : function() {

      }
//      data : function(start, end, callback) {
//         callback(getEventData());
//      }
   });

   function getEventDataForAjaxRequest($event) {
       return {
                'event': 
                        {
                        'id'            :   $event.id,
                        'start'         :   $event.start.getTime(),
                        'end'           :   $event.end.getTime(),
                        'trainer_id'    :   $event.trainer_id,
                        'exercise_id'   :   $event.exercise_id,
                        'calendar_id'   :   calendarId
                        }
               };
   } 

   function resetForm($dialogContent) {
      $dialogContent.find("input").val("");
      $dialogContent.find("select").val("");
   }

   function getEventData() {
      var year = new Date().getFullYear();
      var month = new Date().getMonth();
      var day = new Date().getDate();

      return {
         events : [
            {
               "id":1,
               "start": new Date(year, month, day, 12),
               "end": new Date(year, month, day, 13, 30),
               "title":"Lunch with Mike",
               "color": "#555555"
            }

         ]
      };
   }


   /*
    * Sets up the start and end time fields in the calendar event
    * form for editing based on the calendar event being edited
    */
   function setupStartAndEndTimeFields($startTimeField, $endTimeField, calEvent, timeslotTimes) {

      for (var i = 0; i < timeslotTimes.length; i++) {
         var startTime = timeslotTimes[i].start;
         var endTime = timeslotTimes[i].end;
         var startSelected = "";
         if (startTime.getTime() === calEvent.start.getTime()) {
            startSelected = "selected=\"selected\"";
         }
         var endSelected = "";
         if (endTime.getTime() === calEvent.end.getTime()) {
            endSelected = "selected=\"selected\"";
         }
         $startTimeField.append("<option value=\"" + startTime + "\" " + startSelected + ">" + timeslotTimes[i].startFormatted + "</option>");
         $endTimeField.append("<option value=\"" + endTime + "\" " + endSelected + ">" + timeslotTimes[i].endFormatted + "</option>");

      }
      $endTimeOptions = $endTimeField.find("option");
      $startTimeField.trigger("change");
   }

   var $endTimeField = $("select[name='end']");
   var $endTimeOptions = $endTimeField.find("option");

   //reduces the end time options to be only after the start time options.
   $("select[name='start']").change(function() {
      var startTime = $(this).find(":selected").val();
      var currentEndTime = $endTimeField.find("option:selected").val();
      $endTimeField.html(
            $endTimeOptions.filter(function() {
               return startTime < $(this).val();
            })
            );

      var endTimeSelected = false;
      $endTimeField.find("option").each(function() {
         if ($(this).val() === currentEndTime) {
            $(this).attr("selected", "selected");
            endTimeSelected = true;
            return false;
         }
      });

      if (!endTimeSelected) {
         //automatically select an end date 2 slots away.
         $endTimeField.find("option:eq(1)").attr("selected", "selected");
      }

   });


   var $about = $("#about");

   $("#about_button").click(function() {
      $about.dialog({
         title: "About this calendar demo",
         width: 600,
         close: function() {
            $about.dialog("destroy");
            $about.hide();
         },
         buttons: {
            close : function() {
               $about.dialog("close");
            }
         }
      }).show();
   });


});
