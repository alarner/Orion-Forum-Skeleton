<form action="/<?php echo $slug; ?>/register" method="post">
	Email <input type="text" name="email" value="<?php if(!empty($post['email'])) echo $post['email']; ?>" /> <?php if(!empty($errors['email'])) echo $errors['email'][0]; ?><br />
	Password <input type="password" name="password" /> <?php if(!empty($errors['password'])) echo $errors['password'][0]; ?><br />
	Confirm Password <input type="password" name="passwordConfirmation" /> <?php if(!empty($errors['passwordConfirmation'])) echo $errors['passwordConfirmation'][0]; ?><br />
	<?php if(!empty($errors['captcha'])) echo $errors['captcha'][0]; ?><br />
	<?php echo $captcha; ?>
	<input type="submit" value="Register" />
</form>
