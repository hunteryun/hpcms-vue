<?php

namespace Hunter\Core\FormApi;

class Element {

    private $form;
    private $form_show_type;
    private $form_id;

    /**
     * start the form
     */
    public function start($action, $id = null, $class = null, $show_type = 'block', $enctype = false, $method = 'post') {
        // <el-form-item label="活动区域">
        //   <el-select v-model="form.region" placeholder="请选择活动区域">
        //     <el-option label="区域一" value="shanghai"></el-option>
        //     <el-option label="区域二" value="beijing"></el-option>
        //   </el-select>
        // </el-form-item>
        // <el-form-item label="活动时间">
        //   <el-col :span="11">
        //     <el-date-picker type="date" placeholder="选择日期" v-model="form.date1" style="width: 100%;"></el-date-picker>
        //   </el-col>
        //   <el-col class="line" :span="2">-</el-col>
        //   <el-col :span="11">
        //     <el-time-picker type="fixed-time" placeholder="选择时间" v-model="form.date2" style="width: 100%;"></el-time-picker>
        //   </el-col>
        // </el-form-item>
        // <el-form-item label="即时配送">
        //   <el-switch v-model="form.delivery"></el-switch>
        // </el-form-item>

        $this->form_show_type = $show_type;
        $this->form_id = $id.'Form';
        $this->form = '<el-form ref="'.$id.'Form" :model="'.$id.'Form" label-width="100px">';
        return $this;
    }

    /**
     * generate a file input
     */
    public function file($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }

        $this->form .= '<div class="form-group">';
        if($this->form_show_type == 'nolabel' || $this->form_show_type == 'inline'){
          $this->form .= '<div class="col-sm-6">
          <div class="input-group">
            <span class="input-group-addon" id="basic-addon1">'.$field['#title'].'</span>
            <input type="text" class="form-control" id="'.$name.'"'.hunter_attributes($field['#attributes']).' aria-describedby="basic-addon1">
          </div>
          </div>
          <div class="col-sm-2" style="padding:0;">
          <a href="javascript:;" class="file btn btn-primary">'.t('选择').'
            <input type="file" name="uploadfile" class="liteupload" multiple="multiple">
          </a>';
        }else {
          $this->form .= '<label class="col-sm-2 control-label">'.$field['#title'].'</label><div class="col-sm-6"><input type="text" class="form-control" id="'.$name.'"'.hunter_attributes($field['#attributes']).'>
          </div>
          <div class="col-sm-2" style="padding:0;">
          <a href="javascript:;" class="file btn btn-primary">'.t('选择').'
            <input type="file" name="uploadfile" class="liteupload" multiple="multiple">
          </a>';
        }

        if(isset($field['#description'])){
          $this->form .= '<p class="help-block">'.$field['#description'].'</p>';
        }

        $this->form .= '</div></div>';

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }

        return $this;
    }

    /**
     * generate a hidden input
     */
    public function hidden($name, $value) {
        $this->form .= '<input type="hidden" name="'.$name.'" value="'.$value.'" />';

        return $this;
    }

    /**
     * generate a new input
     */
    public function input($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }

        if($this->form_show_type == 'nolabel'){
          $field['#attributes']['placeholder'] = $field['#title'];
          $this->form .= '<el-form-item>
            <el-input v-model="'.$this->form_id.'.'.$field['#name'].'"'.hunter_attributes($field['#attributes']).'></el-input>
          </el-form-item>';
        }elseif ($this->form_show_type == 'inline') {
          $this->form .= '<el-form-item>
            <el-input v-model="'.$this->form_id.'.'.$field['#name'].'">
              <template slot="prepend">'.$field['#title'].'</template>
            </el-input>
          </el-form-item>';
        }else {
          $this->form .= '<el-form-item label="'.$field['#title'].'">
            <el-input v-model="'.$this->form_id.'.'.$field['#name'].'"'.hunter_attributes($field['#attributes']).'></el-input>
          </el-form-item>';
        }

        if(isset($field['#description'])){
          $this->form .= '<span class="help-block">'.$field['#description'].'</span>';
        }


        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * generate a textarea
     */
    public function textarea($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }

        if(!isset($field['#attributes']['rows'])){
          $field['#attributes']['rows'] = 6;
        }

        if($this->form_show_type == 'nolabel'){
          $field['#attributes']['placeholder'] = $field['#title'];
          $this->form .= '<el-form-item>
            <el-input type="textarea" v-model="'.$this->form_id.'.'.$field['#name'].'"'.hunter_attributes($field['#attributes']).'></el-input>
          </el-form-item>';
        }elseif ($this->form_show_type == 'inline') {
          $this->form .= '<el-form-item>
            <el-input type="textarea" v-model="'.$this->form_id.'.'.$field['#name'].'">
              <template slot="prepend">'.$field['#title'].'</template>
            </el-input>
          </el-form-item>';
        }else {
          $this->form .= '<el-form-item label="'.$field['#title'].'">
            <el-input type="textarea" v-model="'.$this->form_id.'.'.$field['#name'].'"'.hunter_attributes($field['#attributes']).'></el-input>
          </el-form-item>';
        }

        if(isset($field['#description'])){
          $this->form .= '<span class="help-block">'.$field['#description'].'</span>';
        }

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * generate a image
     */
    public function img($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }
        $this->form .= '
        <div class="form-group">
            <label class="col-sm-2 control-label">'.$field['#title'].'</label>
            <div class="col-sm-10">
              <img src="'.$field['#default_value'].'"'.hunter_attributes($field['#attributes']).'>
            </div>
        </div>';

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * generate a captcha
     */
    public function captcha($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }
        $this->form .= '
        <div class="form-group">
            <label class="col-sm-2 control-label">'.$field['#title'].'</label>
            <div class="col-sm-3">
            <input type="text" name="_captcha" id="'.$name.'" class="form-control">
            </div>
            <div class="col-sm-3">
            <div class="captcha">
            <img class="" src="/captcha/make" onclick="this.src=\'/captcha/make?ver=\'+new Date().getTime()" title="'.t('点击刷新验证码').'">
            </div>
            </div>
        </div>';

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * generate a fieldset
     */
    public function fieldset($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }
        $this->form .= '
        <fieldset class="'.hunter_attributes($field['#attributes']).'" style="margin-top: 20px;">
          <legend>'.$field['#title'].'</legend>
        </fieldset>';

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * generate a image
     */
    public function markup($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }
        if(isset($field['#title'])){
          if(isset($field['#hidden']) && $field['#hidden']){
            $this->form .= '
            <div class="form-group" id="'.$name.'" style="display:none;">';
          }else {
            $this->form .= '
            <div class="form-group">';
          }

          if($this->form_show_type == 'nolabel' || $this->form_show_type == 'inline'){
            $this->form .= '<div class="col-sm-6">'.$field['#markup'].'
            </div></div>';
          }else {
            $this->form .= '<label class="col-sm-2 control-label">'.$field['#title'].'</label>
              '.$field['#markup'].'
            </div>';
          }
        }else {
          $this->form .= $field['#markup'];
        }

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * generate a submit button
     */
    public function submit($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }

        $this->form .= '<el-form-item>
          <el-button type="primary" @click="'.$this->form_id.'Submit"'.hunter_attributes($field['#attributes']).'>'.$field['#value'].'</el-button>
          <el-button>'.t('取消').'</el-button>
        </el-form-item>"';

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * generate a select
     */
    public function select($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }

        $this->form .= '<div class="form-group">';
        if($this->form_show_type == 'nolabel'){
          $this->form .= '<div class="col-sm-6"><select class="form-control"'.hunter_attributes($field['#attributes']).'>';
          $this->form .= ' <option value=""> '.t('请选择').$field['#title'].'</option>';
        }elseif ($this->form_show_type == 'inline') {
          $this->form .= '<div class="col-sm-6"><div class="input-group">
          <span class="input-group-addon" id="basic-addon1">'.$field['#title'].'</span>
          <select class="form-control"'.hunter_attributes($field['#attributes']).' aria-describedby="basic-addon1">';
          $this->form .= ' <option value=""> '.t('请选择').'</option>';
        }else {
          $this->form .= '<label class="col-sm-2 control-label">'.$field['#title'].'</label> <div class="col-sm-10"><select class="form-control"'.hunter_attributes($field['#attributes']).'>';
          $this->form .= ' <option value=""> '.t('请选择').'</option>';
        }

        foreach ($field['#options'] as $v => $title)
        {
          if($v == $field['#value']){
            $this->form .= ' <option value="'.$v.'" selected=""> '.$title.'</option>';
          }else{
            $this->form .= ' <option value="'.$v.'"> '.$title.'</option>';
          }
        }

        $this->form .= '  </select></div>';
        if($this->form_show_type == 'inline'){
          $this->form .= '</div>';
        }
        $this->form .= '</div>';

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * create a checkbox
     */
    public function checkbox($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }

        if($this->form_show_type == 'nolabel' || $this->form_show_type == 'inline'){
          if(!isset($field['#attributes']['hide-label'])){
            $this->form .= ' <el-form-item label="'.$field['#title'].'">';
          }
        }else {
          $this->form .= ' <el-form-item label="'.$field['#title'].'">';
        }

        if(!empty($field['#options'])){
          $this->form .= '<el-checkbox-group v-model="'.$this->form_id.'.'.$field['#name'].'">';
          foreach ($field['#options'] as $v => $title) {
            $field['#attributes']['value'] = $v;
            $field['#attributes']['name'] = $name.'['.$v.']';
            if((is_array($field['#value']) && in_array($v, $field['#value'])) || $field['#value'] == $field['#attributes']['value']){
              $field['#attributes']['checked'] = 'checked';
              $this->form .= '<el-checkbox label="'.$title.'"'.hunter_attributes($field['#attributes']).'></el-checkbox>';
            }else {
              unset($field['#attributes']['checked']);
              $this->form .= '<el-checkbox label="'.$title.'"'.hunter_attributes($field['#attributes']).'></el-checkbox>';
            }
          }
          $this->form .= '</el-checkbox-group>';
        }

        $this->form .= '</el-form-item>';

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * create a radio input
     */
    public function radio($name, $field) {
        if(isset($field['#prefix'])){
          $this->form .= $field['#prefix'];
        }

        if($this->form_show_type == 'nolabel' || $this->form_show_type == 'inline'){
          if(!isset($field['#attributes']['hide-label'])){
            $this->form .= '<el-form-item label="'.$field['#title'].'">';
          }
        }else {
          $this->form .= '<el-form-item label="'.$field['#title'].'">';
        }

        if(!empty($field['#options'])){
          $this->form .= '<el-radio-group v-model="'.$this->form_id.'.'.$field['#name'].'">';
          foreach ($field['#options'] as $v => $title) {
            $field['#attributes']['value'] = $v;
            if($v == $field['#value']){
              $field['#attributes']['checked'] = 'checked';
              $this->form .= '<el-radio label="'.$title.'"'.hunter_attributes($field['#attributes']).'></el-radio>';
            }else {
              unset($field['#attributes']['checked']);
              $this->form .= '<el-radio label="'.$title.'"'.hunter_attributes($field['#attributes']).'></el-radio>';
            }
          }
          $this->form .= '</el-radio-group>';
        }

        $this->form .= '</el-form-item>';

        if(isset($field['#suffix'])){
          $this->form .= $field['#suffix'];
        }
        return $this;
    }

    /**
     * close the form
     */
    public function end($form_id) {
       if(!empty($form_id)){
         $this->form .= '<input type="hidden" name="form_id" value="'.$form_id.'">';
       }

       $this->form .= csrf_field();
       $this->form .= '</form>';

       return $this->form;
    }

}
