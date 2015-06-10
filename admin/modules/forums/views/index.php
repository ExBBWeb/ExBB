<div class="module">
<div class="head">

	<h2><span><?php echo $lang->forums_icons_help; ?></span></h2>
</div> 

	<div class="module-body">

		<p><img src="<?php echo $template->url('images/add.png'); ?>" width="16" height="16" /> - <?php echo $lang->icon_add_help; ?></p>
		<p><img src="<?php echo $template->url('images/edit.png'); ?>" width="16" height="16" /> - <?php echo $lang->icon_edit_help; ?></p>
		<p><img src="<?php echo $template->url('images/delete.png'); ?>" width="16" height="16" /> - <?php echo $lang->icon_delete_help; ?></p>
	</div>
</div>

<div class="right">
	<a href="" class="button">Создать категорию</a>
	<a href="" class="button">Создать форум</a>
</div>

<?php foreach ($data->categories as $category) : ?>

<div class="module">
<div class="head">

	<h2><span><?php echo $category['title']; ?></span></h2>
	<div class="right">
		<a href=""><img class="m-icon" src="<?php echo $template->url('images/add.png'); ?>" /></a>
        <a href=""><img class="m-icon" src="<?php echo $template->url('images/edit.png'); ?>" /></a>
        <a href=""><img class="m-icon" src="<?php echo $template->url('images/delete.png'); ?>" /></a>	
	</div>
</div> 
	
    <div class="module-table-body">

        <table>
        	<thead>
<tr>
    <th style="width:5%">#</th>
    <th style="width:40%"><?php echo $lang->forum_title; ?></th>
    <th style="width:20%"><?php echo $lang->forum_topics; ?></th>
    <th style="width:15%"><?php echo $lang->forum_posts; ?></th>
    <th style="width:20%"></th>
</tr>
            </thead>
            <tbody>
			<?php if (isset($data->forums[$category['id']])) : ?>
		<?php foreach ($data->forums[$category['id']] as $forum) : ?>	
<tr>
    <td class="align-center"><?php echo $forum['id']; ?></td>
    <td><a href=""><?php echo $forum['title']; ?></a></td>
    <td><?php echo $forum['topics']; ?></td>
    <td><?php echo $forum['posts']; ?></td>
    <td>
		<a href=""><img class="m-icon" src="<?php echo $template->url('images/add.png'); ?>"  /></a>
        <a href=""><img class="m-icon" src="<?php echo $template->url('images/edit.png'); ?>" /></a>
        <a href=""><img class="m-icon" src="<?php echo $template->url('images/delete.png'); ?>" /></a>
    </td>
</tr>
	<?php endforeach; ?>
<?php endif; ?>
		</tbody>
	</table>

	<div style="clear: both"></div>
</div>

</div>

<?php endforeach; ?>