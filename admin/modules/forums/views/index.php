<div class="module">
<div class="head">
	<?php echo $lang->forums_icons_help; ?>
</div> 

	<div class="module-body">

		<p><img src="<?php echo $template->url('images/add.png'); ?>" width="16" height="16" /> - <?php echo $lang->icon_add_help; ?></p>
		<p><img src="<?php echo $template->url('images/edit.png'); ?>" width="16" height="16" /> - <?php echo $lang->icon_edit_help; ?></p>
		<p><img src="<?php echo $template->url('images/delete.png'); ?>" width="16" height="16" /> - <?php echo $lang->icon_delete_help; ?></p>
	</div>
</div>

<div class="buttons-line">
	<a href="<?php echo $url->module('forums', 'category', 'add'); ?>" class="button"><?php echo $lang->create_category; ?></a>
	<a href="<?php echo $url->module('forums', 'forum', 'add'); ?>" class="button"><?php echo $lang->create_forum; ?></a>
</div>

<?php foreach ($data->categories as $category) : ?>

<div class="module">
<div class="head">

	<?php echo $category['title']; ?>
	<span class="right">
		<a href="<?php echo $url->module('forums', 'forum', 'add', false, array('category'=>$category['id'])); ?>"><img class="m-icon" src="<?php echo $template->url('images/add.png'); ?>" /></a>
        <a href="<?php echo $url->module('forums', 'category', 'edit', $category['id']); ?>"><img class="m-icon" src="<?php echo $template->url('images/edit.png'); ?>" /></a>
        <a href="<?php echo $url->module('forums', 'category', 'delete', $category['id']); ?>"><img class="m-icon" src="<?php echo $template->url('images/delete.png'); ?>" /></a>	
	</span>
</div> 
	
    <div class="module-table-body">

        <table>
        	<thead>
<tr>
    <th style="width:5%" class="align-center">#</th>
    <th style="width:40%"><?php echo $lang->forum_title; ?></th>
    <th style="width:20%"><?php echo $lang->forum_topics; ?></th>
    <th style="width:15%"><?php echo $lang->forum_posts; ?></th>
    <th style="width:20%"></th>
</tr>
            </thead>
		<tbody>
			<?php viewForums($data, $category['id'], 0, 0, $template, $url); ?>
		</tbody>
	</table>

	<div style="clear: both"></div>
</div>

</div>

<?php endforeach; ?>

<?php
function viewForums($data, $category_id, $parent_id, $margin=0, $template, $url) {
?>
			<?php if (isset($data->forums[$category_id])) : ?>
		<?php foreach ($data->forums[$category_id][$parent_id] as $forum) : ?>	
<tr>
    <td class="align-center"><?php echo $forum['id']; ?></td>
    <td><a style="margin-left: <?php echo $margin; ?>px" href=""><?php echo $forum['title']; ?></a></td>
    <td><?php echo $forum['topics']; ?></td>
    <td><?php echo $forum['posts']; ?></td>
    <td>
		<a href="<?php echo $url->module('forums', 'forum', 'edit', $forum['id']); ?>"><img class="m-icon" src="<?php echo $template->url('images/edit.png'); ?>" /></a>
        <a href="<?php echo $url->module('forums', 'forum', 'delete', $forum['id']); ?>"><img class="m-icon" src="<?php echo $template->url('images/delete.png'); ?>" /></a>
		<?php if ($parent_id == 0) : ?>
		<a href="<?php echo $url->module('forums', 'forum', 'add', false, array('category'=>$category_id, 'parent'=>$forum['id'])); ?>"><img class="m-icon" src="<?php echo $template->url('images/add.png'); ?>"  /></a>
        <?php endif; ?>
	</td>
</tr>
<?php if (isset($data->forums[$category_id][$forum['id']])) viewForums($data, $category_id, $forum['id'], $margin+15, $template, $url); ?>

	<?php endforeach; ?>
<?php endif; ?>
<?php } ?>