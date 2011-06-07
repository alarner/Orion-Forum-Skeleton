<pre><?php print_r($parentPost); ?></pre>
<form action="/<?php echo $slug; ?>/reply" method="post">
	<?php
	if(array_key_exists('default', $errors)) {
		foreach($errors['default'] as $error) {
			echo $error,'<br />';
		}
	}
	?>
	Body <textarea name="body"><?php if(!empty($post['body'])) echo $post['body']; ?></textarea>  <?php if(!empty($errors['body'])) echo $errors['body'][0]; ?><br />
	<input type="hidden" name="parentPostId" value="<?php echo $parentPost['post_id']; ?>" />
	<?php if(!empty($errors['captcha'])) echo $errors['captcha'][0]; ?><br />
	<?php echo $captcha; ?>
	<input type="submit" value="Post" />
</form>

