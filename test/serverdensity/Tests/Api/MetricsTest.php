<?php

namespace serverdensity\Tests\Api;

class MetricsTest extends TestCase
{
    protected function getApiClass()
    {
        return 'serverdensity\Api\Metrics';
    }

    /**
    * @test
    */
    public function shouldGetAvailableMetrics()
    {

        $expectedParam = array(
            'start' => "2013-09-15T00:00:00Z",
            'end' => "2013-09-15T17:10:00Z"
        );

        $start = mktime(0, 0, 0, 9, 15, 2013);
        $end = mktime(17, 10, 0, 9, 15, 2013);

        $api = $this->getApiMock('metrics');
        $api->expects($this->once())
            ->method('get')
            ->with('metrics/definitions/1', $expectedParam);

        $result = $api->available('1', $start, $end);
    }

    /**
    * @test
    */
    public function shouldGetDynamicGraphs()
    {
        $expectedParam = array(
            'start' => "2013-09-15T00:00:00Z",
            'end' => "2013-09-15T17:10:00Z"
        );

        $start = mktime(0, 0, 0, 9, 15, 2013);
        $end = mktime(17, 10, 0, 9, 15, 2013);

        $filter = json_encode(array(
            'networkTraffic' => array(
                'eth0' => ['rxMByteS']
            ))
        );

        $expectedParam = array(
            'start' => "2013-09-15T00:00:00Z",
            'end' => "2013-09-15T17:10:00Z",
            'filter' => $filter
        );

        $searchPattern = 'test';

        $api = $this->getApiMock('metrics');
        $api->expects($this->once())
            ->method('get')
            ->with('metrics/dynamicgraphs/?inventoryFilter=test', $expectedParam);

        $result = $api->dynamicMetrics($filter, $start, $end, $searchPattern);

    }


    /**
    * @test
    */
    public function shouldGetMetrics()
    {
        $inputFilter = array(
            'networkTraffic' => array(
                'eth0' => ['rxMByteS']
            )
        );

        $filter = json_encode(array(
            'networkTraffic' => array(
                'eth0' => ['rxMByteS']
            ))
        );

        $expectedParam = array(
            'start' => "2013-09-15T00:00:00Z",
            'end' => "2013-09-15T17:10:00Z",
            'filter' => $filter
        );

        $start = mktime(0, 0, 0, 9, 15, 2013);
        $end = mktime(17, 10, 0, 9, 15, 2013);

        $api = $this->getApiMock('metrics');
        $api->expects($this->once())
            ->method('get')
            ->with('metrics/graphs/1', $expectedParam);

        $result = $api->metrics('1', $filter, $start, $end);
    }

    // /**
    // * @test
    // */
    // public function shouldGetAllUsers(){
    //     $expectedArray = array(
    //         array('_id' => '1', 'username' => 'Joe'),
    //         array('_id' => '2', 'username' => 'Joe2')
    //     );

    //     $api = $this->getApiMock('users');
    //     $api->expects($this->once())
    //         ->method('get')
    //         ->with('users/users/')
    //         ->will($this->returnValue($expectedArray));

    //     $this->assertEquals($expectedArray, $api->all());
    // }
}
