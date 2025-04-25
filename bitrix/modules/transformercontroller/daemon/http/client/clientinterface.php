<?php

namespace Bitrix\TransformerController\Daemon\Http\Client;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * Instance should be immutable. Options such as timeout, redirects and stuff are passed on object construct.
 * But options can be overwritten for specific request via options passed to a called method.
 */
interface ClientInterface
{
	public function __construct(array $defaultOptions = []);

	/**
	 * @param string|UriInterface $uri
	 * @param array $options
	 *
	 * @return ResponseInterface
	 *
	 * @throws ClientExceptionInterface
	 */
	public function get(string|UriInterface $uri, array $options = []): ResponseInterface;

	/**
	 * @param string|UriInterface $uri
	 * @param string $saveToFilePath
	 * @param array $options
	 *
	 * @return ResponseInterface
	 *
	 * @throws ClientExceptionInterface
	 */
	public function download(string|UriInterface $uri, string $saveToFilePath, array $options = []): ResponseInterface;

	/**
	 * @param string|UriInterface $uri
	 * @param array $options
	 *
	 * @return ResponseInterface
	 *
	 * @throws ClientExceptionInterface
	 */
	public function post(string|UriInterface $uri, array $options = []): ResponseInterface;
}
