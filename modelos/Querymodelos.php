<?php
//Incluímos inicialmente la conexión a la base de datos
require_once('../../../config.php');
Class Consultas
{
    protected $global;
    
    //Implementamos nuestro constructor
    public function __construct()
    {
        global $DB;
        $this->global = $DB;
    }

    //Datos generales
    public function reportegeneral($inicio, $fin, $curso, $vp){

        global $DB, $USER; 
        $query = ""; 
        $query .= " SELECT  @s:=@s + 1 id, ";
        $query .= " rolica.username as encargado, rolica.data as rolicasa, ";
        $query .= " roluser.fullname AS curso, "; 
        $query .= " roluser.id as courseid, ";
        $query .= " COUNT(roluser.username ) AS totalusuarios,  ";
        $query .= " CONCAT(CAST((SUM((IFNULL(compl.num, 0) *100)/act.num) / COUNT(roluser.username)) as DECIMAL(16,0)) ,'%')   ";
        $query .= " as avancetotal,   ";
        $query .= " SUM(if(IFNULL(compl.num, 0) = act.num, 1, 0)) AS usuariosfin,   ";
        $query .= " CONCAT(CAST(SUM(IF(compl.num IS null, (act.num * 100) / act.num,    ";
        $query .= " IF(compl.num < act.num, (compl.num * 100) / act.num, 0) ))   ";
        $query .= " / COUNT(roluser.username) as DECIMAL(16,0)) ,'%') AS porfinusuarios,   ";
        $query .= " SUM(IF(compl.num IS NULL OR compl.num < act.num, 1, 0)) AS usuariosnofin  ";
        $query .= " FROM (select @s:=0) as s, mdl_user u  ";
        $query .= " LEFT JOIN (SELECT distinct c.id, c.fullname, uid.data, c.startdate, u.username, u.id as iduser, ";
        $query .= " cf.shortname, cd.intvalue "; 
        $query .= " FROM mdl_course c   ";
        $query .= " LEFT JOIN mdl_context con on con.instanceid = c.id   ";
        $query .= " LEFT JOIN mdl_role_assignments as asg on asg.contextid = con.id   ";
        $query .= " LEFT JOIN mdl_user as u on asg.userid = u.id   ";
        $query .= " LEFT JOIN mdl_role r on asg.roleid = r.id   ";
        $query .= " LEFT join mdl_customfield_data cd on c.id = cd.instanceid   ";
        $query .= " LEFT join mdl_customfield_field cf on cf.id = cd.fieldid  ";
        $query .= " LEFT JOIN mdl_user_info_data uid ON uid.userid = u.id  ";
        $query .= " LEFT JOIN mdl_user_info_field uif ON uif.id = uid.fieldid  ";
        $query .= " where r.shortname = 'student' ";
        $query .= " ) AS roluser ";
        $query .= " ON u.id = roluser.iduser ";
        $query .= " LEFT JOIN (SELECT distinct u.id, u.username, r.shortname, uid.data FROM mdl_user u  ";
        $query .= " INNER JOIN mdl_role_assignments ra ON ra.userid = u.id ";
        $query .= " INNER JOIN mdl_role r ON ra.roleid = r.id  ";
        $query .= " INNER JOIN mdl_user_info_data uid ON uid.userid = u.id  ";
        $query .= " INNER JOIN mdl_user_info_field uif ON uif.id = uid.fieldid  ";
        $query .= " where r.shortname = 'icasa-completo'  ";
        $query .= " ) AS rolica ";
        $query .= " ON rolica.data = roluser.data ";
        $query .= " LEFT JOIN(Select count(id)as num, course from mdl_course_completion_criteria  ";
        $query .= " GROUP BY course) as act on act.course=roluser.id  ";
        $query .= " LEFT JOIN (Select userid, course, count(id) AS num   ";
        $query .= " FROM mdl_course_completion_crit_compl   ";
        $query .= " GROUP BY course, userid) AS compl   ";
        $query .= " ON compl.userid=roluser.iduser and compl.course=roluser.id   ";
        $query .= " where rolica.username is not null "; 
        $query .= " and roluser.fullname is not null  ";
        $query .= " and roluser.shortname = 'reporte'  ";
        $query .= " and roluser.intvalue = 1 ";
        $query .= " and roluser.fullname like '%$curso%' "; 
        $query .= " and rolica.data like '%$vp%' ";
        $query .= " and roluser.startdate >= $inicio ";
        $query .= " and roluser.startdate <= $fin ";
        $query .= " group by rolicasa ";
        $query .= " order by id ";
        return $DB->get_records_sql($query); 

    }



    public function reportegeneralold($inicio, $fin, $curso )
    {
        global $DB, $USER;
        $query = "";
        $query .=" SELECT  @s:=@s+1 id, c.shortname AS curso, c.id AS courseid, ";
        $query .=" COUNT(usrcourse.userid ) AS totalusuarios, ";
        $query .=" CONCAT(CAST((SUM((IFNULL(compl.num, 0) *100)/act.num) / COUNT(usrcourse.userid)) as DECIMAL(16,0)) ,'%') ";
        $query .=" as avancetotal, ";
        $query .=" SUM(if(IFNULL(compl.num, 0) = act.num, 1, 0)) AS usuariosfin, ";
        $query .=" CONCAT(CAST(SUM(IF(compl.num IS null, (act.num * 100) / act.num,  ";
        $query .=" IF(compl.num < act.num, (compl.num * 100) / act.num, 0) )) ";
        $query .=" / COUNT(usrcourse.userid) as DECIMAL(16,0)) ,'%') AS porfinusuarios, ";
        $query .=" SUM(IF(compl.num IS NULL OR compl.num < act.num, 1, 0)) AS usuariosnofin ";
        $query .=" FROM (SELECT @s:= 0) AS s, mdl_user u ";
        $query .=" INNER JOIN (Select distinct ue.userid, e.courseid,ue.timestart  ";
        $query .=" FROM mdl_user_enrolments ue ";
        $query .=" INNER join mdl_user u on ue.userid = u.id ";
        $query .=" INNER join mdl_enrol e on ue.enrolid=e.id  ";
        $query .=" INNER JOIN mdl_role_assignments as asg on asg.userid = u.id ";
        $query .=" INNER JOIN mdl_context as con on asg.contextid = con.id ";
        $query .=" INNER JOIN mdl_role r on asg.roleid = r.id ";
        $query .=" WHERE r.shortname = 'student' ";
        $query .=" ) AS usrcourse  ";
        $query .=" ON u.id=usrcourse.userid  ";
        $query .=" LEFT JOIN(Select count(id)as num,course from mdl_course_completion_criteria  ";
        $query .=" GROUP BY course) as act on act.course=usrcourse.courseid  ";
        $query .=" LEFT JOIN (Select userid, course, count(id) AS num  ";
        $query .=" FROM mdl_course_completion_crit_compl  ";
        $query .=" GROUP BY course, userid) AS compl  ";
        $query .=" ON compl.userid=u.id AND compl.course=usrcourse.courseid  ";
        $query .=" LEFT JOIN mdl_course c ON usrcourse.courseid = c.id ";
        $query .=" inner join mdl_customfield_data cd on c.id = cd.instanceid ";
        $query .=" inner join mdl_customfield_field cf on cf.id = cd.fieldid ";
        $query .=" where cf.shortname = 'reporte' and cd.intvalue = 1 ";
        $query .=" and c.shortname like '%$curso%' "; 
        $query .=" and c.startdate >= $inicio ";
        $query .=" and c.startdate <= $fin ";
        $query .=" GROUP BY fullname ";
        $query .=" ORDER BY id ASC ";
        $data  = $DB->get_records_sql($query);
        
        return $data;
    }

    public function reportGrafic($inicio, $fin, $curso){
        
        global $DB, $USER;
        $query = "";
        $query .=" SELECT  @s:=@s+1 id, c.shortname AS curso, c.id AS courseid, ";
        $query .=" COUNT(usrcourse.userid ) AS totalusuarios, ";
        $query .=" CAST((SUM((IFNULL(compl.num, 0) *100)/act.num) / COUNT(usrcourse.userid)) as DECIMAL(16,0)) ";
        $query .=" as avancetotal, ";
        $query .=" SUM(if(IFNULL(compl.num, 0) = act.num, 1, 0)) AS usuariosfin, ";
        $query .=" CAST(SUM(IF(compl.num IS null, (act.num * 100) / act.num,  ";
        $query .=" IF(compl.num < act.num, (compl.num * 100) / act.num, 0) )) ";
        $query .=" / COUNT(usrcourse.userid) as DECIMAL(16,0)) AS porfinusuarios, ";
        $query .=" SUM(IF(compl.num IS NULL OR compl.num < act.num, 1, 0)) AS usuariosnofin ";
        $query .=" FROM (SELECT @s:= 0) AS s, mdl_user u ";
        $query .=" INNER JOIN (Select distinct ue.userid, e.courseid,ue.timestart  ";
        $query .=" FROM mdl_user_enrolments ue ";
        $query .=" INNER join mdl_user u on ue.userid = u.id ";
        $query .=" INNER join mdl_enrol e on ue.enrolid=e.id  ";
        $query .=" INNER JOIN mdl_role_assignments as asg on asg.userid = u.id ";
        $query .=" INNER JOIN mdl_context as con on asg.contextid = con.id ";
        $query .=" INNER JOIN mdl_role r on asg.roleid = r.id ";
        $query .=" WHERE r.shortname = 'student' ";
        $query .=" ) AS usrcourse  ";
        $query .=" ON u.id=usrcourse.userid  ";
        $query .=" LEFT JOIN(Select count(id)as num,course from mdl_course_completion_criteria  ";
        $query .=" GROUP BY course) as act on act.course=usrcourse.courseid  ";
        $query .=" LEFT JOIN (Select userid, course, count(id) AS num  ";
        $query .=" FROM mdl_course_completion_crit_compl  ";
        $query .=" GROUP BY course, userid) AS compl  ";
        $query .=" ON compl.userid=u.id AND compl.course=usrcourse.courseid  ";
        $query .=" LEFT JOIN mdl_course c ON usrcourse.courseid = c.id ";
        $query .=" inner join mdl_customfield_data cd on c.id = cd.instanceid ";
        $query .=" inner join mdl_customfield_field cf on cf.id = cd.fieldid ";
        $query .=" where cf.shortname = 'reporte' and cd.intvalue = 1 ";
        $query .=" and c.shortname like '%$curso%' "; 
        $query .=" and c.startdate >= $inicio ";
        $query .=" and c.startdate <= $fin ";
        $query .=" GROUP BY fullname ";
        $query .=" ORDER BY id ASC ";
        $data  = $DB->get_records_sql($query);
        return $data;
    }

    public function reportegeneralTwo($inicio, $fin, $curso, $vp){
        global $DB, $USER; 
        $query = ""; 
        $query .= " SELECT  @s:=@s + 1 id,  ";
        $query .= " rolica.username as encargado, rolica.data as rolicasa,  ";
        $query .= " roluser.shortname AS curso,  ";
        $query .= " roluser.id as courseid,  ";
        $query .= " COUNT(roluser.username ) AS totalusuarios,   ";
        $query .= " CONCAT(CAST((SUM((IFNULL(compl.num, 0) *100)/act.num) / COUNT(roluser.username)) as DECIMAL(16,0)) ,'%')    ";
        $query .= " as avancetotal,    ";
        $query .= " SUM(if(IFNULL(compl.num, 0) = act.num, 1, 0)) AS usuariosfin,    ";
        $query .= " CONCAT(CAST(SUM(IF(compl.num IS null, (act.num * 100) / act.num,     ";
        $query .= " IF(compl.num < act.num, (compl.num * 100) / act.num, 0) ))    ";
        $query .= " / COUNT(roluser.username) as DECIMAL(16,0)) ,'%') AS porfinusuarios,    ";
        $query .= " SUM(IF(compl.num IS NULL OR compl.num < act.num, 1, 0)) AS usuariosnofin   ";
        $query .= " FROM (select @s:=0) as s, mdl_user u   ";
        $query .= " LEFT JOIN (SELECT distinct c.id, c.shortname as curso, uid.data, c.startdate, u.username, u.id as iduser,  ";
        $query .= " cf.shortname, cd.intvalue  ";
        $query .= " FROM mdl_course c    ";
        $query .= " LEFT JOIN mdl_context con on con.instanceid = c.id    ";
        $query .= " LEFT JOIN mdl_role_assignments as asg on asg.contextid = con.id    ";
        $query .= " LEFT JOIN mdl_user as u on asg.userid = u.id    ";
        $query .= " LEFT JOIN mdl_role r on asg.roleid = r.id    ";
        $query .= " LEFT join mdl_customfield_data cd on c.id = cd.instanceid    ";
        $query .= " LEFT join mdl_customfield_field cf on cf.id = cd.fieldid   ";
        $query .= " LEFT JOIN mdl_user_info_data uid ON uid.userid = u.id   ";
        $query .= " LEFT JOIN mdl_user_info_field uif ON uif.id = uid.fieldid   ";
        $query .= " where r.shortname = 'student'  ";
        $query .= " ) AS roluser  ";
        $query .= " ON u.id = roluser.iduser  ";
        $query .= " LEFT JOIN (SELECT distinct u.id, u.username, r.shortname, uid.data FROM mdl_user u   ";
        $query .= " INNER JOIN mdl_role_assignments ra ON ra.userid = u.id  ";
        $query .= " INNER JOIN mdl_role r ON ra.roleid = r.id   ";
        $query .= " INNER JOIN mdl_user_info_data uid ON uid.userid = u.id   ";
        $query .= " INNER JOIN mdl_user_info_field uif ON uif.id = uid.fieldid     ";
        $query .= " ) AS rolica  ";
        $query .= " ON rolica.data = roluser.data  ";
        $query .= " LEFT JOIN(Select count(id)as num, course from mdl_course_completion_criteria   ";
        $query .= " GROUP BY course) as act on act.course=roluser.id   ";
        $query .= " LEFT JOIN (Select userid, course, count(id) AS num    ";
        $query .= " FROM mdl_course_completion_crit_compl    ";
        $query .= " GROUP BY course, userid) AS compl    ";
        $query .= " ON compl.userid=roluser.iduser and compl.course=roluser.id    ";
        $query .= " where roluser.shortname = 'reporte'  ";
        $query .= " and roluser.intvalue = 1 ";
        $query .= " and roluser.shortname like '%$curso%' "; 
        $query .= " and rolica.data like '%$vp%' ";
        $query .= " and roluser.startdate >= $inicio ";
        $query .= " and roluser.startdate <= $fin ";
        $query .= " and rolica.shortname = 'icasa-individual' and rolica.id = $USER->id ";
        $query .= " group by rolicasa ";
        $query .= " order by id  " ;
        return $DB->get_records_sql($query);
    }


    public function reportegeneralTwoOld($inicio, $fin, $curso){
        
        global $DB, $USER; 
        $query = ""; 
        $query .= " SELECT  @s:=@s + 1 id, c.shortname AS curso, c.id AS courseid,   ";
        $query .= " COUNT(usr.username ) AS totalusuarios, ";
        $query .= " CONCAT(CAST((SUM((IFNULL(compl.num, 0) *100)/act.num) / COUNT(usr.username)) as DECIMAL(16,0)) ,'%')  ";
        $query .= " as avancetotal,  ";
        $query .= " SUM(if(IFNULL(compl.num, 0) = act.num, 1, 0)) AS usuariosfin,  ";
        $query .= " CONCAT(CAST(SUM(IF(compl.num IS null, (act.num * 100) / act.num,   ";
        $query .= " IF(compl.num < act.num, (compl.num * 100) / act.num, 0) ))  ";
        $query .= " / COUNT(usr.username) as DECIMAL(16,0)) ,'%') AS porfinusuarios,  ";
        $query .= " SUM(IF(compl.num IS NULL OR compl.num < act.num, 1, 0)) AS usuariosnofin ";
        $query .= " from (select @s:=0) as s, mdl_course c    ";
        $query .= " INNER JOIN mdl_context con on con.instanceid = c.id  ";
        $query .= " INNER JOIN mdl_role_assignments as asg on asg.contextid = con.id  ";
        $query .= " INNER JOIN mdl_user as u on asg.userid = u.id  ";
        $query .= " INNER JOIN mdl_role r on asg.roleid = r.id  ";
        $query .= " inner join mdl_customfield_data cd on c.id = cd.instanceid  ";
        $query .= " inner join mdl_customfield_field cf on cf.id = cd.fieldid  ";
        $query .= " LEFT JOIN (SELECT c.shortname, c.id as course, u.id as iduser,  ";
        $query .= " u.username, r.shortname as fa FROM mdl_course c  ";
        $query .= " INNER JOIN mdl_context con on con.instanceid = c.id  ";
        $query .= " INNER JOIN mdl_role_assignments as asg on asg.contextid = con.id  ";
        $query .= " INNER JOIN mdl_user as u on asg.userid = u.id  ";
        $query .= " INNER JOIN mdl_role r on asg.roleid = r.id  ";
        $query .= " inner join mdl_customfield_data cd on c.id = cd.instanceid  ";
        $query .= " inner join mdl_customfield_field cf on cf.id = cd.fieldid  ";
        $query .= " where r.shortname = 'student'  ";
        $query .= " ) as usr  ";
        $query .= " ON usr.course = c.id ";
        $query .= " LEFT JOIN(Select count(id)as num, course from mdl_course_completion_criteria ";
        $query .= " GROUP BY course) as act on act.course=usr.course ";
        $query .= " LEFT JOIN (Select userid, course, count(id) AS num  ";
        $query .= " FROM mdl_course_completion_crit_compl  ";
        $query .= " GROUP BY course, userid) AS compl  ";
        $query .= " ON compl.userid=usr.iduser and compl.course=usr.course  ";
        $query .= " where  ";
        $query .= " r.shortname = 'editingteacher'  ";
        $query .= " and u.id = $USER->id  ";
        $query .= " and cf.shortname = 'reporte'  ";
        $query .= " and cd.intvalue = 1 ";
        $query .= " and c.shortname like '%$curso%'  ";
        $query .= " and c.startdate >= $inicio ";
        $query .= " and c.startdate <= $fin ";
        $query .= " GROUP BY curso ";
        $query .= " order by c.id ASC ";
        return $DB->get_records_sql($query);
    }
  
    public function getRol($id)
    {
        global $DB;
        $query = "";
        $query .= " SELECT r.shortname as rol FROM mdl_user u  ";
        $query .= " INNER JOIN mdl_role_assignments ra ON ra.userid = u.id ";
        $query .= " INNER JOIN mdl_role r ON ra.roleid = r.id  ";
        $query .= " INNER JOIN mdl_user_info_data uid ON uid.userid = u.id  ";
        $query .= " INNER JOIN mdl_user_info_field uif ON uif.id = uid.fieldid   ";
        $query .= " where  u.id = $id ";
        return $DB->get_records_sql($query);
    }
    
    public function cursos()
    {
        global $DB, $USER;
        $query = "";
        $query .= " SELECT distinct(c.shortname), c.id from mdl_course c ";
        $query .= " inner join mdl_customfield_data cd on c.id = cd.instanceid ";
        $query .= " inner join mdl_customfield_field cf on cf.id = cd.fieldid ";
        $query .= " where c.shortname is not null  and c.id<>1   ";
        $query .= " and cf.shortname = 'reporte' and cd.intvalue = 1  ";
        $query .= " order by shortname asc ";
        return $DB->get_records_sql($query);
    }

    public function cursoTwo(){
        global $DB, $USER; 
        $query = ""; 
        $query .= " SELECT distinct(c.shortname), c.id from mdl_course c   ";
        $query .= " INNER JOIN mdl_context con on con.instanceid = c.id ";
        $query .= " INNER JOIN mdl_role_assignments as asg on asg.contextid = con.id ";
        $query .= " INNER JOIN mdl_user as u on asg.userid = u.id ";
        $query .= " INNER JOIN mdl_role r on asg.roleid = r.id ";
        $query .= " inner join mdl_customfield_data cd on c.id = cd.instanceid ";
        $query .= " inner join mdl_customfield_field cf on cf.id = cd.fieldid ";
        $query .= " where c.shortname is not null and u.id = $USER->id and cf.shortname = 'reporte' and cd.intvalue = 1 ";
        $query .= " and c.id<>1  ";
        $query .= " order by shortname asc  ";
        return $DB->get_records_sql($query);
    }


    public function vp(){
        global $DB, $USER; 

        $query = ""; 
        $query .= " SELECT distinct uid.data from mdl_user_info_data uid  "; 
        $query .= " INNER JOIN mdl_user_info_field uif ON uif.id = uid.fieldid  ";
        $query .= " WHERE  uif.shortname = 'vicepresidencia' ";  
        return $DB->get_records_sql($query);
    }

    public function vpTwo(){
        global $DB, $USER; 
        $query = ""; 
        $query .= " SELECT distinct uid.data FROM mdl_user u   "; 
        $query .= " INNER JOIN mdl_role_assignments ra ON ra.userid = u.id  "; 
        $query .= " INNER JOIN mdl_role r ON ra.roleid = r.id   "; 
        $query .= " INNER JOIN mdl_user_info_data uid ON uid.userid = u.id   "; 
        $query .= " INNER JOIN mdl_user_info_field uif ON uif.id = uid.fieldid "; 
        $query .= " where r.shortname = 'icasa-individual' and u.id = $USER->id "; 
        return $DB->get_records_sql($query);
    }

    public function reportUser($inicio, $fin, $curso, $vp) {
        global $DB, $USER; 
        $query = ""; 
        $query .= " SELECT @s:=@s + 1 id, "; 
        $query .= " vp.data as vp,  ";
        $query .= " emp.data as emp,  ";
        $query .= " c.shortname as curso,   ";
        $query .= " u.firstname,  ";
        $query .= " u.lastname,  ";
        $query .= " u.department,  ";
        $query .= " puest.data as puest,  ";
        $query .= " gen.data as gen,  ";
        $query .= " dpi.data as dpi,  ";
        $query .= " status.data as status  ";
        $query .= " FROM  (select @s:=0) as s, mdl_course c    "; 
        $query .= " LEFT JOIN mdl_context con on con.instanceid = c.id    "; 
        $query .= " LEFT JOIN mdl_role_assignments as asg on asg.contextid = con.id    "; 
        $query .= " LEFT JOIN mdl_user as u on asg.userid = u.id    "; 
        $query .= " LEFT JOIN mdl_role r on asg.roleid = r.id    "; 
        $query .= " LEFT join mdl_customfield_data cd on c.id = cd.instanceid    "; 
        $query .= " LEFT join mdl_customfield_field cf on cf.id = cd.fieldid   "; 
        $query .= " INNER JOIN (SELECT u.id, uid.data FROM mdl_user u  "; 
        $query .= "  INNER JOIN mdl_user_info_data uid ON uid.userid = u.id  "; 
        $query .= "  LEFT JOIN mdl_user_info_field uif ON uif.id = uid.fieldid   "; 
        $query .= "  WHERE uif.shortname = 'vicepresidencia' "; 
        $query .= " ) as vp  "; 
        $query .= " on vp.id = u.id "; 
        $query .= " INNER JOIN (SELECT u.id, uid.data FROM mdl_user u  "; 
        $query .= "  INNER JOIN mdl_user_info_data uid ON uid.userid = u.id  "; 
        $query .= "  LEFT JOIN mdl_user_info_field uif ON uif.id = uid.fieldid   "; 
        $query .= "  WHERE uif.shortname = 'empresa' "; 
        $query .= " ) as emp  "; 
        $query .= " on emp.id = u.id "; 
        $query .= " INNER JOIN (SELECT u.id, uid.data FROM mdl_user u  "; 
        $query .= "  INNER JOIN mdl_user_info_data uid ON uid.userid = u.id  "; 
        $query .= "  LEFT JOIN mdl_user_info_field uif ON uif.id = uid.fieldid   "; 
        $query .= "  WHERE uif.shortname = 'puesto' "; 
        $query .= " ) as puest  "; 
        $query .= " on puest.id = u.id "; 
        $query .= " INNER JOIN (SELECT u.id, uid.data FROM mdl_user u  "; 
        $query .= "  INNER JOIN mdl_user_info_data uid ON uid.userid = u.id  "; 
        $query .= "  LEFT JOIN mdl_user_info_field uif ON uif.id = uid.fieldid   "; 
        $query .= "  WHERE uif.shortname = 'genero' "; 
        $query .= " ) as gen  "; 
        $query .= " on gen.id = u.id "; 
        $query .= " INNER JOIN (SELECT u.id, uid.data FROM mdl_user u  "; 
        $query .= "  INNER JOIN mdl_user_info_data uid ON uid.userid = u.id  "; 
        $query .= "  LEFT JOIN mdl_user_info_field uif ON uif.id = uid.fieldid   "; 
        $query .= "  WHERE uif.shortname = 'dpi' "; 
        $query .= " ) as dpi  "; 
        $query .= " on dpi.id = u.id "; 
        $query .= " INNER JOIN (SELECT u.id, uid.data FROM mdl_user u  "; 
        $query .= "  INNER JOIN mdl_user_info_data uid ON uid.userid = u.id  "; 
        $query .= "  LEFT JOIN mdl_user_info_field uif ON uif.id = uid.fieldid   "; 
        $query .= "  WHERE uif.shortname = 'status' "; 
        $query .= " ) as status  "; 
        $query .= " on status.id = u.id "; 
        $query .= " where r.shortname = 'student'  "; 
        $query .= " and cf.shortname = 'reporte'  ";
        $query .= " and cd.intvalue = 1 ";
        $query .= " and c.shortname like '%$curso%' "; 
        $query .= " and vp.data like '%$vp%' ";
        $query .= " and c.startdate >= $inicio ";
        $query .= " and c.startdate <= $fin ";
        $query .= " order by id "; 
        return $DB->get_records_sql($query);
    }

    public function reportUserTwo($inicio, $fin, $curso, $vp) {
        global $DB, $USER; 
        $query = ""; 
        $query .= " SELECT @s:=@s + 1 id,  ";
        $query .= " vp.data as vp,  ";
        $query .= " emp.data as emp,  ";
        $query .= " c.fullname as curso,   ";
        $query .= " u.firstname,  ";
        $query .= " u.lastname,  ";
        $query .= " u.department,  ";
        $query .= " puest.data as puest,  ";
        $query .= " gen.data as gen,  ";
        $query .= " dpi.data as dpi,  ";
        $query .= " status.data as status  ";
        $query .= " FROM  (select @s:=0) as s, mdl_course c    ";
        $query .= " LEFT JOIN mdl_context con on con.instanceid = c.id    ";
        $query .= " LEFT JOIN mdl_role_assignments as asg on asg.contextid = con.id    ";
        $query .= " LEFT JOIN mdl_user as u on asg.userid = u.id    ";
        $query .= " LEFT JOIN mdl_role r on asg.roleid = r.id    ";
        $query .= " LEFT join mdl_customfield_data cd on c.id = cd.instanceid    ";
        $query .= " LEFT join mdl_customfield_field cf on cf.id = cd.fieldid   ";
        $query .= " INNER JOIN (SELECT uid.data, uid.userid FROM mdl_user_info_data uid ";
        $query .= " LEFT JOIN mdl_user_info_field uif ON uif.id = uid.fieldid     ";
        $query .= "  WHERE uif.shortname = 'vicepresidencia' ";
        $query .= " ) as vp  ";
        $query .= " on vp.userid = u.id ";
        $query .= " INNER JOIN (SELECT uid.data, uid.userid FROM mdl_user_info_data uid ";
        $query .= " LEFT JOIN mdl_user_info_field uif ON uif.id = uid.fieldid     ";
        $query .= "  WHERE uif.shortname = 'empresa' ";
        $query .= " ) as emp  ";
        $query .= " on emp.userid = u.id ";
        $query .= " INNER JOIN (SELECT uid.data, uid.userid FROM mdl_user_info_data uid ";
        $query .= " LEFT JOIN mdl_user_info_field uif ON uif.id = uid.fieldid       ";
        $query .= "  WHERE uif.shortname = 'puesto' ";
        $query .= " ) as puest  ";
        $query .= " on puest.userid = u.id ";
        $query .= " INNER JOIN (SELECT uid.data, uid.userid FROM mdl_user_info_data uid ";
        $query .= " LEFT JOIN mdl_user_info_field uif ON uif.id = uid.fieldid     ";
        $query .= "  WHERE uif.shortname = 'genero' ";
        $query .= " ) as gen  ";
        $query .= " on gen.userid = u.id ";
        $query .= " INNER JOIN (SELECT uid.data, uid.userid FROM mdl_user_info_data uid ";
        $query .= " LEFT JOIN mdl_user_info_field uif ON uif.id = uid.fieldid     ";
        $query .= "  WHERE uif.shortname = 'dpi' ";
        $query .= " ) as dpi  ";
        $query .= " on dpi.userid = u.id ";
        $query .= " INNER JOIN (SELECT uid.data, uid.userid FROM mdl_user_info_data uid ";
        $query .= " LEFT JOIN mdl_user_info_field uif ON uif.id = uid.fieldid     ";
        $query .= "  WHERE uif.shortname = 'status' ";
        $query .= " ) as status  ";
        $query .= " on status.userid = u.id ";
        $query .= " INNER JOIN (SELECT distinct u.id, u.username, r.shortname, uid.data FROM mdl_user u   ";
        $query .= " INNER JOIN mdl_role_assignments ra ON ra.userid = u.id  ";
        $query .= " INNER JOIN mdl_role r ON ra.roleid = r.id   ";
        $query .= " INNER JOIN mdl_user_info_data uid ON uid.userid = u.id   ";
        $query .= " INNER JOIN mdl_user_info_field uif ON uif.id = uid.fieldid     ";
        $query .= " ) AS rolica  ";
        $query .= " ON rolica.data = vp.data  ";
        $query .= " WHERE r.shortname = 'student'  ";
        $query .= " AND rolica.shortname = 'icasa-individual'  ";
        $query .= " AND rolica.id = $USER->id ";
        $query .= " order by id "; 
        return $DB->get_records_sql($query);
    }

}

?>