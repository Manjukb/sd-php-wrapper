<?php

namespace serverdensity\Tests\HttpClient;

use serverdensity\Client;
use serverdensity\HttpClient\HttpClient;
use serverdensity\HttpClient\Message\ResponseMediator;

use GuzzleHttp\Message\Response;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Event\Emitter;
use GuzzleHttp\Subscriber\Prepare;

class HttpClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeAbleToPassOptionsToConstructor()
    {
        $httpClient = new TestHttpClient(array(
            'timeout' => 33
        ), $this->getBrowserMock());

        $this->assertEquals(33, $httpClient->getOption('timeout'));
    }


    /**
     * @test
     */
    public function ApiLimitShouldBeSetFromTheBeginning()
    {
        $this->markTestSkipped('Not yet implemented');
        $httpClient = new TestHttpClient(array(), $this->getBrowserMock());
        $this->assertEquals(5000, $httpClient->getOption('api_limit'));
    }

    /**
     * @test
     */
    public function shouldBeAbleToSetOption()
    {
        $httpClient = new TestHttpClient(array(), $this->getBrowserMock());
        $httpClient->setOption('timeout', 666);

        $this->assertEquals(666, $httpClient->getOption('timeout'));
    }

    /**
     * @test
     */
    public function shouldHaveErrorListener()
    {
        $httpclient = new TestHttpClient(array(), new GuzzleClient());
        $listeners = $httpclient->client->getEmitter()->listeners('error');
        $this->assertCount(1, $listeners);

        // $authListener = $listeners[0][1];
        // $this->assertInstanceOf('serverdensity\HttpClient\Listener\AuthListener', $authListener);
    }


    /**
     * @test
     * @dataProvider getAuthenticationFullData
     */
    public function shouldAuthenticateUsingAllGivenParameters($token, $method)
    {
        $httpClient = new TestHttpClient(array(), new GuzzleClient());
        $httpClient->authenticate($token, $method);

        $listeners = $httpClient->client->getEmitter()->listeners('before');
        $this->assertCount(1, $listeners);

        // $authListener = $listeners[0][1];
        // $this->assertInstanceOf('serverdensity\HttpClient\Listener\AuthListener', $authListener);
    }

    public function getAuthenticationFullData()
    {
        return array(
            array('tokenHere', Client::AUTH_URL_TOKEN)
        );
    }

    /**
     * @test
     */
    public function shouldDoGETRequest()
    {
        $path       = '/some/path';
        $parameters = array('a' => 'b');
        $headers    = array('c' => 'd');

        $client = $this->getBrowserMock();

        $client->expects($this->once())
            ->method('createRequest')
            ->with('GET', $path, ['headers' => $headers, 'query' => $parameters]);

        $httpClient = new HttpClient(array(), $client);
        $response = $httpClient->get($path, $parameters, $headers);
    }

    /**
     * @test
     */
    public function shouldDoPOSTRequest()
    {
        $path       = '/some/path';
        $body       = 'somebody';
        $headers    = array('c' => 'd');

        $client = $this->getBrowserMock();

        $client->expects($this->once())
            ->method('createRequest')
            ->with('POST', $path, ['body' => $body, 'headers' => $headers]);

        $httpClient = new HttpClient(array(), $client);
        $httpClient->post($path, $body, $headers);
    }

    /**
     * @test
     */
    public function shouldDoPOSTRequestWithoutContent()
    {
        $path       = '/some/path';

        $client = $this->getBrowserMock();
        $client->expects($this->once())
            ->method('createRequest')
            ->with('POST', $path, $this->isType('array'));

        $httpClient = new HttpClient(array(), $client);
        $httpClient->post($path);
    }

    /**
     * @test
     */
    public function shouldDoPATCHRequest()
    {
        $path       = '/some/path';
        $body       = 'a = b';
        $headers    = array('c' => 'd');

        $client = $this->getBrowserMock();

        $client->expects($this->once())
            ->method('createRequest')
            ->with('PATCH', $path, ['headers' => $headers, 'body' => $body]);

        $httpClient = new HttpClient(array(), $client);
        $httpClient->patch($path, $body, $headers);
    }

    /**
     * @test
     */
    public function shouldDoDELETERequest()
    {
        $path       = '/some/path';
        $body       = 'a = b';
        $headers    = array('c' => 'd');

        $client = $this->getBrowserMock();

        $client->expects($this->once())
            ->method('createRequest')
            ->with('DELETE', $path, ['headers' => $headers, 'body' => $body]);

        $httpClient = new HttpClient(array(), $client);
        $httpClient->delete($path, $body, $headers);
    }

    /**
     * @test
     */
    public function shouldDoPUTRequest()
    {
        $path       = '/some/path';
        $headers    = array('c' => 'd');

        $client = $this->getBrowserMock();

        $client->expects($this->once())
            ->method('createRequest')
            ->with('PUT', $path, ['headers' => $headers]);

        $httpClient = new HttpClient(array(), $client);
        $httpClient->put($path, null, $headers);
    }

    /**
     * @test
     */
    public function shouldDoCustomRequest()
    {
        $path       = '/some/path';
        $body       = 'a = b';
        $options    = array('someval' => 'd');

        $expectedResult = array_merge($options, ['body' => $body]);
        $client = $this->getBrowserMock();

        $client->expects($this->once())
            ->method('createRequest')
            ->with('HEAD', $path, $expectedResult);

        $httpClient = new HttpClient(array(), $client);
        $httpClient->request($path, $body, 'HEAD', [], $options);
    }


    /**
     * @test
     */
    public function shouldAllowToReturnRawContent()
    {
        $path       = '/some/path';
        $parameters = array('a = b');
        $headers    = array('c' => 'd');

        $message = $this->getMock('Guzzle\Http\Message\Response', array());
        $message->expects($this->once())
            ->method('getBody')
            ->will($this->returnValue('Just raw context'));

        $client = $this->getBrowserMock();
        $client->expects($this->once())
            ->method('send')
            ->will($this->returnValue($message));

        $httpClient = new TestHttpClient(array(), $client);
        $response = $httpClient->get($path, $parameters, $headers);

        $this->assertEquals("Just raw context", $response->getBody());
        $this->assertInstanceOf('Guzzle\Http\Message\MessageInterface', $response);
    }

    /**
     * @test
     * @expectedException \serverdensity\Exception\ApiLimitExceedException
     */
    public function shouldThrowExceptionWhenApiIsExceeded()
    {
        $this->markTestSkipped(
            'Broken, but unused feature at the moment. Have to fix in Guzzle5');

        $path       = '/some/path';
        $parameters = array('a = b');
        $headers    = array('c' => 'd');

        $response = new Response(403);
        $response->addHeader('X-RateLimit-Remaining', 0);

        $mockPlugin = new Mock();
        $mockPlugin->addResponse($response);

        $client = new GuzzleClient(['baseUrl' => 'http://123.com/']);
        $client->addSubscriber($mockPlugin);

        $httpClient = new TestHttpClient(array(), $client);
        $httpClient->get($path, $parameters, $headers);
    }

    protected function getBrowserMock(array $methods = array(), array $args = array())
    {

        $callMethods = array(
            'GET',
            'POST',
            'PUT',
            'DELETE',
            'HEAD',
        );

        $mock = $this->getMockBuilder('GuzzleHttp\Client')
            ->setMethods(array_merge(
                array('send', 'createRequest'),$methods))
            ->getMock();

        $emitter = $this->getMockBuilder('GuzzleHttp\Event\Emitter')
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $mockRequest = $this->getMockBuilder('GuzzleHttp\Message\Request')
            ->disableOriginalConstructor()
            ->setConstructorArgs(array('Get', 'some URL'))
            ->getMock();

        $subscriber = new Prepare();
        $emitter->attach($subscriber);

        $mock->expects($this->any())
            ->method('createRequest')
            ->will($this->returnValue($mockRequest));

        return $mock;
    }
}

class TestHttpClient extends HttpClient
{
    public function getOption($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }

    public function request($path, $body, $httpMethod = 'GET', array $headers = array(), array $options = array())
    {
        if(!empty($body)){
            $options = array_merge($options, ['body' => $body]);
        }
        if(!empty($headers)){
            $options = array_merge($options, ['headers' => $headers]);
        }

        $request = $this->client->createRequest($httpMethod, $path, $options);

        return $this->client->send($request);
    }
}
