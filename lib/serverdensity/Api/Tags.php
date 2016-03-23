<?php

namespace serverdensity\Api;

use serverdensity\Exception\ErrorException;

class Tags extends AbstractApi
{

    // do a mongoId check, if mongoId use find instead.

    /**
    * Find a tag by ID
    * @link     https://developer.serverdensity.com/docs/viewing-an-individual-tag
    * @param    string  $id the id of the tag.
    * @return   an array that is the tag.
    */
    public function view($id)
    {
        return $this->get('inventory/tags/'.rawurlencode($id));
    }

    /**
    * Get all tags
    * @link     https://developer.serverdensity.com/docs/view-all-tags
    * @return   an array of arrays that contains all the tags.
    */
    public function all()
    {
        return $this->get('inventory/tags/');
    }

    /**
    * Delete tag
    * @link     https://developer.serverdensity.com/docs/deleting-a-tag
    * @param    string $id the id of the tag
    * @return   an array with the id of the tag that got deleted
    */
    public function delete($id)
    {
        return $this->HTTPdelete('inventory/tags/'.rawurlencode($id));
    }

    /**
    * Find a tag by name
    * @param    string $name of the tag
    * @return   an array of the tag in question or an empty array if not found
    */
    public function find($name)
    {
        $tags = $this->all();

        $found = array();
        foreach ($tags as $tag){
            if ($tag['name'] === $name){
                $found = $tag;
                break;
            }
        }
        return $found;
    }

    /**
    * Find all tags by name
    * @param    array   names   tag names in an array
    * @return   an array of array objects.
    */
    public function findAll(array $names)
    {
        $tags = $this->all();
        $transformTags = function($array) {
                $newArray = array();
                foreach($array as $entry){
                    $newArray[$entry['name']] = $entry;
                }
                return $newArray;
            };
        $tagNames = $transformTags($tags);
        $found = array(
            'tags' => array(),
            'notFound' => array()
        );
        foreach ($names as $name){
            if (in_array($name, array_keys($tagNames))){
                $found['tags'][] = $tagNames[$name];
            } else {
                $found['notFound'][] = $name;
            }
        }
        return $found;
    }

    /**
    * Format tags to be inserted
    * @param    array   tags    an array of tags
    * @param    string  type    either user or other
    * @return   formatted tags
    */
    public function format($tags, $type)
    {
        $formattedTags = array();
        if ($type === 'user'){
            $formattedTags['tags'] = array();
            foreach($tags as $tag){
                $formattedTags['tags'][$tag['_id']] = array(
                    'mode' => 'readWrite'
                );
            }
        } else if ($type === 'other'){
            foreach($tags as $tag){
                $formattedTags['tags'][] = $tag['_id'];
            }
        } else {
            throw ErrorException("Only 'user' and 'other' are allowed types");
        }

        return $formattedTags;
    }

    /**
    * Create a tag
    * @link     https://developer.serverdensity.com/docs/tags
    * @param    string $name name of tag
    * @param    string $hexColor hexcolor of tag
    * @return   an array with the fields of the tag that got created
    */
    public function create($name, $hexColor = null)
    {
        if (empty($hexColor)){
            # generates random hexcode.
            $hexColor = "#".substr(md5(rand()), 0, 6);
        }
        $fields = array(
            "name" => $name,
            "color" => $hexColor
        );
        return $this->post('inventory/tags/', $fields);
    }


    // do a mongoId check

    /**
    * Update a tag
    * @link     https://developer.serverdensity.com/docs/updating-a-tag
    * @param    string $id
    * @param    array $fields
    * @return   an array with the fields that got updated plus when it got updated
    */
    public function update($id, $fields)
    {
        return $this->put('inventory/tags/'.rawurlencode($id), $fields);
    }

}

