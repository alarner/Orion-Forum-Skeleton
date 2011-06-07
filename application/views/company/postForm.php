<form action="/<?php echo $slug; ?>/post" method="post">
	Topic <input type="text" name="topic" value="<?php if(!empty($post['topic'])) echo $post['topic']; ?>"> <?php if(!empty($errors['topic'])) echo $errors['topic'][0]; ?><br />
	Body <textarea name="body"><?php if(!empty($post['body'])) echo $post['body']; ?></textarea>  <?php if(!empty($errors['body'])) echo $errors['body'][0]; ?><br />
	<?php if(!empty($errors['captcha'])) echo $errors['captcha'][0]; ?><br />
	<?php echo $captcha; ?>
	<input type="submit" value="Post" />
</form>
