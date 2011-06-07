<form action="/<?php echo $slug; ?>/login" method="post">
	<?php
	if(array_key_exists('default', $errors)) {
		foreach($errors['default'] as $error) {
			echo $error,'<br />';
		}
	}
	?>
	Email <input type="text" name="email" value="<?php if(!empty($post['email'])) echo $post['email']; ?>" /><br />
	Password <input type="password" name="password" /><br />
	<input type="submit" value="Log In" />
</form>
