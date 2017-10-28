<?php
if(SITENAME!="cms") {
	echo("ONLY CMS can access this service.");
	return;
}

$categories=_db()->_selectQ("blog_category","*",['blocked'=>"false"])->_GET();

$tableData=[
		"vers","blocked",
		"published","published_on","published_by",
		"created_by","created_on","edited_by","edited_on",
		"meta_title","meta_description","meta_keywords","text_excerpt","stats_views"
	];
$seoCols=["meta_title","meta_description","meta_keywords"];
$statsCols=["stats_views","stats_likes","stats_reviews"];
?>
<div style='max-width: 98%;'>
	<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">Properties</a></li>
		<li role="presentation"><a href="#seo" aria-controls="seo" role="tab" data-toggle="tab">SEO</a></li>
		<li role="presentation"><a href="#stats" aria-controls="stats" role="tab" data-toggle="tab">Stats</a></li>
    <li role="presentation"><a href="#profile" aria-controls="profile" role="tab" data-toggle="tab">Other Infos</a></li>
  </ul>

  <!-- Tab panes -->
  <div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="home">
			<form class="form-horizontal">
				<div class="form-group">
					<label for="slug" class="col-sm-3 control-label">Blog Slug/Code</label>
					<div class="col-sm-9">
						<p class="form-control-static"><?=$_POST['slug']?></p>
					</div>
				</div>
				<?php
					foreach($data as $key=>$val) {
						if(in_array($key,$tableData)) continue;
				?>
					<div class="form-group">
						<label for="<?=$key?>" class="col-sm-3 control-label"><?=toTitle($key)?></label>
						<div class="col-sm-9">
							<?php
								if($key=="category") {
									echo "<select class='form-control' name='{$key}' data-value='{$val}'>";
									foreach($categories as $cat) {
										if($cat['slug']==$val) {
											echo "<option value='{$cat['slug']}' selected>{$cat['title']}</option>";
										} else {
											echo "<option value='{$cat['slug']}'>{$cat['title']}</option>";
										}
									}
									echo "</select>";
								} else {
									echo "<input type='text' class='form-control' name='{$key}' placeholder='".toTitle($key)."' value='{$val}' />";
								}
							?>
						</div>
					</div>
				<?php
					}
				?>
				<div class="form-group">
					<label for="blocked" class="col-sm-3 control-label">Blocked</label>
					<div class="col-sm-9">
						<select class="form-control" name="blocked">
							<?php
								if($data['blocked']=="true") {
									echo "<option value='false'>False</option><option value='true' selected>True</option>";
								} else {
									echo "<option value='false' selected>False</option><option value='true'>True</option>";
								}
							?>
						</select>
					</div>
				</div>
				<br>
				<div class="form-group">
					<div class="col-sm-offset-3 col-sm-9">
						<button onclick='saveProperties(this)' type="button" class="btn btn-default btn-success pull-right">Submit</button>
					</div>
				</div>
			</form>
		</div>
		<div role="tabpanel" class="tab-pane" id="seo">
			<form class="form-horizontal">
				<div class="form-group">
					<label for="slug" class="col-sm-3 control-label">Blog Slug/Code</label>
					<div class="col-sm-9">
						<p class="form-control-static"><?=$_POST['slug']?></p>
					</div>
				</div>
				<?php
					foreach($data as $key=>$val) {
						if(!in_array($key,$seoCols)) continue;
				?>
					<div class="form-group">
						<label for="<?=$key?>" class="col-sm-3 control-label"><?=toTitle($key)?></label>
						<div class="col-sm-9">
							<?php
								if($key=="category") {
									echo "<select class='form-control' name='{$key}' data-value='{$val}'>";
									foreach($categories as $cat) {
										if($cat['slug']==$val) {
											echo "<option value='{$cat['slug']}' selected>{$cat['title']}</option>";
										} else {
											echo "<option value='{$cat['slug']}'>{$cat['title']}</option>";
										}
									}
									echo "</select>";
								} else {
									echo "<input type='text' class='form-control' name='{$key}' placeholder='".toTitle($key)."' value='{$val}' />";
								}
							?>
						</div>
					</div>
				<?php
					}
				?>
				<br>
				<div class="form-group">
					<div class="col-sm-offset-3 col-sm-9">
						<button onclick='saveProperties(this)' type="button" class="btn btn-default btn-success pull-right">Submit</button>
					</div>
				</div>
			</form>
		</div>
		<div role="tabpanel" class="tab-pane" id="stats">
			<div class="table-responsive">
				<table class="table table-bordered table-hover">
					<tbody>
					<?php
					foreach($data as $key=>$val) {
						if(!in_array($key,$statsCols)) continue;
					?>
						<tr>
							<th>#</th>
							<th><?=toTitle($key)?></th>
							<td><?=$val?></td>
						</tr>
					<?php
						}
					?>
					</tbody>
				</table>
			</div>
		</div>
    <div role="tabpanel" class="tab-pane" id="profile">
			<div class="table-responsive">
				<table class="table table-bordered table-hover">
					<tbody>
					<?php
					$tableData=array_diff($tableData, ['blocked','text_excerpt'] );
					$tableData=array_diff($tableData, $seoCols );
					$tableData=array_diff($tableData, $statsCols );
					foreach($data as $key=>$val) {
						if(!in_array($key,$tableData)) continue;
					?>
						<tr>
							<th>#</th>
							<th><?=toTitle($key)?></th>
							<td><?=$val?></td>
						</tr>
					<?php
						}
					?>
					</tbody>
				</table>
			</div>
		</div>
  </div>
</div>