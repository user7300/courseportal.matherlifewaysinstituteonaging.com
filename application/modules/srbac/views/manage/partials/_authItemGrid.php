<?php
$this->widget('zii.widgets.grid.CGridView',
		array(
				'id' => $gridId,
				'filter' => $model,
				'dataProvider' => $model->search(),
				'columns' => array(
						'name',
						array(
								'name' => 'type',
								'value' => 'AuthItem::$TYPES[$data->getAttribute("type")].($this->grid->getController()->getModule()->superUser === $data->getAttribute("name") ? " (Super User)" : "")',
								'filter' => AuthItem::$TYPES,
								'sortable' => false
						),
						array(
								'class' => 'CButtonColumn',
								'template' => '{update}{delete}',
								'buttons' => array(
										'update' => array(
												'url' => 'Yii::app()->getController()->createUrl("/srbac/manage/authItem", array("id" => $data->getAttribute("id")))',
												'imageUrl' => $this->getModule()->getIconsUrl('update.png'),
												'click' => 'function(){'.CHtml::ajax(
														array(
																'type' => 'GET',
																'url' => new CJavaScriptExpression('$(this).attr("href")+"&ajax='.$formId.'"'),
																'beforeSend' => 'function(){$("#'.$formId.'").addClass("srbacLoading");}',
																'complete' => 'function(){$("#'.$formId.'").removeClass("srbacLoading");}',
																'replace' => '#'.$formId
														)
												).'return false;}'
										),
										'delete' => array(
												'url' => 'Yii::app()->getController()->createUrl("/srbac/manage/authItem", array("id" => $data->getAttribute("id")))',
												'imageUrl' => $this->getModule()->getIconsUrl('delete.png'),
												'click' => 'function(){'.
														'if(!confirm("'.Yii::t('srbac', 'Are you sure you want to delete this item?').'")) return false;'.
														'var id = $(this).attr("href").split("?").pop().split("=").pop();'.
														'$("#'.$gridId.'").yiiGridView("update",  '.
														CJavaScript::encode(array(
																'type' => 'DELETE',
																'url' => 'js:$(this).attr("href").split("?").shift()',
																'data' => 'js:"id="+id',
																'success' => 'js:function(data){$("#'.$gridId.'").yiiGridView("update");if($("#'.$formId.' #AuthItem_id").val()==id)$("#'.$formId.'_createNewButton").trigger("click");}',
																'error' => 'js:function(jqXHR,textStatus,errorThrown){alert(errorThrown);}'
														)).');'.
														'return false;}',
												'visible' => '$this->grid->getController()->getModule()->superUser !== $data->getAttribute("name")'
										)
								),
						)
				),
		)
);
?>
