<table id="<?php echo $id.'-'.$dimension->id; ?>-activities">
	<tr>
		<td colspan="2"><p><?php echo $dimension->description; ?></p></td>
	</tr>
	<tr id="<?php echo $id.'-'.$dimension->id; ?>-activityInfo">
		<td id="<?php echo $id.'-'.$dimension->id; ?>-activityInfo-datePeriod">
			{t}Date Period{/t}:
			<?php
			$this->widget('zii.widgets.jui.CJuiDatePicker',
					array(
							'name' => 'date',
							'value' => date($userActivitySearchModel->dateFormat, $time),
							'language' => Yii::app()->getLanguage(),
							'htmlOptions' => array(
								'id' => $id.'-'.$dimension->id.'-activityDate',
								'onchange' => '$.fn.yiiGridView.update("'.$id.'-'.$dimension->id.'-userActivityGrid")'
							)
					));
			echo '&nbsp;-&nbsp;'.CHtml::dropDownList(
					'range', 
					$range, 
					array(
						'day' => '{t}Day{/t}', 
						'week' => '{t}Week{/t}', 
						'month' => '{t}Month{/t}', 
						'year' => '{t}Year{/t}', 
						'all' => '{t}All Time{/t}'
					),
					array(
						'id' => $id.'-'.$dimension->id.'-activityRange',
						'onchange' => '$.fn.yiiGridView.update("'.$id.'-'.$dimension->id.'-userActivityGrid")' 
					)
			);
			?>
		</td>
		<td id="<?php echo $id.'-'.$dimension->id; ?>-activityInfo-crContributions">
			{t}Total CR Contributions{/t}: <?php echo $userActivitySearchModel->getCRtotal(); ?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<?php 
			$dataProvider = $userActivitySearchModel->search();
			$dataProvider->getSort()->route = $actionPrefix.'dimension';
			$dataProvider->getSort()->params = array('id' => $dimension->id);
			$this->widget('zii.widgets.grid.CGridView',
					array(
						'id' => $id.'-'.$dimension->id.'-userActivityGrid',
						'filter' => $userActivitySearchModel,
						'actionPrefix' => $actionPrefix,
						'dataProvider' => $dataProvider,
						'ajaxUrl' => $this->createUrl($actionPrefix.'dimension', array('id' => $dimension->id)),
						'columns' => array(
							'activity.name',
							'activity.cr',
							'comment',
							'dateCompleted',
							array(
								'class' => 'CButtonColumn',
								'template' => '{view}{delete}',
								'buttons' => array(
									'view' => array(
										'label' => '{t}Edit{/t}',
										'url' => '$this->grid->getOwner()->createUrl("'.$actionPrefix.'logActivity", array("UserActivity" => array("id" => $data->id)));',
										'click' => 'function(){'.
										CHtml::ajax(
											array(
												'type' => 'GET',
												'url' => 'js:$(this).attr("href")',
												'beforeSend' => 'function(){'.
													'$("div#'.$id.'-activityLogForm").spUserActivityForm({"scenario":"update","loading":true});'.
													'$("div#'.$id.'-activityDialog").dialog("open");'.
												'}',
												'success' => 'function(data){'.
													'var $form = $("div#'.$id.'-activityLogForm");'.
													'$.each($.parseJSON(data), function(attribute, value){'.
														'$form.spUserActivityForm(attribute, value);'.
													'});'.
												'}',
												'error' => 'function(data){'.
													'$("div#'.$id.'-activityDialog").dialog("close");'.
													'alert("{t}Unable to contact server.{/t}");'.
												'}',
												'complete' => 'function(){'.
													'$("div#'.$id.'-activityLogForm").spUserActivityForm("loading", false);'.
												'}',
											)
										).
										'return false;'.
										'}',
									),
									'delete' => array(
										'label' => '{t}Delete{/t}',
										'url' => '$this->grid->getOwner()->createUrl("'.$actionPrefix.'logActivity", array("UserActivity" => array("id" => $data->id, "dimensions" => array('.$dimension->id.'))));',
										'click' => 'function(){'.
											CHtml::ajax(
												array(
													'type' => 'DELETE',
													'url' => 'js:$(this).attr("href")',
													'beforeSend' => 'function(){$("#'.$id.'-'.$dimension->id.'-activities").addClass("loading");}',
													'success' => 'function(data){'.
														'$.fn.yiiGridView.update("'.$id.'-'.$dimension->id.'-userActivityGrid");'.
														'var $data = $.parseJSON(data);'.
														//'alert($data.message);'.
													'}',
													'error' => 'function(data){alert("{t}Unable to contact server.{/t}");}',
													'complete' => 'function(){$("#'.$id.'-'.$dimension->id.'-activities").removeClass("loading");}',
												)
											).
											'return false;'.
										'}',
									),
								)
							)
						),
						'beforeAjaxUpdate' => 'function(id,options){'.
							'options.data = $("input#'.$id.'-'.$dimension->id.'-activityDate, select#'.$id.'-'.$dimension->id.'-activityRange").serialize();'.
							'return true;'.
						'}',
						'afterAjaxUpdate' => 'function(id,data){'.
							'$("#'.$id.'-'.$dimension->id.'-activityInfo-crContributions").text($(data).find("#'.$id.'-'.$dimension->id.'-activityInfo-crContributions").text());'.
							'return true;'.
						'}'
						
				)
			);
			?>
		</td>
	</tr>
</table>