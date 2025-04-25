<?php

namespace Bitrix\TransformerController\Controllers;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Context;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Error;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Result;
use Bitrix\Main\Web\Json;

abstract class Base
{
	protected $request;
	protected $action;
	protected $result;
	protected $isCheckPermissions = true;
	protected ?string $daemonGuid = null;

	public function __construct(HttpRequest $request = null)
	{
		if(!empty($request))
		{
			$this->request = $request;
		}
		else
		{
			$this->request = Context::getCurrent()->getRequest();
		}
		$this->result = new Result();
	}

	/**
	 * Return list of actions. key - action, value - array of parameters
	 *
	 * @return array
	 */
	protected function getActionList()
	{
		return array();
	}

	public function disableCheckPermissions(): self
	{
		$this->isCheckPermissions = false;

		return $this;
	}

	public function enableCheckPermissions(): self
	{
		$this->isCheckPermissions = true;

		return $this;
	}

	public function isCheckPermissionsEnabled(): bool
	{
		return $this->isCheckPermissions;
	}

	/**
	 * Check required parameters of the current action.
	 * Returns true if all required parameters are specified.
	 *
	 * @return bool
	 */
	protected function checkParams()
	{
		if(!$this->action)
		{
			$this->result->addError(new Error('action is not specified'));
			return false;
		}
		if(!method_exists($this, $this->action))
		{
			$this->result->addError(new Error('action is not found'));
			return false;
		}
		$actions = $this->getActionList();
		if(!isset($actions[$this->action]) && !is_array($actions[$this->action]))
		{
			$this->result->addError(new Error('action is not found'));
			return false;
		}
		if(isset($actions[$this->action]['permissions']) && $this->isCheckPermissionsEnabled())
		{
			if(!$this->checkPermissions($actions[$this->action]['permissions']))
			{
				$this->result->addError(new Error('Access denied'));
			}
		}
		if(isset($actions[$this->action]['params']))
		{
			foreach($actions[$this->action]['params'] as $param => $description)
			{
				if(is_array($description) && isset($description['required']) && $description['required'] === true)
				{
					$value = $this->request->getQuery($param);
					if(!$value)
					{
						$this->result->addError(new Error('Required parameter '.$param.' is not specified'));
					}
				}
			}
		}
		return $this->result->isSuccess();
	}

	/**
	 * Set current action of the controller.
	 *
	 * @param string $action
	 * @return $this
	 * @throws ArgumentNullException
	 * @throws ArgumentTypeException
	 */
	public function setAction($action)
	{
		if(empty($action))
		{
			throw new ArgumentNullException('action');
		}
		if(!is_string($action))
		{
			throw new ArgumentTypeException('action', 'string');
		}
		$this->action = $action;

		return $this;
	}

	/**
	 * @return Result
	 */
	public function getResult()
	{
		return $this->result;
	}

	/**
	 * Execute action, send json response
	 * @param bool $sendResponse
	 */
	public function exec($sendResponse = true)
	{
		$this->checkParams();
		if($this->result->isSuccess())
		{
			$result = $this->runAction();
		}
		if($sendResponse === true)
		{
			$this->sendResponse($result);
		}
	}

	/**
	 * Send json response. If $result is empty - builds response based on $this->result
	 *
	 * @param array $result
	 */
	public function sendResponse($result = array())
	{
		if(!is_array($result) || empty($result))
		{
			if(!$this->result->isSuccess())
			{
				$result = array(
					'success' => false,
					'error' => $this->result->getErrorMessages(),
				);
			}
			else
			{
				$result = array(
					'success' => true,
					'data' => $this->result->getData(),
				);
			}
		}

		echo Json::encode($result);
	}

	/**
	 * Prepare params for action and invoke it.
	 *
	 * @return array|mixed
	 */
	protected function runAction()
	{
		$actions = $this->getActionList();
		$params = array();
		foreach($actions[$this->action]['params'] as $param => $description)
		{
			if(is_int($param) && is_string($description))
			{
				$param = $description;
			}
			$params[$param] = $this->request->getQuery($param);
			if($params[$param] === null && is_array($description) && isset($description['default']))
			{
				$params[$param] = $description['default'];
			}
		}
		return $this->{$this->action}($params);
	}

	protected function checkPermissions($roles = array())
	{
		foreach($roles as $role)
		{
			if($role === 'admin')
			{
				global $USER;
				if($USER->isAdmin())
				{
					return true;
				}
			}
			if ($role === 'daemon')
			{
				$checkResult = ServiceLocator::getInstance()->get('transformercontroller.service.token')->check($this->request);
				if (!$checkResult->isSuccess())
				{
					return false;
				}

				$daemonGuid = $checkResult->getData()['guid'] ?? null;
				if ($daemonGuid)
				{
					$this->daemonGuid = $daemonGuid;
				}

				return true;
			}
		}

		return false;
	}
}
