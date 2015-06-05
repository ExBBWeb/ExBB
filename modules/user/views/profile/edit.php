<div class="panel">

<div class="header">
	<h1><?php echo $lang->edit_profile_title; ?></h1>
</div>

<div class="body">

<div class="tabs">
	<ul>
		<li<?php if ($edit_tab == 'user_data') echo ' class="active"'; ?>><i class="fa fa-user"></i> <?php echo $lang->tab_user_data; ?></li>
		<li<?php if ($edit_tab == 'secret') echo ' class="active"'; ?>><i class="fa fa-user-secret"></i> <?php echo $lang->tab_user_password; ?></li>
		<li<?php if ($edit_tab == 'avatar') echo ' class="active"'; ?>><i class="fa fa-file-image-o"></i> <?php echo $lang->tab_user_avatar; ?></li>
		<li<?php if ($edit_tab == 'sig') echo ' class="active"'; ?>><i class="fa fa-bell-o"></i> <?php echo $lang->tab_user_sig; ?></li>
	</ul>
    <div>

		<p class="alert alert-info no-close"><i class="fa fa-asterisk"></i> - <?php echo $lang->field_is_required; ?></p>

		<div><?php include $edit_tab_content; ?></div>
		<div><?php include $edit_tab_secret; ?></div>
		<div>Третье содержимое</div>
	</div>            
</div> 
</div>

</div>