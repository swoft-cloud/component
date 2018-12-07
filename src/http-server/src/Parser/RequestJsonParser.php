<?php
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://doc.swoft.org
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */
namespace Swoft\Http\Server\Parser;

use Psr\Http\Message\ServerRequestInterface;
use Swoft\Bean\Annotation\Bean;
use Swoft\Helper\JsonHelper;
use Swoft\Http\Message\Server\Request;

/**
 * The json parser of request
 * @Bean()
 */
class RequestJsonParser implements RequestParserInterface
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Psr\Http\Message\ServerRequestInterface
     * @throws \RuntimeException
     */
    public function parse(ServerRequestInterface $request): ServerRequestInterface
    {
        if ($request instanceof Request && \strtoupper($request->getMethod()) !== 'GET') {
            $bodyStream = $request->getBody();
            $bodyContent = $bodyStream->getContents();
            try {
                $bodyParams = JsonHelper::decode($bodyContent, true);
            } catch (\Exception $e) {
                $bodyParams = $bodyContent;
            }

            return $request->withBodyParams($bodyParams);
        }

        return $request;
    }
}
