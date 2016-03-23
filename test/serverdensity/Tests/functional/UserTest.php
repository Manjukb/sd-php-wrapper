<?php
namespace serverdensity\Tests\functional;

use serverdensity\HttpClient\HttpClient as HttpClient;

/**
* @group functional
*/
class UserTest extends TestCase
{
    protected $user;

    /**
    * @test
    */
    public function shouldGetErrorWhenEmptyFields(){

        $this->setExpectedException('serverdensity\Exception\ValidationFailedException');
        $createdUser = $this->client->api('users')->create(array());

    }

    /**
    * @test
    */
    public function shouldCreateUser(){
        $user = array(
            "_id" => '1234567890',
            "admin" => true,
            "firstName" => "Llama",
            "lastName" => "Hat",
            "login" => "llama",
            "password" => "testpassword",
            "emailAddresses" => array(
                "llama@gmail.com"
            ),
            "phoneNumbers" => array(
                "+342351412"
            )
        );

        $createdUser = $this->client->api('users')->create($user);


        $this->assertArrayHasKey('_id', $createdUser);
        $this->assertArrayHasKey('firstName', $createdUser);
        $this->assertArrayHasKey('lastName', $createdUser);
        $this->assertArrayHasKey('phoneNumbers', $createdUser);

        $this->user = $createdUser;

        return $createdUser;
    }

    /**
    * @test
    */
    public function shouldCreateUserWithTag(){
        $user = array(
            "_id" => '1234567890',
            "admin" => true,
            "firstName" => "Llama2",
            "lastName" => "Hat2",
            "login" => "llama2",
            "password" => "testpassword",
            "emailAddresses" => array(
                "llama2@gmail.com"
            ),
            "phoneNumbers" => array(
                "+342351412"
            ),
            "permissions" => array(
                "limited" => true
            )
        );

        $createdUser = $this->client->api('users')->create($user, ['my', 'tags']);
        $my = $this->client->api('tags')->find('my');
        $tag = $this->client->api('tags')->find('tags');

        $this->assertEquals(true, array_key_exists('limited', $createdUser['permissions']));
        $this->assertEquals(true, array_key_exists($my['_id'], $createdUser['permissions']['tags']));
        $this->assertEquals(true, array_key_exists($tag['_id'], $createdUser['permissions']['tags']));

        //teardown
        $this->client->api('tags')->delete($tag['_id']);
        $this->client->api('tags')->delete($my['_id']);
        $this->client->api('users')->delete($createdUser['_id']);
    }

    /**
    * @test
    * @depends shouldCreateUser
    */
    public function shouldGetErrorWhenDuplicatingUser(){
        $user = array(
            "_id" => '1234567890',
            "admin" => true,
            "firstName" => "Llama",
            "lastName" => "Hat",
            "login" => "llama",
            "password" => "password",
            "emailAddresses" => array(
                "llama@gmail.com"
            ),
            "phoneNumbers" => array(
                "+342351412"
            )
        );
        $this->setExpectedException('serverdensity\Exception\ValidationFailedException');
        $createdUser = $this->client->api('users')->create($user);

    }


    /**
    * @test
    * @depends shouldCreateUser
    */
    public function shouldViewUser($createdUser){

        $result = $this->client->api('users')->view($createdUser['_id']);

        $this->assertArrayHasKey('_id', $result);
        $this->assertArrayHasKey('firstName', $result);
        $this->assertArrayHasKey('lastName', $result);
        $this->assertArrayHasKey('phoneNumbers', $result);

    }

    /**
    * @test
    * @depends shouldCreateUser
    */
    public function shouldViewAllUsers(){

        $result = $this->client->api('users')->all();


        $this->assertGreaterThan(0, count($result));
        $this->assertEquals(true, is_array($result[0]));

        $this->assertArrayHasKey('_id', $result[0]);
        $this->assertArrayHasKey('firstName', $result[0]);
        $this->assertArrayHasKey('lastName', $result[0]);
        $this->assertArrayHasKey('emailAddresses', $result[0]);

    }


    /**
    * @test
    * @depends shouldCreateUser
    */
    public function shouldUpdateUser($createdUser){
        $fields = array(
            'firstName' => 'AnotherName',
            'emailAddresses' => ['newEmail@gmail.com']
        );

        $result = $this->client->api('users')->update($createdUser['_id'], $fields);

        $this->assertArrayHasKey('firstName', $result);
        $this->assertEquals($fields['firstName'], $result['firstName']);
        $this->assertArrayHasKey('emailAddresses', $result);
        $this->assertEquals($fields['emailAddresses'][0], $result['emailAddresses'][0]);
    }

    /**
    * @test
    * @depends shouldCreateUser
    */
    public function shouldDeleteUser($createdUser){

        $result = $this->client->api('users')->delete($createdUser['_id']);

        $this->assertArrayHasKey('_id', $result);
        $this->assertEquals($createdUser['_id'], $result['_id']);

    }

}
