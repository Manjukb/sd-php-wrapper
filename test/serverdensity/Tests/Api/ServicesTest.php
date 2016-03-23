<?php

namespace serverdensity\Tests\Api;

class ServicesTest extends TestCase
{
    protected function getApiClass(){
        return 'serverdensity\Api\Services';
    }

    /**
    * @test
    */
    public function shouldCreateService(){
        $input = array(
            '_id' => '1',
            'name' => 'myService',
            'checkLocations' => array(
                'dub'
            )
        );

        $expectedArray = array(
            '_id' => '1',
            'name' => 'myService',
            'checkLocations' => json_encode(array(
                'dub'
            ))
        );

        $api = $this->getApiMock('services');
        $api->expects($this->once())
            ->method('post')
            ->with('inventory/services/', $expectedArray)
            ->will($this->returnValue($expectedArray));

        $this->assertEquals($expectedArray, $api->create($input));
    }

    /**
    * @test
    */
    public function shouldDeleteService(){
        $expectedArray = array('_id' => '1');

        $api = $this->getApiMock('services');
        $api->expects($this->once())
            ->method('HTTPdelete')
            ->with('inventory/services/1')
            ->will($this->returnValue($expectedArray));

        $this->assertEquals($expectedArray, $api->delete('1'));
    }

    /**
    * @test
    */
    public function shouldListServices(){
        $expectedArray = array(
            array('_id' => '1', 'name' => 'myService'),
            array('_id' => '2', 'name' => 'myService 2')
        );

        $api = $this->getApiMock('services');
        $api->expects($this->once())
            ->method('get')
            ->with('inventory/services/')
            ->will($this->returnValue($expectedArray));

        $this->assertEquals($expectedArray, $api->all());
    }

    /**
    * @test
    */
    public function shouldSearchService(){
        $expectedArray = array(
            array('_id' => '1', 'name' => 'myService'),
            array('_id' => '2', 'name' => 'myService')
        );

        $filter = array(
            'name' => 'myService',
            'type' => 'service'
        );

        $fields = array(
            'name',
            '_id'
        );

        $param = array(
            'filter' => json_encode($filter),
            'fields' => json_encode($fields)
        );

        $api = $this->getApiMock('services');
        $api->expects($this->once())
            ->method('get')
            ->with('inventory/resources/', $param)
            ->will($this->returnValue($expectedArray));

        $this->assertEquals($expectedArray, $api->search($filter, $fields));
    }

    /**
    * @test
    */
    public function shouldUpdateService(){
        $change = array(
            '_id' => '1',
            'name' => 'myService',
            'checkLocations' => array(
                'dub'
            )
        );

        $expectedArray = array(
            '_id' => '1',
            'name' => 'myService',
            'checkLocations' => json_encode(array(
                'dub'
            ))
        );

        $api = $this->getApiMock('services');
        $api->expects($this->once())
            ->method('put')
            ->with('inventory/services/1', $expectedArray)
            ->will($this->returnValue($expectedArray));

        $this->assertEquals($expectedArray, $api->update('1', $change));
    }

    /**
    * @test
    */
    public function shouldViewService(){
        $expectedArray = array('_id' => '1', 'name' => 'myService');

        $api = $this->getApiMock('services');
        $api->expects($this->once())
            ->method('get')
            ->with('inventory/services/1')
            ->will($this->returnValue($expectedArray));

        $this->assertEquals($expectedArray, $api->view('1'));
    }

        /**
    * @test
    */
    public function shouldGetDeviceGroups(){
        $expectedArray = array(
            array('_id' => '1', 'name' => 'myServiceGroup'),
            array('_id' => '2', 'name' => 'myServiceGroup2')
        );

        $api = $this->getApiMock('devices');

        $api->expects($this->once())
            ->method('get')
            ->with('inventory/services/groups')
            ->will($this->returnValue($expectedArray));

        $this->assertEquals($expectedArray, $api->serviceGroups('1'));
    } 

}
