<?php foreach ($articles as $article) : ?>
<div class="article">
	<h2><a href="<?php $url->view('article', array('article'=>$article['alias'])); ?>"><?php echo $article['title']; ?></a></h2>
	<div class="description"><?php echo $article['description']; ?><a href="<?php $url->view('article', array('article'=>$article['alias'])); ?>">Читать дальше...</a></div>
	<div class="date"><p><small><?php echo $article['date_added'] ?></small></p></div>
</div>
<?php endforeach; ?>