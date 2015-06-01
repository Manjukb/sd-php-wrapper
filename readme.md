To get this up and running you need to to use composer. 

Download composer

    curl -s http://getcomposer.org/installer | php

Then install the dependencies
    
    php composer.phar install

To run the tests run the following command in the terminal
    
    vendor/phpunit/phpunit/phpunit test

To run functional tests you need to type in the following. This will only run the functional tests. You need to submit your own token for this to work though. Which you can do in the following path: `test/serverdensity/Tests/functional/TestCase`.

    vendor/phpunit/phpunit/phpunit --group functional test

## How to use it

    <?php

    use serverdensity\Client;
    
    $client = new Client();
    $client->authenticate('auth_token_here');
    
    // A basic call to view devices
    $devices = $client->api('devices')->all();
    

The following verbs exist
* Create - creating a resource, takes an array
* Delete - deleting a resource, takes an ID
* View - viewing a single resource, taken an ID
* All - view all resources
* Update - updating a resource, takes fields that needs updating. 
* Search (for some endpoints). 
