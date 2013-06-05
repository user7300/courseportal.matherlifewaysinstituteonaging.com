<?php

class TViewCompileCommand extends CConsoleCommand
{

	const ID = 'module.translate.commands.TViewCompileCommand';

	public static $regularExpression = '/\{t(?:\s+category\s*=\s*(\w+?))?\}\s*(.+?)\s*\{\/t\}/s';
	
	public static $messageReference = '$2';

	public $defaultAction = 'compileView';
	
	public $viewSource = 'views';
	
	public $messageSource = 'messages';
	
	private $_viewSource;
	
	private $_messageSource;

	private $_currentViewMessages = array();

	private $_currentViewId;
	
	public function getMessageSource()
	{
		if(!isset($this->_messageSource))
		{
			$this->_messageSource = Yii::app()->getComponent($this->messageSource);
			if($this->_messageSource === null)
				throw new CException(Yii::t(self::ID, 'A message source must be defined.'));
		}
		return $this->_messageSource;
	}
	
	public function getViewSource()
	{
		if(!isset($this->_viewSource))
		{
			$this->_viewSource = Yii::app()->getComponent($this->viewSource);
			if($this->_viewSource === null)
				throw new CException(Yii::t(self::ID, 'A view source component must be defined.'));
		}
		return $this->_viewSource;
	}
	
	public function actionStripTags($sourcePath, $compiledPath, $filePermission = 0755)
	{
		if(!is_dir(dirname($compiledPath)))
			mkdir(dirname($compiledPath), $filePermission, true);
	
		file_put_contents($compiledPath, preg_replace(self::$regularExpression, self::$messageReference, file_get_contents($sourcePath)));
		@chmod($compiledPath, $filePermission);
	}

	public function actionCompileView($sourcePath, $compiledPath, $id, $language, $filePermission = 0755, $useTransaction = true)
	{
		if($useTransaction)
			$transaction = $this->getViewSource()->getDbConnection()->beginTransaction();
		
		try
		{
			if(!is_dir(dirname($compiledPath)))
				mkdir(dirname($compiledPath), $filePermission, true);
			
			$this->_currentViewMessages = array();
			foreach($this->getViewSource()->getViewMessages($id) as $row)
				$this->_currentViewMessages[$row['message']] = array('id' => $row['id'], 'confirmed' => false);
			$this->_currentViewId = $id;
	
			file_put_contents(
				$compiledPath,
				preg_replace_callback(
					self::$regularExpression,
					array(&$this, 'compileViewCallback'),
					file_get_contents($sourcePath)
				)
			);
			@chmod($compiledPath, $filePermission);
	
			$this->getViewSource()->deleteViewMessages(
					$viewId,
					array_filter(array_map(create_function('$message', 'return $message["id"];'), $viewMessages), create_function('$id', 'return isset($id)'))
			);
			$this->getViewSource()->updateViewCreated($id, $language);
			if(isset($transaction))
				$transaction->commit();
		}
		catch(Exception $e)
		{
			if(file_exists($compiledPath))
				unlink($compiledPath);
			if(isset($transaction))
				$transaction->rollback();
			throw $e;
		}
	}

	protected function compileViewCallback($matches)
	{
		$category = $matches[1] === '' ? $this->getMessageSource()->messageCategory : $matches[1];
		$message = $matches[2];
		$translation = Yii::t($category, $message, array(), null, null);

		if(isset($this->_currentViewMessages[$message]))
		{
			$this->_currentViewMessages[$message]['confirmed'] = true;
		}
		else
		{
			$messageId = $this->getMessageSource()->getMessageId($category, $message);
		
			if($messageId === false)
			{
				$messageId = $this->getMessageSource()->addSourceMessage($category, $message);
			}
		
			if($messageId === null)
			{
				Yii::log("The source message '$message' could not be found in or added to the database table '{$this->dal->messageSourceTableSchema->name}'.", CLogger::LEVEL_ERROR, self::ID);
			}
			elseif($this->getViewSource()->addViewMessage($this->_currentViewId, $messageId) === null)
			{
				Yii::log("The view with ID '$this->_currentViewId' could not be associated with the source message with ID '$messageId'.", CLogger::LEVEL_ERROR, self::ID);
			}
			else
			{
				$this->_currentViewMessages[$message] = array('id' => $messageId, 'confirmed' => true);
			}
		}
		
		return $translation;
	}

}