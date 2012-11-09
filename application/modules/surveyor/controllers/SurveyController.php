<?php defined('BASEPATH') or exit('No direct script access allowed');

class SurveyController extends OnlineCoursePortalController {
	
	public function actionSubmit() {
		if(isset($_POST['Survey'])) {
			$errors = array();
			foreach($_POST['Survey'] as $surveyName => $surveyAttributes) {
				$survey = SurveyorModule::surveyor()->$surveyName->form;
				$survey->attributes = $surveyAttributes;
				$survey->save();
				$errors[$surveyName] = $survey->getErrors();
			}
			echo CJSON::encode($errors);
		}
	}
	
	public function actionChart($name, $qNum = 0) {
		$survey = SurveyorModule::surveyor()->$name;
		if($qNum < 0 || $qNum >= count($survey->questions))
			$qNum = 0;
		$this->render('chart', array('survey' => $survey, 'qNum' => $qNum));
	}
	
}