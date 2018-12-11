<?php
declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://doc.swoft.org
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */
namespace Swoft\Event;

/**
 * Application event
 */
class AppEvent
{
    /**
     * Application loader event
     */
    const APPLICATION_LOADER = 'applicationLoader';

    /**
     * Pipe message event
     */
    const PIPE_MESSAGE = 'pipeMessage';

    /**
     * Resource release event behind application
     */
    const RESOURCE_RELEASE = 'resourceRelease';

    /**
     * Before resource release
     */
    const RESOURCE_RELEASE_BEFORE = 'resourceReleaseBefore';

    /**
     * Worker start event
     */
    const WORKER_START = 'workerStart';
}
