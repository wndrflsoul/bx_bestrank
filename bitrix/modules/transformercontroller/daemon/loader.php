<?php

namespace Bitrix\TransformerController\Daemon;

final class Loader
{
	private const ROOTS = [
		'Psr\\Http\\Client' =>[
			__DIR__ . '/../../main/vendor/psr/http-client/src',
		],
		'Psr\\Http\\Message' =>[
			__DIR__ . '/../../main/vendor/psr/http-message/src',
		],
		'Psr\\Log' => [
			__DIR__ . '/../../main/vendor/psr/log/src',
		],
		'Bitrix\\TransformerController\\Daemon' => [
			__DIR__,
		],
	];

	public static function autoLoad($className): void
	{
		$normalized = ltrim($className, '\\');

		[$rootNamespace, $rootDirs] = self::findRoot($normalized);
		if (!$rootNamespace || empty($rootDirs))
		{
			return;
		}

		$withoutRootNamespace = ltrim(str_replace($rootNamespace, '', $className), '\\');
		$partsWithoutRootNamespace = explode('\\', $withoutRootNamespace);

		foreach ($rootDirs as $singleRootDir)
		{
			$fileName = $singleRootDir . '/' . implode('/', $partsWithoutRootNamespace) . '.php';
			$lowerFileName = strtolower($fileName);
			if (file_exists($fileName) && is_readable($fileName))
			{
				require_once $fileName;
			}
			elseif (file_exists($lowerFileName) && is_readable($lowerFileName))
			{
				require_once $lowerFileName;
			}
		}
	}

	private static function findRoot(string $fqn): array
	{
		foreach (self::ROOTS as $rootNamespace => $rootDirs)
		{
			if (str_starts_with($fqn, $rootNamespace))
			{
				return [$rootNamespace, $rootDirs];
			}
		}

		return [null, null];
	}
}
