var simplemde = null;
var currentContent = null;
$(function() {
	$("#componentSpace").css("height","78%");
	$("#pgtoolbar .nav.navbar-nav.navbar-left").css("width",$(".pageCompContainer.withSidebar .pageCompSidebar").width());
	$("<label id='titleContent' class='titleContent'></label>").insertAfter($("#pgtoolbar .nav.navbar-nav.navbar-left"));
	
	$('#componentTree').delegate(".list-group-item.list-file a","click",function() {
		file=$(this).closest(".list-group-item");
		
		title=$(this).text();
		slug=$(file).data("slug");
		vers=$(file).data("vers");
		
		openContent(title+" [v"+vers+"]",slug);
	});
	listContent();
});
function listContent() {
	//closeContentFile();
	$("#componentTree").html("<div class='ajaxloading5'></div>");
	
	processAJAXQuery(_service("blog","list"),function(txt) {
		fs=txt.Data;
		if(fs==null || fs.length<=0) {
			$("#componentTree").html("<p align=center><br>No Content Found.</p>");
			return;
		}
		html="";html1="";
		$.each(fs,function(k,v) {
			if(v.length<=0) return;
			kx=md5(k);
			
			html1+="<div class='list-group-item list-folder'><a href='#item-"+kx+"' data-toggle='collapse'><i class='glyphicon glyphicon-folder-close'></i>"+toTitle(k)+"</a></div>";
			html1+="<div class='list-group-folder collapse' id='item-"+kx+"'>";
			$.each(v,function(m,n) {
				//data-schema='"+k+"/"+n+"' 
				html1+="<div class='list-group-item list-file' title='"+n.title+"' data-id='"+n.id+"' data-vers='"+n.vers+"' data-slug='"+n.slug+"'>";
				html1+="<a href='#'><i class='fa fa-file'></i><span class='text'>"+n.title+"</span></a>";
				html1+="<input type='checkbox' name='selectFile' class='pull-right' data-slug='"+n.slug+"' data-title='"+n.title+"' /></div>";
			});
			html1+="</div>";
		});
		$("#componentTree").html(html+html1);

		if($('#componentTree .list-group-item[data-slug="'+currentContent+'"]').length>0) {
			$('#componentTree .list-group-item[data-slug="'+currentContent+'"]').closest(".list-group-folder.collapse").addClass("in");
			$('#componentTree .list-group-item[data-slug="'+currentContent+'"]').addClass("active");
			
			tag=$('#componentTree .list-group-item[data-slug="'+currentContent+'"]');
			title=$(tag).text();
			vers=$(tag).data("vers");
			
			$("#pgtoolbar .titleContent").html(title+" [v"+vers+"]");
		} else {
			$("#pgtoolbar .nav.navbar-right li.active").removeClass('active');
		}
	},"json");
}

function openContent(title,slug) {
	currentContent=slug;
	$("#pgtoolbar .titleContent").html(title);
	
	$('#componentTree .list-group-item.active').removeClass("active");
	$('#componentTree .list-group-item[data-slug="'+currentContent+'"]').addClass("active");
	
	loadEditorComponent();
}

function loadEditorComponent(slug) {
	if(currentContent==null) {
		lgksToast("Please load an article to edit its content");
		return;
	}
	
	$("#pgtoolbar .nav.navbar-right li.active").removeClass('active');
	$("#toolbtn_loadEditorComponent").closest("li").addClass("active");
	
	$("#componentSpace").html("<h2 class='ajaxloading5'></h2>");
	processAJAXPostQuery(_service("blog","fetch"),"slug="+currentContent,function(txt) {
		err=txt.split(":");
		if(err[0]=="error") {
			$("#componentSpace").html("<h2 class='errorMsg'>"+err[1]+"</h2>");
		} else {
			rid="content"+Math.ceil(Math.random()*1000000);
			$("#componentSpace").html("<textarea id='"+rid+"' style='width:100%;height:100%;border:0px;'></textarea>");
			$("#"+rid).val(txt);
			
			loadEditor(rid);
		}
	},"RAW");
}
function loadInfoComponent() {
	if(currentContent==null) {
		lgksToast("Please load an article to edit its content");
		return;
	}
	
	$("#pgtoolbar .nav.navbar-right li.active").removeClass('active');
	$("#toolbtn_loadInfoComponent").closest("li").addClass("active");
	
	$("#componentSpace").html("<h2 class='ajaxloading5'></h2>");
	processAJAXPostQuery(_service("blog","properties"),"slug="+currentContent,function(txt) {
		err=txt.split(":");
		if(err[0]=="error") {
			$("#componentSpace").html("<h2 class='errorMsg'>"+err[1]+"</h2>");
		} else {
			$("#componentSpace").html(txt);
		}
	},"RAW");
}
function loadPreviewComponent() {
	if(currentContent==null) {
		lgksToast("Please load an article to edit its content");
		return;
	}
	
	$("#pgtoolbar .nav.navbar-right li.active").removeClass('active');
	$("#toolbtn_loadPreviewComponent").closest("li").addClass("active");
	$("#componentSpace").html("<h2 class='ajaxloading5'></h2>");
	
	processAJAXPostQuery(_service("blog","preview"),"slug="+currentContent,function(txt) {
		err=txt.split(":");
		if(err[0]=="error") {
			$("#componentSpace").html("<h2 class='errorMsg'>"+err[1]+"</h2>");
		} else {
			$("#componentSpace").html("<div class='contentPreview'>"+txt+"</div>");
		}
	},"RAW");
}
function loadReviewComponent() {
	if(currentContent==null) {
		lgksToast("Please load an article to edit its content");
		return;
	}
	
	$("#pgtoolbar .nav.navbar-right li.active").removeClass('active');
	$("#toolbtn_loadReviewComponent").closest("li").addClass("active");
	$("#componentSpace").html("<h2 class='ajaxloading5'></h2>");
	
	processAJAXPostQuery(_service("blog","reviews"),"slug="+currentContent,function(txt) {
		err=txt.split(":");
		if(err[0]=="error") {
			$("#componentSpace").html("<h2 class='errorMsg'>"+err[1]+"</h2>");
		} else {
			$("#componentSpace").html(txt);
		}
	},"RAW");
	
}
function createContent() {
	lgksPrompt("New Blog Slug/Code! (No Space or special characters allowed.)","New Content",function(newName) {
			if(newName!=null && newName.length>0) {
				processAJAXPostQuery(_service("blog","create"),"slug="+newName,function(ans) {
						err=ans.split(":");
						if(err[0]=="error") {
							lgksToast(err[1]);
						} else {
							openContent(newName,ans)
							listContent();
						}
					},"RAW");
			}
		});
}
function deleteContent() {
	q=[];q1=[];
	$("#componentTree input[type=checkbox]:checked").each(function() {
		q.push($(this).data("slug"));
		q1.push("<li>"+$(this).data("title")+"</li>");
	});
	htmlMsg="Are you sure about deleting the following contents?<br><ul style='margin-top: 10px;list-style-type: decimal;'>";
	htmlMsg+=q1.join("");
	htmlMsg+="</ul>";
	lgksConfirm(htmlMsg,"Delete Contents",function(ans) {
		if(ans) {
			processAJAXPostQuery(_service("blog","delete"),"slug="+q.join(","),function(ans) {
						err=ans.split(":");
						if(err[0]=="error") {
							lgksToast(err[1]);
						} else {
							lgksToast(ans);
							listContent();
						}
					},"RAW");
		}
	});
}

function saveProperties(btn) {
	if(currentContent==null) {
		lgksToast("Please load an article to edit its content");
		return;
	}
	frm=$(btn).closest("form");
	
	q=[];
	q.push("slug="+currentContent);
	$(frm).find("input[name],select[name]").each(function() {
		q.push($(this).attr("name")+"="+$(this).val());
	});
	processAJAXPostQuery(_service("blog","save"),q.join("&"),function(ans) {
		err=ans.split(":");
		if(err[0]=="error") {
			lgksToast(err[1]);
		} else {
			lgksToast(ans);
			listContent();
		}
	},"RAW");
}
function saveContentFile() {
	if(currentContent==null) {
		lgksToast("Please load an article to edit its content");
		return;
	}
	
	processAJAXPostQuery(_service("blog","save"),"slug="+currentContent+"&text_draft="+encodeURIComponent(simplemde.value()),function(ans) {
		err=ans.split(":");
		if(err[0]=="error") {
			lgksToast(err[1]);
		} else {
			lgksToast(ans);
		}
	},"RAW");
}
function publishContentFile() {
	if(currentContent==null) {
		lgksToast("Please load an article to edit its content");
		return;
	}
	
	lgksConfirm("Are you sure about publishing this content.<br>Publishing will make this content live.","Publish Content",function(ans) {
		if(ans) {
			txt=simplemde.value();
			htm=simplemde.options.previewRender(txt);
			q="slug="+currentContent+"&text_draft="+encodeURIComponent(txt)+"&text_published="+encodeURIComponent(htm);
			processAJAXPostQuery(_service("blog","publish"),q,function(ans) {
						err=ans.split(":");
						if(err[0]=="error") {
							lgksToast(err[1]);
						} else {
							lgksToast(ans);
							listContent();
						}
					},"RAW");
		}
	});
}

function closeContentFile() {
	currentContent=null;
	$("#pgtoolbar .titleContent").html("");
	$("#componentSpace").html("<h2 align=center>Please load an article to edit its content</h2>");
}

function loadEditor(rid) {
	simplemde = new SimpleMDE({ 
						element: document.getElementById(rid),
						autoDownloadFontAwesome: false,
						promptURLs: true,
						spellChecker: true,
						hideIcons: ["guide"],
						showIcons: ["code", "table", "italic", "strikethrough", "horizontal-rule", "clean-block"],
						insertTexts: {
							horizontalRule: ["", "\n\n-----\n\n"],
							//image: ["![](http://", ")"],
							//link: ["[", "](http://)"],
							//table: ["", "\n\n| Column 1 | Column 2 | Column 3 |\n| -------- | -------- | -------- |\n| Text     | Text      | Text     |\n\n"],
						},
				});
	$(simplemde.gui.toolbar).append("<i class='separator'>|</i>");
	
	$(simplemde.gui.toolbar).append("<a class='fa fa-check no-disable pull-right' title='Publish Article' onclick='publishContentFile()'></a>");
	
	$(simplemde.gui.toolbar).append("<a class='fa fa-floppy-o no-disable pull-right' title='Save Article' onclick='saveContentFile()'></a>");
}