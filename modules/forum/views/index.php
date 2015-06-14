<?php if (count($data->subforums) > 0) : ?>
<div class="panel">

	<div class="header">
		<h1><?php echo $lang->subforums; ?></h1>
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
		<?php foreach ($data->subforums as $forum) : ?>
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

<div class="panel">

	<div class="header">
		<h1><a href="<?php echo $url->module('forum', 'index', 'index', $data->forum->id); ?>"><?php echo $data->forum->title; ?></a></h1>
	</div>
	<div class="body">
	
		<div class="buttons-right">
			<?php if ($data->add_topic_access) : ?>
			<a href="<?php echo $url->module('topic', 'add', 'index', $data->forum->id); ?>" class="btn btn-default"><i class="fa fa-plus"></i> <?php echo $lang->add_topic; ?></a>
			<?php endif; ?>
			
			<?php if ($data->add_poll_access) : ?>
			<a href="<?php echo $url->module('topic', 'add', 'poll', $data->forum->id); ?>" class="btn btn-default"><i class="fa fa-bar-chart"></i> <?php echo $lang->add_poll; ?></a>
			<?php endif; ?>
		</div>
	
<?php if (!empty($data->topics)) : ?>
		<table class="table topics">
			<thead>
				<tr>
					<th class="icon"></th>
					<th class="title"><?php echo $lang->topic_title; ?></th>
					<th class="author"><?php echo $lang->topic_author; ?></th>
					<th class="posts"><?php echo $lang->topic_posts; ?></th>
					<th class="views"><?php echo $lang->topic_views; ?></th>
					<th class="update"><?php echo $lang->topic_update; ?></th>
				</tr>
			</thead>
		<?php foreach ($data->topics as $topic) : ?>
			<tr>
				<td class="icon"><img src="<?php echo $topic['icon']; ?>"></td>
				<td class="title"><a href="<?php echo $url->module('topic', 'index', 'index', $topic['id']); ?>"><?php echo $topic['title']; ?></a>
				<?php if (!empty($topic['description'])) echo '<br>&gt;&gt; '.$topic['description']; ?>
				</td>
				<td class="author"><a href="<?php echo $url->module('user', 'view', 'index', $topic['author_id']); ?>"><?php echo $topic['author_login']; ?></a></td>
				<td class="posts"><?php echo $topic['posts']; ?></td>
				<td class="views"><?php echo $topic['views']; ?></td>
				<td class="update">
				<?php if (!empty($topic['updated_date'])) : ?>
					<?php echo $lang->update_date.' '.$topic['updated_date']; ?><br>
					<?php echo $lang->update_user.' <a href="'.$url->module('user', 'view', 'index', $topic['post_author_id']).'">'.$topic['post_author_login'].'</a>'; ?>
				<?php else : ?>
					<?php echo $lang->no_update_forum; ?>
				<?php endif; ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</table>
		
		<?php pagination($data->pages, $url->module('forum', 'index', 'index', $data->forum->id, array('page'=>'[page]')), $data->page); ?>
		<?php endif; ?>
	</div>

</div>
