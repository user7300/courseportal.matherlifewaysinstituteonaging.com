<?php

class TViewRenderer extends CViewRenderer
{
	
	public $compiledViewTable = '{{translate_compiled_view}}';
	
	public $compiledViewMessageTable = '{{translate_compiled_view_message}}';

	private $_translator;
	
	private $_db;
	
	private $_selectMessageIdCommand;
	
	private $_insertCompiledViewMessageCommand;
	
	private $_compiledViewMessageNotExistsCommand;
	
	private $_messageIDs = array();
	
	public function init()
	{
		$this->_translator = TranslateModule::translator();
	}

	/**
	 * Returns the DB connection used for the message source.
	 * @return CDbConnection the DB connection used for the message source.
	 */
	public function getDbConnection()
	{
		if($this->_db === null)
		{
			$this->_db = Yii::app()->getMessages()->getDbConnection();
		}
		return $this->_db;
	}

	/**
	 * Parses the source view file and saves the results as another file.
	 * This method is required by the parent class.
	 * @param string $sourceFile the source view file path
	 * @param string $viewFile the resulting view file path
	 */
	protected function generateViewFile($sourceFile, $viewFile)
	{
		$ignore_user_abort = ini_get('ignore_user_abort');
		if(!$ignore_user_abort)
			ignore_user_abort(true);
		
		$this->_selectMessageIdCommand = $this->getDbConnection()->createCommand(
				'SELECT id FROM ' . Yii::app()->getMessages()->sourceMessageTable . ' ' . 
				'WHERE (category=:category AND message=:message)');
		
		$this->_compiledViewMessageNotExistsCommand = $this->getDbConnection()->createCommand(
				"SELECT NOT EXISTS(SELECT 1 FROM $this->compiledViewMessageTable " .
				'WHERE (message_source_id=:message_source_id AND compiled_view_id=:compiled_view_id))')
				->bindValue(':compiled_view_id', $viewFile['id']);
		
		$this->_insertCompiledViewMessageCommand = $this->getDbConnection()->createCommand(
				"INSERT INTO $this->compiledViewMessageTable " .
				'(message_source_id, compiled_view_id) VALUES (:message_source_id, :compiled_view_id)')
				->bindValue(':compiled_view_id', $viewFile['id']);

		file_put_contents(
			$viewFile['compiled_path'], 
			preg_replace_callback(
				'/\{t(?:\s+category\s*=\s*(\w+?))?\}(.+?)\{\/t\}/s', 
				array(&$this, 'pregReplaceCallback'), 
				file_get_contents($sourceFile)
			)
		);
		
		$this->getDbConnection()->createCommand()->update($this->compiledViewTable, array('created' => date('Y-m-d H:i:s')), 'id=:id', array(':id' => $viewFile['id']));
		
		if(!$ignore_user_abort)
			ignore_user_abort($ignore_user_abort);
	}

	private function pregReplaceCallback($matches)
	{
		$category = $matches[1] === '' ? $this->_translator->messageCategory : $matches[1];
		$message = $matches[2];
		$translation = Yii::t($category, $message, array(), null, null);
		
		if(isset($this->_messageIDs[$message]))
			$messageId = $this->_messageIDs[$message];
		else
			$messageId = $this->_messageIDs[$message] = $this->_selectMessageIdCommand->bindValues(array(':category' => $category, ':message' => $message))->queryScalar();
		
		if($messageId === false)
		{
			$model = new MessageSource;
			$model->setAttribute('category', $category);
			$model->setAttribute('message', $message);
			
			if($model->save())
			{
				$messageId = $this->_messageIDs[$message] = $model->id;
			}
			else
			{
				Yii::log("Message '$message' in category '$category' could not be added to source message table", CLogger::LEVEL_ERROR, MPTranslate::ID.'.'.get_class($this));
				return $translation;
			}
		}
		
		if($this->_compiledViewMessageNotExistsCommand->bindValue(':message_source_id', $messageId)->queryScalar() === 1)
		{
			$this->_insertCompiledViewMessageCommand->bindValue(':message_source_id', $messageId)->execute();
		}
		
		return $translation;
	}
	
	public function renderFile($context, $sourceFile, $data, $return)
	{
		if(!is_file($sourceFile) || ($file = realpath($sourceFile)) === false)
			throw new CException(Yii::t('yii', 'View file "{file}" does not exist.', array('{file}' => $sourceFile)));
		
		$messageSource = Yii::app()->getMessages();
		
		$command = $this->getDbConnection()->createCommand(
			"SELECT $this->compiledViewTable.id AS id, $this->compiledViewTable.compiled_path AS compiled_path, $this->compiledViewTable.created AS created, MAX($messageSource->translatedMessageTable.last_modified) AS last_modified " .
			"FROM $this->compiledViewTable " .
			"INNER JOIN $this->compiledViewMessageTable ON ($this->compiledViewTable.id=$this->compiledViewMessageTable.compiled_view_id) " .
			"INNER JOIN $messageSource->translatedMessageTable ON ($this->compiledViewMessageTable.message_source_id=$messageSource->translatedMessageTable.id AND $this->compiledViewTable.language=$messageSource->translatedMessageTable.language) " .
			"WHERE ($this->compiledViewTable.source_path=:source_path AND $this->compiledViewTable.language=:language)")
			->bindValues(array(':source_path' => $sourceFile, ':language' => $this->_translator->getLanguageID()));

		$transaction = $this->getDbConnection()->beginTransaction();
		try
		{
			$viewFile = $command->queryRow();
			
			if(empty($viewFile) || $viewFile['id'] === null)
			{
				$model = new CompiledView;
				$model->setAttributes(array('source_path' => $sourceFile, 'compiled_path' => $this->getViewFile($sourceFile), 'language' => $this->_translator->getLanguageID()));
				
				if($model->save())
				{
					$viewFile['id'] = $model->getAttribute('id');
					$viewFile['compiled_path'] = $model->getAttribute('compiled_path');
					$viewFile['created'] = $viewFile['last_modified'] = $model->getAttribute('created');
				}
				else
				{
					Yii::log("A compiled view for source file '$sourceFile' using language '{$this->_translator->getLanguageID()}' could not be added to the database.", CLogger::LEVEL_ERROR, MPTranslate::ID.'.'.get_class($this));
				}
			}
			else
			{
				$viewFile['last_modified'] = strtotime($viewFile['last_modified']);
				$viewFile['created'] = strtotime($viewFile['created']);
			}
			
			if(isset($viewFile['compiled_path']))
			{
				if(!is_file($viewFile['compiled_path']))
				{
					@mkdir(dirname($viewFile['compiled_path']), $this->filePermission, true);
					$viewFile['last_modified'] = time();
				}
				
				if($viewFile['last_modified'] >= $viewFile['created'] || @filemtime($sourceFile) >= $viewFile['created'])
				{
					$this->generateViewFile($sourceFile, $viewFile);
					@chmod($viewFile, $this->filePermission);
				}
				$transaction->commit();
			}
			else
			{
				$viewFile['compiled_path'] = $sourceFile;
				Yii::log("A compiled view for source file '$sourceFile' using language '{$this->_translator->getLanguageID()}' could not be found.", CLogger::LEVEL_ERROR, MPTranslate::ID.'.'.get_class($this));
				$transaction->rollback();
			}
		}
		catch(Exception $e)
		{
			$transaction->rollback();
			throw $e;
		}

		return $context->renderInternal($viewFile['compiled_path'], $data, $return);
	}
	
	/**
	 * Generates the resulting view file path.
	 * @param string $file source view file path
	 * @return string resulting view file path
	 */
	protected function getViewFile($file)
	{
		return $this->useRuntimePath ? 
			Yii::app()->getRuntimePath().DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.sprintf('%x', crc32(get_class($this).Yii::getVersion().dirname($file))).DIRECTORY_SEPARATOR.$this->_translator->getLanguageID().DIRECTORY_SEPARATOR.basename($file) : 
			$file.'c.'.$this->_translator->getLanguageID();
	}
	
}