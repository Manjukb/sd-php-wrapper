<?php

namespace serverdensity\Tests\HttpClient;

use serverdensity\HttpClient\Listener\ErrorListener;

class ErrorListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldPassIfResponseNotHaveErrorStatus()
    {
        $response = $this->getMockBuilder('Guzzle\Http\Message\Response')->disableOriginalConstructor()->getMock();
        $response->expects($this->once())
            ->method('isClientError')
            ->will($this->returnValue(false));

        $listener = new ErrorListener(array('api_limit' => 5000));
        $listener->onRequestError($this->getEventMock($response));
    }

    /**
     * @test
     * @expectedException \serverdensity\Exception\ApiLimitExceedException
     */
    public function shouldFailWhenApiLimitWasExceed()
    {
        $response = $this->getMockBuilder('Guzzle\Http\Message\Response')->disableOriginalConstructor()->getMock();
        $response->expects($this->once())
            ->method('isClientError')
            ->will($this->returnValue(true));
        $response->expects($this->once())
            ->method('getHeader')
            ->with('X-RateLimit-Remaining')
            ->will($this->returnValue(0));

        $listener = new ErrorListener(array('api_limit' => 5000));
        $listener->onRequestError($this->getEventMock($response));
    }

    /**
     * @test
     * @expectedException \serverdensity\Exception\RuntimeException
     */
    public function shouldNotPassWhenContentWasNotValidJson()
    {
        $response = $this->getMockBuilder('Guzzle\Http\Message\Response')->disableOriginalConstructor()->getMock();
        $response->expects($this->once())
            ->method('isClientError')
            ->will($this->returnValue(true));
        $response->expects($this->once())
            ->method('getHeader')
            ->with('X-RateLimit-Remaining')
            ->will($this->returnValue(5000));
        $response->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue('fail'));

        $listener = new ErrorListener(array('api_limit' => 5000));
        $listener->onRequestError($this->getEventMock($response));
    }

    /**
     * @test
     * @expectedException \serverdensity\Exception\RuntimeException
     */
    public function shouldNotPassWhenContentWasValidJsonButStatusIsNotCovered()
    {
        $response = $this->getMockBuilder('Guzzle\Http\Message\Response')->disableOriginalConstructor()->getMock();
        $response->expects($this->once())
            ->method('isClientError')
            ->will($this->returnValue(true));
        $response->expects($this->once())
            ->method('getHeader')
            ->with('X-RateLimit-Remaining')
            ->will($this->returnValue(5000));
        $response->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue(json_encode(array('message' => 'test'))));
        $response->expects($this->any())
            ->method('getStatusCode')
            ->will($this->returnValue(404));

        $listener = new ErrorListener(array('api_limit' => 5000));
        $listener->onRequestError($this->getEventMock($response));
    }

    /**
     * @test
     * @expectedException \serverdensity\Exception\ErrorException
     */
    public function shouldNotPassWhen400IsSent()
    {
        $response = $this->getMockBuilder('Guzzle\Http\Message\Response')->disableOriginalConstructor()->getMock();
        $response->expects($this->once())
            ->method('isClientError')
            ->will($this->returnValue(true));
        $response->expects($this->once())
            ->method('getHeader')
            ->with('X-RateLimit-Remaining')
            ->will($this->returnValue(5000));
        $response->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue(json_encode(array('message' => 'test'))));
        $response->expects($this->any())
            ->method('getStatusCode')
            ->will($this->returnValue(400));

        $listener = new ErrorListener(array('api_limit' => 5000));
        $listener->onRequestError($this->getEventMock($response));
    }

    /**
     * @test
     * @dataProvider getErrorCodesProvider
     * @expectedException \serverdensity\Exception\ValidationFailedException
     */
    public function shouldNotPassWhen422IsSentWithErrorCode($errorCode)
    {
        $content = json_encode(array(
            'message' => 'Validation Failed',
            'errors'  => array(
                array(
                    'code'     => $errorCode,
                    'field'    => 'test',
                    'value'    => 'wrong',
                    'resource' => 'fake'
                )
            )
        ));

        $response = $this->getMockBuilder('Guzzle\Http\Message\Response')->disableOriginalConstructor()->getMock();
        $response->expects($this->once())
            ->method('isClientError')
            ->will($this->returnValue(true));
        $response->expects($this->once())
            ->method('getHeader')
            ->with('X-RateLimit-Remaining')
            ->will($this->returnValue(5000));
        $response->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue($content));
        $response->expects($this->any())
            ->method('getStatusCode')
            ->will($this->returnValue(422));

        $listener = new ErrorListener(array('api_limit' => 5000));
        $listener->onRequestError($this->getEventMock($response));
    }

    public function getErrorCodesProvider()
    {
        return array(
            array('missing'),
            array('missing_field'),
            array('invalid'),
            array('already_exists'),
        );
    }

    private function getEventMock($response)
    {
        $mock = $this->getMockBuilder('Guzzle\Common\Event')->getMock();

        $request = $this->getMockBuilder('Guzzle\Http\Message\Request')->disableOriginalConstructor()->getMock();

        $request->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($response));

        $mock->expects($this->any())
            ->method('offsetGet')
            ->will($this->returnValue($request));

        return $mock;
    }
}
