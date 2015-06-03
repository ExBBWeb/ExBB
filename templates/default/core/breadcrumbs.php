<?php if (count($breadcrumbs) >= 1) : ?>
<div class="breadcrumbs">
<ul class="breadcrumbs-list">
<?php foreach ($breadcrumbs as $breadcrumb) : ?>
  <li>
	
	<a href="<?php if ($breadcrumb['url']) echo $breadcrumb['url']; else echo '#'; ?>"<?php if ($breadcrumb['active']) echo ' class="current"'; ?>>

	<?php echo $breadcrumb['title']; ?>

	</a>

	</li>
 <?php endforeach; ?>
 </ul>
</div>
<?php endif; ?>