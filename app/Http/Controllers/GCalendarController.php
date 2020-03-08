<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Google APiClient Package
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;

// File Facades
use Illuminate\Support\Facades\File;

class GCalendarController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public $credentials, $gclient;
    public function __construct(){
        $this->credentials = rtrim(app()->basePath('storage\app\credentials.json'));
        $this->gclient = new Google_Client();
        $this->gclient->setAccessType('offline');
        $this->gclient->setApplicationName("Penjadwalan_Mudik_2020");
        $this->gclient->setScopes(Google_Service_Calendar::CALENDAR);
        $this->gclient->setAuthConfig($this->credentials);
        $this->gclient->setPrompt('select_account consent');
        $this->gclient->setRedirectUri("http://localhost:8000/gcalendar");
    }

    public function getAuthUrl(Request $req){   
        $auth_url = $this->gclient->createAuthUrl();
        return ['code' => 1,'message' => "success",'oauth_url' => $auth_url]; 
    }

    public function getAccessToken(Request $req){
        $this->gclient->authenticate($req->input("auth"));
        return ['code' => 1,'message' => "success",'access_token' => $this->gclient->getAccessToken()];
    }

    public function getCalendar(Request $req){
        $access_token = $req->access_token;
        $this->gclient->setAccessToken($access_token);

        $calendar = new Google_Service_Calendar($this->gclient);
        $calendarId = 'primary';

        // Detail Event By Id (Optional)
        if($req->input("eventId")){
            $event = $calendar->events->get('primary', $req->input("eventId"));
            return ['code' => 1,'message' => "success",'result' => $event];
        }

        $optParams = array(
          'maxResults' => 10,
          'orderBy' => 'startTime',
          'singleEvents' => true,
          'timeMin' => date('c'),
        );
        $results = $calendar->events->listEvents($calendarId, $optParams);
        $events = $results->getItems();
        
        return ['code' => 1,'message' => "success",'result' => $events];
    }

    public function insertEvent(Request $req){
        $access_token = $req->access_token;
        $this->gclient->setAccessToken($access_token);
        
        $init_event = new Google_Service_Calendar_Event(array(
          'summary' => 'Mudik Lebaran 2020 ',
          'location' => 'Malang',
          'description' => 'Rencana Mudik Lebaran 2020',
          'start' => array(
            'dateTime' => '2020-05-28T09:00:00-07:00',
            'timeZone' => 'America/Los_Angeles',
          ),
          'end' => array(
            'dateTime' => '2020-06-04T17:00:00-07:00',
            'timeZone' => 'America/Los_Angeles',
          ),
          'recurrence' => array(
            'RRULE:FREQ=DAILY;COUNT=2'
          ),
          'attendees' => array(
            array('email' => 'azharogi@gmail.com'),
          ),
          'reminders' => array(
            'useDefault' => FALSE,
            'overrides' => array(
              array('method' => 'email', 'minutes' => 24 * 60),
              array('method' => 'popup', 'minutes' => 10),
            ),
          ),
        ));

        $calendar = new Google_Service_Calendar($this->gclient);
        $calendarId = 'primary';
        $event = $calendar->events->insert($calendarId, $init_event);
        
        return ['code' => 1,'message' => "success",'result' => "Event created: $event->htmlLink"];
    }
}
