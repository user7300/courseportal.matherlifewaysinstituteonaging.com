<?php

$this->pageTitle = Yii::app()->name . ' - '.t('Guest');
$clientScript = Yii::app()->getClientScript();
$clientScript->registerCssFile($this->getStylesUrl('homeGuest.css'));
$clientScript->registerCssFile($this->getStylesUrl('course.css'));
$clientScript->registerScriptFile($this->getScriptsUrl('jquery.cycle.js'), CClientScript::POS_HEAD);
$clientScript->registerScript('customers_cycle', "$('#customers').cycle({ fx: 'fade' });");

$this->widget(
		'ext.fancybox.EFancyBox',
		array(
				'id' => '.open-tutorial',
				'config' => array(
						'width' => '1024px',
						'height' => '768px',
						'arrows' => false,
						'autoSize' => false,
						'mouseWheel' => false,
							
				)
		)
);

$this->widget(
		'ext.fancybox.EFancyBox',
		array(
				'id' => '.survey',
				'config' => array(
						'width' => '1024px',
						'height' => '768px',
						'arrows' => false,
						'autoSize' => false,
						'mouseWheel' => false,
				)
		)
);

?>

<!-- index page main image -->

<div id="home-image">
  <h1><?php echo t('Web-based Training for Caregivers'); ?></h1>
</div>

<!-- Begin sidebar -->

<div id="sidebar">

   <!-- Sidebar #1 with 3 buttons here --> 
   
  <div class="box-sidebar zero"> 
        
    <a href="<?php echo $this->createUrl('user/register'); ?>"><?php echo t('Register'); ?></a>
    <a href="<?php echo $this->createUrl('home/contact'); ?>" class="teal"><?php echo t('Request Information'); ?></a>
    <a href="#slide-1" data-fancybox-group="open-tutorial" class="teal open-tutorial"><?php echo t('Tutorial'); ?></a>
    <a href="#slide-2" data-fancybox-group="open-tutorial" class="open-tutorial" style="display: none;"></a>
    <a href="#slide-3" data-fancybox-group="open-tutorial" class="open-tutorial" style="display: none;"></a>
    <a href="#slide-4" data-fancybox-group="open-tutorial" class="open-tutorial" style="display: none;"></a>
    <a href="#slide-5" data-fancybox-group="open-tutorial" class="open-tutorial" style="display: none;"></a>
    <a href="#slide-6" data-fancybox-group="open-tutorial" class="open-tutorial" style="display: none;"></a>
    
  </div>
    
    <!-- Clients sidebar here -->
    
  <div class="box-sidebar">
    <h3><?php echo t('Our Clients'); ?></h3>
    <div id="customers">
    <a href="http://www.ibm.com" target="_blank"><img src="<?php echo $this->getImagesUrl('customers/ibm.png'); ?>" alt="IBM" /></a>
    <a href="http://www.ti.com/" target="_blank"><img src="<?php echo $this->getImagesUrl('customers/ti.png'); ?>" alt="Texas Instrument" /></a>
    <a href="http://www.merck.com" target="_blank"><img src="<?php echo $this->getImagesUrl('customers/merck.png'); ?>" alt="Merck Pharmaceuticals" /></a> 
    <a href="http://www.exxonmobil.com" target="_blank"><img src="<?php echo $this->getImagesUrl('customers/exxon.png'); ?>" alt="Merck Pharmaceuticals" /></a>
    <a href="http://www.deloitte.com" target="_blank"><img src="<?php echo $this->getImagesUrl('customers/deloitte.png'); ?>" alt="Deloitte" /></a>
    <a href="http://matherlifeways.com/" target="_blank"><img src="<?php echo $this->getImagesUrl('customers/mather.png'); ?>" alt="Mather Lifeways" /></a>
  	</div>
  </div>
  
  <!-- sidebar for Participating locations here -->
  
  <div class="box-sidebar one">
    <h3><?php echo t('Participating Locations'); ?></h3>
    <div id="flags">
    	<table>
    		<tr>
    			<td><img src="<?php echo $this->getImagesUrl('flags/United-States-Flag-64.png'); ?>" alt="<?php echo t('USA'); ?>" /></td> 
    			<td><img src="<?php echo $this->getImagesUrl('flags/China-Flag-64.png'); ?>" alt="<?php echo t('China'); ?>" /></td>
      			<td><img src="<?php echo $this->getImagesUrl('flags/Hong-Kong-Flag-64.png'); ?>" alt="<?php echo t('Hong Kong'); ?>" /></td> 
      		</tr>
      		<tr>
		      	<td><img src="<?php echo $this->getImagesUrl('flags/Brazil-Flag-64.png'); ?>" alt="<?php echo t('Brazil'); ?>" /></td>
		      	<td><img src="<?php echo $this->getImagesUrl('flags/Mexico-Flag-64.png'); ?>" alt="<?php echo t('Mexico'); ?>" /></td> 
		      	<td><img src="<?php echo $this->getImagesUrl('flags/Taiwan-Flag-64.png'); ?>" alt="<?php echo t('Taiwan'); ?>" /></td>
	      	</tr>
	      	<tr>
		      	<td><img src="<?php echo $this->getImagesUrl('flags/Argentina-Flag-64.png'); ?>" alt="<?php echo t('Argentina'); ?>" /></td> 
		      	<td><img src="<?php echo $this->getImagesUrl('flags/United-Kingdom-flag-64.png'); ?>" alt="<?php echo t('England'); ?>" /></td>
		      	<td><img src="<?php echo $this->getImagesUrl('flags/Luxembourg-Flag-64.png'); ?>" alt="<?php echo t('Luxembourg'); ?>" /></td>
	      	</tr>
      	</table>
  	</div>
  </div>
  
  <!-- sidebar for Stats on Caregivers here -->
  
  <div class="box-sidebar one">
    <h3><?php echo t('Statistics on Caregivers'); ?></h3>
    <div>
    	<img src="<?php echo $this->getImagesUrl('stat-two-thirds.png'); ?>" alt="2/3" style="margin-bottom: 8px;" /><br />
      	<?php echo t('2/3 of working caregivers in the USA report conflicts between work and caregiving that result in increased
					absenteeism, workday interruptions, reduced hours, and workload shifting to other employees.'); ?> 
	</div>
  </div>
  
  <!-- sidebar on Resent Research here -->
  
  <div class="box-sidebar two">
    <h3><?php echo t('Recent Research (USA)'); ?></h3>
    <img class="block-center" src="<?php echo $this->getImagesUrl('metlife.jpg'); ?>" alt="MetLife" />
    <p style="text-align:center;"><b><?php echo t('Double Jeopardy for Baby Boomers Caring for Their Parents'); ?></b></p>
    <p> <?php echo t('Nearly 10 million adult children over the age of 50 care for their aging parents. These family caregivers are themselves aging as well
					as providing care at a time when they also need to be planning and saving for their own retirement. The study is an updated, national
					look at adult children who work and care for their parents and the impact of caregiving on their earnings and lifetime wealth.'); ?> </p>
    <p> <a href="http://www.metlife.com/assets/cao/mmi/publications/studies/2010/mmi-working-caregivers-employers-health-care-costs.pdf" class="pdf" target="_blank"><?php echo t('The MetLife Study of Working Caregivers and Employer Health Care Costs (English)'); ?> </a> </p>
  </div>
  <div class="box-sidebar three">
    <h3><?php echo t('Whitepapers (English)'); ?></h3>
    <p> <a href="http://www.matherlifewaysinstituteonaging.com/wp-content/uploads/2012/03/eLearning-Maturing-Technology.pdf" class="pdf" target="_blank"><?php echo t('e-Learning: Maturing Technology Brings Balance &amp; Possibilities to Nursing Education'); ?> </a> <a href="http://www.matherlifewaysinstituteonaging.com/wp-content/uploads/2012/03/How-eLearning-Can-Reduce-Expenses-and-Improve-Staff-Performance.pdf"
				class="pdf" target="_blank"><?php echo t('The Bottom Line: How e-Learning Can Reduce Expenses and Improve Staff Performance'); ?> </a> </p>
  </div>
  <div class="box-sidebar four">
    <h3>Aging in Action</h3>
    <p>Aging in Action is Mather LifeWays Institute on Aging's monthly e-newsletter and blog containing the latest research news in the field of aging.</p>
    <a href="http://twitter.com/aginginaction" target="_blank"> <img class="block-center" src="<?php echo $this->getImagesUrl('twitter-bird.png'); ?>" alt="Twitter" /></a>
  </div>
</div>
<div class="column-wide">
  <h2 class="flowers">Mather LifeWays Institute on Aging</h2>
  <p> <?php echo t('Through research-based programs and innovative techniques, '); ?>Mather LifeWays Institute on Aging <?php echo t('is committed to advancing the field of geriatric care. Cutting-edge research lays the foundation for our
				solid solutions to senior care challenges, including recruitment, mentorship, training, and retention.'); ?> </p>
  <p> <?php echo t('Used by individuals and entire organizations, our nationally recognized, award-winning programs include training modules, online
				courses, toolkits, and learning modules designed to make learning fun and easy. Our programs have been shown to result in measurable
				improvements in the quality of care provided and workforce retention.'); ?> </p>
  <h2 class="flowers top-pad"><?php echo t('Online Courses for Caregivers'); ?></h2>
  <p style="padding-bottom: 5px;"><?php echo t('We deliver online learning and web-based modalities using the latest technologies to efficiently and cost-effectively empower professionals.
				In addition, we are well-positioned to help conduct pilot studies that measure the impact on both working caregivers and the bottom line for interested corporations.'); ?> </p>
  <h2 class="flowers top-pad"><?php echo t('A Closer Look - Lives of Caregivers'); ?></h2>
  <p style="padding-bottom: 25px;"> <?php echo t('Join us in looking at the incredible lives of several, unique caregivers, as they recall their experience and emotion. Capturing various age groups and ethnicities, you will quickly relate to the
		situation these caregivers were in. (English)'); ?> </p>
  <div class="box-grey">
    <div id="MatherCaregivers">
      <?php 
		$this->widget(
				'ext.JWplayer.JWplayer',
				array(
						'id' => 'MatherCaregivers',
						'config' => array(
								'image' => $this->createUrl('download').'/videos/MatherCaregivers/poster.jpg',
								'width' => '540px',
								'height' => '305px',
								'levels' => array(
										array('file' => $this->createUrl('download').'/videos/MatherCaregivers/video.m4v'),
										array('file' => $this->createUrl('download').'/videos/MatherCaregivers/video.webm'),
										array('file' => $this->createUrl('download').'/videos/MatherCaregivers/video.ogv')
								)
						)
				)
		);
		?>
    </div>
  </div>
  <h2 class="flowers top-pad"><?php echo t('Pedagogy'); ?></h2>
  <p> <?php echo t('Effective online instruction depends on learning experiences appropriately designed and facilitated by knowledgeable facilitators.
				Because learners have different learning styles or a combination of styles, our web-based training has been design using activities that
				address their modes of learning in order to provide significant experiences for each course user.'); ?> </p>
  <p> <?php echo t('Institution Wide Content Management - 25%')?><br />
    <?php echo t('Online Course Delivery - 25%')?><br />
    <?php echo t('Targeted Collaboration - 50%')?> </p>
  <img id="pie-chart" class="block-center" height="300" src="<?php echo $this->getImagesUrl('home-chart.png'); ?>" alt="<?php echo t('Pie chart'); ?>" />
  <?php $workingCaregiverSurvey->run(); ?>
  <div class="box-white">
    <h2 class="flowers"> <?php echo t('Health status of your working caregivers'); ?> </h2>
    <p> <?php echo t('Please choose one of the surveys below to take. Depending on your position, employer 
			or employee, submit this voluntary survey and view aggregate feedback from all previous users.'); ?> </p>
    <p> 
    	<a id="hrEmployerSurvey" href="#survey_hrEmployer"
				class="survey button" title="<?php echo t('HR/Employer Survey'); ?>"><?php echo t('HR/Employer Survey'); ?> </a> 
		<a id="caregiverSurvey" href="#survey_caregiver"
				class="survey button" title="<?php echo t('Caregiver Survey'); ?>"><?php echo t('Caregiver Survey'); ?> </a> 
	</p>
  </div>
</div>
<div id="bottom-logos">
  <h4><?php echo t('Partners'); ?></h4>
  <a href="http://www.rushu.rush.edu" id="rush">Rush University (Chicago)</a> 
  <a href="http://www.alz.org/" id="aa" target="_blank">Alzheimer's Asssociation</a> 
  <a href="https://github.com/" id="git" target="_blank">GitHub</a> 
  <a href="http://www.yiiframework.com/" id="yii" target="_blank">Yii Framework</a> 
  <a href="http://wfd.com/" id="wfd" target="_blank">WFD</a> 
  <a href="http://www.discoursellc.com/" id="discourse" target="_blank">Discourse, LLC.</a> 
  <a href="http://gladerfilmworks.com/" id="glader" target="_blank">Glader Filmworks</a> 
  <a href="http://www.mediastorm.com/" id="mediastorm" target="_blank">Mediastorm</a>
</div>
<?php 
$hrEmployerSurvey->run();
$caregiverSurvey->run();
?>

<!--  start tutorial course here -->

<div id="tutorial" style="display: none;">
  <div id="slide-1" class="slide">
    <h2 class="flowers"> <?php echo t('Tutorial'); ?> </h2>
    <hr />
    <p style="text-align: center;"> <b><?php echo t('Thank you for your interest and support!'); ?></b> </p>
    <p> <?php echo t('Through conducting applied research, we have developed award-winning, 
			evidence-based education programs for professionals who serve older adults. Staffed by researchers and educators, 
			we are a global resource for information about wellness, successful aging service innovations, and 
			educational programming.'); ?> </p>
    <p> <?php echo t('This tutorial is designed to help demonstrate our course model. We will briefly explore dementia, 
			Alzheimer\'s disease, and how they are related.'); ?> </p>
    <p> <?php echo t('Memory loss and other signs of mental decline have profound effects on the lives of individuals and families. But we are
			convinced that a good quality of life can still be maintained for all concerned by learning to make changes in lifestyle and outlook. For
			many family members, this involves a change in relationships and priorities. At times, the demands may seem overwhelming, but
			understanding the type of cognitive impairment can be the first step in combating a challenging situation.'); ?> </p>
    <p> <b><?php echo t('Objectives'); ?></b> </p>
    <ul>
      <li><?php echo t('Describe dementia'); ?></li>
      <li><?php echo t('Describe Alzheimer\'s disease'); ?></li>
      <li><?php echo t('Describe the relationship between dementia and Alzheimer\'s disease'); ?></li>
    </ul>
    <p style="font-size: small;"> 
    	<i>
      	<?php 
			echo t('Data and research have been developed or collected for this tutorial
					course by the following:'); 
		?>
    	</i> 
    </p>
    <ul id="developers">
      <li> <a href="http://www.alz.org" target="_blank">Alzheimer's Association</a> <?php echo t('(USA)'); ?> </li>
      <li> <a href="http://matherlifewaysinstituteonaging.com" target="_blank">Mather
        LifeWays Institute on Aging</a> <?php echo t('(USA)'); ?> </li>
      <li> <a href="http://nih.gov" target="_blank">United States
        Department of Health &amp; Human Services - National Institute on
        Aging</a> </li>
    </ul>
    <p class="course-buttons"> <a href="javascript:;" class="button right"
				onclick="$.fancybox.next();"><?php echo t('Start Tutorial &raquo;'); ?> </a> </p>
  </div>
  
  <!--   Slide #2 Alzheimer's disease    -->
  
  <div id="slide-2" class="slide">
    <h2 class="flowers"> <?php echo t('Alzheimer\'s disease'); ?> </h2>
    <hr />
    <p> <?php echo t('Alzheimer\'s disease is an irreversible, progressive brain disease that slowly destroys memory, thinking skills, behavior, and
					eventually even the ability to carry out the simplest tasks of daily living. Symptoms usually develop slowly, worsen over time, and first appear after age 60. Alzheimer\'s disease is the most common form and cause of dementia among older people. Alzheimer\'s has no current cure. The disease is named after Dr. Alois Alzheimer.'); ?> </p>
    <ul>
      <li class="pull-quote"><?php echo t('Millions of Americans are living with Alzheimer\'s disease.'); ?> </li>
      <li class="pull-quote"><?php echo t('Alzheimer\'s is not a normal part of aging'); ?> </li>
    </ul>
    <p> <b><?php echo t('What to look for if you suspect someone is suffering from Alzheimer\'s disease:'); ?></b> </p>
    <ul>
      <li><?php echo t('Challenges in planning or solving problems'); ?></li>
      <li><?php echo t('Difficulty completing familiar tasks at home, at work or at leisure'); ?> </li>
    </ul>
    <p> <b><?php echo t('Alzheimer\'s disease Facts and Figures 2012 (USA / English)'); ?></b> </p>
    <p>
      <iframe width="560" height="315" src="http://www.youtube.com/embed/In1IJocVor8?rel=0" 
                frameborder="1" allowfullscreen class="tutorial-video"></iframe>
    </p>
    <p class="course-buttons"> <a href="javascript:;" class="button left" onclick="$.fancybox.prev();">&laquo;&nbsp;<?php echo t('Back'); ?> </a> <a href="javascript:;" class="button right" onclick="$.fancybox.next();"><?php echo t('Next'); ?>&nbsp;&raquo; </a> </p>
  </div>
  
  <!--  Slide #3 dementia   -->
  
  <div id="slide-3" class="slide">
    <h2 class="flowers"> <?php echo t('Dementia'); ?> </h2>
    <hr />
    <p> <?php echo t('Dementia refers to an acquired and progressive loss of mental functions due to a brain disorder. Dementia is not a specific
			disease. It is an overall term that describes a wide range of symptoms associated with a decline in memory or other thinking skills 
			severe enough to reduce a persons ability to perform everyday activities. A medical diagnosis is required to determine the underlying 			cause or causes of symptoms.'); ?> </p>
    <p> <b><?php echo t('Symptoms and signs of dementia'); ?></b> </p>
    <p> <?php echo t('While symptoms of dementia can vary greatly, at least two of the following core mental functions, amongst others, must be significantly impaired to be
			considered dementia:'); ?> </p>
    <ul>
      <li><?php echo t('Memory'); ?></li>
      <li><?php echo t('Communication and language'); ?></li>
      <li><?php echo t('Ability to focus and pay attention'); ?></li>
    </ul>
    <p> <?php echo t('People with dementia may have problems with short-term memory, keeping track of a purse or wallet, paying bills, planning and
			preparing meals, remembering appointments or traveling out of the neighborhood. Many dementias are progressive, meaning symptoms start
			out slowly and gradually get worse.'); ?> </p>
    <img src="<?php echo $this->getImagesUrl('image-men.png'); ?>" alt="" style="margin: 20px 20px; width: 95%;" />
    <p class="course-buttons"> <a href="javascript:;" class="button left"
				onclick="$.fancybox.prev();">&laquo;&nbsp;<?php echo t('Back'); ?> </a> <a
				href="javascript:;" class="button right"
				onclick="$.fancybox.next();"><?php echo t('Next'); ?>&nbsp;&raquo; </a> </p>
  </div>
  
  <!--  Slide #4 Relationship    -->
  
  <div id="slide-4" class="slide">
    <h2 class="flowers"> <?php echo t('The Relationship'); ?> </h2>
    <hr />
    <p> <b><?php echo t('Dementia and Alzheimer\'s disease - How are they related?'); ?></b> </p>
    <p> <?php echo t('Dementia is defined as the global, progressive deterioration of memory, language, thought, 
				behavior and personality or mood. All or only a combination of these symptoms need to be present for a doctor 
				to someone has dementia. Under the umbrella of dementia are specific types of dementia like Reversible Dementia, 
				Vascular Dementia, Frontal-temporal Dementia and some diseases as well.'); ?> </p>
    <p> <?php echo t('Alzheimer\'s disease, is a disease under the label of dementia. Alzheimer\'s disease
				 is estimated to account for 60-65% of all dementia, while reversible dementia account for 10% (2011, USA statistics).
				 The relationship of dementia vs Alzheimers is not one of opposition. Alzheimer\'s disease is a type of dementia. 
				 If someone has Alzheimers, they also have dementia. But if they have dementia they would have been diagnosed 
				with a particular type of dementia or disease, like Alzheimer\'s disease.'); ?> </p>
    <p> <?php echo t('Alzheimer\'s Disease results in a gradual deterioration of cognitive function over time. For this reason, Alzheimer\'s 
				stages have been designated to refer to the amount of memory loss and function during the disease process.'); ?> </p>
    <p> <b><?php echo t('Statistics (USA)'); ?></b> </p>
    <ul>
      <li><?php echo t('Alzheimer\'s is the sixth-leading cause of death and the only cause of death among the 
				top 10 in the USA that cannot be prevented, cured or even slowed.'); ?> </li>
      <li><?php echo t('Millions of people in the USA have some degree of
							dementia, and that number will increase over the next few decades
							with the aging of the population.'); ?> </li>
      <li><?php echo t('It is the leading reason for placing elderly people in
							institutions such as nursing homes.'); ?> </li>
    </ul>
    <p class="course-buttons"> <a href="javascript:;" class="button left" onclick="$.fancybox.prev();">&laquo;&nbsp;<?php echo t('Back'); ?> </a> <a href="javascript:;" class="button right" onclick="$.fancybox.next();"><?php echo t('Next'); ?>&nbsp;&raquo; </a> </p>
  </div>
  
  <!--   Slide #5 Assessment   -->
  
  <div id="slide-5" class="slide">
    <h2 class="flowers"><?php echo t('Assessment'); ?></h2>
    <hr />
    <p><?php echo t('Thinking about the material you just read, please try and correctly answer the assessment questions below without searching the Internet. Your responses are not recorded, and you will receive immediate feedback.'); ?></p>
    <br />
    <div id="question1" class="question">
      <p><?php echo t('Is dementia a disease of the brain?'); ?>
        <select>
          <option selected="selected" value="select"> <?php echo t('Select'); ?> </option>
          <option value="1"> <?php echo t('Yes'); ?> </option>
          <option value="0"> <?php echo t('No') ?> </option>
        </select>
      </p>
      <p class="answer"></p>
      <?php $clientScript->registerScript('slide-5-question1',
					"$('#slide-5 #question1 select').change(function() {
					if($(this).val() == '1') {
					$('#slide-5 #question1 p.answer').html('Great! Yes, Dementia is a disease of the brain.');
} else {
					$('#slide-5 #question1 p.answer').html('Please review this tutorial again. Dementia is a disease of the brain.');
}
});");
			?>
    </div>
    <div id="question2" class="question">
      <p> <?php echo t('Is Alzehimer\'s disease a disease of the brain?'); ?>
        <select>
          <option selected="selected" value="select"> <?php echo t('Select'); ?> </option>
          <option value="1"> <?php echo t('Yes'); ?> </option>
          <option value="0"> <?php echo t('No') ?> </option>
        </select>
      </p>
      <p class="answer"></p>
      <?php $clientScript->registerScript('slide-5-question2',
					"$('#slide-5 #question2 select').change(function() {
					if($(this).val() == '1') {
					$('#slide-5 #question2 p.answer').html('Great! Yes, Alzheimer\'s disease is a disease of the brain.');
} else {
					$('#slide-5 #question2 p.answer').html('Please review this tutorial again. Alzheimer\'s disease is a disease of the brain.');
}
});");
			?>
    </div>
    <div id="question3" class="question">
      <p> <?php echo t('Alzheimer\'s disease is the most common form of dementia.'); ?>
        <select>
          <option selected="selected" value="select"> <?php echo t('Select'); ?> </option>
          <option value="1"> <?php echo t('True'); ?> </option>
          <option value="0"> <?php echo t('False'); ?> </option>
        </select>
      </p>
      <p class="answer"></p>
      <?php $clientScript->registerScript('slide-5-question3',
					"$('#slide-5 #question3 select').change(function() {
					if($(this).val() == '1') {
					$('#slide-5 #question3 p.answer').html('Yes, Alzheimer\'s disease is the most common form of dementia.');
} else {
					$('#slide-5 #question3 p.answer').html('Please review this tutorial again. Alzheimer\'s disease is the most common form of dementia.');
}
});");
			?>
    </div>
    <br />
    <br />
    <div id="question" class="question">
      <p> <?php echo t('Using the search box below, find additional data and statistics on dementia and 
						Alzheimer\'s disease in your area. It is important that you learn to conduct your own research.'); ?> </p>
      <form method="get" action="http://www.google.com/search" target="_blank">
        <input type="text" id="google-search" name="q" size="65" maxlength="255" value="" />
        <input type="submit" value="<?php echo t('Google Search'); ?>" class="teal" />
      </form>
    </div>
    <br />
    <br />
    <p class="course-buttons"> <a href="javascript:;" class="button left"
				onclick="$.fancybox.prev();">&laquo;&nbsp;<?php echo t('Back'); ?> </a> <a
				href="javascript:;" class="button right"
				onclick="$.fancybox.next();"><?php echo t('Next'); ?>&nbsp;&raquo;</a> </p>
  </div>
  
  <!--   slide 6 - closing here    -->
  
  <div id="slide-6" class="slide">
    <h2 class="flowers"> <?php echo t('Conclusion'); ?> </h2>
    <hr />
    <p> <?php echo t('With such a profound impact on society, business, and potentially on your family, understanding
					dementia and Alzheimer\'s disease is very important. After participating in this tutorial, you
					should now be able to understand and explaing what Alzheimer\'s disease and dementia are.'); ?> </p>
    <p> <?php echo t('Thank you for participating in this tutorial course! Now that you have a better idea on what to expect, your next step is to register and
					begin participating in the various online courses. Please contact us if you have questions or need help.'); ?> </p>
    <p> <b><?php echo t('Certificate of Completion (English)'); ?></b> </p>
    <p> 
    	<a href="/themes/onlinecourseportal/images/CourseCompletionCertificate.pdf" title="<?php echo t('Course Completion Certificate'); ?>" target="_blank"> 
    		<img src="<?php echo $this->getImagesUrl('AcrobatReaderIcon.png'); ?>" alt="<?php echo t('Course Completion Certificate'); ?>" /> 
    	</a> 
    </p>
    <p class="course-buttons"> <a href="#" onclick="parent.jQuery.fancybox.close();"
				class="button left"> <?php echo t('Exit'); ?> </a> </p>
  </div>
</div>