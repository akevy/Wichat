<?php	


	/*数据库连接开始*/
	$link = @ mysql_connect("42.96.146.204", "enmotech", "welcome1") or die("数据库链接错误");
	mysql_select_db("enmo_weixin", $link);
	mysql_query("set names 'UTF8'"); //编码;

	include("wechat.class.php");
	require('phpQuery/phpQuery.php'); 	

	$options = array(
		'token'       =>'1a14512b50581c4557e12bc881d9d6cc', //填写你设定的key
       'appid'=>'wxe3165e70ddd226d3',
		'appsecret'=>'6f83b3b82689ce35251605f46720dd7a',
	);
	$weObj = new Wechat($options);
	$weObj->valid();	
	$type     = $weObj->getRev()->getRevType();
	$content  = $weObj->getRev()->getRevContent();//微信发送过来的原始内容
	$getevent = $weObj->getRev()->getRevEvent();
    $tname    =$weObj->getRev()->getRevTo();//获取接受账号
	$fname    =$weObj->getRev()->getRevFrom();//获取发送者

	
	
   function quchu($str){
	/*去除抓取内容中的html字符*/
     $str=str_replace("<br>","",$str);
     $str = trim($str);
     $str=str_replace("&lt;","<",$str);
     $str=str_replace("&gt;",">",$str);
     $str=str_replace("&amp;","&",$str);
     $str=str_replace("&quot;",'"',$str);
     $str=str_replace("&#039;","'",$str);
     $str=str_replace("<i>","",$str);
     $str=str_replace("</i>","",$str);
     return $str;
    }

	$sql_aa = "select * from `user`  where appid = '$fname'";
	$ret_aa = mysql_query($sql_aa, $link);
	while ($row_aa = mysql_fetch_assoc($ret_aa)) {$status = $row_aa['status'];}

	switch($type) {	
		case Wechat::MSGTYPE_TEXT:
        
        if($status==2){
            
            $reg = "/(\D+)/"; 
            preg_match($reg,$content,$m); 
            $type = $m[1];
            preg_match("/^.*?([0-9]+).*?$/", $content, $out);			
            $code = $out[1];
            
            if($type!='' && $code!=''){
                  $sql_y = "select * from `post`  where type = 'case' and industry = '$type' and solution = '$code' order by id desc limit 8";
            
            }else{
                
                  $sql_y = "select * from `post`  where type = 'case' and (industry = '$content' or solution = '$content') order by id desc limit 8";
            
            }
            
                     $ret_y = mysql_query($sql_y, $link);
                     $num  = mysql_num_rows($ret_y);
                     while($row_y = mysql_fetch_assoc($ret_y)) {
                            $post[] = $row_y;
                     }
                     if($num==0){
                            $weObj->text("好桑心，暂时没有查到您想看的案例呢/:P-(不过小墨墨会记下您的需要，后续会及时更新嗒\n\n【墨墨提示】\n如果您需要查询ORA错误，请先点击菜单栏的「错误查询」，再输入错误代码/:8-)")->reply();
                     }elseif($num==1){
                                $newsmsg = array(
                                    0=>array(
                                    'Title'=>"相关案例",
                                    'Description'=>$post[0][title],						
                                    'PicUrl'=>$post[0][picurl],
                                    'Url'=> $post[0][url]
                                    )
                                );
                                $weObj->news($newsmsg)->reply();
                     }else{
                         $new_num = $num+1;
                                $newsmsg = array();
                                $newsmsg[0]['Title']	= "相关案例";
                                $newsmsg[0]['Description']	= '';
                                $newsmsg[0]['PicUrl']	= $post[0][picurl];
                                $newsmsg[0]['Url']	= $post[0][url];
                            for($i=1;$i<$num+1;$i++){
                                $ii=$i-1;							
                                $newsmsg[$i]['Title']       = $post[$ii][title];;
                                $newsmsg[$i]['Description'] = '';
                                $newsmsg[$i]['PicUrl']      = $post[$ii][picurl];
                                $newsmsg[$i]['Url']         = $post[$ii][url];;
                            }
                                $newsmsg[$new_num]['Title']	= "按行业查看案例请输入行业简拼，如金融输入【fin】,电信输入【tel】。\n按解决方案查看请输入解决方案类型，如：规划【1】,集成【2】,运维【3】,优化【4】";
                                $newsmsg[$new_num]['Description']	= '';
                                $newsmsg[$new_num]['PicUrl']	='';
                                $newsmsg[$new_num]['Url']	= '';
                            $weObj->news($newsmsg)->reply();
                    }
            	


        
        }elseif($status==3){
                    if($content=='0'){                //指定关键字回复，可根据需要自行修改
                        $weObj->text("亲，你输入0干嘛呢，0是自然数吗？")->reply();
                    }elseif($content=='1'){			 //指定关键字回复，可根据需要自行修改，如需增加可自行增加
                        $weObj->text("亲，你又输入1干什么呢？是对我一心一意吗？")->reply();
                    }elseif($content=='我爱你'){			 //指定关键字回复，可根据需要自行修改，如需增加可自行增加
                        $weObj->text("小墨墨也爱你~么么哒/::*")->reply();   
                    }else{
                        /*不指定关键词回复则进行错误代码匹配*/
                        $isspace = strpos($content," ");
                        if($isspace==false){
                            /*将用户发送过来的格式转换为固定格式*/
                            $content=str_replace("－","-",$content);
                            $content=str_replace("，",",",$content);
                            $douhao = strpos($content,",");
                            if($douhao==false){
                              /*不存在连接符-的转换*/		
                                $lianjie = strpos($content,"-");
                                if($lianjie==false){
                                    $reg = "/(\D+)/"; 
                                    preg_match($reg,$content,$m); 
                                    $type = $m[1];
                                    $type =  strtoupper($type);
                                    preg_match("/^.*?([0-9]+).*?$/", $content, $out);			
                                    $code = $out[1];
                                    $code_n = str_pad($code,5,"0",STR_PAD_LEFT);
                                    $type_code = $type."-".$code_n;	
                                    $type_code1 = $type_code;					
                                }else{
                                    $new_con = explode("-",$content);
                                    $type = $new_con[0];
                                    $type =  strtoupper($type);
                                    $code =  $new_con[1];
                                    $code_n = str_pad($code,5,"0",STR_PAD_LEFT);			
                                    $type_code = $type."-".$code_n;
                                    if($type_code=='')	{
                                        $type_code1 = '0000000';
                                    }else{
                                        $type_code1 = $type_code;
                                    }										
                                }
                                
                                $sub_code1='';
                                $sql_a = "select * from `oracode`  where error_code = '$type_code1' and published = 'Y'";		
                            }else{
                                 /*存在分割符,时的转换*/
                                $content_arr = explode(",",$content);
                                $content_qian = $content_arr[0];			
                                $sub_code1 = $content_arr[1];
                                $errorcode = strpos($content_qian,"-");
                                if($errorcode==false){
                                    $reg = "/(\D+)/"; 
                                    preg_match($reg,$content_qian,$m); 
                                    $type = $m[1];
                                    $type =  strtoupper($type);
                                    preg_match("/^.*?([0-9]+).*?$/", $content_qian, $out);			
                                    $code = $out[1];
                                    $code_n = str_pad($code,5,"0",STR_PAD_LEFT);
                                    $type_code = $type."-".$code_n;		
                                }else{
                                    $new_con = explode("-",$content_qian);
                                    $type = $new_con[0];
                                    $type =  strtoupper($type);
                                    $code =  $new_con[1];
                                    $code_n = str_pad($code,5,"0",STR_PAD_LEFT);			
                                    $type_code = $type."-".$code_n;					
                                }
                                $type_code1 = $type_code."-".$sub_code1;	    
                                $sql_a = "select * from `oracode`  where error_code = '$type_code'  and sub_code1 = '$sub_code1' and published = 'Y'";
                            }
                            
                            /*查询600或者7445，无subcode时直接返回结果*/
                            if($sub_code1=='' && ($type_code=='ORA-00600' || $type_code=='ORA-07445')){
                                $text_code = "【".$type_code."】";	
                                $text_description = "Description:".$type_code."错误查询方式";
                                $comment = "Comment:查询".$type_code."错误需同时输入子错误号，用逗号分隔，如：".$type_code;
                                $reference = "Reference:关于".$type_code."错误的相关提示都是基于特定的场景，如果您无法判断或确定如何处理，请联络云和恩墨专家：400-660-8755";
                                if($type_code=='ORA-00600') {
                                    $text_comment  = $comment.",13310";
                                }else if($type_code=='ORA-07445') {
                                    $text_comment  = $comment.",kcsgrsn";
                                }
                                $text_back = $text_code."\n\n".$text_description."\n\n".$text_comment."\n\n".$reference;
                                $weObj->text($text_back)->reply();
                            }
                            
                          
                            /*数据库查询*/
                            $ret_a = mysql_query($sql_a, $link);
                            while ($row_a = mysql_fetch_assoc($ret_a)) {
                                $error_code= $row_a['error_code'];
                                $sub_code1= $row_a['sub_code1'];
                                $sub_code2= $row_a['sub_code2'];
                                $description = $row_a['description'];
                                $cause = $row_a['cause'];
                                $action = $row_a['action'];
                                $db_version = $row_a['db_version'];
                                $comment = $row_a['comment'];
                                $reference = $row_a['reference'];
                                $contact = $row_a['contact'];
                            }
                            if($error_code!=''){   
                                  /*如果数据库中存在则返回内容*/
                                  /*
                            	   *查询出数据的情况下，记录状态A
                            	   */
                            $sql_insert = "insert into `user_analysis`(id,content,time,category) values(null,'$type_code1',now(),'A')";
                            mysql_query($sql_insert, $link);
                                
                                
                                $text_code = "【".$error_code."】";			
                                if($sub_code1 !='' && $sub_code2 !=''){
                                    $text_sub_code = "Sub_code:internal error code, arguments:【".$sub_code1."】,【".$sub_code2."】\n";
                                }elseif($sub_code1 =='' && $sub_code2 !=''){
                                    $text_sub_code = "Sub_code:internal error code, arguments:【".$sub_code2."】\n";
                                }elseif($sub_code1 !='' && $sub_code2 ==''){
                                    $text_sub_code = "Sub_code:internal error code, arguments:【".$sub_code1."】\n";
                                }else{
                                    $text_sub_code = '';			
                                }
                                
                                $array_reference = explode(";",$reference);
                                $num = count($array_reference);
                                 for($i=0;$i<$num-1;$i++){
                                    $array_single[$i] = $array_reference[$i];
                                    $array_single1[$i] = explode(",http://",$array_single[$i]);
                                    $title[$i] =	$array_single1[$i][0];
                                    $tink[$i]  =	$array_single1[$i][1];
                                    $ttt[$i] = "<a href ='http://".$tink[$i]."'>".$title[$i]."</a>\n";
                                 }
                                if($reference!=''){
                                    $text_reference = "Reference:\n".$ttt[0].$ttt[1].$ttt[2].$ttt[3].$ttt[4].$ttt[5].$ttt[6].$ttt[7].$ttt[8].$ttt[9];	
                                    
                                    if($type_code=='ORA-00600' || $type_code=='ORA-07445'){
                                        $text_reference = $text_reference."\n关于".$type_code."错误的相关提示都是基于特定的场景，如果您无法判断或确定如何处理，请联络云和恩墨专家：400-660-8755";
                                    }
                                }		
                                $text_description  = "Description:".$description;
                                $text_cause  = "Cause:".$cause;
                                $text_action  = "Action:".$action;
                                $text_db_version  = "DB Version:".$db_version;
                                if($contact=''){
                                    $text_contact  = "\n\nContact:".$contact;			
                                }	
                                if($comment!=''){
                                    $text_comment  = "\n\nComment:".$comment;			
                                }						
                                $text_back = $text_code."\n\n".$text_sub_code.$text_description."\n\n".$text_cause."\n\n".$text_action."\n\n".$text_db_version.$text_comment."\n\n".$text_reference.$text_contact;
                                $weObj->text($text_back)->reply();
                            }else{
                            
                               /*
                            	*如果数据库中没有查询到，用户输入的为中文，则记录状态B
                            	*/
                            $sql_insert1 = "insert into `user_analysis`(id,content,time,category) values(null,'$type_code1',now(),'B')";
                            mysql_query($sql_insert, $link);
                            	
                                /*如果数据库中不存在则进行抓取*/
                                if (preg_match("/[\x7f-\xff]/", $type_code1)) { 
                                    $weObj->text("亲，您输入的字符包含中文，墨墨无法匹配到正确的错误代码~ /::(")->reply();
                            
                                }else{ 
                                    $url = "http://".$type_code1.".ora-code.com"; 
                                    $opts = array(   
                                               'http'=>array(   
                                                 'method'=>"GET",   
                                                 'timeout'=>2, //设置超时  
                                            )   
                                        );   
                                    $context = stream_context_create($opts);   
                                    $contents = @file_get_contents($url,false,$context); 
            
                                    if($contents==''){
                                    	
                           
                                    	
                                            $weObj->text("亲，服务器开小差了~请稍后再试！")->reply();
                                    }else{
                                           phpQuery::newDocumentFile($url); 
                                           $a = pq("table td:eq(0) h2")->html(); 
                                           $b = pq("table td:eq(1)")->html(); 
                                           $c = pq("table td:eq(2) b")->html();
                                           $d = pq("table td:eq(3)")->html();
                                           $e = pq("table td:eq(4) b")->html();
                                           $f = pq("table td:eq(5)")->html();
                                           $aa = str_replace(":","",$a);
                                           $dd = quchu($d);
                                           $ff = quchu($f);
                                           $bb= quchu($b);$ddd= quchu($dd);$fff= quchu($ff);
                                           if($aa!=$type_code){
                                                $weObj->text("亲，您搜索的代码现在数据库中还无法匹配，我们根据您的输入随时更新数据库，以为您提供更好的服务/::)")->reply();
                                           }else{
                                                $text = "【".$aa."】\n".$bb."\n\n".$c.$ddd."\n\n".$e.$fff;
                                                $weObj->text($text)->reply();
                                           } 
            
                                    }
            
            
                                } 
                    
                            } 
            
                        }else{
                            $weObj->text("亲，您输入的代码格式有误，正确的格式如：ORA-00059 /:heart")->reply();
                        }
            
                    }        
        
        }
        
		

				exit;
				break;
		case Wechat::MSGTYPE_EVENT:
			$key=$getevent['key'];
			$event = $getevent['event'];
			if($event=='subscribe'){
				/*用户关注时的回复*/  
                
                if($status == '') {				
					$sql = "INSERT INTO `user` (`appid`,`status`)VALUES('$fname','0')";						
				} else {
					$sql = "update `user` set status='0' where appid = '$fname'";				
				}
				$ret = mysql_query($sql, $link);
				if($ret===false) {
					$weObj->text("用户注册失败！")->reply();
				} else {
					$weObj->text("感谢您关注云和恩墨!/::*\n小墨墨现在支持ORA错误速查，推荐阅读等小功能，快来点击下方的菜单体验一下吧！")->reply();
				} 

                			
	
			}
        	if($key=='1'){
				//DBA职业规划
                 $sql = "select * from `post`  where type = 'dba' order by id desc limit 9";
                 $ret = mysql_query($sql, $link);
				 $num  = mysql_num_rows($ret);
				 while($row = mysql_fetch_assoc($ret)) {
						$post[] = $row;
				 }
                 if($num==0){
						$weObj->text("暂无相关文章")->reply();
				 }elseif($num==1){
                     		$newsmsg = array(
								0=>array(
								'Title'=>"近期相关文章",
								'Description'=>$post[0][title],						
								'PicUrl'=>$post[0][picurl],
								'Url'=> $post[0][url]
								)
							);
							$weObj->news($newsmsg)->reply();
                 }else{
							$newsmsg = array();
							$newsmsg[0]['Title']	= "近期相关文章";
							$newsmsg[0]['Description']	= '';
							$newsmsg[0]['PicUrl']	= $post[0][picurl];
							$newsmsg[0]['Url']	= $post[0][url];
						for($i=1;$i<$num+1;$i++){
							$ii=$i-1;							
							$newsmsg[$i]['Title']       = $post[$ii][title];;
							$newsmsg[$i]['Description'] = '';
							$newsmsg[$i]['PicUrl']      = $post[$ii][picurl];
							$newsmsg[$i]['Url']         = $post[$ii][url];;
						}
						$weObj->news($newsmsg)->reply();
				}


            }elseif($key=='2'){
              //案例分析 
                
                if($status == '') {				
					$sql = "INSERT INTO `user` (`appid`,`status`)VALUES('$fname','2')";						
				} else {
					$sql = "update `user` set status='2' where appid = '$fname'";				
				}
				$ret = mysql_query($sql, $link);
				if($ret===false) {
					$weObj->text("用户注册失败！")->reply();
				} else {
                    
                    
                     $sql_m = "select * from `post`  where type = 'case' order by id desc limit 8";
                     $ret_m = mysql_query($sql_m, $link);
                     $num  = mysql_num_rows($ret_m);
                     while($row_m = mysql_fetch_assoc($ret_m)) {
                            $post[] = $row_m;
                     }
                     if($num==0){
                            $weObj->text("暂无相关文章")->reply();
                     }elseif($num==1){
                                $newsmsg = array(
                                    0=>array(
                                    'Title'=>"近期相关文章",
                                    'Description'=>$post[0][title],						
                                    'PicUrl'=>$post[0][picurl],
                                    'Url'=> $post[0][url]
                                    )
                                );
                                $weObj->news($newsmsg)->reply();
                     }else{
                         $new_num = $num+1;
                                $newsmsg = array();
                                $newsmsg[0]['Title']	= "近期相关文章";
                                $newsmsg[0]['Description']	= '';
                                $newsmsg[0]['PicUrl']	= $post[0][picurl];
                                $newsmsg[0]['Url']	= $post[0][url];
                            for($i=1;$i<$num+1;$i++){
                                $ii=$i-1;							
                                $newsmsg[$i]['Title']       = $post[$ii][title];;
                                $newsmsg[$i]['Description'] = '';
                                $newsmsg[$i]['PicUrl']      = $post[$ii][picurl];
                                $newsmsg[$i]['Url']         = $post[$ii][url];;
                            }
                                $newsmsg[$new_num]['Title']	= "按行业查看案例请输入行业简拼，如金融输入【fin】,电信输入【tel】。\n按解决方案查看请输入解决方案类型，如：规划【1】,集成【2】,运维【3】,优化【4】";
                                $newsmsg[$new_num]['Description']	= '';
                                $newsmsg[$new_num]['PicUrl']	='';
                                $newsmsg[$new_num]['Url']	= '';
                            $weObj->news($newsmsg)->reply();
                    }
					 
				} 
                


        
            }elseif($key=='3'){
              //进入查询 
                
                if($status == '') {				
					$sql = "INSERT INTO `user` (`appid`,`status`)VALUES('$fname','3')";						
				} else {
					$sql = "update `user` set status='3' where appid = '$fname'";				
				}
				$ret = mysql_query($sql, $link);
				if($ret===false) {
					$weObj->text("用户注册失败！")->reply();
				} else {
					$weObj->text("云和恩墨微信现支持ORA错误自动查询，如查询ORA-00059错误原因及解决方案，可输入：\n【ORA00059 】\n\n另外，我们还增加了200余篇ORA00600和ORA07445错误参考文章，可分别回复\n【ORA00600】或\n【ORA07445】查询。\n\n*无需输入【】")->reply(); 
				} 
                          	
        
            }elseif($key=='4'){
              //专家介绍 多图文
                 $sql = "select * from `post`  where type = 'intro' order by id desc limit 9";
                 $ret = mysql_query($sql, $link);
				 $num  = mysql_num_rows($ret);
				 while($row = mysql_fetch_assoc($ret)) {
						$post[] = $row;
				 }
                 if($num==0){
						$weObj->text("暂无相关专家信息")->reply();
				 }elseif($num==1){
                     		$newsmsg = array(
								0=>array(
								'Title'=>"专家介绍",
								'Description'=>$post[0][title],						
								'PicUrl'=>$post[0][picurl],
								'Url'=> $post[0][url]
								)
							);
							$weObj->news($newsmsg)->reply();
                 }else{
							$newsmsg = array();
							$newsmsg[0]['Title']	= "有关专家介绍";
							$newsmsg[0]['Description']	= '';
							$newsmsg[0]['PicUrl']	= $post[0][picurl];
							$newsmsg[0]['Url']	= $post[0][url];
						for($i=1;$i<$num+1;$i++){
							$ii=$i-1;							
							$newsmsg[$i]['Title']       = $post[$ii][title];;
							$newsmsg[$i]['Description'] = '';
							$newsmsg[$i]['PicUrl']      = $post[$ii][picurl];
							$newsmsg[$i]['Url']         = $post[$ii][url];;
						}
						$weObj->news($newsmsg)->reply();
				}
        
            }elseif($key=='5'){
              //联系方式   单图文 
                 $sql = "select * from `post`  where type = 'contact' order by id desc limit 9";
                 $ret = mysql_query($sql, $link);
				 $num  = mysql_num_rows($ret);
				 while($row = mysql_fetch_assoc($ret)) {
						$post[] = $row;
				 }
                 if($num==0){
						$weObj->text("暂无联系方式")->reply();
				 }elseif($num==1){
                     		$newsmsg = array(
								0=>array(
								'Title'=>"联系方式",
								'Description'=>$post[0][title],						
								'PicUrl'=>$post[0][picurl],
								'Url'=> $post[0][url]
								)
							);
							$weObj->news($newsmsg)->reply();
                 }else{
							$newsmsg = array();
							$newsmsg[0]['Title']	= "您可以通过以下方式联系我们";
							$newsmsg[0]['Description']	= '';
							$newsmsg[0]['PicUrl']	= $post[0][picurl];
							$newsmsg[0]['Url']	= $post[0][url];
						for($i=1;$i<$num+1;$i++){
							$ii=$i-1;							
							$newsmsg[$i]['Title']       = $post[$ii][title];;
							$newsmsg[$i]['Description'] = '';
							$newsmsg[$i]['PicUrl']      = $post[$ii][picurl];
							$newsmsg[$i]['Url']         = $post[$ii][url];;
						}
						$weObj->news($newsmsg)->reply();
				}
        
            }elseif($key=='6'){
              //恩墨学院 
                 $sql = "select * from `post`  where type = 'emedu' order by id desc limit 9";
                 $ret = mysql_query($sql, $link);
				 $num  = mysql_num_rows($ret);
				 while($row = mysql_fetch_assoc($ret)) {
						$post[] = $row;
				 }
                 if($num==0){
						$weObj->text("暂无相关信息")->reply();
				 }elseif($num==1){
                     		$newsmsg = array(
								0=>array(
								'Title'=>"恩墨学院",
								'Description'=>$post[0][title],						
								'PicUrl'=>$post[0][picurl],
								'Url'=> $post[0][url]
								)
							);
							$weObj->news($newsmsg)->reply();
                 }else{
							$newsmsg = array();
							$newsmsg[0]['Title']	= "您可以通过以下方式联系我们";
							$newsmsg[0]['Description']	= '';
							$newsmsg[0]['PicUrl']	= $post[0][picurl];
							$newsmsg[0]['Url']	= $post[0][url];
						for($i=1;$i<$num+1;$i++){
							$ii=$i-1;							
							$newsmsg[$i]['Title']       = $post[$ii][title];;
							$newsmsg[$i]['Description'] = '';
							$newsmsg[$i]['PicUrl']      = $post[$ii][picurl];
							$newsmsg[$i]['Url']         = $post[$ii][url];;
						}
						$weObj->news($newsmsg)->reply();
				}
        
            }
			break;
		default:
			$weObj->text("help info")->reply();
	}
