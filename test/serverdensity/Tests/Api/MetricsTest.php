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
            'filter' => urlencode('{"scope": [{"item": "1"}]}')
        );

        $api = $this->getApiMock('metrics');
        $api->expects($this->once())
            ->method('get')
            ->with('metrics/v3/metrics/', $expectedParam);

        $result = $api->available('1');
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
        $metric = 'system.load.1';
        $id = '5a79e996b03e85b1378b456f';
        $expectedParam = array(
            'start' => "2013-09-15T00:00:00Z",
            'end' => "2013-09-15T17:10:00Z",
            'requests' => '[{"metric":"'.$metric.'","scope":[{"item":"'.$id.'"}]}]',
        );

        $start = mktime(0, 0, 0, 9, 15, 2013);
        $end = mktime(17, 10, 0, 9, 15, 2013);

        $api = $this->getApiMock('metrics');
        $api->expects($this->once())
            ->method('post')
            ->with('metrics/v3/query/', $expectedParam);

        $result = $api->metrics('5a79e996b03e85b1378b456f', $metric, $start, $end);
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
