<?php
require_once($CFG->libdir.'/accesslib.php');


class block_report extends block_list {



    public function init() {
        $this->title = get_string('report', 'block_report');

    }

   public function get_content() {
    global $COURSE;

  if ($this->content !== null) {

    return $this->content;

  }



  $this->content         = new stdClass;
  $this->content->items  = array();
  $this->content->icons  = array();

// if(is_siteadmin()){
  $url = new moodle_url('/blocks/report/vistas/tableroadministrador.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id));
  $this->content->items[] = html_writer::tag('a', 'Reportes', array('href' => $url));
  $this->content->icons[] = html_writer::empty_tag('img', array('src'=>'../blocks/report/images/icons/pie-chart.png','class'=>'icon'));
// }

  // Add more list items here



  return $this->content;

}

public function specialization() {
        if (isset($this->config)) {
            if (empty($this->config->title)) {
                $this->title = get_string('defaulttitle', 'block_report');
            } else {
                $this->title = $this->config->title;
            }

            if (empty($this->config->text)) {
                $this->config->text = get_string('defaulttext', 'block_report');
            }
        }
    }


public function instance_allow_multiple() {
  return true;
}

 function has_config () {
    return true;}




}
