<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("getBlogContent")) {
  function getBlogContent($slugID,$silent=false) {
    $data=_db()->_selectQ("blog_contents","id,slug,title,category,tags,text_excerpt as summary,vers,meta_title,meta_description,meta_keywords,blocked,published,published_on,published_by,created_by,created_on,edited_by,edited_on")
							->_where(["slug"=>$slugID])->_GET();
    if(isset($data[0])) {
			if(!$silent) {
				_db()->_increment("blog_contents",["stats_views"],["slug"=>$_POST['slug']])->_RUN();
			}
			return $data[0];
		}
    else return false;
  }
  
  function getBlogContentHTML($slugID) {
    $data=_db()->_selectQ("blog_contents","text_published as txt")
							->_where(["slug"=>$slugID])->_GET();
    
    if(isset($data[0])) return $data[0]['txt'];
    else return "";
  }
  function getBlogContentDraft($slugID) {
    $data=_db()->_selectQ("blog_contents","text_draft as txt")
							->_where(["slug"=>$slugID])->_GET();
    if(isset($data[0])) return $data[0]['txt'];
    else return "";
  }
	
	function updateBlogMeta() {
		_dbQuery("UPDATE blog_contents SET stats_reviews=(SELECT COUNT(*) FROM `blog_reviews` WHERE blog_reviews.blog_slug=blog_contents.slug), stats_likes=(SELECT COUNT(*) FROM `blog_likes` WHERE blog_likes.blog_slug=blog_contents.slug)");
	}
}
?>