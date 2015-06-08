<?php foreach ($data->categories as $category) : ?>
<?php if (!empty($data->forums[$category['id']])) : ?>
<div class="panel">

	<div class="header">
		<h1><?php echo $category['title']; ?></h1>
	</div>

	<div class="body">
		<table class="table forums">
			<thead>
				<tr>
					<th class="icon"></th>
					<th class="title"><?php echo $lang->forum_title; ?></th>
					<th class="topics"><?php echo $lang->forum_topics; ?></th>
					<th class="posts"><?php echo $lang->forum_posts; ?></th>
					<th class="update"><?php echo $lang->forum_update; ?></th>
				</tr>
			</thead>
		<?php foreach ($data->forums[$category['id']] as $forum) : ?>
			<tr>
				<td class="icon"><img src="<?php echo $forum['icon']; ?>"></td>
				<td class="title"><a href="<?php echo $url->module('forum', 'index', 'index', $forum['id']); ?>"><?php echo $forum['title']; ?></a></td>
				<td class="topics"><?php echo $forum['topics']; ?></td>
				<td class="posts"><?php echo $forum['posts']; ?></td>
				<td class="update">
				<?php if (!empty($forum['update_date'])) : ?>
					<?php echo $lang->update_date.' '.$forum['update_date']; ?><br>
					<a href="<?php echo $url->module('topic', 'index', 'index', $forum['updated_topic_id']); ?>"><?php echo $forum['topic_title']; ?></a><br>
					<?php echo $lang->update_user.' <a href="'.$url->module('user', 'view', 'index', $forum['author_id']).'">'.$forum['author_login'].'</a>'; ?>
				<?php else : ?>
					<?php echo $lang->no_update_forum; ?>
				<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</table>
	</div>

</div>
<?php endif; ?>
<?php endforeach; ?>