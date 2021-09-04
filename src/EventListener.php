<?php declare(strict_types=1);
namespace Imbo\Plugin\MetadataCache;

use DateTime;
use Imbo\EventListener\ListenerInterface;
use Imbo\EventManager\EventInterface;
use Imbo\Exception\InvalidArgumentException;
use Imbo\Model\Metadata as MetadataModel;
use Imbo\Plugin\MetadataCache\Cache\CacheInterface;

/**
 * Metadata cache
 */
class EventListener implements ListenerInterface
{
    private CacheInterface $cache;

    /**
     * Class constructor
     *
     * @param array $params Parameters for the event listener
     */
    public function __construct(array $params)
    {
        if (!isset($params['cache']) || !($params['cache'] instanceof CacheInterface)) {
            throw new InvalidArgumentException('The cache parameter is missing or not valid', 500);
        }

        $this->cache = $params['cache'];
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Load and store in cache
            'db.metadata.load' => [
                'loadFromCache' => 10,
                'storeInCache' => -10,
            ],

            // Delete from cache
            'db.metadata.delete' => ['deleteFromCache' => -10],
            'db.image.delete' => ['deleteFromCache' => -10],

            // Store updated data in cache
            'db.metadata.update' => ['storeInCache' => -10],
        ];
    }

    /**
     * Get data from the cache
     *
     * @param EventInterface $event The event instance
     */
    public function loadFromCache(EventInterface $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $cacheKey = $this->getCacheKey(
            $request->getUser(),
            $request->getImageIdentifier(),
        );

        /** @var mixed */
        $result = $this->cache->get($cacheKey);

        if (
            is_array($result) &&
            isset($result['lastModified']) &&
            $result['lastModified'] instanceof DateTime &&
            isset($result['metadata']) &&
            is_array($result['metadata'])
        ) {
            $model = new MetadataModel();
            $model->setData($result['metadata']);

            $response->setModel($model)
                     ->setLastModified($result['lastModified']);

            $response->headers->set('X-Imbo-MetadataCache', 'Hit');

            // Stop propagation of listeners for this event
            $event->stopPropagation();
            return;
        } elseif ($result) {
            // Invalid result stored in the cache, delete
            $this->cache->delete($cacheKey);
        }

        $response->headers->set('X-Imbo-MetadataCache', 'Miss');
    }

    /**
     * Store metadata in the cache
     *
     * @param EventInterface $event The event instance
     */
    public function storeInCache(EventInterface $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $cacheKey = $this->getCacheKey(
            $request->getUser(),
            $request->getImageIdentifier(),
        );

        // Store the response in the cache for later use
        if ($response->getStatusCode() === 200) {
            $metadata = [];

            if ($model = $response->getModel()) {
                /** @var mixed */
                $metadata = $model->getData();
            }

            $this->cache->set($cacheKey, [
                'lastModified' => $response->getLastModified(),
                'metadata' => $metadata,
            ]);
        }
    }

    /**
     * Delete data from the cache
     *
     * @param EventInterface $event The event instance
     */
    public function deleteFromCache(EventInterface $event): void
    {
        $request = $event->getRequest();

        $cacheKey = $this->getCacheKey(
            $request->getUser(),
            $request->getImageIdentifier(),
        );

        $this->cache->delete($cacheKey);
    }

    /**
     * Generate a cache key
     *
     * @param string $user The user which the image belongs to
     * @param string $imageIdentifier The current image identifier
     * @return string Returns a cache key
     */
    private function getCacheKey(string $user, string $imageIdentifier = null): string
    {
        return 'metadata:' . $user . '|' . (string) $imageIdentifier;
    }
}
