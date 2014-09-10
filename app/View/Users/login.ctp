<style>
form {
    margin: 15px auto;
}
</style>

<div class='large-3 large-centered columns'>
<?php
echo $this->Form->create();
echo $this->Form->input('username');
echo $this->Form->input('password');
echo $this->Form->end(array('label'=>'Login', 'class'=>'button'));
?>
</div>
