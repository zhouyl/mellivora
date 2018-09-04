<?php

namespace Mellivora\Events;

use Mellivora\Application\Container;
use Mellivora\Queue\InteractsWithQueue;
use Mellivora\Support\Contracts\Queue\Job;

class CallQueuedHandler
{
    /**
     * The container instance.
     *
     * @var \Mellivora\Application\Container
     */
    protected $container;

    /**
     * Create a new job instance.
     *
     * @param \Mellivora\Application\Container $container
     *
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Handle the queued job.
     *
     * @param \Mellivora\Support\Contracts\Queue\Job $job
     * @param array                                  $data
     *
     * @return void
     */
    public function call(Job $job, array $data)
    {
        $handler = $this->setJobInstanceIfNecessary(
            $job,
            $this->container->make($data['class'])
        );

        call_user_func_array(
            [$handler, $data['method']],
            unserialize($data['data'])
        );

        if (!$job->isDeletedOrReleased()) {
            $job->delete();
        }
    }

    /**
     * Set the job instance of the given class if necessary.
     *
     * @param \Mellivora\Support\Contracts\Queue\Job $job
     * @param mixed                                  $instance
     *
     * @return mixed
     */
    protected function setJobInstanceIfNecessary(Job $job, $instance)
    {
        if (in_array(InteractsWithQueue::class, class_uses_recursive(get_class($instance)))) {
            $instance->setJob($job);
        }

        return $instance;
    }

    /**
     * Call the failed method on the job instance.
     *
     * The event instance and the exception will be passed.
     *
     * @param array      $data
     * @param \Exception $e
     *
     * @return void
     */
    public function failed(array $data, $e)
    {
        $handler = $this->container->make($data['class']);

        $parameters = array_merge(unserialize($data['data']), [$e]);

        if (method_exists($handler, 'failed')) {
            call_user_func_array([$handler, 'failed'], $parameters);
        }
    }
}
