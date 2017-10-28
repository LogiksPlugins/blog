<?php
if(!defined('ROOT')) exit('No direct script access allowed');
checkServiceSession();

if(SITENAME!="cms") {
	printServiceMSG("ONLY CMS can access this service.");
	return;
}

include_once __DIR__."/api.php";

switch($_REQUEST["action"]) {
	case "list":
		$data=_db()->_selectQ("blog_contents","id,slug,title,category,tags,vers,blocked,published,published_on,published_by,created_by,created_on,edited_by,edited_on")->_GET();
		
		$fData=["NoGroup"=>[]];
		foreach($data as $a=>$b) {
			if($b['category']==null || strlen($b['category'])<=0) $b['category']="NoGroup";
			if(!isset($fData[$b['category']])) $fData[$b['category']]=[];
			$fData[$b['category']][$b['id']]=$b;
		}
		printServiceMSG($fData);
		break;
	case "fetch":
		if(isset($_POST['slug'])) {
			updateBlogMeta();
			
			$data=_db()->_selectQ("blog_contents","text_draft as txt")
							->_where(["slug"=>$_POST['slug']])->_GET();
			
			if(isset($data[0])) echo $data[0]["txt"];
			else echo "error: Content Not Found";
		} else {
			echo "error: Reference Not Found";
		}
		break;
	case "preview":
		if(isset($_POST['slug'])) {
			$data=_db()->_selectQ("blog_contents","text_published as txt,title,category,tags,vers,blocked,published,created_by,created_on,edited_by,edited_on,published_on,published_by")
							->_where(["slug"=>$_POST['slug']])->_GET();
			
			if(isset($data[0])) {
				$data=$data[0];
				if(strlen(strip_tags($data["txt"]))<=2) {
					echo "<br><h2 align=center class='metaTitle'>Not published yet.</h2><p align=center>Publish blog first to preview.</p>";
				} else {
					echo "<span class='label label-success pull-right metaLabel'>{$data['published_on']}</span>";
					echo "<span class='label label-warning pull-right metaLabel'>{$data['published_by']}</span>";
					echo "<h1 class='metaTitle'>Last Published Content</h1>";
					echo $data["txt"];
				}
			} else echo "error: Content Not Found";
		} else {
			echo "error: Reference Not Found";
		}
		break;
	case "reviews":
		if(isset($_POST['slug'])) {
			$data=_db()->_selectQ("blog_reviews","*")->_where(["blog_slug"=>$_POST['slug']])->_GET();
			
			if(count($data)>0) {
				
			} else {
				echo "<h2 align=center>No Reviews Yet</h2>";
			}
		} else {
			echo "error: Reference Not Found";
		}
		break;
	case "properties":
		if(isset($_POST['slug'])) {
			$data=_db()->_selectQ("blog_contents","title,category,tags,vers,blocked,published,published_on,published_by,created_by,created_on,edited_by,edited_on,meta_title,meta_description,meta_keywords,text_excerpt,stats_views,stats_likes,stats_reviews")
								->_where(["slug"=>$_POST['slug']])->_GET();
			
			if(!isset($data[0])) echo "error: Content Not Found";
			else {
				$data=$data[0];
				include __DIR__."/cmsComps/properties.php";
			}
		} else {
			echo "error: Reference Not Found";
		}
		break;
	case "delete":
		if(isset($_POST['slug']) && strlen($_POST['slug'])>0) {
			$slugs=explode(",",$_POST['slug']);
			$ans=_db()->_deleteQ("blog_contents")->_whereIn("slug",$slugs)->_RUN();
			
			if($ans) {
				echo "Requested records deleted successfully.";
			} else {
				echo "error: Sorry, requested records could not be deleted.";
			}
		} else {
			echo "error: Reference Not Found";
		}
		break;
	case "save":
		if(isset($_POST['slug'])) {
			$slug=$_POST['slug'];
			unset($_POST['slug']);
			$_POST['edited_by']=$_SESSION['SESS_USER_ID'];
			$_POST['edited_on']=date("Y-m-d H:i:s");
			$ans=_db()->_updateQ("blog_contents",$_POST,["slug"=>$slug])->_RUN();
			
			if($ans) echo "Successfully updated the properties.";
			else echo "error: Update failed. Try again later.";
		} else {
			echo "error: Reference Not Found";
		}
		break;
	case "create":
		if(isset($_POST['slug'])) {
			$slug=_slugify($_POST['slug']);
			
			$data=_db()->_selectQ("blog_contents","count(*) as cnt")
							->_where(["slug"=>$_POST['slug']])->_GET();
			
			if(isset($data[0]) && $data[0]['cnt']>0) {
				echo "error: The defined Code already exists.";
				return;
			}
			
			$_POST['slug']=$slug;
			$_POST['title']=$_POST['slug'];
			$_POST['category']="";
			$_POST['tags']="";
			$_POST['vers']="0";
			$_POST['text_draft']="";
			$_POST['text_published']="";
			$_POST['published']="false";
			//$_POST['published_on']="";
			//$_POST['published_by']="";
			$_POST['blocked']="false";
			$_POST['created_by']=$_SESSION['SESS_USER_ID'];
			$_POST['created_on']=date("Y-m-d H:i:s");
			$_POST['edited_by']=$_SESSION['SESS_USER_ID'];
			$_POST['edited_on']=date("Y-m-d H:i:s");
			$ans=_db()->_insertQ1("blog_contents",$_POST)->_RUN();
			
			if($ans) echo $slug;
			else echo "error: Update failed. Try again later.";
		} else {
			echo "error: Reference Not Found";
		}
		break;
	case "publish":
		if(isset($_POST['slug']) && isset($_POST['text_published']) && isset($_POST['text_draft'])) {
			$slug=$_POST['slug'];
			unset($_POST['slug']);
			
			$data=_db()->_selectQ("blog_contents","vers,text_published")
							->_where(["slug"=>$slug])->_GET();
			
			if(isset($data[0]) && isset($data[0]['vers'])) {
				$txt1=trim(str_replace("&#39%3B","&#39;",$data[0]['text_published']));
				$txt2=trim($_POST['text_published']);
				
				if(md5($txt1)==md5($txt2)) {
					echo "No changes found. Maintaining the last state of the article.";
					return;
				}
				
				$vers=(int)$data[0]['vers'];
				$vers++;
			} else {
				echo "error: Content Not Found";
				return;
			}
			
			$_POST['vers']=$vers;
			$_POST['published']="true";
			$_POST['blocked']="true";
			$_POST['published_by']=$_SESSION['SESS_USER_ID'];
			$_POST['published_on']=date("Y-m-d H:i:s");
			$_POST['edited_by']=$_SESSION['SESS_USER_ID'];
			$_POST['edited_on']=date("Y-m-d H:i:s");
			
			$ans=_db()->_updateQ("blog_contents",$_POST,["slug"=>$slug])->_RUN();
			
			if($ans) echo "Successfully updated the properties.";
			else echo "error: Update failed. Try again later.";
		} else {
			echo "error: Reference/Text Not Found";
		}
		break;
}
?>