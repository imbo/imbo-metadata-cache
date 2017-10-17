<?php
namespace Imbo\Plugin\MetadataCache;

use PHPUnit_Framework_TestCase;
use DateTime;

/**
 * @coversDefaultClass Imbo\Plugin\MetadataCache\EventListener
 */
class EventListenerTest extends PHPUnit_Framework_TestCase {
    /**
     * @var EventListener
     */
    private $listener;

    private $event;
    private $request;
    private $cache;
    private $response;
    private $responseHeaders;
    private $user = 'user';
    private $imageIdentifier = 'imageid';

    /**
     * Set up the listener
     */
    public function setUp() {
        $this->cache = $this->createMock('Imbo\Plugin\MetadataCache\Cache\CacheInterface');
        $this->request = $this->createMock('Imbo\Http\Request\Request');
        $this->request->expects($this->any())->method('getUser')->will($this->returnValue($this->user));
        $this->request->expects($this->any())->method('getImageIdentifier')->will($this->returnValue($this->imageIdentifier));
        $this->responseHeaders = $this->createMock('Symfony\Component\HttpFoundation\HeaderBag');
        $this->response = $this->createMock('Imbo\Http\Response\Response');
        $this->response->headers = $this->responseHeaders;
        $this->event = $this->createMock('Imbo\EventManager\Event');
        $this->event->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
        $this->event->expects($this->any())->method('getResponse')->will($this->returnValue($this->response));

        $this->listener = new EventListener(['cache' => $this->cache]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getListener() {
        return $this->listener;
    }

    /**
     * @covers ::loadFromCache
     */
    public function testUpdatesResponseOnCacheHit() {
        $date = new DateTime();

        $this->cache->expects($this->once())->method('get')->with($this->isType('string'))->will($this->returnValue([
            'lastModified' => $date,
            'metadata' => ['key' => 'value'],
        ]));

        $this->responseHeaders->expects($this->once())->method('set')->with('X-Imbo-MetadataCache', 'Hit');
        $this->response->expects($this->once())->method('setModel')->with($this->isInstanceOf('Imbo\Model\Metadata'))->will($this->returnSelf());
        $this->response->expects($this->once())->method('setLastModified')->with($date);

        $this->event->expects($this->once())->method('stopPropagation');

        $this->listener->loadFromCache($this->event);
    }

    /**
     * @covers ::loadFromCache
     */
    public function testDeletesInvalidCachedData() {
        $this->cache->expects($this->once())->method('get')->with($this->isType('string'))->will($this->returnValue([
            'lastModified' => 'preformatted date',
            'metadata' => ['key' => 'value'],
        ]));
        $this->cache->expects($this->once())->method('delete')->with($this->isType('string'));
        $this->responseHeaders->expects($this->once())->method('set')->with('X-Imbo-MetadataCache', 'Miss');
        $this->response->expects($this->never())->method('setModel');
        $this->listener->loadFromCache($this->event);
    }

    /**
     * @covers ::storeInCache
     */
    public function testStoresDataInCacheWhenResponseCodeIs200() {
        $lastModified = new DateTime();
        $data = ['some' => 'value'];

        $this->cache->expects($this->once())->method('set')->with($this->isType('string'), [
            'lastModified' => $lastModified,
            'metadata' => $data,
        ]);

        $model = $this->createMock('Imbo\Model\ArrayModel');
        $model->expects($this->once())->method('getData')->will($this->returnValue($data));

        $this->response->expects($this->once())->method('getStatusCode')->will($this->returnValue(200));
        $this->response->expects($this->once())->method('getLastModified')->will($this->returnValue($lastModified));
        $this->response->expects($this->once())->method('getModel')->will($this->returnValue($model));

        $this->listener->storeInCache($this->event);
    }

    /**
     * @covers ::storeInCache
     */
    public function testStoresDataInCacheWhenResponseCodeIs200AndHasNoModel() {
        $lastModified = new DateTime();

        $this->cache->expects($this->once())->method('set')->with($this->isType('string'), [
            'lastModified' => $lastModified,
            'metadata' => [],
        ]);

        $this->response->expects($this->once())->method('getStatusCode')->will($this->returnValue(200));
        $this->response->expects($this->once())->method('getLastModified')->will($this->returnValue($lastModified));
        $this->response->expects($this->once())->method('getModel')->will($this->returnValue(null));

        $this->listener->storeInCache($this->event);
    }

    /**
     * @covers ::storeInCache
     */
    public function testDoesNotStoreDataInCacheWhenResponseCodeIsNot200() {
        $this->cache->expects($this->never())->method('set');
        $this->response->expects($this->once())->method('getStatusCode')->will($this->returnValue(404));

        $this->listener->storeInCache($this->event);
    }

    /**
     * @covers ::deleteFromCache
     * @covers ::getCacheKey
     */
    public function testCanDeleteContentFromCache() {
        $this->cache->expects($this->once())->method('delete')->with('metadata:' . $this->user . '|' . $this->imageIdentifier);
        $this->listener->deleteFromCache($this->event);
    }

    /**
     * @covers ::__construct
     * @expectedException Imbo\Exception\InvalidArgumentException
     * @expectedExceptionMessage The cache parameter is missing or not valid
     */
    public function testThrowsExceptionOnMissingCacheAdapter() {
        $listener = new EventListener([]);
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEventsReturnsAnArrayOfEvents() {
        $events = EventListener::getSubscribedEvents();

        $this->assertInternalType('array', $events);
    }
}
