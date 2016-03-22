<?php

namespace serverdensity\Api;

class Services extends AbstractApi
{

    /**
    * Create a service
    * @link     https://developer.serverdensity.com/docs/creating-a-service
    * @param    array  $service with all it's attributes.
    * @return   an array that is the device.
    */
    public function create($service, array $tagNames = array()){
        if (!empty($tagNames)){
            $tagEndpoint = new Tags($this->client);
            $tags = $tagEndpoint->findAll($tagNames);
            if(!empty($tags['notFound'])){
                foreach($tags['notFound'] as $name){
                    $tags['tags'][] = $tagEndpoint->create($name);
                }
            }

            $formattedTags = $tagEndpoint->format($tags['tags'], 'other');
            $service['tags'] = $formattedTags['tags'];
        }

        $service = $this->makeJsonReady($service);
        return $this->post('inventory/services/', $service);
    }

    /**
    * Delete a service
    * @link     https://developer.serverdensity.com/docs/deleting-a-service
    * @param    string  $id     the id of the service
    * @return   an array that is the service id.
    */
    public function delete($id){
        return $this->HTTPdelete('inventory/services/'.rawurlencode($id));
    }

    /**
    * List all services
    * @link     https://developer.serverdensity.com/docs/listing-services
    * @return   an array of arrays with all services.
    */
    public function all(){
        return $this->get('inventory/services/');
    }

    /**
    * Search a service
    * @link     https://developer.serverdensity.com/docs/searching-for-a-service
    * @param    array   $filter     an array of arrays of fields to filter on
    * @param    array   $fields     an array of fields to keep in search
    * @return   an array of arrays with all services.
    */
    public function search($filter, $fields){
        $param = array(
            'filter' => json_encode($filter),
            'fields' => json_encode($fields)
        );

        return $this->get('inventory/resources/', $param);
    }

    /**
    * Update a service
    * @link     https://developer.serverdensity.com/docs/updating-a-service
    * @param    string  $id     id of the device to change
    * @param    array   $fields an array of array of fields that you want to change
    * @return   an array of arrays with all services.
    */
    public function update($id, $fields){
        $fields = $this->makeJsonReady($fields);
        return $this->put('inventory/services/'.rawurlencode($id), $fields);
    }

    /**
    * View service by ID
    * @link     https://developer.serverdensity.com/docs/view-service-by-id
    * @param    $id     an id of the service
    * @return   an array of arrays with all services.
    */
    public function view($id){
        return $this->get('inventory/services/'.rawurlencode($id));
    }


}
