<?php 
  error_reporting(0);
  require_once 'firebird.php';
  require_once 'converter.php';
//  header('Access-Control-Allow-Origin: *');  
  $request  = strstr($_SERVER['REQUEST_URI'],".php");
  $p     = explode("/", $request);
  $func = $p[1];
  $r = $_REQUEST;
  function _log($szoveg){
       //return false;
	   
       $dat=date('y.m.d H:i:s');
	   $datd=date('y.m.d');
	   $dats=str_replace('.','',$datd);
	   $ip = strip_tags($_SERVER['REMOTE_ADDR']);
	   $fnev = str_replace('.','',$ip).'_'.$dats.'.log';
	   @mkdir("log", 0777);
       $fnev="log/$fnev";
       $fp = fopen($fnev, 'a');
       $szoveg1='###'.$dat." $ip:";
       $return=" \r\n";
       fwrite($fp, $szoveg1.$szoveg.$return);
       fclose($fp);
    }         
  function _debug($stmt,$r) {
    $error_text='';
    if($stmt->errorCode() == 0) {
    } else {
        $errors = $stmt->errorInfo();
        $error_text = ' error:'.$errors[2];
    }
  
    $sql = $stmt->queryString;
    foreach ($r as $key => $value) {
        $field=$key;
        $sql = str_replace(':'.$field,addslashes($value),$sql);
    }
    return 'sql:'.$sql.' r:'.json_encode($r).$error_text;
  }
  
  function _logSQL($func, $stmt,$r) {
    //_log('[START] {'.$func.'}');
    $stmt->execute();
    _log('[PARAMS] {' .$func.'} '. _debug($stmt,$r));  
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    _log('[RESULT] {'.$func.'} '.json_encode($res));
    return $res;
  }
  
  function tcpMessage($msg){
	$ip = "192.168.1.164";
	$band='ORZOTTJKV';
	$db='F2017';
	//'192.168.1.164;9099;'||TEMPS||' '||DBNAME||' c:\Alfa\Tir\Repib\au_abroncs_pda.AR '||TEMPS||' '||MIBIZ;	
    $command=$band.' '.$db.' c:\Alfa\Tir\Repib\au_abroncs_pda.AR '.$band.' '.$msg;
	$fp = fsockopen($ip, 9099, $errno, $errstr, 30); 
    if (!$fp) {
        echo "$errstr ($errno)<br />\n";
    } else {
        fwrite($fp, "$command\n");
        
        /*while (!feof($fp)) {
          echo fgets($fp, 128);
        }*/

        fclose($fp);
    }
	  
  }
  //_log(json_encode($r));  
  
  switch ($func) {
  case 'tcp':
		tcpMessage(971421928);
		break;
  case 'log':
		$log=$r['log'];  
        _log('[LOG] ' . $log);
        echo json_encode("OK");
        break;
  
  case 'checkLogin':
		$sql="select kezelo RCOUNT,telep from pda_kezelok where kezelo=:login";
		$stmt = Firebird::prepare($sql);
		$login=trim($r['user']);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		echo json_encode(Converter::win2utf_array($res));
        break;
  
  case 'loadSettings':
		$sql="select tetel,ertek  from ini where konftip='SAJC' and szekcio = 'ANDROID' /*and tetel like 'ORZOTT%'*/";
		$stmt = Firebird::prepare($sql);
		/*$login=trim($r['user']);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);*/
        $res = _logSQL($func,$stmt,$r);
		echo json_encode(Converter::win2utf_array($res));
        
        break;
  
  /* lerakodas */
  case 'beerk.mibizList':
		$sql="SELECT * FROM PDA_MIBIZLIST_ORZOTTLERAK2 (:login)";
		$stmt = Firebird::prepare($sql);
		$login=$r['login'];
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		echo json_encode(Converter::win2utf_array($res));
        break;

  case 'beerk.panelInit':
		$mibiz = $r['mibiz'];
		$login = $r['login'];
		$sql=" SELECT FIRST 1  BFEJ.AZON,BFEJ.MIBIZ
				FROM BFEJ 
				WHERE BFEJ.MIBIZ=:mibiz AND (COALESCE(BFEJ.STAT3,'R') IN ('R', 'W','H') --AND (SELECT COUNT(1) FROM BSOR SOROK WHERE SOROK.BFEJ=BFEJ.AZON AND COALESCE(SOROK.DEVEAR,0)=CAST(:login AS INTEGER))>0
				)";

		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':mibiz', $mibiz, PDO::PARAM_STR);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		echo json_encode(Converter::win2utf_array($res));
        break;

  case 'beerk.rszAdatokGet':
		$azon = $r['azon'];
		$rsz = $r['rsz'];
		$sql=" SELECT FIRST 1  SORSZ, CAST(ABS(DRB) AS INTEGER) AS DRB ,CEG.NEV CEGNEV ,CAST(DRB2 AS INTEGER) AS CDRB,AKTSOR.DEVEAR AS PDAKEZ, AKTSOR.STAT3 AS ROWSTAT,
		CASE WHEN COALESCE(BFEJ.MSZAM3,'9999')='9999' AND BFEJ.MIBIZ NOT LIKE 'ELOSZ%' AND BFEJ.MIBIZ NOT LIKE 'TELEP%' THEN 'GY�JT�' 
         WHEN BFEJ.MIBIZ LIKE 'ELOSZ%' OR BFEJ.MIBIZ LIKE 'TELEP%' THEN CEG.NEV 
         ELSE COALESCE(BFEJ.MSZAM3,'')||' '||COALESCE(MSZAM.NEV,'') END MSZAM3,
    AKTSOR.TAPADO RENDSZAM, COALESCE(AKTSOR.GYSZAM,'')||' '||COALESCE(AKTSOR.LEIR,'') MERETMINTA, CAST(COALESCE(JARUL2,0) AS INTEGER)||'/'||CAST(COALESCE(JARUL1,0) AS INTEGER) FEGU,
	AKTSOR.MJBEL RSZADATOK,CAST(COALESCE(JARUL2,0) AS INTEGER) FEDB,CAST(COALESCE(JARUL1,0) AS INTEGER) GUDB,(SELECT FEGU FROM PDA_ORZOTTLERAK_POSDETAILS(CAST(AKTSOR.MJSOR2 AS VARCHAR(500)))) FEGUKESZ,
    AKTSOR.POZIC EVSZAK 
    FROM BSOR AKTSOR 
    INNER JOIN BFEJ ON BFEJ.AZON=AKTSOR.BFEJ 
    LEFT JOIN CEG ON COALESCE(AKTSOR.PONTOZ, AKTSOR.CEG)=CEG.AZON
	LEFT JOIN MSZAM ON BFEJ.MSZAM3=MSZAM.SZAM AND TIPUS='3'
    WHERE BFEJ.AZON=:azon AND AKTSOR.TAPADO=:rsz
    ORDER BY AKTSOR.SORSZ";

		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
		$stmt->bindParam(':rsz', $rsz, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		echo json_encode(Converter::win2utf_array($res));
        break;

  
  case 'beerk.rszAdatokSet':
		$azon = $r['azon'];
		$rsz = $r['rsz'];
		$fedb = $r['fedb'];
        $evszak = $r['evszak'];
		$login = $r['login'];
		$rszadatok = implode("\r\n", str_replace("\r",'',json_decode($r['data'])));
		$sql=" SELECT RESULT FROM PDA_ORZOTTLERAK_RSZUPDATE(:azon, :rsz, '$rszadatok', :fedb, :evszak, :login) ";
		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
		$stmt->bindParam(':rsz', $rsz, PDO::PARAM_STR);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
		$stmt->bindParam(':fedb', $fedb, PDO::PARAM_STR);
        $stmt->bindParam(':evszak', utf8_decode($evszak), PDO::PARAM_STR);
		try { 
            $res = _logSQL($func,$stmt,$r);
			Firebird::commit();
		} catch (PDOException $e) { 
			Firebird::rollback();
			$res=array('errorcode'=>$e->getCode(),'errorinfo'=>$e->getMessage());
		} 

		echo json_encode($res);
        break;

  case 'beerk.taskReg':
		$mibiz = $r['mibiz'];
		$login = $r['login'];
		$sql=" EXECUTE PROCEDURE PDA_ORZOTTLERAK_TASKREG ( :mibiz, :login ) ";
		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':mibiz', $mibiz, PDO::PARAM_STR);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		Firebird::commit();
		$res=null;
		//$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		echo json_encode(Converter::win2utf_array($res));
        break;

  case 'beerk.rszMent':
		$azon = $r['azon'];
		$sorsz = $r['sorsz'];
		$drb2 = $r['drb2'];
		$drb2 = $drb2 + 1;
		$login = $r['login'];
		$poz = $r['poz'];
		$tip = $r['tip'];
		$rowstat='R';
		$sql=" SELECT RESULT,FE,GU FROM PDA_ORZOTTLERAK_SORUPDATE(:azon, :sorsz, :drb2, :poz, :tip, :rowstat, :login) ";
		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
		$stmt->bindParam(':sorsz', $sorsz, PDO::PARAM_STR);
		$stmt->bindParam(':drb2', $drb2, PDO::PARAM_STR);
		$stmt->bindParam(':poz', $poz, PDO::PARAM_STR);
		$stmt->bindParam(':tip', $tip, PDO::PARAM_STR);
		$stmt->bindParam(':rowstat', $rowstat, PDO::PARAM_STR);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;

  case 'beerk.rszMentMind':
		$azon = $r['azon'];
		$sorsz = $r['sorsz'];
		$drb2 = $r['drb2'];
		$login = $r['login'];
		$pozmind = $r['poz'];
		$tip = $r['tip'];
        
        $poz     = explode(";", $pozmind);
		$rowstat='R';
        foreach ($poz as $aktpoz) {
            $aktpoz=str_replace('#','',$aktpoz);
            if ($aktpoz!='') {
                $res=null;
                $sql=" SELECT RESULT,FE,GU FROM PDA_ORZOTTLERAK_SORUPDATE(:azon, :sorsz, :drb2, :poz, :tip, :rowstat, :login) ";
                $stmt = Firebird::prepare($sql);
                $stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
                $stmt->bindParam(':sorsz', $sorsz, PDO::PARAM_STR);
                $stmt->bindParam(':drb2', $drb2, PDO::PARAM_STR);
                $stmt->bindParam(':poz', $aktpoz, PDO::PARAM_STR);
                $stmt->bindParam(':tip', $tip, PDO::PARAM_STR);
                $stmt->bindParam(':rowstat', $rowstat, PDO::PARAM_STR);
                $stmt->bindParam(':login', $login, PDO::PARAM_STR);
                $res = _logSQL($func,$stmt,$r);
            }
        }
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;
        
  case 'beerk.rszJav':
		$azon = $r['azon'];
		$sorsz = $r['sorsz'];
		$rsz = $r['rsz'];
		$login = $r['login'];
		$sql=" SELECT RESULT,FEGU FROM PDA_ORZOTTLERAK_JAVITAS(:azon, :sorsz, :rsz, :login) ";
		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
		$stmt->bindParam(':sorsz', $sorsz, PDO::PARAM_STR);
		$stmt->bindParam(':rsz', $rsz, PDO::PARAM_STR);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
	    break;
  case 'beerk.reviewRszFilter':
		$azon = $r['azon'];
		$login = $r['login'];
		$sql=" SELECT DISTINCT LEFT(BSOR.TAPADO,2) RENDSZAM
				FROM BSOR
				WHERE BSOR.BFEJ=:azon AND TAPADO<>'TOBBLET'
				";

		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		echo json_encode(Converter::win2utf_array($res));
        break;
  case 'beerk.reviewRszGet':
		$filter = $r['filter'];
		$filterStr='';
		if ($filter!='*') {
			$filterStr = " AND BSOR.TAPADO STARTING WITH '$filter' ";
		}
		$azon = $r['azon'];
		$login = $r['login'];
		$sql=" SELECT BSOR.TAPADO RENDSZAM, FOBIZ KARTYA, CAST(DRB AS INTEGER) DRB, CAST(DRB2 AS INTEGER) DRB2
				FROM BSOR
				WHERE BSOR.BFEJ=:azon $filterStr
				";

		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		echo json_encode(Converter::win2utf_array($res));
        break;
    
    case 'beerk.folytUpdate':
		$azon = $r['azon'];
		$login = $r['login'];
		/*
		$sql=" UPDATE BSOR SET STAT3='U' WHERE COALESCE(BSOR.STAT3,'')='N' AND BSOR.BFEJ = :azon  ";
		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
		$stmt->execute();
		*/

		$sql=" UPDATE BFEJ SET STAT3='R',KONTI1=NULL WHERE BFEJ.AZON = :azon  ";
		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
		$stmt->execute();
        _log(_debug($stmt,$r));
		//$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$res=array();
		$res[0]['STATUS']='OK';
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;
	case 'beerk.lezarUpdate':
		$mibiz = $r['mibiz'];
		$stat = $r['stat'];
		$login = $r['login'];
		$sql=" SELECT RESULT FROM PDA_LERAKODAS_LEZAR(:mibiz,:stat,:login)  ";
		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':mibiz', $mibiz, PDO::PARAM_STR);
		$stmt->bindParam(':stat', $stat, PDO::PARAM_STR);
        $stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		//$res=array();
		//$res[0]['STATUS']='OK';
		Firebird::commit();
		tcpMessage($mibiz);
		echo $log=json_encode(Converter::win2utf_array($res));
        _log($log);
        break;
	  
  case 'beerk.updateTobblet':
		$azon = $r['azon'];
		$drb = $r['drb'];
		$login = $r['login'];
		$sql=" SELECT * FROM PDA_ORZOTTLERAK_TOBBLET(:azon,:drb,:login)  ";
		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
		$stmt->bindParam(':drb', $drb, PDO::PARAM_STR);
        $stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		Firebird::commit();
		echo $log=json_encode(Converter::win2utf_array($res));
        break;
  case 'beerk.getPositions':
		$rsz = $r['rsz'];
		$mibiz = $r['mibiz'];
		$login = $r['login'];
		$sql=" SELECT * FROM PDA_ORZOTTLERAK_GETPOZ(:mibiz, :rsz) ";
		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':rsz', $rsz, PDO::PARAM_STR);
		$stmt->bindParam(':mibiz', $mibiz, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;
  
  case 'beerk.getMelyseg':
  case 'leltar.getMelyseg':
  case 'elrak.getMelyseg':
		$login = $r['login'];
        $tip=$r['tip'];
        $wherestr="";
        if ($tip=='bFelni' || $tip=='F') $wherestr=" AND KOD IN ('FS','FCS') ";
		$sql=" SELECT KOD FROM AKHSTAT WHERE TIPUS='G' $wherestr ORDER BY KOD3,KOD2,KOD";
		$stmt = Firebird::prepare($sql);
        $res = _logSQL($func,$stmt,$r);
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;
   case 'beerk.allapotMent':
   case 'elrak.allapotMent':
		$rsz = $r['rsz'];
		$mibiz = $r['mibiz'];
		$login = $r['login'];
		$poz = $r['poz'];
		$tip = $r['tip'];
		$melyseg = $r['melyseg'];
        $csereok = $r['csereok'];
		$sql=" SELECT * FROM PDA_ORZOTTLERAK_ALLAPOTMENT(:mibiz, :rsz, :poz,:tip, :melyseg,:csereok,:login) ";
		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':rsz', $rsz, PDO::PARAM_STR);
		$stmt->bindParam(':mibiz', $mibiz, PDO::PARAM_STR);
		$stmt->bindParam(':poz', $poz, PDO::PARAM_STR);
		$stmt->bindParam(':tip', $tip, PDO::PARAM_STR);
		$stmt->bindParam(':melyseg', $melyseg, PDO::PARAM_STR);
        $stmt->bindParam(':csereok', utf8_decode($csereok), PDO::PARAM_STR);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;
  case 'getMarka':
		$marka = trim($r['marka']);
		$meret = trim($r['meret']);
		$minta = trim($r['minta']);
		$si = trim($r['si']);
        $evszak = trim($r['evszak']);
		if (strtoupper($marka)=='MIND') $marka='';
		if (strtoupper($meret)=='MIND') $meret='';
		if (strtoupper($minta)=='MIND') $minta='';
		if (strtoupper($si)=='MIND') $si='';
        if (strtoupper($evszak)=='MIND') $evszak='';
        else $evszak=strtoupper($evszak[0]);
		$marka=str_replace('\\r','',$marka);
		$meret=str_replace('\\r','',$meret);		
		$minta=str_replace('\\r','',$minta);		
		$si=str_replace('\\r','',$si);
        $evszak=str_replace('\\r','',$evszak);

		if (strtoupper($marka)=='NINCS') $marka='';
		if (strtoupper($meret)=='NINCS') $meret='';
		if (strtoupper($minta)=='NINCS') $minta='';
		if (strtoupper($si)=='NINCS') $si='';
		$where='';

		if (trim($marka)!='') $where = " MARKA = '$marka' ";
		if (trim($meret)!='') {if ($where!='') $where.=' AND ';$where .= " replace(replace(MERET,'/',''),' ','') = replace(replace('$meret','/',''),' ','') ";}
		if (trim($minta)!='') {if ($where!='') $where.=' AND ';$where .= " replace(replace(replace(replace(MINTA,'/',''),' ',''),'.',''),'*','') = replace(replace(replace(replace('$minta','/',''),' ',''),'.',''),'*','') ";}
		if (trim($si)!='') 	  {if ($where!='') $where.=' AND ';$where .= " SI = '$si' ";}
		if (trim($evszak)!='') 	  {if ($where!='') $where.=' AND ';$where .= " EVSZAK = '$evszak' ";}        
		if ($where!='') $where = ' AND ' .$where;
		$sql=" SELECT DISTINCT MARKA FROM AKHTORZS WHERE CICSOP STARTING WITH 'A' $where";
		$stmt = Firebird::prepare($sql);
        $res = _logSQL($func,$stmt,$r);
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));	  
        break;
  case 'getMeret':
		$marka = trim($r['marka']);
		$meret = trim($r['meret']);
		$minta = trim($r['minta']);
		$si = trim($r['si']);
        $evszak = trim($r['evszak']);
		if (strtoupper($marka)=='MIND') $marka='';
		if (strtoupper($meret)=='MIND') $meret='';
		if (strtoupper($minta)=='MIND') $minta='';
		if (strtoupper($si)=='MIND') $si='';
        if (strtoupper($evszak)=='MIND') $evszak='';
        else $evszak=strtoupper($evszak[0]);
		$marka=str_replace('\\r','',$marka);
		$meret=str_replace('\\r','',$meret);		
		$minta=str_replace('\\r','',$minta);		
		$si=str_replace('\\r','',$si);
        $evszak=str_replace('\\r','',$evszak);
		if (strtoupper($marka)=='NINCS') $marka='';
		if (strtoupper($meret)=='NINCS') $meret='';
		if (strtoupper($minta)=='NINCS') $minta='';
		if (strtoupper($si)=='NINCS') $si='';

		$where='';
		if (trim($marka)!='') $where = " MARKA = '$marka' ";
		if (trim($meret)!='') {if ($where!='') $where.=' AND ';$where .= " replace(replace(MERET,'/',''),' ','') = replace(replace('$meret','/',''),' ','') ";}
		if (trim($minta)!='') {if ($where!='') $where.=' AND ';$where .= " replace(replace(replace(replace(MINTA,'/',''),' ',''),'.',''),'*','') = replace(replace(replace(replace('$minta','/',''),' ',''),'.',''),'*','') ";}
		if (trim($si)!='') 	  {if ($where!='') $where.=' AND ';$where .= " SI = '$si' ";}
        if (trim($evszak)!='') 	  {if ($where!='') $where.=' AND ';$where .= " EVSZAK = '$evszak' ";}        
		if ($where!='') $where = ' AND ' .$where;
		$sql=" SELECT DISTINCT MERET FROM AKHTORZS  WHERE CICSOP STARTING WITH 'A' $where";
		$stmt = Firebird::prepare($sql);
        $res = _logSQL($func,$stmt,$r);
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));	  
        break;
  case 'getMinta':
		$marka = trim($r['marka']);
		$meret = trim($r['meret']);
		$minta = trim($r['minta']);
		$si = trim($r['si']);
        $evszak = trim($r['evszak']);
		if (strtoupper($marka)=='MIND') $marka='';
		if (strtoupper($meret)=='MIND') $meret='';
		if (strtoupper($minta)=='MIND') $minta='';
		if (strtoupper($si)=='MIND') $si='';
        if (strtoupper($evszak)=='MIND') $evszak='';
        else $evszak=strtoupper($evszak[0]);
		$marka=str_replace('\\r','',$marka);
		$meret=str_replace('\\r','',$meret);		
		$minta=str_replace('\\r','',$minta);		
		$si=str_replace('\\r','',$si);
        $evszak=str_replace('\\r','',$evszak);
		if (strtoupper($marka)=='NINCS') $marka='';
		if (strtoupper($meret)=='NINCS') $meret='';
		if (strtoupper($minta)=='NINCS') $minta='';
		if (strtoupper($si)=='NINCS') $si='';
		$where='';
		if (trim($marka)!='') $where = " MARKA = '$marka' ";
		if (trim($meret)!='') {if ($where!='') $where.=' AND ';$where .= " replace(replace(MERET,'/',''),' ','') = replace(replace('$meret','/',''),' ','') ";}
		if (trim($minta)!='') {if ($where!='') $where.=' AND ';$where .= " replace(replace(replace(replace(MINTA,'/',''),' ',''),'.',''),'*','') = replace(replace(replace(replace('$minta','/',''),' ',''),'.',''),'*','') ";}
		if (trim($si)!='') 	  {if ($where!='') $where.=' AND ';$where .= " SI = '$si' ";}
        if (trim($evszak)!='') 	  {if ($where!='') $where.=' AND ';$where .= " EVSZAK = '$evszak' ";}        
		if ($where!='') $where = ' AND ' .$where;

		$sql=" SELECT DISTINCT MINTA FROM AKHTORZS  WHERE CICSOP STARTING WITH 'A' $where";
		$stmt = Firebird::prepare($sql);
        $res = _logSQL($func,$stmt,$r);
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));	  
        break;
    case 'getSI':
		$marka = trim($r['marka']);
		$meret = trim($r['meret']);
		$minta = trim($r['minta']);
		$si = trim($r['si']);
        $evszak = trim($r['evszak']);
		if (strtoupper($marka)=='MIND') $marka='';
		if (strtoupper($meret)=='MIND') $meret='';
		if (strtoupper($minta)=='MIND') $minta='';
		if (strtoupper($si)=='MIND') $si='';
        if (strtoupper($evszak)=='MIND') $evszak='';
        else $evszak=strtoupper($evszak[0]);
		$marka=str_replace('\\r','',$marka);
		$meret=str_replace('\\r','',$meret);		
		$minta=str_replace('\\r','',$minta);		
		$si=str_replace('\\r','',$si);
        $evszak=str_replace('\\r','',$evszak);
		if (strtoupper($marka)=='NINCS') $marka='';
		if (strtoupper($meret)=='NINCS') $meret='';
		if (strtoupper($minta)=='NINCS') $minta='';
		if (strtoupper($si)=='NINCS') $si='';
		$where='';
		if (trim($marka)!='') $where = " MARKA = '$marka' ";
		if (trim($meret)!='') {if ($where!='') $where.=' AND ';$where .= " replace(replace(MERET,'/',''),' ','') = replace(replace('$meret','/',''),' ','') ";}
		if (trim($minta)!='') {if ($where!='') $where.=' AND ';$where .= " replace(replace(replace(replace(MINTA,'/',''),' ',''),'.',''),'*','') = replace(replace(replace(replace('$minta','/',''),' ',''),'.',''),'*','') ";}
		if (trim($si)!='') 	  {if ($where!='') $where.=' AND ';$where .= " SI = '$si' ";}
        if (trim($evszak)!='') 	  {if ($where!='') $where.=' AND ';$where .= " EVSZAK = '$evszak' ";}        
		if ($where!='') $where = ' AND ' .$where;

		$sql=" SELECT DISTINCT SI FROM AKHTORZS  WHERE CICSOP STARTING WITH 'A' $where";
		$stmt = Firebird::prepare($sql);
        $res = _logSQL($func,$stmt,$r);
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));	  
        break;
  /* lerakodas eddig */
  
  /* elrakodas */
  case 'elrak.getRszInProgress':
    $login = $r['login'];
	$sql=" SELECT * FROM PDA_ORZOTTHKOD_GETRSZSTARTED (:login) ";
	$stmt = Firebird::prepare($sql);
    $stmt->bindParam(':login', $login, PDO::PARAM_STR);
	$stmt->execute();
    
    _log(_debug($stmt,$r));
	$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
	echo json_encode(Converter::win2utf_array($res));
    break;
    
  case 'elrak.rszAdatokGet':
	/* elrakodasnal rendszam adatok + adott rendszambol mennyi van kiszedve*/
	$rsz = $r['rsz'];
    $login = $r['login'];
    //if ($login=='') $login=100;
	$sql=" SELECT * FROM PDA_ORZOTTHKOD_GETRSZ(:rsz,:login) ";
	$stmt = Firebird::prepare($sql);
	$stmt->bindParam(':rsz', $rsz, PDO::PARAM_STR);
    $stmt->bindParam(':login', $login, PDO::PARAM_STR);
    $res = _logSQL($func,$stmt,$r);
	Firebird::commit();
	echo json_encode(Converter::win2utf_array($res));
    break;

  case 'elrak.hkodSaveCheck':
	/* elrakodasnal hkod mentes elotti ellenorzesek*/
	$rsz = $r['rsz'];
	$hkod = $r['hkod'];
	$login = $r['login'];
	$sql=" SELECT * FROM PDA_ORZOTTHKOD_HKODCHECK(:rsz,:hkod, :login) ";
	$stmt = Firebird::prepare($sql);
	$stmt->bindParam(':rsz', $rsz, PDO::PARAM_STR);
	$stmt->bindParam(':hkod', $hkod, PDO::PARAM_STR);
	$stmt->bindParam(':login', $login, PDO::PARAM_STR);
    $res = _logSQL($func,$stmt,$r);
	Firebird::commit();
	echo json_encode(Converter::win2utf_array($res));
    break;

  case 'elrak.hkodSave':
	/* elrakodasnal hkod mentes (elotte ellenorzes volt, ide csak akkor kerul, ha azon tuljutott)*/
	$azon = $r['azon'];
	$sorsz = $r['sorsz'];
	$rsz = $r['rsz'];
	$hkod = $r['hkod'];
	$login = $r['login'];
	$sql=" SELECT * FROM PDA_ORZOTTHKOD_HKODSAVE(:azon, :sorsz, :rsz,:hkod, :login) ";
	$stmt = Firebird::prepare($sql);
	$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
	$stmt->bindParam(':sorsz', $sorsz, PDO::PARAM_STR);
	$stmt->bindParam(':rsz', $rsz, PDO::PARAM_STR);
	$stmt->bindParam(':hkod', $hkod, PDO::PARAM_STR);
	$stmt->bindParam(':login', $login, PDO::PARAM_STR);
    $res = _logSQL($func,$stmt,$r);
    Firebird::commit();
	echo json_encode(Converter::win2utf_array($res));
    break;
  case 'elrak.hkodDel':
	/* elrakodasnal adott rendszamhoz tartozo hkodok torlese */
	$azon = $r['azon'];
	$rsz = $r['rsz'];
	$login = $r['login'];
	$sql=" SELECT * FROM PDA_ORZOTTHKOD_HKODDEL(:azon, :rsz, :login) ";
	$stmt = Firebird::prepare($sql);
	$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
	$stmt->bindParam(':rsz', $rsz, PDO::PARAM_STR);
	$stmt->bindParam(':login', $login, PDO::PARAM_STR);
    $res = _logSQL($func,$stmt,$r);
	Firebird::commit();
	echo json_encode(Converter::win2utf_array($res));	
    break;
	
  case 'elrak.reviewRszFilter':
		/* atnezo panel , rendszam szuro*/
		$login = $r['login'];
		$sql=" SELECT DISTINCT LEFT(BSOR.TAPADO,2) RENDSZAM
				FROM BSOR
				WHERE BSOR.BIZTIP=(SELECT OBEVET_BIZTIP FROM PDA_LOGINDATA(:login)) AND DRB=DRB2
				";

		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		echo json_encode(Converter::win2utf_array($res));
        break;
  case 'elrak.reviewRszGet':
		/* atnezo panel, rendszam szuro eredmeny*/
       
		$filter = $r['filter'];
		$filterStr='';
		if ($filter!='*') {
			$filterStr = " AND BSOR.TAPADO STARTING WITH '$filter' ";
		}
		$azon = $r['azon'];
		$login = $r['login'];
		$sql=" SELECT BSOR.TAPADO RENDSZAM, CAST(DRB AS INTEGER) DRB, CAST(DRB2 AS INTEGER) DRB2,CAST(ROGDRB AS INTEGER) ROGDRB
				FROM BSOR
				WHERE BSOR.BIZTIP=(SELECT OBEVET_BIZTIP FROM PDA_LOGINDATA(:login)) AND DRB=DRB2 $filterStr
				";

		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		echo json_encode(Converter::win2utf_array($res));
        break;
  /* orzott leltar */
  case 'leltar.mibizList':
		$sql="SELECT * FROM PDA_MIBIZLIST_ORZOTTLELTAR (:login)";
		$stmt = Firebird::prepare($sql);
		$login=$r['login'];
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;
  case 'leltar.rszAdatokGet':
		$rsz = $r['rendszam'];
        $fejazon=$r['fejazon'];
        $hkod=$r['hkod'];
		$sql=" select * from pda_orzottleltar_rszadatok(:rsz,:fejazon,:hkod)";
		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':rsz', $rsz, PDO::PARAM_STR);
        $stmt->bindParam(':fejazon', $fejazon, PDO::PARAM_STR);
        $stmt->bindParam(':hkod', $hkod, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		echo json_encode(Converter::win2utf_array($res));
        break;

  case 'leltar.rszAdatokUpdate':
		$sql="SELECT * FROM PDA_ORZOTTLELTAR_RSZUPDATE (:fejazon, :rendszam, :rszadatok,:login)";
		$stmt = Firebird::prepare($sql);
		$login=$r['login'];
		$fejazon=$r['fejazon'];
		$rszadatok=utf8_decode($r['rszadatok']);
		$rendszam=$r['rendszam'];
		$stmt->bindParam(':fejazon', $fejazon, PDO::PARAM_STR);
		$stmt->bindParam(':rszadatok', $rszadatok, PDO::PARAM_STR);
		$stmt->bindParam(':rendszam', $rendszam, PDO::PARAM_STR);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;

  case 'leltar.rszSave':
		$sql="SELECT * FROM PDA_ORZOTTLELTAR_SORUPDATE (:login,:fejazon, :hkod, :rendszam,:melyseg, :csereok)";
		$stmt = Firebird::prepare($sql);
		$login=$r['login'];
		$fejazon=$r['fejazon'];
		$hkod=$r['hkod'];
		$rendszam=$r['rendszam'];
		$melyseg = $r['melyseg'];
        $csereok = $r['csereok'];
		$stmt->bindParam(':fejazon', $fejazon, PDO::PARAM_STR);
		$stmt->bindParam(':hkod', $hkod, PDO::PARAM_STR);
		$stmt->bindParam(':rendszam', $rendszam, PDO::PARAM_STR);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
		$stmt->bindParam(':melyseg', $melyseg, PDO::PARAM_STR);
        $stmt->bindParam(':csereok', utf8_decode($csereok), PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
       
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;
  
  case 'leltar.reviewLoad':
		$sql="SELECT * FROM PDA_ORZOTTLELTAR_REVIEW (:login,:fejazon)";
		$stmt = Firebird::prepare($sql);
		$login=$r['login'];
		$fejazon=$r['fejazon'];
		$stmt->bindParam(':fejazon', $fejazon, PDO::PARAM_STR);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		echo json_encode(Converter::win2utf_array($res));
        break;
  
  case 'leltar.folytUpdate':
		$azon = $r['azon'];
		$login = $r['login'];

		$sql=" UPDATE BFEJ SET STAT3='R' WHERE BFEJ.AZON = :azon  ";
		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
		$stmt->execute();

		//$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$res=array();
		$res[0]['STATUS']='OK';
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;
  case 'leltar.lezarUpdate':
		$azon = $r['azon'];
		$sql=" UPDATE BFEJ SET STAT3='Z' WHERE AZON=:azon  ";
		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
		$stmt->execute();
		$res=array();
		$res[0]['STATUS']='OK';
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;
  case 'leltar.delRsz':
		$azon = $r['azon'];
		$rendszam = $r['rendszam'];
		$sql=" DELETE FROM BSOR WHERE BFEJ=:azon and tapado=:rendszam ";
		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
		$stmt->bindParam(':rendszam', $rendszam, PDO::PARAM_STR);
		$stmt->execute();
		$res=array();
		$res[0]['RENDSZAM']=$rendszam;
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;

  /* orzott kiadas */
  case 'kiadas.raktarList':
		$sql="SELECT * FROM PDA_ORZOTTKI_RAKTARLIST (:login,:akttip)";
		$stmt = Firebird::prepare($sql);
		$login=$r['login'];
        $akttip=$r['akttip'];
        $stmt->bindParam(':akttip', $akttip, PDO::PARAM_STR);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);		
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;
  case 'kiadas.telepList':
		$sql="SELECT * FROM PDA_ORZOTTKI_TELEPLIST (:login,:akttip, :aktraktar)";
		$stmt = Firebird::prepare($sql);
		$login=$r['login'];
        $akttip=$r['akttip'];
        $aktraktar=$r['aktraktar'];
        $stmt->bindParam(':akttip', $akttip, PDO::PARAM_STR);
        $stmt->bindParam(':aktraktar', $aktraktar, PDO::PARAM_STR);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);		
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;
  case 'kiadas.mibizList':
		$sql="SELECT * FROM PDA_MIBIZLIST_ORZOTTKI (:login,:akttip,:raktar,:cegazon)";
		$stmt = Firebird::prepare($sql);
		$login=$r['login'];
        $akttip=$r['akttip'];
        $raktar=$r['raktar'];
        $cegazon=$r['cegazon'];
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $stmt->bindParam(':akttip', $akttip, PDO::PARAM_STR);
        $stmt->bindParam(':raktar', $raktar, PDO::PARAM_STR);
        $stmt->bindParam(':cegazon', $cegazon, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;
  case 'kiadas.nextHkodGet':
		$sql="SELECT * FROM PDA_ORZOTTKI_HKOD (:azon, :hkod)";
		$stmt = Firebird::prepare($sql);
		$login=$r['login'];
		$azon=$r['azon'];
		$hkod=$r['hkod'];
		//$hkod=utf8_decode($hkod);
		$r['hkod']=$hkod;
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
		$stmt->bindParam(':hkod', $hkod, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);		
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;
  case 'kiadas.nextRszGet':
		$sql="SELECT * FROM PDA_ORZOTTKI_RSZ (:azon, :hkod, :rsz)";
		$stmt = Firebird::prepare($sql);
		$login=$r['login'];
		$azon=$r['azon'];
		$hkod=$r['hkod'];
		//$hkod=utf8_decode($hkod);
		$rsz=$r['rsz'];
		$r['hkod']=$hkod;
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
		$stmt->bindParam(':hkod', $hkod, PDO::PARAM_STR);
		$stmt->bindParam(':rsz', $rsz, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);		
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;
  case 'kiadas.rszSave':
		$sql="SELECT * FROM PDA_ORZOTTKI_SORUPDATE (:azon, :hkod, :rsz, :rszshort,:login,:lastrsz)";
		$stmt = Firebird::prepare($sql);
		$login=$r['login'];
		$azon=$r['azon'];
		$rsz=$r['rsz'];
		$lastrsz=$r['lastrsz'];
		$rszshort=$r['rszshort'];		
		$hkod=$r['hkod'];
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
		$stmt->bindParam(':rsz', $rsz, PDO::PARAM_STR);
		$stmt->bindParam(':lastrsz', $lastrsz, PDO::PARAM_STR);
		$stmt->bindParam(':rszshort', $rszshort, PDO::PARAM_STR);		
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);		
		$stmt->bindParam(':hkod', utf8_decode($hkod), PDO::PARAM_STR);		
        $res = _logSQL($func,$stmt,$r);		
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;
  case 'kiadas.rszEmpty':
		$sql="SELECT * FROM PDA_ORZOTTKI_SORVISSZA (:azon, :hkod, :rszshort,:login)";
		$stmt = Firebird::prepare($sql);
		$login=$r['login'];
		$azon=$r['azon'];
		$rszshort=$r['rszshort'];		
		$hkod=$r['hkod'];
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
		$stmt->bindParam(':rszshort', $rszshort, PDO::PARAM_STR);		
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);		
		$stmt->bindParam(':hkod', utf8_decode($hkod), PDO::PARAM_STR);		
        $res = _logSQL($func,$stmt,$r);		
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;
  case 'kiadas.reviewRszFilter':
		/* atnezo panel , rendszam szuro*/
		$login = $r['login'];
		$azon=$r['azon'];
		$sql=" SELECT DISTINCT LEFT(BSOR.TAPADO,2) RENDSZAM
				FROM BSOR
				WHERE BFEJ=:azon
				";

		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		echo json_encode(Converter::win2utf_array($res));
        break;
  case 'kiadas.reviewRszGet':
		/* atnezo panel, rendszam szuro eredmeny*/
		$filter = $r['filter'];
		$filterStr='';
		if ($filter!='*') {
			$filterStr = " AND BSOR.TAPADO STARTING WITH '$filter' ";
		}
		$azon = $r['azon'];
		$login = $r['login'];
		$sql=" SELECT BSOR.CIKK RENDSZAM, BSOR.HKOD, CAST(DRB AS INTEGER) DRB, CAST(DRB2 AS INTEGER) DRB2,COALESCE(BSOR.STAT3,'') STAT3
				FROM BSOR
				WHERE BSOR.BFEJ = :azon $filterStr
				";

		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		echo json_encode(Converter::win2utf_array($res));
        break;

  case 'kiadas.closeCheck':
		/* lezaras elotti ellenorzes*/
		$azon = $r['azon'];
		$login = $r['login'];
		$sql=" SELECT * FROM PDA_ORZOTTKI_CLOSECHECK(:azon, :login)";
		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		echo json_encode(Converter::win2utf_array($res));
        break;
  case 'kiadas.closeIt':
		/* lezaras */
		$azon = $r['azon'];
		$login = $r['login'];
		$sql=" SELECT * FROM PDA_ORZOTTKI_CLOSE(:azon, :login)";
		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
        Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;
  case 'kiadas.setLabelData':
		$sql="SELECT * FROM PDA_ORZOTTKI_CIMKEADATOK (:azon, :rsz)";
		$stmt = Firebird::prepare($sql);
		$login=$r['login'];
		$azon=$r['azon'];
		$rsz=$r['rsz'];
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
		$stmt->bindParam(':rsz', $rsz, PDO::PARAM_STR);
		//$stmt->bindParam(':login', $login, PDO::PARAM_STR);		
        $res = _logSQL($func,$stmt,$r);
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;
  case 'kiadas.rszReset':
		$sql="SELECT * FROM PDA_ORZOTTKI_RSZRESET (:azon, :rsz, :login)";
		$stmt = Firebird::prepare($sql);
		$login=$r['login'];
		$azon=$r['azon'];
		$rsz=$r['rsz'];
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
		$stmt->bindParam(':rsz', $rsz, PDO::PARAM_STR);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);		
        $res = _logSQL($func,$stmt,$r);
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;
  /* szortir */        
  case 'szortir.mibizList':
		$sql="SELECT * FROM PDA_ORZOTTSZORTIR_MIBIZLIST (:login)";
		$stmt = Firebird::prepare($sql);
		$login=$r['login'];
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		echo json_encode(Converter::win2utf_array($res));
        break;
  case 'szortir.getRszDetails':
		$sql="SELECT * FROM PDA_ORZOTTSZORTIR_RSZADATOK (:azon,:rsz)";
		$stmt = Firebird::prepare($sql);
		$azon=$r['azon'];
		$rsz=$r['rsz'];
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
		$stmt->bindParam(':rsz', $rsz, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		echo json_encode(Converter::win2utf_array($res));
        break;
  case 'szortir.rszSave':
		$sql="SELECT * FROM PDA_ORZOTTSZORTIR_SORUPDATE (:azon, :rsz, :login)";
		$stmt = Firebird::prepare($sql);
		$login=$r['login'];
		$azon=$r['azon'];
		$rsz=$r['rsz'];
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
		$stmt->bindParam(':rsz', $rsz, PDO::PARAM_STR);
		$stmt->bindParam(':login', $login, PDO::PARAM_STR);		
        $res = _logSQL($func,$stmt,$r);		
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;
  case 'szortir.reviewLoad':
		/* atnezo panel, rendszam szuro eredmeny*/

		$azon = $r['azon'];
		$login = $r['login'];
		$sql=" SELECT * FROM PDA_ORZOTTSZORTIR_REVIEWLOAD(:azon,:login)";

		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
        $stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		echo json_encode(Converter::win2utf_array($res));
        break;
	case 'szortir.lezarUpdate':
		$azon = $r['azon'];
		$login = $r['login'];
		$sql=" SELECT * FROM PDA_ORZOTTSZORTIR_LEZARCHECK(:azon,:login)  ";
		$stmt = Firebird::prepare($sql);
		$stmt->bindParam(':azon', $azon, PDO::PARAM_STR);
        $stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $res = _logSQL($func,$stmt,$r);
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;
  case 'qprint.setLabelData':
		$sql="SELECT * FROM PDA_ORZOTTPRINT_RSZADATOK (:rsz)";
		$stmt = Firebird::prepare($sql);
		//$login=$r['login'];
		$rsz=$r['rsz'];
		$stmt->bindParam(':rsz', $rsz, PDO::PARAM_STR);
		//$stmt->bindParam(':login', $login, PDO::PARAM_STR);		
        $res = _logSQL($func,$stmt,$r);
		Firebird::commit();
		echo json_encode(Converter::win2utf_array($res));
        break;
        
    default:
        echo json_encode('unknown command');
  }
?>