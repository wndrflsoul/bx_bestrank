<?
namespace Bitrix\TransformerController;

use Bitrix\Main\Error;
use Bitrix\Main\IO\File;
use Bitrix\Main\Result;
use Bitrix\TransformerController\Runner\Runner;

abstract class BaseCommand
{
	const DIRECTORY = 'base';

	protected $params;
	protected $result;
	protected $runner;

	/** @return Result*/
	abstract public function execute();

	/**
	 * BaseCommand constructor.
	 * Validate parameters. If validation fails writes errors to $this->result.
	 *
	 * @param array $params Parameters of this command.
	 * @param Runner $runner Object to execute commands.
	 */
	public function __construct($params, Runner $runner)
	{
		$this->result = new Result();
		$errors = self::validate($params);

		if($errors)
		{
			$this->result->addErrors($errors);
		}
		else
		{
			$this->params = $params;
		}

		$this->runner = $runner;
	}

	/**
	 * @return string
	 */
	public static function getClassName()
	{
		return get_called_class();
	}

	/**
	 * @return array
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * Add $value on $key to data array in $this->result.
	 *
	 * @param string $key Key of the value in result.
	 * @param mixed $value Value to put in result.
	 * @return void
	 */
	protected function addResult($key, $value)
	{
		$result = $this->result->getData();
		$result[$key] = $value;
		$this->result->setData($result);
	}

	/**
	 * Pass command to the runner (bash).
	 *
	 * @param string $command Command to execute.
	 * @return array|bool
	 */
	protected function exec($command)
	{
		return $this->runner->run($command);
	}

	/**
	 * Returns list of required parameters.
	 * Each parameter may contain a list of valid options.
	 *
	 * @return array
	 */
	protected static function getRequiredParams()
	{
		return array('back_url');
	}

	/**
	 * Get required parameters of the current class, validate them and return array of errors.
	 * Return false on success.
	 *
	 * @param array $params Parameters to validate with particular command.
	 * @return array|bool
	 */
	final public static function validate($params)
	{
		$requiredParams = static::getRequiredParams();
		$errors = false;

		/** @var string|int $paramName - either string paramName or just array index */
		/** @var string|array $allowedVariants - either paramName (is no options for the param are specified) or list of allowed variants */
		foreach($requiredParams as $paramName => $allowedVariants)
		{
			if(is_array($allowedVariants))
			{
				if(isset($params[$paramName]) && !is_array($params[$paramName]))
				{
					$params[$paramName] = [$params[$paramName]];
				}

				foreach($params[$paramName] as $variant)
				{
					if(!in_array($variant, $allowedVariants))
					{
						$errors[] = new Error("value {$variant} is not allowed in {$paramName}", TimeStatistic::ERROR_CODE_COMMAND_ERROR);
					}
				}
			}
			elseif(!isset($params[$allowedVariants]))
			{
				$errors[] = new Error("required param {$allowedVariants} is not specified", TimeStatistic::ERROR_CODE_COMMAND_ERROR);
			}
		}
		return $errors;
	}

	/**
	 * Calculate md5 on the file.
	 *
	 * @param string $file Full path to the file.
	 * @return string
	 */
	protected function md5($file)
	{
		return md5_file($file);
	}

	/**
	 * Calculate sha1 on the file.
	 *
	 * @param string $file Full path to the file.
	 * @return string
	 */
	protected function sha1($file)
	{
		return sha1_file($file);
	}

	/**
	 * Calculate crc32 on the file.
	 *
	 * @param string $file Full path to the file.
	 * @return int
	 */
	protected function crc32($file)
	{
		return crc32(File::getFileContents($file));
	}

	/**
	 * Check results of the command.
	 * If there is no data - its a critical error.
	 *
	 * Returns false if there was at least one transformation error.
	 * Returns true if everything is ok.
	 *
	 * @return bool
	 */
	protected function checkFinalResults()
	{
		if(!$this->result->isSuccess())
		{
			return false;
		}
		$formatsCount = count($this->params['formats']);
		if($formatsCount > 0)
		{
			$resultData = $this->result->getData();
			if(isset($resultData['files']) && is_array($resultData['files']))
			{
				$importantResults = $resultData['files'];
			}
			else
			{
				$importantResults = $resultData;
			}
			$falseCount = 0;
			$allCount = count($importantResults);
			if($allCount == 0)
			{
				return true;
			}
			foreach($importantResults as $fileResult)
			{
				if($fileResult === false)
				{
					$falseCount++;
				}
			}
			if($falseCount == $allCount)
			{
				$this->result->addError(new Error('All transformation has failed', TimeStatistic::ERROR_CODE_COMMAND_FAILED));
				return false;
			}
			elseif($falseCount > 0)
			{
				$this->addResult('error', TimeStatistic::ERROR_CODE_TRANSFORMATION_FAILED);
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns max file size for this command.
	 *
	 * @param string $tarif
	 * @return int
	 */
	public static function getMaxFileSize($tarif = '')
	{
		return 104857600;
	}

	/**
	 * @return false|string
	 */
	protected function getDirectoryForSavingFilesOnError()
	{
		return false;
	}

	protected function zipFiles(string $path, array $files): Result
	{
		$result = new Result();

		if (!class_exists('ZipArchive'))
		{
			return $result->addError(new Error('zipFiles: ZipArchive is not available'));
		}

		if (empty($files))
		{
			return $result->addError(new Error('zipFiles: Empty files'));
		}

		$zip = new \ZipArchive();
		$openResult = $zip->open($path, \ZipArchive::CREATE);
		if ($openResult !== true)
		{
			return $result->addError(new Error('zipFiles: Error trying to create archive on ' . $path . ': ' . $openResult));
		}
		foreach ($files as $archiveName => $absolutePath)
		{
			$zip->addFile($absolutePath, $archiveName);
		}
		$zip->close();

		return $result;
	}
}
