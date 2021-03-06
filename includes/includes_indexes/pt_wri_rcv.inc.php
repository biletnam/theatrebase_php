<?php
            $sql="SELECT wri_rl_id, src_mat_rl, wri_rl FROM ptwrirl WHERE ptid='$pt_id' GROUP BY ptid, wri_rl_id ORDER BY wri_rl_id ASC";
            $result=mysqli_query($link, $sql);
            if(!$result) {$error='Error acquiring writer role data for playtexts: '.mysqli_error($link); include $_SERVER['DOCUMENT_ROOT'].'/includes/error.html.php'; exit();}
            while($row=mysqli_fetch_array($result))
            {$pts[$pt_id]['wri_rls'][$row['wri_rl_id']]=array('src_mat_rl'=>html($row['src_mat_rl']), 'wri_rl'=>html($row['wri_rl']), 'src_mats'=>array(), 'wris'=>array());}

            $sql= "SELECT wri_rlid, mat_nm, frmt_nm FROM ptsrc_mat
                INNER JOIN mat ON src_matid=mat_id INNER JOIN frmt ON frmtid=frmt_id WHERE ptid='$pt_id'
                GROUP BY ptid, wri_rlid, mat_id ORDER BY src_mat_ordr ASC";
            $result=mysqli_query($link, $sql);
            if(!$result) {$error='Error acquiring credited source materials for playtexts: '.mysqli_error($link); include $_SERVER['DOCUMENT_ROOT'].'/includes/error.html.php'; exit();}
            while($row=mysqli_fetch_array($result))
            {$pts[$pt_id]['wri_rls'][$row['wri_rlid']]['src_mats'][]=array('src_mat_nm'=>html($row['mat_nm']), 'src_mat_frmt'=>html($row['frmt_nm']));}

            $sql= "SELECT wri_rlid, comp_id, comp_nm, wri_sb_rl, wri_ordr FROM ptwri
                INNER JOIN comp ON wri_compid=comp_id WHERE ptid='$pt_id' AND wri_prsnid=0
                GROUP BY ptid, wri_rlid, comp_id
                UNION
                SELECT wri_rlid, prsn_id, prsn_fll_nm, wri_sb_rl, wri_ordr FROM ptwri
                INNER JOIN prsn ON wri_prsnid=prsn_id WHERE ptid='$pt_id' AND wri_compid=0
                GROUP BY ptid, wri_rlid, prsn_id
                ORDER BY wri_ordr ASC";
            $result=mysqli_query($link, $sql); if(!$result) {$error='Error acquiring credited writers for playtexts: '.mysqli_error($link); include $_SERVER['DOCUMENT_ROOT'].'/includes/error.html.php'; exit();}
            while($row=mysqli_fetch_array($result))
            {
              if($row['wri_sb_rl']) {if(!preg_match('/^(:|;|,|\.)/', $row['wri_sb_rl'])) {$wri_sb_rl=' '.html($row['wri_sb_rl']).' ';} else {$wri_sb_rl=html($row['wri_sb_rl']).' ';}} else {$wri_sb_rl='';}
              $pts[$pt_id]['wri_rls'][$row['wri_rlid']]['wris'][$row['comp_id']]=array('comp_nm'=>html($row['comp_nm']), 'wri_sb_rl'=>$wri_sb_rl, 'wricomp_ppl'=>array());
            }

            $sql= "SELECT wri_rlid, wri_compid, wri_sb_rl, prsn_fll_nm FROM ptwri
                INNER JOIN prsn ON wri_prsnid=prsn_id WHERE ptid='$pt_id' AND wri_compid!=0
                GROUP BY ptid, wri_rlid, wri_compid, prsn_id ORDER BY wri_ordr ASC";
            $result=mysqli_query($link, $sql);
            if(!$result) {$error='Error acquiring writer (company people) data (for playtexts: '.mysqli_error($link); include $_SERVER['DOCUMENT_ROOT'].'/includes/error.html.php'; exit();}
            while($row=mysqli_fetch_array($result))
            {
              if($row['wri_sb_rl']) {$wri_sb_rl=html($row['wri_sb_rl']).' ';} else {$wri_sb_rl='';}
              $pts[$pt_id]['wri_rls'][$row['wri_rlid']]['wris'][$row['wri_compid']]['wricomp_ppl'][]=array('prsn_nm'=>html($row['prsn_fll_nm']), 'wri_sb_rl'=>$wri_sb_rl);
            }
?>