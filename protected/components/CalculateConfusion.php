<?php

/**
 * A mixin that causes imports to automatically have their eq calculated.
 */
class CalculateConfusion extends CActiveRecordBehavior {
  
  public function attach($owner) {
    parent::attach($owner);
  }
  
  public function events() {
    return array_merge(parent::events(), array(
      'onAfterImport'=>'afterImport',
    ));
  }
  
  public function afterImport($event) {
    $model = Confusion::model()->findByAttributes(array('compileSessionId'=>$this->owner->id));
    if($model == null) {
      $model = new Confusion;
      $model->compileSessionId = $this->owner->id;
      $model->save();
    }
    $model->calculate();
  }
}