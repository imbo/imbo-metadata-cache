<?php declare(strict_types=1);
namespace Imbo\Plugin\MetadataCache;

use DateTime;
use DateTimeImmutable;
use Imbo\EventManager\EventInterface;
use Imbo\Exception\InvalidArgumentException;
use Imbo\Http\Request\Request;
use Imbo\Http\Response\Response;
use Imbo\Model\ArrayModel;
use Imbo\Model\Metadata;
use Imbo\Plugin\MetadataCache\Cache\CacheInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

#[CoversClass(EventListener::class)]
class EventListenerTest extends TestCase
{
    private EventInterface&MockObject $event;
    private Request&MockObject $request;
    private Response&MockObject $response;
    private CacheInterface&MockObject $cache;
    private ResponseHeaderBag&MockObject $responseHeaders;
    private string $user = 'user';
    private string $imageIdentifier = 'imageid';
    private EventListener $listener;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(CacheInterface::class);
        $this->request = $this->createConfiguredMock(Request::class, [
            'getUser' => $this->user,
            'getImageIdentifier' => $this->imageIdentifier,
        ]);
        $this->responseHeaders = $this->createMock(ResponseHeaderBag::class);
        $this->response = $this->createMock(Response::class);
        $this->response->headers = $this->responseHeaders;
        $this->event = $this->createConfiguredMock(EventInterface::class, [
            'getRequest' => $this->request,
            'getResponse' => $this->response,
        ]);

        $this->listener = new EventListener([
            'cache' => $this->cache,
        ]);
    }

    protected function getListener(): EventListener
    {
        return $this->listener;
    }

    public function testLoadFromCacheReturnsEarlyOnMissingUser(): void
    {
        $this->cache
            ->expects($this->never())
            ->method('get');

        $this->listener->loadFromCache(
            $this->createConfiguredMock(EventInterface::class, [
                'getRequest' => $this->createMock(Request::class),
            ]),
        );
    }

    public function testStoreInCacheReturnsEarlyOnMissingUser(): void
    {
        $this->cache
            ->expects($this->never())
            ->method('set');

        /** @var Request&MockObject */
        $request = $this->createMock(Request::class);
        $request
            ->expects($this->never())
            ->method('getImageIdentifier');

        $this->listener->storeInCache(
            $this->createConfiguredMock(EventInterface::class, [
                'getRequest' => $request,
                'getResponse' => $this->createConfiguredMock(Response::class, [
                    'getStatusCode' => 200,
                ]),
            ]),
        );
    }

    public function testDeleteFromCacheReturnsEarlyOnMissingUser(): void
    {
        $this->cache
            ->expects($this->never())
            ->method('delete');

        $this->listener->deleteFromCache(
            $this->createConfiguredMock(EventInterface::class, [
                'getRequest' => $this->createMock(Request::class),
            ]),
        );
    }

    public function testUpdatesResponseOnCacheHit(): void
    {
        $date = new DateTime();

        $this->cache
            ->expects($this->once())
            ->method('get')
            ->with($this->isString())
            ->willReturn([
                'lastModified' => $date,
                'metadata' => [
                    'key' => 'value',
                ],
            ]);

        $this->responseHeaders
            ->expects($this->once())
            ->method('set')
            ->with('X-Imbo-MetadataCache', 'Hit');

        $this->response
            ->expects($this->once())
            ->method('setModel')
            ->with($this->isInstanceOf(Metadata::class))
            ->willReturnSelf();

        $this->response
            ->expects($this->once())
            ->method('setLastModified')
            ->with($date);

        $this->event
            ->expects($this->once())
            ->method('stopPropagation');

        $this->listener->loadFromCache($this->event);
    }

    public function testDeletesInvalidCachedData(): void
    {
        $this->cache
            ->expects($this->once())
            ->method('get')
            ->with($this->isString())
            ->willReturn([
                'lastModified' => 'preformatted date',
                'metadata' => [
                    'key' => 'value',
                ],
            ]);

        $this->cache
            ->expects($this->once())
            ->method('delete')
            ->with($this->isString());

        $this->responseHeaders
            ->expects($this->once())
            ->method('set')
            ->with('X-Imbo-MetadataCache', 'Miss');

        $this->response
            ->expects($this->never())
            ->method('setModel');

        $this->listener->loadFromCache($this->event);
    }

    public function testStoresDataInCacheWhenResponseCodeIs200(): void
    {
        $lastModified = new DateTimeImmutable();
        $data = ['some' => 'value'];

        $this->cache
            ->expects($this->once())
            ->method('set')
            ->with(
                $this->isString(),
                [
                    'lastModified' => $lastModified,
                    'metadata' => $data,
                ],
            );

        /** @var ArrayModel&MockObject */
        $model = $this->createMock(ArrayModel::class);
        $model
            ->expects($this->once())
            ->method('getData')
            ->willReturn($data);

        $this->response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $this->response
            ->expects($this->once())
            ->method('getLastModified')
            ->willReturn($lastModified);

        $this->response
            ->expects($this->once())
            ->method('getModel')
            ->willReturn($model);

        $this->listener->storeInCache($this->event);
    }

    public function testStoresDataInCacheWhenResponseCodeIs200AndHasNoModel(): void
    {
        $lastModified = new DateTimeImmutable();

        $this->cache
            ->expects($this->once())
            ->method('set')
            ->with(
                $this->isString(),
                [
                    'lastModified' => $lastModified,
                    'metadata' => [],
                ],
            );

        $this->response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(200);

        $this->response
            ->expects($this->once())
            ->method('getLastModified')
            ->willReturn($lastModified);

        $this->response
            ->expects($this->once())
            ->method('getModel')
            ->willReturn(null);

        $this->listener->storeInCache($this->event);
    }

    public function testDoesNotStoreDataInCacheWhenResponseCodeIsNot200(): void
    {
        $this->cache
            ->expects($this->never())
            ->method('set');

        $this->response
            ->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(404);

        $this->listener->storeInCache($this->event);
    }

    public function testCanDeleteContentFromCache(): void
    {
        $this->cache
            ->expects($this->once())
            ->method('delete')
            ->with('metadata:' . $this->user . '|' . $this->imageIdentifier);

        $this->listener->deleteFromCache($this->event);
    }

    public function testThrowsExceptionOnMissingCacheAdapter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The cache parameter is missing or not valid');
        new EventListener([]);
    }
}
