<?php
  include_once $_SERVER['DOCUMENT_ROOT'].'/includes/helpers.inc.php';
  include $_SERVER['DOCUMENT_ROOT'].'/includes/location/index.inc.php';

  include $_SERVER['DOCUMENT_ROOT'].'/includes/db.inc.php';

  $lctn_url=cln($_GET['lctn_url']);

  $sql="SELECT lctn_id FROM lctn WHERE lctn_url='$lctn_url'";
  $result=mysqli_query($link, $sql);
  if(!$result) {$error='Error checking that URL has given valid data: '.mysqli_error($link); include $_SERVER['DOCUMENT_ROOT'].'/includes/error.html.php'; exit();}
  $row=mysqli_fetch_array($result);
  $lctn_id=$row['lctn_id'];

  if(mysqli_num_rows($result)==0)
  {
    include $_SERVER['DOCUMENT_ROOT'].'/includes/404.html.php';
  }
  else
  {
    $rel_lctn_cnt=array();

    $sql= "SELECT lctn_nm, lctn_sffx_num, lctn_url, lctn_exp, lctn_fctn, lctn_est_dt_c, lctn_est_dt_bce, lctn_exp_dt_c, lctn_exp_dt_bce, CASE WHEN lctn_est_dt_frmt=1 THEN DATE_FORMAT(lctn_est_dt, '%d %b %Y') WHEN lctn_est_dt_frmt=2 THEN DATE_FORMAT(lctn_est_dt, '%b %Y') WHEN lctn_est_dt_frmt=3 THEN DATE_FORMAT(lctn_est_dt, '%Y') ELSE NULL END AS lctn_est_dt_frmt, CASE WHEN lctn_exp_dt_frmt=1 THEN DATE_FORMAT(lctn_exp_dt, '%d %b %Y') WHEN lctn_exp_dt_frmt=2 THEN DATE_FORMAT(lctn_exp_dt, '%b %Y') WHEN lctn_exp_dt_frmt=3 THEN DATE_FORMAT(lctn_exp_dt, '%Y') ELSE NULL END AS lctn_exp_dt_frmt
          FROM lctn
          WHERE lctn_id='$lctn_id'";
    $result=mysqli_query($link, $sql);
    if(!$result) {$error='Error acquiring setting (location) data: '.mysqli_error($link); include $_SERVER['DOCUMENT_ROOT'].'/includes/error.html.php'; exit();}
    $row=mysqli_fetch_array($result);
    if($row['lctn_sffx_num']) {$sttng_lctn_sffx_rmn=' ('.romannumeral($row['lctn_sffx_num']).')';} else {$sttng_lctn_sffx_rmn='';}
    $pagetab=html($row['lctn_nm'].$sttng_lctn_sffx_rmn);
    $pagetitle=html($row['lctn_nm']);
    $lctn_nm=html($row['lctn_nm']);
    $lctn_url=html($row['lctn_url']);
    if($row['lctn_exp'] && $row['lctn_fctn']) {$lctn_exp_fctn=' [PRE-EXISTING / FICTIONAL]';}
    elseif($row['lctn_exp'] && !$row['lctn_fctn']) {$lctn_exp_fctn=' [PRE-EXISTING]';}
    elseif(!$row['lctn_exp'] && $row['lctn_fctn']) {$lctn_exp_fctn=' [FICTIONAL]';}
    else {$lctn_exp_fctn='';}
    if($row['lctn_est_dt_frmt'] || $row['lctn_exp_dt_frmt'])
    {
      if($row['lctn_est_dt_c']) {$lctn_est_dt_c='c.';} else {$lctn_est_dt_c='';}
      if($row['lctn_est_dt_bce']) {$lctn_est_dt_bce=' BCE';} else {$lctn_est_dt_bce='';}
      if($row['lctn_est_dt_frmt']) {$lctn_est_dt='from '.$lctn_est_dt_c.html(ltrim($row['lctn_est_dt_frmt'], '0')).$lctn_est_dt_bce;} else {$lctn_est_dt='';}
      if($row['lctn_exp_dt_c']) {$lctn_exp_dt_c='c.';} else {$lctn_exp_dt_c='';}
      if($row['lctn_exp_dt_bce']) {$lctn_exp_dt_bce=' BCE';} else {$lctn_exp_dt_bce='';}
      if($row['lctn_exp_dt_frmt']) {$lctn_exp_dt='until '.$lctn_exp_dt_c.html(ltrim($row['lctn_exp_dt_frmt'], '0')).$lctn_exp_dt_bce;} else {$lctn_exp_dt='';}
      if($row['lctn_est_dt_frmt'] && $row['lctn_exp_dt_frmt']) {$spc=' ';} else {$spc='';}
      $lctn_dt=ucfirst($lctn_est_dt.$spc.$lctn_exp_dt);
      $rel_lctn_cnt[]='1';
    }
    else {$lctn_dt='';}
    if($row['lctn_exp'] || $row['lctn_fctn']) {$exp_fctn_insrt='INNER JOIN lctn ON psl.sttng_lctnid=lctn_id';} else {$exp_fctn_insrt='';}
    if($row['lctn_exp']) {$exp_insrt='AND lctn_exp=1';} else {$exp_insrt='';}
    if($row['lctn_fctn']) {$fctn_insrt='AND lctn_fctn=1';} else {$fctn_insrt='';}

    $lnks=array(); $lnk_cnt=array();

    $sql= "SELECT 1 FROM ptsttng_lctn WHERE sttng_lctnid='$lctn_id'
          UNION
          SELECT 1 FROM rel_lctn INNER JOIN ptsttng_lctn ON rel_lctn1=sttng_lctnid WHERE rel_lctn2='$lctn_id'
          LIMIT 1";
    $result=mysqli_query($link, $sql);
    if(!$result) {$error='Error checking for existence of location as setting (location) for playtext: '.mysqli_error($link); include $_SERVER['DOCUMENT_ROOT'].'/includes/error.html.php'; exit();}
    if(mysqli_num_rows($result)>0) {$lnks[]='<a href="/playtext/setting/location/'.$lctn_url.'">Playtexts</a> with '.$lctn_nm.' as setting'; $lnk_cnt[]='1';}

    $sql= "SELECT 1 FROM thtr WHERE thtr_lctnid='$lctn_id'
          UNION
          SELECT 1 FROM rel_lctn INNER JOIN thtr ON rel_lctn1=thtr_lctnid WHERE rel_lctn2='$lctn_id' AND EXISTS(SELECT 1 FROM lctn WHERE lctn_id='$lctn_id' AND lctn_fctn=0)
          LIMIT 1";
    $result=mysqli_query($link, $sql);
    if(!$result) {$error='Error checking for existence of location as theatre location: '.mysqli_error($link); include $_SERVER['DOCUMENT_ROOT'].'/includes/error.html.php'; exit();}
    if(mysqli_num_rows($result)>0) {$thtr_lnk='1'; $lnk_cnt[]='1';} else {$thtr_lnk=NULL;}

    $sql= "SELECT 1 FROM comp_lctn WHERE comp_lctnid='$lctn_id'
          UNION
          SELECT 1 FROM rel_lctn INNER JOIN comp_lctn ON rel_lctn1=comp_lctnid WHERE rel_lctn2='$lctn_id' AND EXISTS(SELECT 1 FROM lctn WHERE lctn_id='$lctn_id' AND lctn_fctn=0)
          LIMIT 1";
    $result=mysqli_query($link, $sql);
    if(!$result) {$error='Error checking for existence of location as company location: '.mysqli_error($link); include $_SERVER['DOCUMENT_ROOT'].'/includes/error.html.php'; exit();}
    if(mysqli_num_rows($result)>0) {$comp_lnk='1'; $lnk_cnt[]='1';} else {$comp_lnk=NULL;}

    if($thtr_lnk && $comp_lnk) {$lnks[]='<a href="/theatre/location/'.$lctn_url.'">Theatres</a> and <a href="/company/location/'.$lctn_url.'">companies</a> located in '.$lctn_nm;}
    elseif($thtr_lnk && !$comp_lnk) {$lnks[]='<a href="/theatre/location/'.$lctn_url.'">Theatres</a> located in '.$lctn_nm;}
    elseif(!$thtr_lnk && $comp_lnk) {$lnks[]='<a href="/company/location/'.$lctn_url.'">Companies</a> located in '.$lctn_nm;}

    $sql= "SELECT 1 FROM prsn WHERE org_lctnid='$lctn_id'
          UNION
          SELECT 1 FROM rel_lctn INNER JOIN prsn ON rel_lctn1=org_lctnid WHERE rel_lctn2='$lctn_id' AND EXISTS(SELECT 1 FROM lctn WHERE lctn_id='$lctn_id' AND lctn_fctn=0)
          LIMIT 1";
    $result=mysqli_query($link, $sql);
    if(!$result) {$error='Error checking for existence of location as place of origin for person: '.mysqli_error($link); include $_SERVER['DOCUMENT_ROOT'].'/includes/error.html.php'; exit();}
    if(mysqli_num_rows($result)>0) {$prsn_lnk='1'; $lnk_cnt[]='1';} else {$prsn_lnk=NULL;}

    $sql= "SELECT 1 FROM charorg_lctn WHERE org_lctnid='$lctn_id'
          UNION
          SELECT 1 FROM rel_lctn INNER JOIN charorg_lctn ON rel_lctn1=org_lctnid WHERE rel_lctn2='$lctn_id'
          LIMIT 1";
    $result=mysqli_query($link, $sql);
    if(!$result) {$error='Error checking for existence of location as place of origin for character: '.mysqli_error($link); include $_SERVER['DOCUMENT_ROOT'].'/includes/error.html.php'; exit();}
    if(mysqli_num_rows($result)>0) {$char_lnk='1'; $lnk_cnt[]='1';} else {$char_lnk=NULL;}

    if($prsn_lnk && $char_lnk) {$lnks[]='<a href="/person/origin/'.$lctn_url.'">People</a> and <a href="/character/origin/'.$lctn_url.'">characters</a> with '.html($lctn_nm).' as place of origin';}
    elseif($prsn_lnk && !$char_lnk) {$lnks[]='<a href="/person/origin/'.$lctn_url.'">People</a> with '.$lctn_nm.' as place of origin';}
    elseif(!$prsn_lnk && $char_lnk) {$lnks[]='<a href="/character/origin/'.$lctn_url.'">Characters</a> with '.$lctn_nm.' as place of origin';}

    if(!empty($lnks)) {$rel_lctn_cnt[]='1';}

    $sql= "SELECT lctn_nm, lctn_url, rel_lctn_nt1, rel_lctn_nt2, lctn_exp, lctn_fctn
          FROM rel_lctn
          INNER JOIN lctn ON rel_lctn2=lctn_id
          WHERE rel_lctn1='$lctn_id' AND (EXISTS(SELECT 1 FROM prdsttng_lctn WHERE sttng_lctnid='$lctn_id') OR EXISTS(SELECT 1 FROM rel_lctn INNER JOIN prdsttng_lctn ON rel_lctn1=sttng_lctnid WHERE rel_lctn2='$lctn_id'))
          ORDER BY rel_lctn_ordr ASC";
    $result=mysqli_query($link, $sql);
    if(!$result) {$error='Error acquiring related location (part of) data: '.mysqli_error($link); include $_SERVER['DOCUMENT_ROOT'].'/includes/error.html.php'; exit();}
    while($row=mysqli_fetch_array($result))
    {
      if($row['rel_lctn_nt1']) {$rel_lctn_nt1=html($row['rel_lctn_nt1']).' ';} else {$rel_lctn_nt1='';}
      if($row['rel_lctn_nt2']) {if(!preg_match('/^(:|;|,|\.)/', $row['rel_lctn_nt2'])) {$rel_lctn_nt2=' '.html($row['rel_lctn_nt2']);} else {$rel_lctn_nt2=html($row['rel_lctn_nt2']);}}
      else {$rel_lctn_nt2='';}
      $rel_lctn_nm=$rel_lctn_nt1.'<a href="/production/setting/location/'.html($row['lctn_url']).'">'.html($row['lctn_nm']).'</a>'.$rel_lctn_nt2;
      if(!$row['lctn_exp'] && !$row['lctn_fctn']) {$rel_lctns2[]=$rel_lctn_nm;}
      elseif(!$row['lctn_fctn']) {$rel_lctns2_exp[]=$rel_lctn_nm;}
      else {$rel_lctns2_fctn[]=$rel_lctn_nm;}
      $rel_lctn_cnt[]='1';
    }

    $sql= "SELECT lctn_nm, COALESCE(lctn_alph, lctn_nm)lctn_alph, lctn_sffx_num, lctn_url, rel_lctn_nt1, rel_lctn_nt2, lctn_exp, lctn_fctn
          FROM rel_lctn
          INNER JOIN prdsttng_lctn ON rel_lctn1=sttng_lctnid INNER JOIN lctn ON sttng_lctnid=lctn_id
          WHERE rel_lctn2='$lctn_id'
          UNION
          SELECT lctn_nm, COALESCE(lctn_alph, lctn_nm)lctn_alph, lctn_sffx_num, lctn_url, rl3.rel_lctn_nt1, rl3.rel_lctn_nt2, lctn_exp, lctn_fctn
          FROM rel_lctn rl1
          INNER JOIN prdsttng_lctn ON rl1.rel_lctn1=sttng_lctnid INNER JOIN rel_lctn rl2 ON sttng_lctnid=rl2.rel_lctn1 INNER JOIN lctn ON rl2.rel_lctn2=lctn_id
          LEFT OUTER JOIN rel_lctn rl3 ON lctn_id=rl3.rel_lctn1 AND '$lctn_id'=rl3.rel_lctn2
          WHERE rl1.rel_lctn2='$lctn_id' AND lctn_id!=rl1.rel_lctn2 AND lctn_id IN(SELECT rel_lctn1 FROM rel_lctn WHERE rel_lctn2='$lctn_id')
          ORDER BY lctn_alph ASC, lctn_sffx_num ASC";
    $result=mysqli_query($link, $sql);
    if(!$result) {$error='Error acquiring related location (comprised of) data: '.mysqli_error($link); include $_SERVER['DOCUMENT_ROOT'].'/includes/error.html.php'; exit();}
    while($row=mysqli_fetch_array($result))
    {
      if($row['rel_lctn_nt1']) {$rel_lctn_nt1=html($row['rel_lctn_nt1']).' ';} else {$rel_lctn_nt1='';}
      if($row['rel_lctn_nt2']) {if(preg_match('/^(:|;|,|\.)/', $row['rel_lctn_nt2'])) {$rel_lctn_nt2=html($row['rel_lctn_nt2']);} else {$rel_lctn_nt2=' '.html($row['rel_lctn_nt2']);}}
      else {$rel_lctn_nt2='';}
      if($row['rel_lctn_nt1'] || $row['rel_lctn_nt2']) {$rel_lctn_nt=' ('.$rel_lctn_nt1.$lctn_nm.$rel_lctn_nt2.')';} else {$rel_lctn_nt='';}
      $rel_lctn_nm='<a href="/production/setting/location/'.html($row['lctn_url']).'">'.html($row['lctn_nm']).'</a>'.$rel_lctn_nt;
      if(!$row['lctn_exp'] && !$row['lctn_fctn']) {$rel_lctns1[]=$rel_lctn_nm;}
      elseif(!$row['lctn_fctn']) {$rel_lctns1_exp[]=$rel_lctn_nm;}
      else {$rel_lctns1_fctn[]=$rel_lctn_nm;}
      $rel_lctn_cnt[]='1';
    }

    $sql= "SELECT lctn_nm, lctn_url, COALESCE(lctn_alph, lctn_nm)lctn_alph, lctn_fctn, lctn_est_dt_c, lctn_est_dt_bce, lctn_exp_dt_c, lctn_exp_dt_bce, lctn_prvs_sg, lctn_sbsq_sg, CASE WHEN lctn_est_dt_frmt=1 THEN DATE_FORMAT(lctn_est_dt, '%d %b %Y') WHEN lctn_est_dt_frmt=2 THEN DATE_FORMAT(lctn_est_dt, '%b %Y') WHEN lctn_est_dt_frmt=3 THEN DATE_FORMAT(lctn_est_dt, '%Y') ELSE NULL END AS lctn_est_dt_frmt, CASE WHEN lctn_exp_dt_frmt=1 THEN DATE_FORMAT(lctn_exp_dt, '%d %b %Y') WHEN lctn_exp_dt_frmt=2 THEN DATE_FORMAT(lctn_exp_dt, '%b %Y') WHEN lctn_exp_dt_frmt=3 THEN DATE_FORMAT(lctn_exp_dt, '%Y') ELSE NULL END AS lctn_exp_dt_frmt
          FROM lctn_aka
          INNER JOIN lctn ON lctn_sbsq_id=lctn_id
          WHERE lctn_prvs_id='$lctn_id' AND EXISTS(SELECT 1 FROM prdsttng_lctn WHERE lctn_id=sttng_lctnid UNION SELECT 1 FROM rel_lctn INNER JOIN prdsttng_lctn ON rel_lctn1=sttng_lctnid WHERE rel_lctn2=lctn_id)
          ORDER BY lctn_est_dt DESC, lctn_exp_dt DESC, lctn_alph ASC, lctn_sffx_num ASC";
    $result=mysqli_query($link, $sql);
    if(!$result) {$error='Error acquiring subsequent location data: '.mysqli_error($link); include $_SERVER['DOCUMENT_ROOT'].'/includes/error.html.php'; exit();}
    while($row=mysqli_fetch_array($result))
    {
      if($row['lctn_est_dt_frmt'] || $row['lctn_exp_dt_frmt'])
      {
        if($row['lctn_est_dt_c']) {$sbsq_lctn_est_dt_c='c.';} else {$sbsq_lctn_est_dt_c='';}
        if($row['lctn_est_dt_bce']) {$sbsq_lctn_est_dt_bce=' BCE';} else {$sbsq_lctn_est_dt_bce='';}
        if($row['lctn_est_dt_frmt']) {$sbsq_lctn_est_dt='from '.$sbsq_lctn_est_dt_c.html(ltrim($row['lctn_est_dt_frmt'], '0')).$sbsq_lctn_est_dt_bce;} else {$sbsq_lctn_est_dt='';}
        if($row['lctn_exp_dt_c']) {$sbsq_lctn_exp_dt_c='c.';} else {$sbsq_lctn_exp_dt_c='';}
        if($row['lctn_exp_dt_bce']) {$sbsq_lctn_exp_dt_bce=' BCE';} else {$sbsq_lctn_exp_dt_bce='';}
        if($row['lctn_exp_dt_frmt']) {$sbsq_lctn_exp_dt='until '.$sbsq_lctn_exp_dt_c.html(ltrim($row['lctn_exp_dt_frmt'], '0')).$sbsq_lctn_exp_dt_bce;} else {$sbsq_lctn_exp_dt='';}
        if($row['lctn_est_dt_frmt'] && $row['lctn_exp_dt_frmt']) {$sbsq_spc=' ';} else {$sbsq_spc='';}
        $sbsq_lctn_dt=' <em>('.$sbsq_lctn_est_dt.$sbsq_spc.$sbsq_lctn_exp_dt.')</em>';
      }
      else {$sbsq_lctn_dt='';}
      $sbsq_lctn_nm='<a href="/production/setting/location/'.html($row['lctn_url']).'">'.html($row['lctn_nm']).'</a>'.$sbsq_lctn_dt;
      if(!$row['lctn_fctn']) {if(!$row['lctn_prvs_sg'] && !$row['lctn_sbsq_sg']) {$sbsq_lctns[]=$sbsq_lctn_nm;} elseif($row['lctn_prvs_sg']) {$sbsq_lctns_prt_of[]=$sbsq_lctn_nm;} else {$sbsq_lctns_cmprs[]=$sbsq_lctn_nm;}}
      else {if(!$row['lctn_prvs_sg'] && !$row['lctn_sbsq_sg']) {$sbsq_lctns_fctn[]=$sbsq_lctn_nm;} elseif($row['lctn_prvs_sg']) {$sbsq_lctns_fctn_prt_of[]=$sbsq_lctn_nm;} else {$sbsq_lctns_fctn_cmprs[]=$sbsq_lctn_nm;}}
      $rel_lctn_cnt[]='1';
    }

    $sql= "SELECT lctn_nm, lctn_url, COALESCE(lctn_alph, lctn_nm)lctn_alph, lctn_sffx_num, lctn_fctn, lctn_est_dt_c, lctn_est_dt_bce, lctn_exp_dt_c, lctn_exp_dt_bce, lctn_prvs_sg, lctn_sbsq_sg, CASE WHEN lctn_est_dt_frmt=1 THEN DATE_FORMAT(lctn_est_dt, '%d %b %Y') WHEN lctn_est_dt_frmt=2 THEN DATE_FORMAT(lctn_est_dt, '%b %Y') WHEN lctn_est_dt_frmt=3 THEN DATE_FORMAT(lctn_est_dt, '%Y') ELSE NULL END AS lctn_est_dt_frmt, CASE WHEN lctn_exp_dt_frmt=1 THEN DATE_FORMAT(lctn_exp_dt, '%d %b %Y') WHEN lctn_exp_dt_frmt=2 THEN DATE_FORMAT(lctn_exp_dt, '%b %Y') WHEN lctn_exp_dt_frmt=3 THEN DATE_FORMAT(lctn_exp_dt, '%Y') ELSE NULL END AS lctn_exp_dt_frmt
          FROM lctn_aka
          INNER JOIN lctn ON lctn_prvs_id=lctn_id
          WHERE lctn_sbsq_id='$lctn_id' AND EXISTS(SELECT 1 FROM prdsttng_lctn WHERE lctn_id=sttng_lctnid UNION SELECT 1 FROM rel_lctn INNER JOIN prdsttng_lctn ON rel_lctn1=sttng_lctnid WHERE rel_lctn2=lctn_id)
          ORDER BY lctn_est_dt DESC, lctn_exp_dt DESC, lctn_alph ASC, lctn_sffx_num ASC";
    $result=mysqli_query($link, $sql);
    if(!$result) {$error='Error acquiring previous location data: '.mysqli_error($link); include $_SERVER['DOCUMENT_ROOT'].'/includes/error.html.php'; exit();}
    while($row=mysqli_fetch_array($result))
    {
      if($row['lctn_est_dt_frmt'] || $row['lctn_exp_dt_frmt'])
      {
        if($row['lctn_est_dt_c']) {$prvs_lctn_est_dt_c='c.';} else {$prvs_lctn_est_dt_c='';}
        if($row['lctn_est_dt_bce']) {$prvs_lctn_est_dt_bce=' BCE';} else {$prvs_lctn_est_dt_bce='';}
        if($row['lctn_est_dt_frmt']) {$prvs_lctn_est_dt='from '.$prvs_lctn_est_dt_c.html(ltrim($row['lctn_est_dt_frmt'], '0')).$prvs_lctn_est_dt_bce;} else {$prvs_lctn_est_dt='';}
        if($row['lctn_exp_dt_c']) {$prvs_lctn_exp_dt_c='c.';} else {$prvs_lctn_exp_dt_c='';}
        if($row['lctn_exp_dt_bce']) {$prvs_lctn_exp_dt_bce=' BCE';} else {$prvs_lctn_exp_dt_bce='';}
        if($row['lctn_exp_dt_frmt']) {$prvs_lctn_exp_dt='until '.$prvs_lctn_exp_dt_c.html(ltrim($row['lctn_exp_dt_frmt'], '0')).$prvs_lctn_exp_dt_bce;} else {$prvs_lctn_exp_dt='';}
        if($row['lctn_est_dt_frmt'] && $row['lctn_exp_dt_frmt']) {$prvs_spc=' ';} else {$prvs_spc='';}
        $prvs_lctn_dt=' <em>('.$prvs_lctn_est_dt.$prvs_spc.$prvs_lctn_exp_dt.')</em>';
      }
      else {$prvs_lctn_dt='';}
      $prvs_lctn_nm='<a href="/production/setting/location/'.html($row['lctn_url']).'">'.html($row['lctn_nm']).'</a>'.$prvs_lctn_dt;
      if(!$row['lctn_fctn']) {if(!$row['lctn_prvs_sg'] && !$row['lctn_sbsq_sg']) {$prvs_lctns[]=$prvs_lctn_nm;} elseif($row['lctn_sbsq_sg']) {$prvs_lctns_prt_of[]=$prvs_lctn_nm;} else {$prvs_lctns_cmprs[]=$prvs_lctn_nm;}}
      else {if(!$row['lctn_prvs_sg'] && !$row['lctn_sbsq_sg']) {$prvs_lctns_fctn[]=$prvs_lctn_nm;} elseif($row['lctn_sbsq_sg']) {$prvs_lctns_fctn_prt_of[]=$prvs_lctn_nm;} else {$prvs_lctns_fctn_cmprs[]=$prvs_lctn_nm;}}
      $rel_lctn_cnt[]='1';
    }

    $sql= "SELECT p2.prd_id, p2.prd_nm, p2.prd_url, DATE_FORMAT(p2.prd_frst_dt, '%d %b %Y') AS prd_frst_dt_dsply, DATE_FORMAT(p2.prd_lst_dt, '%d %b %Y') AS prd_lst_dt_dsply, p2.prd_dts_info, p2.prd_tbc_nt, thtr_fll_nm, p2.prd_frst_dt, COALESCE(p2.prd_alph, p2.prd_nm)prd_alph, (SELECT COUNT(*) FROM prd WHERE coll_ov=p2.prd_id) AS sg_cnt
          FROM prdsttng_lctn
          INNER JOIN prd p1 ON prdid=p1.prd_id INNER JOIN prd p2 ON p1.coll_ov=p2.prd_id INNER JOIN thtr ON p2.thtrid=thtr_id
          WHERE sttng_lctnid='$lctn_id'
          GROUP BY prd_id
          UNION
          SELECT p2.prd_id, p2.prd_nm, p2.prd_url, DATE_FORMAT(p2.prd_frst_dt, '%d %b %Y') AS prd_frst_dt_dsply, DATE_FORMAT(p2.prd_lst_dt, '%d %b %Y') AS prd_lst_dt_dsply, p2.prd_dts_info, p2.prd_tbc_nt, thtr_fll_nm, p2.prd_frst_dt, COALESCE(p2.prd_alph, p2.prd_nm)prd_alph, (SELECT COUNT(*) FROM prd WHERE coll_ov=p2.prd_id) AS sg_cnt
          FROM rel_lctn
          INNER JOIN prdsttng_lctn psl ON rel_lctn1=sttng_lctnid $exp_fctn_insrt
          INNER JOIN prd p1 ON psl.prdid=p1.prd_id INNER JOIN prd p2 ON p1.coll_ov=p2.prd_id INNER JOIN thtr ON p2.thtrid=thtr_id
          LEFT OUTER JOIN prdsttng_lctn_alt psla ON psl.prdid=psla.prdid AND psl.sttngid=psla.sttngid AND psl.sttng_lctnid=psla.sttng_lctnid
          WHERE rel_lctn2='$lctn_id' AND psla.prdid IS NULL $exp_insrt $fctn_insrt
          GROUP BY prd_id
          UNION
          SELECT p2.prd_id, p2.prd_nm, p2.prd_url, DATE_FORMAT(p2.prd_frst_dt, '%d %b %Y') AS prd_frst_dt_dsply, DATE_FORMAT(p2.prd_lst_dt, '%d %b %Y') AS prd_lst_dt_dsply, p2.prd_dts_info, p2.prd_tbc_nt, thtr_fll_nm, p2.prd_frst_dt, COALESCE(p2.prd_alph, p2.prd_nm)prd_alph, (SELECT COUNT(*) FROM prd WHERE coll_ov=p2.prd_id) AS sg_cnt
          FROM prdsttng_lctn_alt
          INNER JOIN prd p1 ON prdid=p1.prd_id INNER JOIN prd p2 ON p1.coll_ov=p2.prd_id INNER JOIN thtr ON p2.thtrid=thtr_id
          WHERE sttng_lctn_altid='$lctn_id'
          GROUP BY prd_id
          UNION
          SELECT prd_id, prd_nm, prd_url, DATE_FORMAT(prd_frst_dt, '%d %b %Y') AS prd_frst_dt_dsply, DATE_FORMAT(prd_lst_dt, '%d %b %Y') AS prd_lst_dt_dsply, prd_dts_info, prd_tbc_nt, thtr_fll_nm, prd_frst_dt, COALESCE(prd_alph, prd_nm)prd_alph, (SELECT COUNT(*) FROM prd WHERE coll_ov=p1.prd_id) AS sg_cnt
          FROM prdsttng_lctn
          INNER JOIN prd p1 ON prdid=prd_id INNER JOIN thtr ON thtrid=thtr_id
          WHERE sttng_lctnid='$lctn_id' AND coll_ov IS NULL
          GROUP BY prd_id
          UNION
          SELECT prd_id, prd_nm, prd_url, DATE_FORMAT(prd_frst_dt, '%d %b %Y') AS prd_frst_dt_dsply, DATE_FORMAT(prd_lst_dt, '%d %b %Y') AS prd_lst_dt_dsply, prd_dts_info, prd_tbc_nt, thtr_fll_nm, prd_frst_dt, COALESCE(prd_alph, prd_nm)prd_alph, (SELECT COUNT(*) FROM prd WHERE coll_ov=p1.prd_id) AS sg_cnt
          FROM rel_lctn
          INNER JOIN prdsttng_lctn psl ON rel_lctn1=sttng_lctnid $exp_fctn_insrt INNER JOIN prd p1 ON psl.prdid=prd_id
          INNER JOIN thtr ON thtrid=thtr_id
          LEFT OUTER JOIN prdsttng_lctn_alt psla ON psl.prdid=psla.prdid AND psl.sttngid=psla.sttngid AND psl.sttng_lctnid=psla.sttng_lctnid
          WHERE rel_lctn2='$lctn_id' AND coll_ov IS NULL AND psla.prdid IS NULL $exp_insrt $fctn_insrt
          GROUP BY prd_id
          UNION
          SELECT prd_id, prd_nm, prd_url, DATE_FORMAT(prd_frst_dt, '%d %b %Y') AS prd_frst_dt_dsply, DATE_FORMAT(prd_lst_dt, '%d %b %Y') AS prd_lst_dt_dsply, prd_dts_info, prd_tbc_nt, thtr_fll_nm, prd_frst_dt, COALESCE(prd_alph, prd_nm)prd_alph, (SELECT COUNT(*) FROM prd WHERE coll_ov=p1.prd_id) AS sg_cnt
          FROM prdsttng_lctn_alt
          INNER JOIN prd p1 ON prdid=prd_id INNER JOIN thtr ON thtrid=thtr_id
          WHERE sttng_lctn_altid='$lctn_id' AND coll_ov IS NULL
          GROUP BY prd_id
          ORDER BY prd_frst_dt DESC, prd_alph ASC";
    $result=mysqli_query($link, $sql);
    if(!$result) {$error='Error acquiring productions: '.mysqli_error($link); include $_SERVER['DOCUMENT_ROOT'].'/includes/error.html.php'; exit();}
    if(mysqli_num_rows($result)>0)
    {
      while($row=mysqli_fetch_array($result))
      {
        include $_SERVER['DOCUMENT_ROOT'].'/includes/includes_indexes/prd_rcv.inc.php';
        $prd_ids[]=$row['prd_id'];
        $prds[$row['prd_id']]=array('prd_nm'=>$prd_nm, 'prd_nm_pln'=>html($row['prd_nm']), 'prd_nm_pln'=>html($row['prd_nm']), 'prd_dts'=>$prd_dts, 'thtr'=>$thtr, 'sg_cnt'=>$row['sg_cnt'], 'wri_rls'=>array(), 'lctns'=>array(), 'sg_prds'=>array());
      }

      if(!empty($prd_ids))
      {
        foreach($prd_ids as $prd_id)
        {
          $sql= "SELECT 1 FROM prdsttng_lctn WHERE prdid='$prd_id' AND sttng_lctnid='$lctn_id'
                UNION
                SELECT 1 FROM rel_lctn INNER JOIN prdsttng_lctn ON rel_lctn1=sttng_lctnid WHERE prdid='$prd_id' AND rel_lctn2='$lctn_id'";
          $result=mysqli_query($link, $sql);
          if(!$result) {$error='Error checking for prd_ids directly credited to this location: '.mysqli_error($link); include $_SERVER['DOCUMENT_ROOT'].'/includes/error.html.php'; exit();}
          if(mysqli_num_rows($result)>0)
          {
            include $_SERVER['DOCUMENT_ROOT'].'/includes/includes_indexes/prd_wri_rcv.inc.php';
          }
        }
      }

      $k=0;
      $sql= "SELECT prd_id, sttng_lctn_nt1, lctn_nm, sttng_lctn_nt2, sttngid, sttng_lctn_ordr
            FROM prdsttng_lctn
            INNER JOIN lctn ON sttng_lctnid=lctn_id LEFT OUTER JOIN prd ON prdid=prd_id
            WHERE sttng_lctnid='$lctn_id' AND coll_ov IS NULL
            GROUP BY prd_id, sttng_lctn_nt1, lctn_nm, sttng_lctn_nt2
            UNION
            SELECT prd_id, sttng_lctn_nt1, lctn_nm, sttng_lctn_nt2, psl.sttngid, sttng_lctn_ordr
            FROM rel_lctn
            INNER JOIN lctn ON rel_lctn1=lctn_id INNER JOIN prdsttng_lctn psl ON lctn_id=sttng_lctnid LEFT OUTER JOIN prd ON psl.prdid=prd_id
            LEFT OUTER JOIN prdsttng_lctn_alt psla ON psl.prdid=psla.prdid AND psl.sttngid=psla.sttngid AND psl.sttng_lctnid=psla.sttng_lctnid
            WHERE rel_lctn2='$lctn_id' AND coll_ov IS NULL AND psla.prdid IS NULL $exp_insrt $fctn_insrt
            GROUP BY prd_id, sttng_lctn_nt1, lctn_nm, sttng_lctn_nt2
            UNION
            SELECT prd_id, sttng_lctn_nt1, lctn_nm, sttng_lctn_nt2, psla.sttngid, sttng_lctn_ordr
            FROM prdsttng_lctn_alt psla
            INNER JOIN prdsttng_lctn psl ON psla.prdid=psl.prdid AND psla.sttngid=psl.sttngid AND psla.sttng_lctnid=psl.sttng_lctnid
            INNER JOIN lctn ON psla.sttng_lctnid=lctn_id LEFT OUTER JOIN prd ON psla.prdid=prd_id
            WHERE sttng_lctn_altid='$lctn_id' AND coll_ov IS NULL
            GROUP BY prd_id, sttng_lctn_nt1, lctn_nm, sttng_lctn_nt2
            ORDER BY sttngid ASC, sttng_lctn_ordr ASC";
      $result=mysqli_query($link, $sql);
      if(!$result) {$error='Error acquiring location data for productions: '.mysqli_error($link); include $_SERVER['DOCUMENT_ROOT'].'/includes/error.html.php'; exit();}
      while($row=mysqli_fetch_array($result))
      {
        if($row['sttng_lctn_nt1']) {$sttng_lctn_nt1=html($row['sttng_lctn_nt1']).' ';} else {$sttng_lctn_nt1='';}
        if($row['sttng_lctn_nt2']) {if(!preg_match('/^(:|;|,|\.)/', $row['sttng_lctn_nt2'])) {$sttng_lctn_nt2=' '.html($row['sttng_lctn_nt2']);} else {$sttng_lctn_nt2=html($row['sttng_lctn_nt2']);}}
        else {$sttng_lctn_nt2='';}
        $sttng_lctn=$sttng_lctn_nt1.html($row['lctn_nm']).$sttng_lctn_nt2;
        if($sttng_lctn!==$lctn_nm) {$k++;}
        $prds[$row['prd_id']]['lctns'][]=$sttng_lctn;
      }

      $sql= "SELECT coll_ov, prd_id, prd_nm, prd_url, DATE_FORMAT(prd_frst_dt, '%d %b %Y') AS prd_frst_dt_dsply, DATE_FORMAT(prd_lst_dt, '%d %b %Y') AS prd_lst_dt_dsply, prd_dts_info, prd_tbc_nt, thtr_fll_nm, prd_frst_dt, coll_sbhdrid, coll_ordr
            FROM prdsttng_lctn
            INNER JOIN prd ON prdid=prd_id INNER JOIN thtr ON thtrid=thtr_id
            WHERE sttng_lctnid='$lctn_id' AND coll_ov IS NOT NULL
            GROUP BY coll_ov, prd_id
            UNION
            SELECT coll_ov, prd_id, prd_nm, prd_url, DATE_FORMAT(prd_frst_dt, '%d %b %Y') AS prd_frst_dt_dsply, DATE_FORMAT(prd_lst_dt, '%d %b %Y') AS prd_lst_dt_dsply, prd_dts_info, prd_tbc_nt, thtr_fll_nm, prd_frst_dt, coll_sbhdrid, coll_ordr
            FROM rel_lctn
            INNER JOIN prdsttng_lctn psl ON rel_lctn1=psl.sttng_lctnid $exp_fctn_insrt INNER JOIN prd ON prdid=prd_id
            INNER JOIN thtr ON thtrid=thtr_id
            LEFT OUTER JOIN prdsttng_lctn_alt psla ON psl.prdid=psla.prdid AND psl.sttngid=psla.sttngid AND psl.sttng_lctnid=psla.sttng_lctnid
            WHERE rel_lctn2='$lctn_id' AND psla.prdid IS NULL AND coll_ov IS NOT NULL $exp_insrt $fctn_insrt
            GROUP BY coll_ov, prd_id
            UNION
            SELECT coll_ov, prd_id, prd_nm, prd_url, DATE_FORMAT(prd_frst_dt, '%d %b %Y') AS prd_frst_dt_dsply, DATE_FORMAT(prd_lst_dt, '%d %b %Y') AS prd_lst_dt_dsply, prd_dts_info, prd_tbc_nt, thtr_fll_nm, prd_frst_dt, coll_sbhdrid, coll_ordr
            FROM prdsttng_lctn_alt
            INNER JOIN prd ON prdid=prd_id INNER JOIN thtr ON thtrid=thtr_id
            WHERE sttng_lctn_altid='$lctn_id' AND coll_ov IS NOT NULL
            GROUP BY coll_ov, prd_id
            ORDER BY prd_frst_dt DESC, coll_sbhdrid ASC, coll_ordr ASC";
      $result=mysqli_query($link, $sql);
      if(!$result) {$error='Error acquiring segment production data for productions: '.mysqli_error($link); include $_SERVER['DOCUMENT_ROOT'].'/includes/error.html.php'; exit();}
      if(mysqli_num_rows($result)>0)
      {
        while($row=mysqli_fetch_array($result))
        {
          include $_SERVER['DOCUMENT_ROOT'].'/includes/includes_indexes/prd_rcv.inc.php';
          $sg_prd_ids[]=$row['prd_id'];
          $prds[$row['coll_ov']]['sg_prds'][$row['prd_id']]=array('prd_nm'=>$prd_nm, 'prd_nm_pln'=>html($row['prd_nm']), 'prd_dts'=>$prd_dts, 'thtr'=>$thtr, 'wri_rls'=>array(), 'lctns'=>array());
        }

        if(!empty($sg_prd_ids))
        {
          foreach($sg_prd_ids as $sg_prd_id)
          {
            include $_SERVER['DOCUMENT_ROOT'].'/includes/includes_indexes/sg_prd_wri_rcv.inc.php';
          }
        }

        $sql= "SELECT coll_ov, prd_id, sttng_lctn_nt1, lctn_nm, sttng_lctn_nt2, sttngid, sttng_lctn_ordr
              FROM prdsttng_lctn
              INNER JOIN lctn ON sttng_lctnid=lctn_id INNER JOIN prd ON prdid=prd_id
              WHERE sttng_lctnid='$lctn_id' AND coll_ov IS NOT NULL
              GROUP BY coll_ov, prd_id, sttng_lctn_nt1, lctn_nm, sttng_lctn_nt2
              UNION
              SELECT coll_ov, prd_id, sttng_lctn_nt1, lctn_nm, sttng_lctn_nt2, psl.sttngid, sttng_lctn_ordr
              FROM rel_lctn
              INNER JOIN lctn ON rel_lctn1=lctn_id INNER JOIN prdsttng_lctn psl ON lctn_id=sttng_lctnid INNER JOIN prd ON psl.prdid=prd_id
              LEFT OUTER JOIN prdsttng_lctn_alt psla ON psl.prdid=psla.prdid AND psl.sttngid=psla.sttngid AND psl.sttng_lctnid=psla.sttng_lctnid
              WHERE rel_lctn2='$lctn_id' AND psla.prdid IS NULL AND coll_ov IS NOT NULL $exp_insrt $fctn_insrt
              GROUP BY coll_ov, prd_id, sttng_lctn_nt1, lctn_nm, sttng_lctn_nt2
              UNION
              SELECT coll_ov, prd_id, sttng_lctn_nt1, lctn_nm, sttng_lctn_nt2, psla.sttngid, sttng_lctn_ordr
              FROM prdsttng_lctn_alt psla
              INNER JOIN prdsttng_lctn psl ON psla.prdid=psl.prdid AND psla.sttngid=psl.sttngid AND psla.sttng_lctnid=psl.sttng_lctnid
              INNER JOIN lctn ON psla.sttng_lctnid=lctn_id INNER JOIN prd ON psla.prdid=prd_id
              WHERE sttng_lctn_altid='$lctn_id' AND coll_ov IS NOT NULL
              GROUP BY coll_ov, prd_id, sttng_lctn_nt1, lctn_nm, sttng_lctn_nt2
              ORDER BY sttngid ASC, sttng_lctn_ordr ASC";
        $result=mysqli_query($link, $sql);
        if(!$result) {$error='Error acquiring location data for segment productions: '.mysqli_error($link); include $_SERVER['DOCUMENT_ROOT'].'/includes/error.html.php'; exit();}
        while($row=mysqli_fetch_array($result))
        {
          if($row['sttng_lctn_nt1']) {$sttng_lctn_nt1=html($row['sttng_lctn_nt1']).' ';} else {$sttng_lctn_nt1='';}
          if($row['sttng_lctn_nt2']) {if(preg_match('/^(:|;|,|\.)/', $row['sttng_lctn_nt2'])) {$sttng_lctn_nt2=html($row['sttng_lctn_nt2']);} else {$sttng_lctn_nt2=' '.html($row['sttng_lctn_nt2']);}}
          else {$sttng_lctn_nt2='';}
          $sttng_lctn=$sttng_lctn_nt1.html($row['lctn_nm']).$sttng_lctn_nt2;
          if($sttng_lctn!==$lctn_nm) {$k++;}
          $prds[$row['coll_ov']]['sg_prds'][$row['prd_id']]['lctns'][]=$sttng_lctn;
        }
      }
    }

    $lctn_id=html($lctn_id);
    include 'prod-setting-location.html.php';
  }
?>