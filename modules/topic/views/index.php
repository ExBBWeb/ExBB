<?php if (!empty($data->posts)) : ?>
<div class="panel">

	<div class="header">
		<h1><a href="<?php echo $url->module('topic', 'index', 'index', $data->topic->id); ?>"><?php echo $data->topic->title; ?></a></h1>
	</div>

	<div class="body">
		
		<div class="buttons-right">
			<?php if ($data->add_topic_access) : ?>
			<a href="<?php echo $url->module('topic', 'add', 'index', $data->topic->forum_id); ?>" class="btn btn-default"><i class="fa fa-plus"></i> <?php echo $lang->add_topic; ?></a>
			<?php endif; ?>
			
			<?php if ($data->add_poll_access) : ?>
			<a href="<?php echo $url->module('topic', 'add', 'poll', $data->topic->forum_id); ?>" class="btn btn-default"><i class="fa fa-bar-chart"></i> <?php echo $lang->add_poll; ?></a>
			<?php endif; ?>
		</div>
	
		<table class="table posts">
		<?php foreach ($data->posts as $post) : ?>
			<?php $author = $data->authors[$post['author_id']]; ?>
		
			<tr class="head">
				<td class="user"><?php echo $author['login']; ?></td>
				<td class="post"></td>
			</tr>
			<tr>
				<td class="user">
					<p class="avatar"><img src="<?php echo $author['avatar']; ?>"></p>
					<p><?php echo $lang->user_posts; ?> <?php echo $author['posts']; ?></p>
					<p><?php echo $lang->user_register_date; ?> <?php echo $author['register_date']; ?></p>
				</td>
				<td class="post">
				<?php echo $post['text']; ?>
				</td>
			</tr>
			<tr class="footer">
				<td class="user"></td>
				<td class="post"></td>
			</tr>
		<?php endforeach; ?>
		</table>
		
		<?php pagination($data->pages, $url->module('topic', 'index', 'index', $data->topic->id, array('page'=>'[page]')), $data->page); ?>
	</div>

</div>
<?php endif; ?>