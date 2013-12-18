<?php $this->breadcrumbs = array('{t}Online Courses - Work/Life Balance{/t}'); ?>

<div class="small-masthead" style="background-image: url(<?php echo $this->getImagesUrl('header-courses.png'); ?>);">
	<h1 class="bottom">{t}Online Courses - Work/Life Balance{/t}</h1>
</div>
<div id="single-column">
	<h2 class="flowers">{t}Online Courses - Work/Life Balance{/t}</h2>
	<p>{t}To help individual caregivers transition into their new role, be better prepared to manage their loved one's needs, and learn how to effectively practice self-care, Mather LifeWays Institute on Aging has developed online programs that are designed to educate caregivers while fitting into any schedule.{/t}</p>
	<hr />
	<?php foreach($courses as $course):

        /*
	// The following 2 if statements are a BAD HACK!!! Need to find a better way here.
	if($course->name === 'introtocaregivingonline')
	{
		echo CHtml::image($this->getImagesUrl('image-hands.png'), '{t}hands{/t}', array('class' => 'image-right'));
	}
	else if($course->name === 'carecoachingonline')
	{
		echo CHtml::image($this->getImagesUrl('image-grocery.png'), '{t}groceries{/t}', array('class' => 'image-right'));
	}
         */
	?>
	<h3>
		<?php echo CHtml::link(t($course->title), $this->createUrl($course->name)); ?>
	</h3>
	<p>
		<?php echo t($course->description); ?>
	</p>
	<h4>{t}Objectives{/t}</h4>
	<ul>
		<?php 
		foreach($course->objectives as $objective)
			echo '<li>' . t($objective->text) . '</li>';
		?>
	</ul>
	<hr />
	<?php endforeach; ?>

<h2 class="flowers">{t}CARE Coaching Online{/t}</h2>
<p>{t}CARE Coaching Online provides working caregivers with essential tools, knowledge, and skills to effectively deal with the variety of issues arising from caring for their aging parents, relatives, or loved ones. Developed by Mather LifeWays Institute on Aging, CARE Coaching Online improves working caregivers abilities to communicate, advocate, relate, and encourage their older parents or loved ones in making future plans.{/t}</p>

<h2 class="flowers">{t}Spencer Powell Online Training (SPOT){/t}</h2>
<p>{t}Spencer Powell Online Training is designed to promote cognitive health and healthy lifestyle changes. Fake textFake textFake textFake textFake textFake textFake textFake textFake textFake textFake textFake textFake textFake textFake textFake textFake textFake textFake textFake textFake text{/t}</p>
</div>
