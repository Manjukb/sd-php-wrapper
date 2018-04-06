<?php

namespace serverdensity\Api;

class Alerts extends AbstractApi
{
    /**
    * Create an alert
    * @link     https://developer.serverdensity.com/docs/creating-an-alert
    * @param    array  $metric       with the basic attributes
    * @param    array  $comparison  with all its recipients
    * @param    array  $value        with seconds, enabled and displayunit
    * @param    array  $subjectType      with seconds, enabled and displayunit
    * @param    array  $subject       with the basic attributes
    * @param    array  $recipients  with all its recipients
    * @param    array  $wait        with seconds, enabled and displayunit
    * @param    array  $repeat      with seconds, enabled and displayunit
    * @return   an array that is the alert
    */
    public function create($metric, $comparison, $value, $subjectType, $subject, $recipients, $wait, $repeat, $tags){
        $alert = array(
            'metric' => $metric,
            'comparison' => $comparison,
            'value' => $value,
            'scope' => '{"scope":{"type":"'.$subjectType.'","value":"'.$subject.'"}}',
        );
        $alert['recipients'] = json_encode($recipients);
        $alert['wait'] = json_encode($wait);
        $alert['repeat'] = json_encode($repeat);
        $alert['tags'] = json_encode($tags);

        return $this->post('alerts/v3/configs/', $alert);
    }

    /**
    * Delete alert by ID
    * @link     https://developer.serverdensity.com/docs/deleting-an-alert
    * @param    string  $id the id of the alert.
    * @return   an array with the alert id that got deleted.
    */
    public function delete($id){
        return $this->HTTPdelete('alerts/v3/configs/'.rawurlencode($id));
    }

    /**
    * Get all alerts
    * @link     https://developer.serverdensity.com/docs/listing-all-alerts
    * @return   an array of arrays with devices.
    */
    public function all(){
        return $this->get('alerts/v3/configs/');
    }

    /**
    * Update alert
    * @link     https://developer.serverdensity.com/docs/updating-alerts
    * @param    string  $id     the id of the alert
    * @param    array   $fields the fields to updated in the alert.
    * @param    array   $other  an array that optionally consists of wait, repeat etc.
    * @return   an array of arrays with devices.
    */
    public function update($id, $fields, $other=array()){
        if(array_key_exists('wait', $other)){
            $fields['wait'] = json_encode($other['wait']);
        }
        if(array_key_exists('recipients', $other)){
            $fields['recipients'] = json_encode($other['recipients']);
        }
        if(array_key_exists('repeat', $other)){
            $fields['repeat'] = json_encode($other['repeat']);
        }


        return $this->put('alerts/configs/'.rawurlencode($id), $fields);
    }


    /**
    * Get all alerts by subjectId
    * @link     https://developer.serverdensity.com/docs/listing-alerts-by-subject
    * @param    string  $subjectId  Id of the subject tied to alert
    * @param    string  $subjectType either device or service
    * @return   an array of arrays with devices.
    */
    public function bySubject($subjectId, $subjectType){
        $filter = array('filter' => '{"scope":{"type":"'.$subjectType.'","value":"'.$subjectId.'"}}');
        return $this->get('alerts/v3/configs/', $filter);
    }


    /**
    * Get alert by ID
    * @link     https://developer.serverdensity.com/docs/viewing-an-alert-by-id
    * @param    string  $id the id of the alert.
    * @return   an array of arrays with devices.
    */
    public function view($id){
        return $this->get('alerts/v3/configs/'.rawurlencode($id));
    }

    /**
    * Get triggered alerts
    * @link     https://developer.serverdensity.com/docs/triggered-alerts
    * @param    bool    $closed         whether alert is closed or open
    * @param    string  $subjectType    the subjecttype to filter on
    * @param    string  $subjectId      optional subjectID to filter on.
    * @return   an array of arrays with devices.
    */
    public function triggered($closed='', $subjectType='', $subjectId=''){
        $fields = array();
        if(!empty($closed)){
            $fields['closed'] = $closed;
        }
        if (!empty($subjectType)) {
            $fields['subjectType'] = $subjectType;
        }

        return $this->get('alerts/triggered/'.rawurlencode($subjectId), $fields);
    }

}
