<?php
/**
 * assignments.php
 *
 * @author Spyros Soldatos <spyros@valor.gr>
 * @link http://code.google.com/p/srbac/
 */
/**
 * The view of the users assignments
 * If no user id is passed a drop down with all users is shown
 * Else the user's assignments are shown.
 *
 * @author Spyros Soldatos <spyros@valor.gr>
 * @package srbac.views.authitem
 * @since 1.0.1
 */

$this->breadcrumbs = array(
    'Srbac Assignments'
  );
Yii::app()->getClientScript()->registerCssFile($this->getModule()->getStylesUrl('srbac.css'));
if(Yii::app()->getUser()->hasFlash($this->getModule()->flashKey))
{
?>
<div id="srbacError">
	<?php
	echo Yii::app()->getUser()->getFlash($this->getModule()->flashKey);
	echo Yii::app()->getUser()->setFlash($this->getModule()->flashKey, null);
	?>
</div>
<?php
}
if (!$id):
	if ($this->getModule()->getShowHeader())
	{
		$this->renderPartial($this->getModule()->header);
	}
?>
<div class="simple">
	<?php
	$this->renderPartial("../frontpage");
	?>
	<?php echo SHtml::beginForm(); ?>
	<?php
	echo Yii::t('srbac', 'User') . ": " .
			CHtml::activeDropDownList(
			$this->getModule()->getUserModel(),
			$this->getModule()->userId,
			CHtml::listData(
				$this->getModule()->getUserModel()->findAll(),
				$this->getModule()->userId,
				$this->getModule()->username
			),
			array(
				//'disabled'=>'disabled',
				'size' => 1,
				'class' => 'dropdown',
				'ajax' => array(
	            	'type' => 'POST',
	            	'url' => array('showAssignments'),
	            	'update' => '#assignments',
	            	'beforeSend' => 'function(){'.
							'$("#assignments").addClass("srbacLoading");'.
	                  '}',
					'complete' => 'function(){'.
							'$("#assignments").removeClass("srbacLoading");'.
	                  '}'
				),
				'prompt' => Yii::t('srbac', 'select user')
    ));
    ?>
	<?php
	$parent = $this->getModule()->parentModule ? $this->getModule()->parentModule->name."/" : "" ;
	$data = $this->getModule()->getUserModel()->findAll();
	$url = Yii::app()->getUrlManager()->createUrl("srbac/authitem/showAssignments");

	$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
        'model' =>$this->getModule()->getUserModel(),
        'attribute'=>$this->getModule()->username,
        'sourceUrl' => Yii::app()->getUrlManager()->createUrl($parent."srbac/authitem/getUsers") ,
        // additional javascript options for the autocomplete plugin
        'options' => array(
			'autoFocus' => true,
            'minLength' => '1',
            'select' => 'js:function(event,ui){'.
				              '$.ajax({'.
				                'url: "'.$url.'",'.
				                'data : "id="+ui.item.id,'.
				                'success: function(html){'.
				                  '$("#assignments").html(html);'.
				                '}'.
				              '});'.
				        '}',
        ),
        'htmlOptions' => array(
            'style' => 'height:20px;'
        ),
    ));
    ?>
	<?php echo SHtml::endForm(); ?>
</div>
<?php else:
$url = Yii::app()->getUrlManager()->createUrl("srbac/authitem/showAssignments", array('id' => $id));

Yii::app()->clientScript->registerScript(
	"alert",
	'$.ajax({'.
 		'type: "POST",'.
 		'url: "' . $url . '",'.
 		'success: function(html){'.
	 		'$("#assignments").html(html);'.
	    '}'.
    '});'
 	, CClientScript::POS_READY
);
?>
<?php endif; ?>
<div id="assignments"></div>
<?php
if(!$id && $this->getModule()->getShowFooter())
{
	$this->renderPartial($this->getModule()->footer);
}
?>