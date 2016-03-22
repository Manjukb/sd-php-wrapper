<?php

namespace serverdensity\Api;

class Users extends AbstractApi
{
    /**
    * View user by ID
    * @link     https://developer.serverdensity.com/docs/viewing-a-user
    * @param    string  $id the id of the user.
    * @return   an array that is the user.
    */
    public function view($id){
        return $this->get('users/users/'.rawurlencode($id));
    }

    /**
    * Get all users
    * @link     https://developer.serverdensity.com/docs/listing-all-users
    * @return   an array of arrays that contains the users.
    */
    public function all(){
        return $this->get('users/users/');
    }

    /**
    * Delete user
    * @link     https://developer.serverdensity.com/docs/deleting-a-user
    * @param    string $id the id of the user
    * @return   an array with the user that got deleted
    */
    public function delete($id)
    {
        return $this->HTTPdelete('users/users/'.rawurlencode($id));
    }

    /**
    * Create a user
    * @link     https://developer.serverdensity.com/docs/creating-a-user
    * @param    array $user
    * @return   an array with the user that got created
    */
    public function create(array $user, array $tagNames = array())
    {
        if (!empty($tagNames)){
            $tagEndpoint = new Tags($this->client);
            $tags = $tagEndpoint->findAll($tagNames);
            if(!empty($tags['notFound'])){
                foreach($tags['notFound'] as $name){
                    $tags['tags'][] = $tagEndpoint->create($name);
                }
            }

            $formattedTags = $tagEndpoint->format($tags['tags'], 'user');
            // don't overwrite permission array if user creates his own.
            if (!empty($user['permissions'])){
                $user['permissions'] = array_merge($user['permissions'], $formattedTags);
            } else {
                $user['permissions'] = $formattedTags;
            }

        }

        $user = $this->makeJsonReady($user);
        return $this->post('users/users/', $user);
    }

    /**
    * Update a user
    * @link     https://developer.serverdensity.com/docs/updating-a-user
    * @param    string $id
    * @param    array $fields
    * @return   an array with the fields that got updated plus when it got updated
    */
    public function update($id, $fields)
    {
        $fields = $this->makeJsonReady($fields);
        return $this->put('users/users/'.rawurlencode($id), $fields);
    }
}
