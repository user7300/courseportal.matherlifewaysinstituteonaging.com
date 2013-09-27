<?php
Yii::app()->getClientScript()->registerCss('route-grid-table-width', 'div#route-grid table.items{min-width:100%;width:100%;max-width:100%;}');
$this->widget('zii.widgets.grid.CGridView',
		array(
				'id' => 'route-grid',
				'filter' => $model,
				'dataProvider' => $model->search(),
				'selectableRows' => 0,
				'columns' => array(
						'id',
						array(
								'name' => 'route',
								'htmlOptions' => array('style' => 'word-wrap:break-word;word-break:break-all;'),
						),
						array(
								'class' => 'CButtonColumn',
								'template' => '{view}{delete}',
								'viewButtonLabel' => TranslateModule::t('View Details'),
								'viewButtonUrl' => 'Yii::app()->getController()->createUrl("route/view", array("id" => $data->id))',
								'deleteButtonUrl' => 'Yii::app()->getController()->createUrl("route/delete", array("id" => $data->id))',
						)
				),
		)
);
?>