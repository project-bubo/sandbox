<?php

namespace Model;

final class AjaxModel extends BaseModel {

    
    /* CALENDAR SECTION */
    
    
    
    /**
     * Update calendar event
     * 
     * $event is received array from jQuery calendar.
     * Input array contains following items
     * 
     * - start
     * - end
     * - trainer_id
     * - exercise_id
     * - id (only in edit mode)
     * 
     * First two pamaeters (start and end) are serialized and stored as event_data
     * to [sqarena_calendar_events].
     * 
     * Other parametres are stored as foreign keys
     * 
     * @param type $event
     * @return type 
     */
    public function updateCalendarEvent($event) {
        $res = FALSE;

        // detect mode
        $mode = 'add';
        if (isset($event['id'])) {
            $mode = 'edit';
        }
        
        $eventId = isset($event['id']) ? $event['id'] : NULL;
               
        $serializedKeys = $this->_extractEventValues(array('start', 'end'), $event);
        $foreignKeys = $this->_extractEventValues(array('trainer_id', 'exercise_id', 'calendar_id'), $event);
        
        
        $data = array(
                    'event_data'      => serialize($serializedKeys),
        );
        
        $data = $data + $foreignKeys;

        $modifiedEvent = NULL;
        switch ($mode) {
            case 'add':
                // insert
                $res = $this->connection->query('INSERT INTO [sqarena_calendar_events] ',$data);
                if ($res) {
                    $res = $this->getLastId();
                }
                $modifiedEvent =  $this->getModifiedEvent($res);
                break;
            case 'edit':
                // update           
                $res = $this->connection->query('UPDATE [sqarena_calendar_events] SET ',$data, 'WHERE [event_id] = %i', $eventId);
                $modifiedEvent =  $this->getModifiedEvent($eventId);
                break;
        }
        
        return $modifiedEvent;
    }
    
    private function _extractEventValues($keys, $event) {
        $returnArray = array();
        foreach ($keys as $key) {
            $returnArray[$key] = ($event[$key] && $event[$key] != 'null') ? $event[$key] : NULL;
        }
        return $returnArray;
    }
    
    
    public function getModifiedEvent($eventId) {
        return $this->_queryModifiedEvent($eventId)->fetch();
    }
    
    private function _queryModifiedEvent($eventId) {
        return $this->connection->query("SELECT 
                                            ev.event_id,
                                            ex.name as title,
                                            CONCAT(t.name,' ',t.surname) as body,
                                            d.color
                                            FROM 
                                                [sqarena_calendar_events] ev
                                            JOIN
                                                [sqarena_exercises] ex
                                            ON
                                                ex.exercise_id = ev.exercise_id
                                            JOIN
                                                [sqarena_difficulties] d
                                            ON
                                                d.difficulty_id = ex.difficulty_id
                                            JOIN
                                                [sqarena_trainers] t
                                            ON
                                                t.[trainer_id] = ev.[trainer_id]
                                            WHERE ev.event_id = %i
                                            ", $eventId);
    }
    
    private function _queryEvents($eventId = NULL) {
        return $this->connection->query("SELECT 
                                            ev.*,
                                            ex.name as title,
                                            CONCAT(t.name,' ',t.surname) as body,
                                            d.color
                                            FROM 
                                                [sqarena_calendar_events] ev
                                            JOIN
                                                [sqarena_exercises] ex
                                            ON
                                                ex.exercise_id = ev.exercise_id
                                            JOIN
                                                [sqarena_difficulties] d
                                            ON
                                                d.difficulty_id = ex.difficulty_id
                                            JOIN
                                                [sqarena_trainers] t
                                            ON
                                                t.[trainer_id] = ev.[trainer_id]
                                            %if WHERE ev.event_id = %i
                                            ", ($eventId !== NULL), $eventId);
    }
    
    
    public function getAllCalendarEvents($calendarId) {
        return $this->connection->fetchAll("SELECT 
                                                ev.*,
                                                ex.name as title,
                                                CONCAT(t.name,' ',t.surname) as body,
                                                d.color
                                                FROM 
                                                    [sqarena_calendar_events] ev
                                                JOIN
                                                    [sqarena_exercises] ex
                                                ON
                                                    ex.exercise_id = ev.exercise_id
                                                JOIN
                                                    [sqarena_difficulties] d
                                                ON
                                                    d.difficulty_id = ex.difficulty_id
                                                JOIN
                                                    [sqarena_calendars] c
                                                ON
                                                    c.calendar_id = ev.calendar_id
                                                JOIN
                                                    [sqarena_trainers] t
                                                ON
                                                    t.[trainer_id] = ev.[trainer_id]
                                                WHERE c.calendar_id = %i
                                                ", $calendarId);
    }
    
    public function getEvent($eventId) {
        return $this->_queryEvents($eventId)->fetch();
    }
    
    public function deleteEvent($eventId) {
        return $this->connection->query("DELETE FROM [sqarena_calendar_events] WHERE [event_id] = %i", $eventId);
    }

}